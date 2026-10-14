# Q1 — checks I would run

These are the four scenarios the brief asks for. They run against the corrected
code inside a WordPress install (or with the WordPress test suite). No PHP
runtime was available where this was prepared, so the **expected** result is
documented rather than captured; I would run these first thing with a PHP 8
shell and paste the output here.

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

`checks.php` (below) sketches the assertions; it needs the WordPress test
bootstrap (`WP_UnitTestCase`) to seed posts and run the query. It is included for
completeness and is not executed in this environment.
