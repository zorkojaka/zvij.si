<?php
/**
 * Formati in gramature Ziggi izdelkov (iz Ziggijevega white-label kataloga
 * »WHITE LABLE CATALOGUE 2026 V7«, Ziggi d.o.o., Ljubljana — ista tovarna,
 * ista produktna linija).
 *
 * Uporabljeni so SAMO podatki (mere, gramature, vsebina paketa), ne risbe iz
 * kataloga — te so Ziggijeva registrirana modelna pravica (RCD).
 *
 * NEPREVERJENO: dolžine zvitka namenoma ne navajamo — katalog je sam pri sebi
 * neskladen (5 m na eni strani, 420 cm na drugi). Preveri na embalaži.
 */

if (! defined('ABSPATH')) {
    exit;
}

$ks_slim = '<strong>King Size Slim</strong> — 110 × 44 mm';
$ks_wide = '<strong>King Size Wide</strong> — 110 × 53 mm';
$paper   = 'papir 14 g/m², počasno gorenje';
$tips    = 'filter konice 170 g/m²';

$specs = [
    435 => $ks_slim . ' · ' . $paper . ' · ' . $tips . ' · zavitek s pokrovčkom',                       // Original Classic Slim
    436 => $ks_slim . ' · konopljin papir 14 g/m², počasno gorenje · ' . $tips . ' · zavitek s pokrovčkom', // Hemp
    437 => $ks_slim . ' · nebeljen papir 14 g/m², počasno gorenje · ' . $tips . ' · zavitek s pokrovčkom', // Natural
    438 => $ks_slim . ' · ' . $paper . ' · ' . $tips . ' · posebna izdaja',                              // Mystery Mix
    439 => $ks_slim . ' · ' . $paper . ' · ' . $tips . ' · posebna izdaja',                              // Mycelium
    440 => $ks_slim . ' · ' . $paper . ' · ' . $tips . ' · posebna izdaja',                              // Rocket's Odyssey
    441 => $ks_wide . ' · ' . $paper . ' · ' . $tips,                                                    // Wide Extra
    444 => $ks_slim . ' · ' . $paper . ' · ' . $tips . ' · dvojna vsebina v enem zavitku',               // Double
    442 => 'Zvitek širine <strong>44 mm</strong> · ' . $paper . ' · filter konice 48 × 25 mm, 170 g/m² · priložena podlaga (tray)', // Original Roll
    443 => 'Zvitek širine <strong>44 mm</strong> · nebeljen papir 14 g/m², počasno gorenje · filter konice 48 × 25 mm, 170 g/m² · priložena podlaga (tray)', // Natural Roll
];

foreach ($specs as $id => $line) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}: ni izdelka\n";
        continue;
    }

    // Obstoječi opis obdržimo, specifikacije pripnemo/zamenjamo na koncu.
    $desc = $product->get_description();
    $desc = preg_replace('#<h3 class="zvij-specs">.*$#s', '', $desc);
    $desc = rtrim($desc) . "\n" . '<h3 class="zvij-specs">Podatki o izdelku</h3>' . "\n<p>" . $line . '</p>';

    $product->set_description($desc);
    $product->save();
    printf("#%d %-42s %s\n", $id, mb_substr($product->get_name(), 0, 41), mb_substr(strip_tags($line), 0, 58));
}
