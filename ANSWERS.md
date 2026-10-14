# Written answers — Q2, Q3, Q7, Q8, Q9, Q10

---

## Q2 — An editor cannot upload images, but a colleague can

A colleague succeeding points at something **specific to this user or this
file**, not a site-wide failure. Likeliest causes, in the order I would check:

1. **Capability / role.** The user's role lacks `upload_files` (Subscriber,
   Contributor without the cap), or a role-management plugin altered the role.
   *Check:* Users → their profile (role), and `current_user_can('upload_files')`;
   compare with the colleague's role. *Evidence:* different roles between the two
   users. *Fix:* correct the role / grant the capability.
2. **The file itself.** Exceeds `upload_max_filesize` / `post_max_size`, or a
   blocked MIME type (SVG/HEIC, or an `upload_mimes` filter), or a filename/size
   policy. *Check:* the exact error message, then retry with a small JPEG.
   *Evidence:* a small JPG succeeds where the original fails; PHP error message
   names the limit. *Fix:* raise the limits or allow the type.
3. **Server / security layer.** `wp-content/uploads` not writable by the web
   user, disk/quota full, or a WAF/security plugin (ModSecurity) blocking the
   request; on multisite, an image quota. *Check:* hosting error log, folder
   permissions, disk usage. *Evidence:* a 403/500 in the log or a full disk.
   *Fix:* fix permissions/quota, allowlist the request.

I check capability first because it is the cheapest, the most common cause, and
the only one that explains "works for a colleague but not for this user on the
same file."

---

## Q3 — A WordPress article page is slow, especially on mobile

Three improvements, and what I would measure first for each:

1. **Full-page caching + CDN at the edge.** *Measure first:* TTFB / server
   response time (is it origin, or network?).
2. **Image optimisation** — WebP/AVIF, responsive `srcset`/`sizes`, correct
   `width`/`height`, lazy-load below the fold. *Measure first:* LCP and total
   image bytes on a mobile profile; find the LCP element.
3. **Trim render-blocking work** — defer non-critical JS, reduce third parties
   (ads/analytics), add object caching for expensive queries. *Measure first:*
   main-thread/blocking time and the number of requests (TBT/INP).

**Chosen: image optimisation** (usually the biggest, lowest-risk win for a
content site). How I would check it helped **without breaking ads, analytics, or
signed-in behaviour:**

- Compare **field data** (RUM / CrUX) LCP and image bytes before vs after, not
  just a one-off Lighthouse run; segment mobile.
- For ads: verify ad viewability/impression metrics in the ad platform are
  unchanged, and that lazy-loading is **not** applied to the above-the-fold image
  (which would delay LCP *and* ad load).
- For analytics: confirm pageview/event counts are unchanged and the tag still
  fires on the optimised markup.
- For signed-in behaviour: ensure signed-in requests bypass shared caches (cache
  varies by cookie / exclude logged-in users) so personalised pages are never
  served a cached anonymous copy.

---

## Q7 — Some readers see an older version of a corrected article after release

**First three checks:**

1. **Is the document cached?** Fetch the URL from the edge and from the origin
   (`curl` both, look for `Age`/`X-Cache` headers); check the CDN + WordPress
   page cache + object cache, and the browser/service worker. Purge and re-test.
2. **Is it a deploy/asset problem?** Confirm the running theme/plugin version
   matches the release (git commit vs what the server reports); check opcache and
   any block/template cache.
3. **Is it content, not code?** Check the post's revisions and
   `post_modified` — did the correction actually save and publish, or is the DB
   still holding the old body (or a meta/cache of it)?

**Caching vs code vs content:**

- **Caching** → different users/regions see different versions, a purge changes
  the result, and an `Age`/`X-Cache` header is present.
- **Code** → *everyone* is affected consistently and a purge does **not** change
  it.
- **Content** → the database itself holds the old value (visible in revisions).

**When I would roll back:** if a purge does not fix it and readers are broadly
affected, or the release produces errors — roll back to the last good release
immediately, then diagnose. **Verify recovery:** the URL returns the corrected
content from multiple locations/devices after caches are cleared, and the error
rate is back to baseline.

**Editor update (two sentences):** *"We found the cause and it's fixed — readers
briefly saw an older copy while caches refreshed, so some of you may need a hard
refresh to see the correction. The article is now live and consistent for
everyone; thank you for flagging it."*

**Assumption:** there is a CDN/page cache in front of the site; I have not yet
confirmed which layer is stale, so the first check is to localise that.

---

## Q8 — Reduce editors' copy-paste across tools

**What I would ask or observe first:** which tools, in what order, what exact
format each one needs, how often this happens, and where mistakes actually
occur. Then watch one editor do it end-to-end (don't guess).

**Smallest useful improvement:** a **"Share kit" copy control** on the editor
screen — or, even lighter, a bookmarklet — that copies a ready-to-paste bundle
(title, permalink, image URL/credit) in the format the tools expect, so the
editor pastes rather than re-types. **Simpler alternative that needs little or no
new code:** a shared template of the standard fields and a bookmarklet that
copies the current tab's title + URL; editors fill a fixed doc instead of free
text.

**Why this one:** it removes transcription errors at the source and saves
keystrokes, but stays a *copy* action — the editor remains the decision-maker.

**Under editorial control:** what gets shared, what image is used, the wording,
and when. The tool only formats; it never posts.

**Test with one editor:** observe one real publish, time it, and count mistakes
before vs after.

**Success looks like:** less time per publish, zero transcription errors, no new
support tickets, measured over a week.

**Similar improvement (≤100 words):** At MerisLabs I built an engine that parsed a
Tier-1 Nigerian bank's litigation portfolio into structured data and generated the
audit report in code, replacing a manual drafting process. It surfaced liability
patterns across the portfolio that the manual review had missed. The outcome was a
faster, more accurate audit — and a repeatable method, not a one-off. I learned that
the win came from making the output rigid and reproducible: encoding the exact
structure the decision needed, rather than clever analysis. My contribution was the
data model and the generation pipeline.

---

## Q9 — Newsletter signup under timeout / rate limit / invalid email

**Approach:** the client generates an **idempotency key** per signup attempt and
sends it with the request. Every retry reuses the **same key**, and the service
deduplicates on that key — so a timeout after the service already accepted the
request cannot create a second record.

```js
// One small function + notes.
async function subscribe(email, idempotencyKey, attempt = 0) {
  if (!isValidEmail(email)) {
    return { status: 'invalid' };          // non-retryable; surface to the user
  }
  try {
    const res = await fetch('/api/subscribe', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Idempotency-Key': idempotencyKey },
      body: JSON.stringify({ email }),
    });

    if (res.status === 429) {                 // rate limited
      const wait = Number(res.headers.get('Retry-After') ?? 1) * 1000;
      return retry(email, idempotencyKey, attempt, wait);
    }
    if (res.status >= 400 && res.status < 500 && res.status !== 408) {
      return { status: 'rejected', code: res.status };  // validation/authz — do not retry
    }
    return await res.json();                  // 2xx (or 200 with the original result on replay)
  } catch (_timeout) {
    // Timeout / network: the service MAY have accepted it. Retry with the SAME
    // key so a duplicate cannot be created; reconcile via a status lookup if one exists.
    return retry(email, idempotencyKey, attempt);
  }
}

function retry(email, key, attempt, waitMs) {
  if (attempt >= 5) return { status: 'pending_reconcile', key };  // queue for a status check
  const delay = (waitMs ?? Math.min(8000, 2 ** attempt * 500)) + Math.random() * 250; // backoff + jitter
  return new Promise((r) => setTimeout(() => r(subscribe(email, key, attempt + 1)), delay));
}
```

- **Store:** idempotency key, hashed email (or token), status, attempt count,
  timestamps, and the last response code — enough to reconcile and answer "did it
  accept?".
- **Avoid logging:** the plaintext email/PII in general logs, request bodies, and
  any secret/API keys; log the key and a status, not the payload.
- **Test a retry after acceptance:** mock the service to accept, then simulate a
  timeout on the response; assert exactly **one** record exists, and that
  replaying the same key returns the original result rather than creating a
  second.

---

## Q10 — Reviewing a "safe because only editors see the button" endpoint

**The claim is wrong.** UI visibility is not authorisation. Anyone can craft the
request with `curl` — with a valid editor session, a leaked one, or if the
endpoint lacks auth entirely — regardless of whether a button is visible. A
`POST` that changes an article's status must be authorised server-side.

**Server-side checks required (in order):**

1. **Authentication** — a valid session (or application password / nonce for
   REST).
2. **Authorisation** — `current_user_can('edit_post', $article_id)`: the user must
   be able to edit **that** post (not merely be logged in).
3. **CSRF / nonce** — verify the nonce (REST: `permission_callback` + `X-WP-Nonce`)
   so another site cannot trigger it.
4. **Input validation** — the ID is a positive integer and the post exists; the
   new status is in the **whitelist** of allowed values.
5. **Sanitisation** of any other input.
6. **Apply + audit** — perform the update and log who changed what.

```text
handler(request):
  if not authenticated(request):                 reject 401
  if not verify_nonce(request):                   reject 403
  id     = int(request.id)
  status = request.status
  if id <= 0 or not post_exists(id):              reject 400
  if status not in ['draft','pending','publish']: reject 422
  if not current_user_can('edit_post', id):       reject 403
  update_post(id, { status })
  audit_log(user, id, status)
  return 200
```

**Two tests that must be rejected:** (1) an **unauthenticated** request (no/!invalid
session) changing a status; (2) an **authenticated low-privilege or wrong-user**
request — a subscriber, or an editor trying to change a post they do **not** have
`edit_post` on — or a request with a status value not in the whitelist.

**How I would raise it:** privately and without blame — show the reproduction (a
`curl` that flips a status), state the impact (arbitrary status change / potential
publication of unpublished content), and propose the fix with the
`permission_callback`/capability check. I would treat it as a **release blocker**
and, if needed, add the capability check immediately before anything else ships.
