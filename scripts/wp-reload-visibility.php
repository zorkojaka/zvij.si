<?php
/**
 * Vklop/izklop javne površine Reloada.
 *
 * Koda, intervali na izdelkih in oznaka `reload` ostanejo nedotaknjeni —
 * skrijejo se samo postavka v meniju, blok na domači strani, filter v
 * trgovini in stran /reload/, opomniki pa se ne pošiljajo.
 *
 * Izklop:  wp eval-file scripts/wp-reload-visibility.php
 * Vklop:   ZVIJ_RELOAD=1 ... (glej spodaj; ali ročno update_option)
 */

if (! defined('ABSPATH')) {
    exit;
}

$on = getenv('ZVIJ_RELOAD') === '1';
update_option('zvij_reload_public', $on ? '1' : '0');

$page = get_page_by_path('reload');
if ($page) {
    $want = $on ? 'publish' : 'draft';
    if ($page->post_status !== $want) {
        wp_update_post(['ID' => $page->ID, 'post_status' => $want]);
    }
    printf("stran /reload/ (#%d): %s → %s\n", $page->ID, $page->post_status, $want);
}

printf("zvij_reload_public = %s (javno: %s)\n", get_option('zvij_reload_public'), zvij_reload_is_public() ? 'da' : 'ne');
printf("intervali na izdelkih ostajajo: %d izdelkov\n",
    (int) $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM {$GLOBALS['wpdb']->postmeta} WHERE meta_key='_zvij_reload_days' AND meta_value != ''"));
