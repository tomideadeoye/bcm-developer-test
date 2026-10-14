# Q1 — errors, effects, corrections

**Environment assumption:** PHP 8, inside a WordPress theme template. Verified
with PHP 8.5: both files pass `php -l`, and the corrected logic passes **14/14**
checks in `verify.php` (output in `checks-output.txt`). WordPress-specific parts
(the `WP_Query` loop and template tags) are reasoned against core behaviour and
sketched as `checks.php` for a full install.

## Errors and their effects

1. **`'page' => $page` does not paginate.**
   `WP_Query` paginates with the **`paged`** argument. `page` is for the
   sub-pages of a single static page. **Effect:** the query always returns the
   first ten posts; `?page=2` is ignored.

2. **`$_GET['s']` is read without a guard.**
   On a request with no `s` parameter, PHP 8 emits `Undefined array key "s"`, and
   an empty `s` still feeds the query. **Effect:** a warning in logs (or on
   screen in a dev setup) and an unintended search filter. `$_GET['page']` is
   guarded with `??`, `s` is not — inconsistent.

3. **`$pages` in the `<h1>` is undefined.**
   It is never computed. **Effect:** `Undefined variable $pages` warning and an
   empty "of" in the heading. The correct value is `$q->max_num_pages`.

4. **The `preg_replace_callback` callback is broken three ways.**
   - It declares **no `$m` parameter**, so `$m[1]` is undefined → PHP 8 error and
     an empty slot class.
   - It **`echo`s** the div instead of **returning** it. `preg_replace_callback`
     uses the callback's *return value*, so the replacement is `null` → the
     `[ad]` marker is **removed and replaced with nothing**, and the echoed div
     is emitted out of order (before the paragraph it belongs to).
   - `$m[1]` implies a capture group that the pattern does not have.
   **Effect:** no ad placeholder is rendered in the right place.

5. **The regex `#[ad]#` is a character class.**
   `[ad]` matches a single `a` or `d` character, not the literal four-character
   marker. **Effect:** every `a` and `d` in the article would be replaced — a
   catastrophic, content-mangling bug. The literal must be escaped: `#\[ad\]#`.

6. **`esc_html(the_title())` double-prints and escapes nothing.**
   `the_title()` **echoes** and returns `null`; `esc_html(null)` is `''`.
   **Effect:** the title is printed (unescaped, by `the_title()`) *and* an empty
   `<h2>` body is produced. Should be `esc_html(get_the_title())`.

7. **`echo $content` outputs unfiltered, unescaped post content.**
   `get_the_content()` does not run the `the_content` filters and the output is
   not sanitised. **Effect:** shortcodes/blocks may not render, and it is an XSS
   surface if the content is not already trusted. Use
   `apply_filters('the_content', get_the_content())` and `wp_kses_post()`.

8. **No `wp_reset_postdata()`.** After a custom loop you must restore the global
   `$post`, or anything after the loop uses the last post's context.

9. **No empty-state handling.** With no results the page shows a heading and
   nothing else — no "no results" message.

10. **Missing output escaping in the heading.** `$page`/`$pages` are ints here
    (fine once cast), but the pattern of printing raw values is unsafe.

## Assumptions about the ad placeholders

- A marker is the **literal string `[ad]`**. I also support an optional numeric
  slot — `[ad-2]` — because the original author wrote `$m[1]`, implying a
  captured variant. If the real markers are only ever `[ad]`, the capture group
  is simply unused and the output is `<div class="ad ad-0"></div>`.
- The div is a **placeholder** (empty, with a class) for a client-side
  advertising script to fill — so no ad HTML is emitted server-side.
- Markers can appear **multiple times** in one post, and inside filtered content
  (after `the_content` has run).

## What I changed

See `corrected.php`. In short: `paged`; guarded, sanitised input; computed and
clamped `$total_pages`; a correct, returning callback with an escaped literal
regex; `get_the_title()`; `apply_filters('the_content', …)` + `wp_kses_post()`;
empty-state handling; pagination links; `wp_reset_postdata()`.
