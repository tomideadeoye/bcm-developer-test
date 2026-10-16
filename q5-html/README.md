# Q5 — HTML corrections

Files: `original-as-given.html` (the markup from the brief, reproduced verbatim),
`corrected.html` (the fix), `validation.txt` + `nu-*.json` (the evidence),
`test.jpeg` (the local image).

## Actual HTML5 validity errors (must-fix)

1. **No doctype.** The document starts at `<html>`. Without `<!doctype html>`
   the browser falls into **quirks mode**, changing the box model and many CSS
   behaviours. (Nu: *"Start tag seen without seeing a doctype first."*)
2. **`<body>` is outside `<html>`.** `<html>…</html>` is closed before `<body>`,
   so the body is a sibling of the root element. The parser silently relocates
   it, but the source is invalid and **everything after it is implicitly
   closed** — which is why the cascade below appears in the validator output.
   (Nu: *"Stray start tag body."*)
3. **Implicitly-closed elements.** Once `<body>` is stray, the parser never
   reaches a real end for `<body>` or the last `<p>`: *"Element `<body>`/`<p>` is
   implicitly closed by document ending."* Same root cause as (2), different
   symptom — worth naming separately because it is what makes the failure mode
   confusing.
4. **`<abbr name="lobortis">`.** `abbr` has no `name` attribute; the expansion
   belongs in `title`. As written the expansion is never exposed.
5. **`<img src=test.jpeg with=300 height=auto>`.** `with` is not an attribute
   (typo for `width`), and `height="auto"` is not a valid dimension — width and
   height must be non-negative integers. The image also has no text alternative.
6. **Mis-nested `<code><b>…</code></b>`.** The `<b>` opened inside `<code>` is
   closed **after** `</code>`, so the tags cross. Browsers repair it, but it is
   invalid and the styling/`code` semantics are ambiguous.
7. **Missing `<meta charset="utf-8">` and `<html lang>`** — a character-encoding
   declaration and a document language are required for correct parsing and
   rendering. (html-validate: `element-required-attributes` on `<html>`.)

## Accessibility / style improvements (not validity errors)

- **`alt` text on the image.** Listed here because its *value* is an
  accessibility decision — but note the attribute itself is required (see the
  validator note below), so this is really a fix that covers both.
- Meaningful link text — the anchor currently contains a fragment of a sentence.
- `type="button"` on the button (`no-implicit-button-type`: the default is
  `submit`, which is a *recommendation*, not a conformance rule).
- Numeric `width`/`height` on the image that **match the file's real
  dimensions**. `test.jpeg` is 212×300; the original `with=300 height=auto`
  gives no aspect ratio at all, and getting the numbers wrong (e.g. 300×200 for
  this portrait image) is worse than omitting them — it reserves the wrong box
  and stretches the image. Correct numbers are what actually prevents layout
  shift.
- `<main>` / `<article>` landmarks to expose the document structure.

## Three HTML-only improvements for discoverability / interpretation

1. **`<html lang="en">` and `<meta charset="utf-8">`** — search engines and
   screen readers need the declared language; the charset guarantees the text is
   decoded as intended.
2. **`<meta name="description" content="…">`** — the snippet search engines may
   show in results; improves click-through and summarisation.
3. **`alt` on the image and `title` on the `<abbr>`** — the image becomes
   describable (and indexable for image search) and the abbreviation is
   expandable for assistive tech.

## Where the validator is stricter than the spec, and vice versa

The brief asks to separate validity errors from improvements, so it is worth
saying where the two tools disagree with that split, rather than letting the
tools' output contradict this file:

- **Unquoted attribute values** (`href=https://…`, `src=test.jpeg`,
  `with=300`, `height=auto`). Valid HTML5 — the parser accepts them — but
  html-validate's default profile reports `attr-quotes` errors. So the "before"
  run has more red than the strict spec requires. I quoted them anyway.
- **The space in `</a >`** is *not* an error in either tool, as stated above.
- **Missing `alt`** is reported by html-validate under its WCAG rule
  (`wcag/h37`) — an accessibility rule, not a parsing rule — while the HTML spec
  requires a text alternative for an image in the general case. It is both.
- **Missing `type` on `<button>`** is html-validate's
  `no-implicit-button-type`, phrased as *"missing recommended"*: a lint
  recommendation.
- **The W3C Nu validator stops early on the given markup** ("Cannot recover
  after last error") and reports only 3 errors, so it cannot enumerate the full
  list. html-validate recovers and reports all 15. Both agree on the corrected
  file: **0 errors**.

## Evidence

`validation.txt` — both checkers, run against `original-as-given.html` (before)
and `corrected.html` (after); raw Nu responses in `nu-original-as-given.json`
and `nu-corrected.json`. `test.jpeg` is a local image, as requested.
