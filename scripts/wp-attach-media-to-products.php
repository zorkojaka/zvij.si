<?php
/**
 * Pripne naložene fotografije iz Mediateke na prave izdelke.
 *
 * Ozadje: Ziggijeve slike so bile naložene razpakirane, ker je bil ZIP
 * prevelik za en prenos. S tem se je izgubila struktura map, zato ujemanje
 * NE more biti po mapi — veže se po imenu datoteke.
 *
 * Ziggijevo poimenovanje: Ziggi-<Linija>-<Razlicica>-<n>[-scaled].png
 * Vzorci so urejeni od bolj specificnih k splosnejsim: "basic" mora stati
 * pred "original", sicer bi "Ziggi-Original-Basic" pristal na navadnem
 * Originalu; enako "wide extra" pred "extra".
 *
 * Zagon (najprej suho, brez sprememb):
 *   wp eval-file scripts/wp-attach-media-to-products.php
 * Izvedba:
 *   ZVIJ_APPLY=1 wp eval-file scripts/wp-attach-media-to-products.php
 */

if (! defined('ABSPATH')) {
    exit;
}

$apply = getenv('ZVIJ_APPLY') === '1';

/** vzorec => ID izdelka, null = razlicica, ki je ne prodajamo. */
$map = [
    // Kljuc je normaliziran (vezaji in podcrtaji -> presledki), zato tudi
    // vzorci uporabljajo presledke.
    'clipper black'   => 211,
    'clipper plin'    => 445,
    'grinder zlat'    => 216,
    'grinder srebrn'  => 217,

    'mystery'         => 438,
    'mycelium'        => 439,
    'rocket'          => 440,

    'wide extra'      => 441,
    'original double' => 444,

    'original roll basic' => null,
    'natural roll basic'  => null,
    'original roll'   => 442,
    'natural roll'    => 443,

    '1 1 4'           => null,
    'basic'           => null,
    'extra'           => null,
    'filter tips'     => null,
    'rolling tray'    => null,
    'canna box'       => null,
    'grinder 4'       => null,
    'grinder 2'       => null,

    'hemp'            => 436,
    'natural'         => 437,
    'original'        => 435,
];

function zvij_media_key(string $file): string {
    $n = mb_strtolower(pathinfo($file, PATHINFO_FILENAME));
    $n = preg_replace('/-scaled$/', '', $n);
    $n = str_replace(['_', '-', '.'], ' ', $n);
    return trim(preg_replace('/\s+/', ' ', $n));
}

/** Slike z belim/prosojnim ozadjem so za trgovino boljse od lifestyle. */
function zvij_media_rank(string $file): int {
    $k = zvij_media_key($file);
    $r = 100;
    if (str_contains($k, 'transparent')) { $r -= 40; }
    if (str_contains($k, 'web'))         { $r -= 20; }
    if (str_contains($k, 'shadow'))      { $r -= 5; }
    return $r;
}

global $wpdb;
$ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' ORDER BY ID");

/** Priponke, ki so ze v uporabi kot glavna slika ali v galeriji. */
$in_use = array_map('intval', $wpdb->get_col("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key='_thumbnail_id'"));
foreach ($wpdb->get_col("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key='_product_image_gallery'") as $csv) {
    foreach (explode(',', (string) $csv) as $g) {
        if (trim($g) !== '') {
            $in_use[] = (int) $g;
        }
    }
}
$in_use = array_values(array_unique(array_filter($in_use)));
printf("v uporabi na izdelkih: %d priponk\n", count($in_use));

$groups = [];      // ID izdelka => datoteke
$skip = [];        // razlicice, ki jih ne prodajamo
$unmatched = [];
$placeholders = [];

foreach ($ids as $aid) {
    $file = get_attached_file((int) $aid);
    if (! $file || ! file_exists($file)) {
        continue;
    }
    $base = basename($file);

    if (get_post_meta($aid, '_zvij_placeholder', true) !== '') {
        $placeholders[] = [(int) $aid, $base];
        continue;
    }
    // Fotografije, ki jih kaksen izdelek ze uporablja, pustimo pri miru —
    // post_parent ni zanesljiv, ker rocno nalozene priponke starsa nimajo.
    if (in_array((int) $aid, $in_use, true)) {
        continue;
    }

    $key = zvij_media_key($base);
    $hit = false;
    foreach ($map as $pattern => $target) {
        if (str_contains($key, $pattern)) {
            $hit = true;
            if ($target === null) {
                $skip[] = [(int) $aid, $base, $pattern];
            } else {
                $groups[$target][] = ['id' => (int) $aid, 'file' => $file, 'base' => $base];
            }
            break;
        }
    }
    if (! $hit) {
        $unmatched[] = [(int) $aid, $base];
    }
}

echo $apply ? "== IZVEDBA ==\n\n" : "== SUHI TEK (nic se ne spremeni) ==\n\n";

foreach ($groups as $pid => $files) {
    usort($files, fn ($a, $b) => [zvij_media_rank($a['base']), $a['base']] <=> [zvij_media_rank($b['base']), $b['base']]);
    $product = wc_get_product((int) $pid);
    if (! $product) {
        printf("#%d — izdelka ni\n", $pid);
        continue;
    }
    printf("#%-4d %-40s %d slik\n", $pid, mb_substr($product->get_name(), 0, 39), count($files));
    foreach (array_slice($files, 0, 5) as $i => $f) {
        printf("        %s %s\n", $i === 0 ? 'glavna  ' : 'galerija', $f['base']);
    }

    if (! $apply) {
        continue;
    }

    $old = (int) get_post_thumbnail_id($pid);
    $keep = array_slice($files, 0, 5);
    foreach ($keep as $f) {
        wp_update_post(['ID' => $f['id'], 'post_parent' => $pid]);
    }
    $product->set_image_id($keep[0]['id']);
    $product->set_gallery_image_ids(array_map(fn ($f) => $f['id'], array_slice($keep, 1)));
    $product->save();

    if ($old && get_post_meta($old, '_zvij_placeholder', true) !== '') {
        wp_delete_attachment($old, true);
    }
}

printf("\n-- razlicice, ki jih ne prodajamo: %d --\n", count($skip));
foreach (array_slice($skip, 0, 12) as [$id, $base, $pattern]) {
    printf("   #%-4d %-56s (%s)\n", $id, mb_substr($base, 0, 55), $pattern);
}
if (count($skip) > 12) {
    printf("   … in še %d\n", count($skip) - 12);
}

printf("\n-- neujemajoce: %d --\n", count($unmatched));
foreach ($unmatched as [$id, $base]) {
    printf("   #%-4d %s\n", $id, $base);
}

printf("\n-- placeholderji: %d --\n", count($placeholders));
