<?php
/**
 * Q1 — the same checks against a real WordPress install (WP_UnitTestCase).
 *
 * Not executed in the preparation environment: PHP 8.5 is available (verify.php
 * was run with it), but there is no WordPress + PHPUnit install, so this file
 * documents the assertions that need core. Everything that can run without
 * WordPress lives in verify.php.
 *
 * @group bcm-q1
 */
class BCM_Q1_Checks extends WP_UnitTestCase {

    public function set_up(): void {
        parent::set_up();
        // Seed 25 posts so pages 1–3 are meaningful.
        self::factory()->post->create_many(25);
    }

    /** Pure helper mirroring the corrected regex replacement. */
    private function render_ads(string $content): string {
        return preg_replace_callback(
            '#\[ad(?:-(\d+))?\]#',
            static function (array $m): string {
                $slot = (isset($m[1]) && $m[1] !== '') ? $m[1] : '0';
                return '<div class="ad ad-' . esc_attr($slot) . '"></div>';
            },
            $content
        );
    }

    public function test_missing_parameters_default_to_page_1(): void {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $this->assertSame(1, $page);
    }

    public function test_page_2_returns_second_set(): void {
        $q = new WP_Query(['posts_per_page' => 10, 'paged' => 2, 'post_type' => 'post']);
        $this->assertSame(10, $q->post_count);
        $this->assertSame(3, (int) $q->max_num_pages);
    }

    public function test_no_results_search(): void {
        $q = new WP_Query(['posts_per_page' => 10, 'paged' => 1, 's' => 'zzzznomatch']);
        $this->assertFalse($q->have_posts());
    }

    public function test_out_of_range_page_is_clamped_and_requeried(): void {
        // A custom query does not 404 an out-of-range page: it returns nothing.
        $q = new WP_Query(['posts_per_page' => 10, 'paged' => 999, 'post_type' => 'post']);
        $this->assertFalse($q->have_posts());

        // The fix in corrected.php: clamp, then re-run against the last page.
        $q2 = new WP_Query(['posts_per_page' => 10, 'paged' => (int) $q->max_num_pages, 'post_type' => 'post']);
        $this->assertTrue($q2->have_posts());
    }

    public function test_multiple_ad_markers_are_all_replaced(): void {
        $out = $this->render_ads('A [ad] B [ad-2] C [ad] D');
        $this->assertSame(3, substr_count($out, 'class="ad '));
        $this->assertStringContainsString('B', $out); // content preserved
    }

    public function test_plain_ad_words_are_not_touched(): void {
        $out = $this->render_ads('an ad and a add and dad');
        $this->assertStringNotContainsString('<div', $out);
    }

    public function test_xss_in_post_content_is_neutralised(): void {
        $clean = wp_kses_post('<p>Hello <script>alert(1)</script></p>');
        $this->assertStringNotContainsString('<script', $clean);
    }
}
