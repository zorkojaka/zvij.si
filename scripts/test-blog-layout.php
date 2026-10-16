<?php
/** Read-only blog rendering regression checks. */
if (PHP_SAPI !== 'cli') {
    exit(1);
}
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';

function blog_check(bool $condition, string $label): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL $label\n");
        exit(1);
    }
    echo "PASS $label\n";
}

$posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1]);
blog_check(count($posts) > 0, 'Published articles available');
foreach ($posts as $post) {
    setup_postdata($post);
    ob_start();
    zvij_theme_blog_card();
    $html = ob_get_clean();
    blog_check(str_contains($html, 'class="zv-blog-card__media"'), "Article {$post->ID} reserves a media row");
    blog_check(str_contains($html, esc_url(get_permalink($post))), "Article {$post->ID} links to its original URL");
}
add_filter('post_thumbnail_id', '__return_zero');
ob_start();
zvij_theme_blog_card();
$fallback = ob_get_clean();
remove_filter('post_thumbnail_id', '__return_zero');
blog_check(str_contains($fallback, 'zv-blog-card__placeholder'), 'Missing thumbnail keeps a placeholder media row');
wp_reset_postdata();

do_action('wp_enqueue_scripts');
blog_check(wp_style_is('zvij-blog-layout', 'enqueued'), 'Blog stylesheet enqueued');
$single = file_get_contents(get_template_directory() . '/single.php');
blog_check(str_contains($single, 'zv-article__body entry-content'), 'Article uses an unframed reading column');
blog_check(! str_contains($single, 'page-layout zv-article'), 'Generic oversized page margins removed');
$css = file_get_contents(get_template_directory() . '/assets/blog.css');
blog_check(str_contains($css, '@media (max-width: 600px)'), 'Mobile layout defined');
blog_check(str_contains($css, 'var(--zvij-text)'), 'Reading text uses theme-aware color');
