# Q5 — HTML corrections

## Actual HTML5 validity errors (must-fix)

1. **No doctype.** The document starts at `<html>`. Without `<!doctype html>`
   the browser falls into **quirks mode**, changing the box model and many CSS
   behaviours.
2. **`<body>` is outside `<html>`.** `<html>…</html>` is closed before `<body>`,
   so the body is a sibling of the root element. The parser silently relocates
   it, but the source is invalid and the intent is unclear.
3. **`<abbr name="lobortis">`.** `abbr` has no `name` attribute; the expansion
   belongs in `title`. As written the expansion is never exposed.
4. **`<img src=test.jpeg with=300 height=auto>`.** `with` is not an attribute
   (typo for `width`), and `height="auto"` is not a valid dimension — width and
   height must be non-negative integers. The image also has no `alt`.
5. **Mis-nested `<code><b>…</code></b>`.** The `<b>` opened inside `<code>` is
   closed **after** `</code>`, so the tags cross. Browsers repair it, but it is
   invalid and the styling/`code` semantics are ambiguous.
6. Missing `<meta charset="utf-8">` and `<html lang>` — a character-encoding
   declaration and a document language are required for correct parsing and
   rendering.

_(The unquoted `href=https://www.bigcabal.com` and the space in `</a >` are
**not** validity errors under HTML5's parsing rules — I quoted them anyway for
consistency, but they are style, not violations.)_

## Accessibility / style improvements (not validity errors)

- `alt` text on the image.
- Meaningful link text — the anchor currently contains a fragment of a sentence.
- `type="button"` on the button (default is `submit`).
- Numeric `width`/`height` on the image (reserves space, avoids layout shift).
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

## Evidence

`validation.txt` — output of `bunx html-validate corrected.html` (0 errors after
the fixes). `test.jpeg` is a local image, as requested.
