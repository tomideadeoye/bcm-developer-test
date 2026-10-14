<?php
declare(strict_types=1);

/**
 * Q1 — runnable checks.
 *
 * No WordPress or database required: this harness mirrors the *corrected*
 * logic (input handling, `paged` semantics, and the ad-marker replacement) so
 * the four scenarios in the brief can be executed and eyeballed directly.
 *
 *   php q1-php/verify.php
 */

$PASS = 0;
$FAIL = 0;

function check(string $label, mixed $actual, mixed $expected): void
{
    global $PASS, $FAIL;
    if ($actual === $expected) {
        $PASS++;
        echo "PASS  {$label}\n";
    } else {
        $FAIL++;
        echo "FAIL  {$label}\n";
        echo '      expected: ' . var_export($expected, true) . "\n";
        echo '      actual:   ' . var_export($actual, true) . "\n";
    }
}

/* ---------- mirrors corrected.php ---------- */

function read_page(array $get): int
{
    return isset($get['page']) ? max(1, (int) $get['page']) : 1;
}

function read_search(array $get): string
{
    return isset($get['s']) ? trim((string) $get['s']) : ''; // sanitize_text_field shim
}

function render_ads(string $content): string
{
    return preg_replace_callback(
        '#\[ad(?:-(\d+))?\]#',
        static function (array $m): string {
            $slot = (isset($m[1]) && $m[1] !== '') ? $m[1] : '0';
            return '<div class="ad ad-' . htmlspecialchars($slot, ENT_QUOTES) . '"></div>';
        },
        $content
    );
}

/** Fake WP_Query: pagination on `paged`, search on `s`. */
function fake_query(array $posts, int $perPage, int $paged, string $s): array
{
    $filtered = $s === ''
        ? $posts
        : array_values(array_filter($posts, static fn (string $p): bool => stripos($p, $s) !== false));

    $totalPages = max(1, (int) ceil(count($filtered) / $perPage));
    $paged = min($paged, $totalPages);

    return [
        'posts'         => array_slice($filtered, ($paged - 1) * $perPage, $perPage),
        'max_num_pages' => $totalPages,
        'paged'         => $paged,
    ];
}

$POSTS = array_map(static fn (int $i): string => "Post {$i} about widgets", range(1, 25));

echo "== 1. Missing parameters ==\n";
$get = [];
check('page defaults to 1', read_page($get), 1);
check('search defaults to ""', read_search($get), '');
$r = fake_query($POSTS, 10, read_page($get), read_search($get));
check('returns 10 posts', count($r['posts']), 10);
check('starts at post 1', $r['posts'][0], 'Post 1 about widgets');

echo "\n== 2. Page 2 ==\n";
$get = ['page' => '2'];
$r = fake_query($POSTS, 10, read_page($get), read_search($get));
check('returns 10 posts', count($r['posts']), 10);
check('starts at post 11 (no repeat of page 1)', $r['posts'][0], 'Post 11 about widgets');
check('max_num_pages = 3', $r['max_num_pages'], 3);

echo "\n== 2b. Page out of range ==\n";
$get = ['page' => '999'];
$r = fake_query($POSTS, 10, read_page($get), read_search($get));
check('clamps to the last page', $r['paged'], 3);

echo "\n== 3. No search results ==\n";
$get = ['s' => 'zzzznomatch'];
$r = fake_query($POSTS, 10, read_page($get), read_search($get));
check('returns 0 posts', count($r['posts']), 0);

echo "\n== 4. Multiple [ad] markers ==\n";
$out = render_ads('First [ad] middle [ad-2] last [ad] end');
check('three markers → three ad divs', substr_count($out, 'class="ad '), 3);
check('slot captured for [ad-2]', str_contains($out, 'class="ad ad-2"'), true);
check('bare [ad] → slot 0', substr_count($out, 'class="ad ad-0"'), 2);
check('surrounding content preserved', str_contains($out, 'First') && str_contains($out, 'end'), true);

echo "\n== 4b. False positives (the '#[ad]#' bug) ==\n";
$plain = 'an ad and a add and a dad and a bad apple';
check('plain words untouched → no divs', substr_count(render_ads($plain), '<div'), 0);

echo "\n{$PASS} passed, {$FAIL} failed\n";
exit($FAIL === 0 ? 0 : 1);
