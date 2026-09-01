<?php
/**
 * Prednastavljeni kiti kot priporočilo, ne kot fiksen paket (Jaka, 1. 9. 2026):
 * vsako mesto v kitu dobi seznam kandidatov, med katerimi stranka izbira,
 * odkljuka pa ga lahko tudi stran.
 *
 * Ob tem popravi papirnata mesta, ki so kazala na izbrisani RAW Rolls oz. na
 * osnutke, in vžigalnik v Silver kitu (srebrnega Clipperja ni na zalogi).
 *
 * Idempotentno. Zagon:
 *   docker compose --profile tools run --rm wp-cli wp eval-file scripts/wp-kits-swappable.php
 */

if (! defined('ABSPATH')) {
    exit;
}

/** Kandidati po vlogi mesta. */
$alternatives = [
    'tube'    => [ 'cat' => ['setup-dodatki'] ],
    'lighter' => [ 'slug' => ['clipper-black', 'clipper-gold'] ],
    'grinder' => [ 'cat' => ['grinderji'] ],
    'paper'   => [ 'cat' => ['rizle', 'rolce'] ],
    'dubi'    => [ 'cat' => ['dubi-filtri'] ],
];

/** Nove privzete izbire (priporočilo kita). */
$defaults = [
    'black'   => [ 'paper' => 'ziggi-original-roll-tips-tray' ],
    'silver'  => [ 'paper' => 'ziggi-original-classic-slim', 'lighter' => 'clipper-black' ],
    'gold'    => [ 'paper' => 'ziggi-mystery-mix-special-edition' ],
    'throwie' => [ 'paper' => 'ziggi-original-classic-slim', 'lighter' => 'clipper-black' ],
];

/** Oznaka mesta — »Rolice« ni več točna, ker gre lahko za rizle ali rolce. */
$labels = [ 'paper' => 'Papir' ];

$kits = get_option('zvij_kits', []);
if (! is_array($kits) || $kits === []) {
    echo "opcija zvij_kits je prazna\n";
    return;
}

foreach ($kits as $ki => $kit) {
    $key = (string) ($kit['key'] ?? '');
    foreach ((array) ($kit['items'] ?? []) as $ii => $item) {
        $role = (string) ($item['role'] ?? '');

        if (isset($defaults[$key][$role]) && $defaults[$key][$role] !== ($item['slug'] ?? '')) {
            printf("%-8s %-8s privzeto: %s → %s\n", $key, $role, $item['slug'] ?? '—', $defaults[$key][$role]);
            $kits[$ki]['items'][$ii]['slug'] = $defaults[$key][$role];
        }

        if (isset($labels[$role]) && ($item['label'] ?? '') !== $labels[$role]) {
            printf("%-8s %-8s oznaka: %s → %s\n", $key, $role, $item['label'] ?? '—', $labels[$role]);
            $kits[$ki]['items'][$ii]['label'] = $labels[$role];
        }

        if (isset($alternatives[$role])) {
            $kits[$ki]['items'][$ii]['alt'] = $alternatives[$role];
        }
    }
}

update_option('zvij_kits', $kits);
echo "\nkiti posodobljeni\n\n";

foreach (get_option('zvij_kits', []) as $kit) {
    printf("KIT %s\n", $kit['key'] ?? '?');
    foreach ((array) ($kit['items'] ?? []) as $item) {
        $options = zvij_kit_slot_alternatives($item);
        $first = $options[0] ?? null;
        printf("   %-10s %-40s %d možnosti%s\n",
            $item['label'] ?? '?',
            $first ? $first['title'] : '*** BREZ IZDELKA ***',
            count($options),
            ($first && ! $first['available']) ? '   ← privzeti ni na voljo' : '');
    }
}
