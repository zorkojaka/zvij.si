<?php
/**
 * Pocisti Mediateko: odstrani priponke, ki jih noben izdelek ne uporablja.
 *
 * Varovala:
 * - nikoli se ne dotakne priponke, ki je glavna slika ali v galeriji
 *   kateregakoli izdelka (bere _thumbnail_id in _product_image_gallery);
 * - nikoli ne izbrise woocommerce-placeholder (potrebuje ga WooCommerce);
 * - brise samo tisto, kar je na seznamu razlogov spodaj — ne cesarkoli
 *   neuporabljenega, ker so med tem lahko slike za prihodnje izdelke.
 *
 * Suhi tek:  wp eval-file scripts/wp-cleanup-media.php
 * Izvedba:   ZVIJ_APPLY=1 wp eval-file scripts/wp-cleanup-media.php
 */

if (! defined('ABSPATH')) {
    exit;
}

$apply = getenv('ZVIJ_APPLY') === '1';
global $wpdb;

$in_use = array_map('intval', $wpdb->get_col("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key='_thumbnail_id'"));
foreach ($wpdb->get_col("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key='_product_image_gallery'") as $csv) {
    foreach (explode(',', (string) $csv) as $g) {
        if (trim($g) !== '') {
            $in_use[] = (int) $g;
        }
    }
}
$in_use = array_values(array_unique(array_filter($in_use)));

/** Vzorec v imenu => razlog za izbris. */
$reasons = [
    '/^ziggi-.*-basic/i'             => 'Ziggi razlicica, ki je ne prodajamo (Basic)',
    '/1_1\.4/i'                      => 'Ziggi razlicica 1 1/4, ki je ne prodajamo',
    '/filter-tips/i'                 => 'Ziggi Filter Tips, ki jih ne prodajamo',
    '/^ziggi-.*-roll-basic/i'        => 'Ziggi Roll Basic, ki ga ne prodajamo',
    '/_200x200\./i'                  => 'sličica 200x200 iz raziskovanja dobavitelja',
    '/^(crn|srebrn|zlat)-tulec.*\.png$/i' => 'PNG izvirnik tulca; v uporabi je JPEG',
    '/^tulec-vse-barve\.png$/i'      => 'PNG izvirnik; v uporabi je JPEG',
    '/^grinder.*\.png$/i'            => 'PNG izvirnik grinderja; v uporabi je JPEG',
];

$rows = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' ORDER BY ID");
$hits = [];
$bytes = 0;

foreach ($rows as $aid) {
    $aid = (int) $aid;
    if (in_array($aid, $in_use, true)) {
        continue;
    }
    $file = get_attached_file($aid);
    if (! $file) {
        continue;
    }
    $base = basename($file);
    if (str_contains($base, 'woocommerce-placeholder')) {
        continue;
    }

    foreach ($reasons as $pattern => $why) {
        if (preg_match($pattern, $base)) {
            $hits[] = [$aid, $base, $why, is_file($file) ? filesize($file) : 0];
            $bytes += is_file($file) ? filesize($file) : 0;
            break;
        }
    }
}

echo $apply ? "== IZVEDBA ==\n\n" : "== SUHI TEK (nic se ne izbrise) ==\n\n";
foreach ($hits as [$aid, $base, $why, $size]) {
    printf("  #%-4d %-56s %6s kB  %s\n", $aid, mb_substr($base, 0, 55), number_format($size / 1024, 0), $why);
    if ($apply) {
        wp_delete_attachment($aid, true);
    }
}
printf("\n%s: %d priponk, %.1f MB\n", $apply ? 'izbrisano' : 'za izbris', count($hits), $bytes / 1048576);
printf("zascitenih (v uporabi na izdelkih): %d\n", count($in_use));
