# Q6 — evidence of checks

Behaviour is implemented in `script.js` and covered by executable tests in
`script.test.mjs` (run with `bun test`). DOM tests use `happy-dom`.

```
$ bun test q6-js
7 pass, 0 fail, 24 expect() calls
```

## What is checked

| Check | How |
|-------|-----|
| Separator pattern `P1, P2, HR, P3, P4, HR, P5` | `separatorIndices(n)` unit test: 3→`[1]`, 5→`[1,3]`, 9→`[1,3,5,7]`, and **no trailing HR** when nothing follows (2→`[]`) |
| Applied **on initial load** | After `init`, 3 paragraphs → exactly one `hr.sep` |
| **Repeated clicks** | Two clicks → 6 then 9 paragraphs, separators re-derived to 2 then 4 |
| **Copies land after the last paragraph** | Two clicks in, the last two children of `<article>` are still the two buttons — so the copies are inserted *after the last paragraph*, not merely at the end of the container |
| **Second button created by JS** | Only the original button exists in the HTML; after `init` there are 2 buttons |
| **Toggling before/after adding paragraphs** | Toggle hides existing image paragraph(s); then clicking add produces new image paragraphs that are **also hidden** (state applied to every image paragraph, not just existing ones); toggling back shows all |
| **Hidden paragraphs are still counted** | With images hidden, 6 paragraphs → still 2 separators |

## Design notes

- `separatorIndices` is pure and exported, so the trickiest rule (where the
  separators go, and not after the final paragraph) is tested directly without a
  DOM.
- Image visibility is applied by a single `applyImageVisibility()` that walks
  *all* paragraphs on every change, which is what guarantees newly added image
  paragraphs inherit the current toggle state.
- Hidden paragraphs use the `hidden` attribute (removes them from the
  accessibility tree) but remain in the query used for counting separators, as
  the brief requires.
- `shuffle` accepts an injectable RNG so it can be tested deterministically.
- `app.html` is the **Q5 corrected markup, kept verbatim** — the same three
  paragraphs, with `test.jpeg` copied into this folder so `q6-js/` works on its
  own. The behaviour runs against the corrected HTML, not against a
  re-typed approximation of it.
- Copies are inserted with `lastParagraph.after(...clones)` rather than appended
  to the container, so they really do follow the last paragraph and the two
  buttons stay below the content they add.

## Manual check

Open `q6-js/app.html` and: click "Hit me" a few times (paragraphs multiply in
random order, separators stay consistent), click "Toggle image paragraphs",
then click "Hit me" again (new image paragraphs stay hidden).
