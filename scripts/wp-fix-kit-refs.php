<?php
/**
 * Popravi sklice v opciji `zvij_kits` na preimenovane izdelke.
 *
 * Grinderja sta 1. 9. 2026 dobila čista sluga (`gold-grinder-placeholder` →
 * `zlat-grinder-52-mm`, `silver-grinder-placeholder` → `srebrn-grinder-40-mm`),
 * kiti pa so še kazali na stara — mesto grinderja se v Silver in Gold kitu
 * sploh ni izrisalo. Idempotentno.
 */

if (! defined('ABSPATH')) {
    exit;
}

$rename = [
    'gold-grinder-placeholder'   => 'zlat-grinder-52-mm',
    'silver-grinder-placeholder' => 'srebrn-grinder-40-mm',
];

$kits = get_option('zvij_kits', []);
if (! is_array($kits) || $kits === []) {
    echo "opcija zvij_kits je prazna\n";
    return;
}

$changed = 0;
foreach ($kits as $ki => $kit) {
    foreach (['items', 'addons'] as $group) {
        foreach ((array) ($kit[$group] ?? []) as $ii => $item) {
            $slug = is_array($item) ? ($item['slug'] ?? '') : (string) $item;
            if (! isset($rename[$slug])) {
                continue;
            }
            if (is_array($item)) {
                $kits[$ki][$group][$ii]['slug'] = $rename[$slug];
            } else {
                $kits[$ki][$group][$ii] = $rename[$slug];
            }
            printf("kit %s/%s: %s → %s\n", $kit['key'] ?? '?', $group, $slug, $rename[$slug]);
            $changed++;
        }
    }
}

if ($changed === 0) {
    echo "ni sprememb (sklici so ze pravilni)\n";
    return;
}

update_option('zvij_kits', $kits);
echo "posodobljenih sklicev: {$changed}\n";
