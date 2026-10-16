<?php
/** Jaka requested reverting the two Roll kit slots to their previous Slim papers. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
if (wp_get_environment_type() !== 'development') { throw new RuntimeException('Development only'); }
$kits = get_option('zvij_kits', []);
$backup = get_option('zvij_kits_before_rolls_v3', []);
$original = [];
foreach ($backup as $kit) {
    if (! in_array($kit['key'], ['silver', 'gold'], true)) { continue; }
    foreach ($kit['items'] as $item) {
        if ($item['role'] === 'paper') { $original[$kit['key']] = $item; }
    }
}
if (count($original) !== 2) { throw new RuntimeException('Original paper slot backup not found'); }
foreach (['silver' => 'ziggi-original-classic-slim', 'gold' => 'ziggi-natural-classic-slim'] as $key => $slug) {
    if ($original[$key]['slug'] !== $slug) { throw new RuntimeException('Unexpected original paper'); }
    $view = zvij_kit_item_view($slug);
    if (empty($view['id'])) { throw new RuntimeException('Original paper no longer resolves'); }
}
foreach ($kits as &$kit) {
    if (! isset($original[$kit['key']])) { continue; }
    foreach ($kit['items'] as &$item) {
        if ($item['role'] === 'paper') { $item = $original[$kit['key']]; }
    }
    unset($item);
    echo $kit['key'] . ': ' . $original[$kit['key']]['slug'] . "\n";
}
unset($kit);
update_option('zvij_kits', $kits);
