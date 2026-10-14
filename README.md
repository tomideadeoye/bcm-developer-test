# BCM Software Developer Test

Submission for the **Big Cabal Media** Software Developer screening assessment
(`talent@bigcabal.com`). Ten questions; code plus short answers.

- **Deadline:** 5pm, Friday 16 October 2026.
- **Budget:** up to 4 hours excluding setup.
- **Submission:** this private repository.
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
# Q4 — open q4-layout/index.html in a browser (or render screenshots with Edge headless)
open q4-layout/index.html

# Q5 — validate the corrected HTML
bunx html-validate q5-html/corrected.html

# Q6 — behaviour + tests
open q6-js/app.html
bun test q6-js

# Q1 — no PHP runtime available locally; see q1-php/NOTES.md for the reasoning
#      and the corrected code (PHP 8 / WordPress theme)
```

## Evidence

Each part records how it was checked:

- **Q4** — `q4-layout/screenshots/` (desktop + mobile, captured headless) incl. a long heading.
- **Q5** — `q5-html/validation.txt` (validator output) with errors separated from improvements.
- **Q6** — `q6-js/EVIDENCE.md` + `bun test` output (repeated clicks, toggling before/after adding paragraphs).
- **Q1** — `q1-php/NOTES.md` (each error, its effect, the corrected code, and the checks I would run).

## AI disclosure

AI (an assistant model) was used to help draft and review wording and to sanity-check
approaches. Every piece of code in this repository was read, reasoned about, and
verified locally by me before submission:

- Q4/Q5 — rendered in a real browser and run through an HTML validator.
- Q6 — covered by executable tests (`bun test`), including the edge cases the brief calls out.
- Q1 — errors traced by hand against the PHP 8 / WordPress behaviour; no PHP runtime was
  available in this environment, which is stated as an assumption in `q1-php/NOTES.md`.

## Assumptions

Stated per question in that question's `README.md` / `NOTES.md`.
