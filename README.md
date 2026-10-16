# BCM Software Developer Test

Submission for the **Big Cabal Media** Software Developer screening assessment
(`talent@bigcabal.com`). Ten questions; code plus short answers.

- **Deadline:** 5pm, Friday 16 October 2026.
- **Budget:** up to 4 hours excluding setup.
- **Submission:** this repository, plus a ZIP copy of the same contents.
- **AI disclosure:** see below.

## Structure

| Folder | Question(s) |
|--------|-------------|
| `q1-php/` | Q1 — debug a WordPress `WP_Query` pagination/search/ad snippet |
| `q4-layout/` | Q4 — desktop + mobile layout in one HTML + one CSS file |
| `q5-html/` | Q5 — HTML validity + accessibility + discoverability corrections |
| `q6-js/` | Q6 — DOM behaviour built on the Q5 markup (with tests) |
| `ANSWERS.md` | Q2, Q3, Q7–Q10 written answers |

## How to run / verify

```bash
# Q1 — needs PHP 8+
php -l q1-php/corrected.php
php q1-php/verify.php          # 17/17 checks (full output in q1-php/checks-output.txt)

# Q4 — open in a browser, or re-render the screenshots with Edge headless
open q4-layout/index.html

# Q5 — validate the corrected markup AND the markup as given
bunx html-validate q5-html/corrected.html
bunx html-validate q5-html/original-as-given.html

# Q6 — behaviour + tests
open q6-js/app.html
bun test q6-js                 # 7 pass (DOM tests use happy-dom)
```

## Evidence

Each part records how it was checked — including the *before* state, where there
is one:

- **Q1** — `q1-php/checks-output.txt` (`php -l` on all four PHP files + **17/17**
  executable checks) and `q1-php/NOTES.md`. The out-of-range check runs the real
  order of operations (query, then clamp-and-re-run), so it reproduces the
  failure it guards against instead of assuming it away.
- **Q4** — `q4-layout/screenshots/` (desktop at 1100px, mobile at 420px,
  captured headless), including a long heading at both sizes.
- **Q5** — `q5-html/validation.txt`: two independent checkers (html-validate and
  the W3C Nu validator), each run against `original-as-given.html` (**15
  errors**) and `corrected.html` (**0**), with the raw Nu responses in
  `nu-*.json`. Validity errors are separated from accessibility/style
  improvements in `q5-html/README.md`, including where the tools and the spec
  disagree about which is which.
- **Q6** — `q6-js/EVIDENCE.md` + `bun test q6-js` (7 pass): repeated clicks,
  toggling before and after adding paragraphs, insertion position, and hidden
  paragraphs still being counted.

## AI disclosure

AI was used in two ways: drafting and reviewing the prose, and reviewing the
finished submission against the brief. That review is where the before/after
validator runs (Q5), the clamp-and-re-query for an out-of-range page (Q1), the
`AbortSignal.timeout` (Q9) and the two-part editor update (Q7) came from. I read
each change, agreed with it, and re-ran the checks myself.

Every code claim here is backed by a check that can be re-run:

- Q4/Q5 — rendered in a real browser; validated by html-validate **and** the W3C
  Nu validator, before and after.
- Q6 — covered by executable tests (`bun test q6-js`), including the edge cases
  the brief calls out.
- Q1 — all four PHP files pass `php -l`; the corrected logic is covered by 17
  executable checks (`q1-php/verify.php`, output in `checks-output.txt`).
  WordPress-specific behaviour is reasoned against core and sketched in
  `checks.php` — labelled as reasoned, not run, because there is no WordPress
  install here.

## Assumptions

Stated per question in that question's `README.md` / `NOTES.md`.
