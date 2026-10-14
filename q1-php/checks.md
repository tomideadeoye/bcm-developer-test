# Q1 — checks I would run

These are the four scenarios the brief asks for. They were **executed with PHP
8.5** via `q1-php/verify.php` — a WordPress-free harness that mirrors
`corrected.php` — together with `php -l` on both PHP files. Output is captured in
`checks-output.txt` (**14/14 pass**).

| Scenario | Input | Expected |
|----------|-------|----------|
| **Missing parameters** | `?` (no `page`, no `s`) | `$page = 1`, `$search = ''`, no PHP warnings; shows "Page 1 of N"; all posts (no search filter) |
| **Page 2** | `?page=2` | Second set of ten posts; heading "Page 2 of N"; pagination highlights page 2; posts from page 1 do **not** repeat |
| **No search results** | `?s=zzzznomatch` | Empty state: "No results found for “zzzznomatch”."; no `<h2>`s; no warnings |
| **Multiple `[ad]` markers** | a post containing `[ad] [ad-2] [ad]` | three `<div class="ad ad-…">` placeholders, in position, one per marker; none dropped; the rest of the content intact |

Additional checks:

- **Page out of range** (`?page=999`) → clamps to the last page, heading does not
  say "Page 999".
- **Marker false-positive** → a post containing the word "ad" or "add" is
  **unchanged** (proves the `#\[ad\]#` escaping).
- **XSS** → a post with `<script>` in the body is neutralised by `wp_kses_post`.

## A minimal harness

`verify.php` is the executable harness (no WordPress needed) and produced the
results above. `checks.php` additionally sketches the same assertions against the
real WordPress test framework (`WP_UnitTestCase`) for a full install; it is
included for completeness.
