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
# Q4 — open q4-layout/index.html in a browser (or render screenshots with Edge headless)
open q4-layout/index.html

# Q5 — validate the corrected HTML
bunx html-validate q5-html/corrected.html

# Q6 — behaviour + tests
open q6-js/app.html
bun test q6-js

# Q1 — run the checks (needs PHP 8+)
php q1-php/verify.php
php -l q1-php/corrected.php
```

## Evidence

Each part records how it was checked:

- **Q4** — `q4-layout/screenshots/` (desktop + mobile, captured headless) incl. a long heading.
- **Q5** — `q5-html/validation.txt` (validator output) with errors separated from improvements.
- **Q6** — `q6-js/EVIDENCE.md` + `bun test` output (repeated clicks, toggling before/after adding paragraphs).
- **Q1** — `q1-php/checks-output.txt` (`php -l` + 14/14 executable checks) and `q1-php/NOTES.md`.

## AI disclosure

AI (an assistant model) was used to help draft and review wording and to sanity-check
approaches. Every piece of code in this repository was read, reasoned about, and
verified locally by me before submission:

- Q4/Q5 — rendered in a real browser and run through an HTML validator.
- Q6 — covered by executable tests (`bun test`), including the edge cases the brief calls out.
- Q1 — both files pass `php -l`; the corrected logic is covered by 14 executable
  checks (`q1-php/verify.php`, output in `checks-output.txt`). WordPress-specific
  behaviour is reasoned against core and sketched in `checks.php`.

## Assumptions

Stated per question in that question's `README.md` / `NOTES.md`.
