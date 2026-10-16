<?php
/**
 * Tri izjave s stare strani (Seth Rogen, Bob Marley, Willie Nelson) kot izhodišče
 * za gradnik [zvij_izjave]. Idempotentno; obstoječih (urejenih) izjav ne prepiše.
 * Na nobeno stran se ne dodajo samodejno.
 *
 * Zagon (dev ali prod): docker exec -u 33 <wordpress> php /var/www/html/scripts/wp-import-quotes.php
 */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'zvij.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$quotes = [
    ['Seth Rogen', 'Igralec', 'SethRogen.png', '“People have smoked marijuana for thousands of years, for fun and for enhanced creativity. I don’t think it’s any worse for you than having a big meal and getting sleepy.”'],
    ['Bob Marley', 'Glasbenik', 'BobMarley.png', '“Herb is the healing of a nation, alcohol is the destruction.”'],
    ['Willie Nelson', 'Glasbenik', 'Willie-Nelson.png', '“The biggest killer on the planet is stress, and I still think the best medicine is and always has been cannabis.”'],
];

foreach ($quotes as $order => [$author, $role, $file, $text]) {
    $existing = get_posts(['post_type' => 'zvij_quote', 'post_status' => 'any', 'meta_key' => '_zvij_quote_source', 'meta_value' => $file, 'numberposts' => 1]);
    if ($existing) {
        echo "obstaja: {$author}\n";
        continue;
    }
    $id = wp_insert_post(['post_type' => 'zvij_quote', 'post_status' => 'publish', 'post_title' => $author, 'post_excerpt' => $role, 'post_content' => $text, 'menu_order' => $order]);
    update_post_meta($id, '_zvij_quote_source', $file);
    $upload = wp_upload_bits('izjava-' . $file, null, file_get_contents(dirname(__DIR__) . '/data/quotes/' . $file));
    if (! $upload['error']) {
        $image = wp_insert_attachment(['post_mime_type' => 'image/png', 'post_title' => $author, 'post_status' => 'inherit'], $upload['file'], $id);
        wp_update_attachment_metadata($image, wp_generate_attachment_metadata($image, $upload['file']));
        set_post_thumbnail($id, $image);
    }
    echo "dodana: {$author}\n";
}
