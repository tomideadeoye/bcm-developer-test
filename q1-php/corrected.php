<?php
/**
 * Q1 — corrected version.
 *
 * Assumes PHP 8, inside a WordPress theme template (the global query and the
 * template hierarchy are already set up by WordPress). Assumptions about the ad
 * placeholders are stated in NOTES.md.
 */

// 1. Read + sanitise input. Cast page to an int and default it; never read a
//    superglobal without a guard (PHP 8 raises a warning for missing keys).
$page   = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

// 2. Build args. Pagination uses `paged`, NOT `page`. Only add `s` when set,
//    otherwise an empty string means "match everything".
$args = [
    'post_type'      => 'post',
    'posts_per_page' => 10,
    'paged'          => $page,
];

if ($search !== '') {
    $args['s'] = $search;
}

$q = new WP_Query($args);

// 3. `page` can exceed the number of pages. A *custom* WP_Query does not 404 the
//    way the main query does — it simply returns no posts — which would print
//    "No results found" underneath a heading that already names the last page.
//    Clamp, then re-run the query against the last page so the two agree.
$total_pages = max(1, (int) $q->max_num_pages);

if ($page > $total_pages) {
    $page          = $total_pages;
    $args['paged'] = $page;
    $q             = new WP_Query($args);
}
?>
<h1>
    Page <?= (int) $page ?> of <?= (int) $total_pages ?>
    <?php if ($search !== '') : ?>
        : Search for <?= esc_html($search) ?>
    <?php endif; ?>
</h1>

<?php if ($q->have_posts()) : ?>

    <?php while ($q->have_posts()) : $q->the_post(); ?>

        <?php
        // Apply the content filters, then replace each literal [ad] marker
        // (optionally with a slot: [ad] or [ad-2]) with a placeholder div.
        // The callback must RETURN a string — echoing inside it is the bug in
        // the original code.
        $content = apply_filters('the_content', get_the_content());

        $content = preg_replace_callback(
            '#\[ad(?:-(\d+))?\]#',
            static function (array $m): string {
                $slot = (isset($m[1]) && $m[1] !== '') ? $m[1] : '0';
                return '<div class="ad ad-' . esc_attr($slot) . '"></div>';
            },
            $content
        );
        ?>

        <?php // get_the_title() returns the title; the_title() echoes it. ?>
        <h2><?= esc_html(get_the_title()) ?></h2>

        <?php // Escape/sanitise output. wp_kses_post allows the ad <div>s. ?>
        <div class="post-content"><?= wp_kses_post($content) ?></div>

    <?php endwhile; ?>

    <?php wp_reset_postdata(); // restore the global $post ?>

    <?php // 4. Pagination links (page 2 etc.). ?>
    <nav class="pagination" aria-label="Posts navigation">
        <?= paginate_links([
            'current'   => $page,
            'total'     => $total_pages,
            'add_args'  => $search !== '' ? ['s' => $search] : [],
            'prev_text' => '&laquo; Previous',
            'next_text' => 'Next &raquo;',
        ]); ?>
    </nav>

<?php else : ?>

    <p>No results found<?= $search !== '' ? ' for &ldquo;' . esc_html($search) . '&rdquo;' : '' ?>.</p>

<?php endif; ?>
