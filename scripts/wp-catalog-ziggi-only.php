<?php
/**
 * Katalog na Jakovo odločitev (1. 9. 2026):
 * - papir/rolce prodajamo SAMO Ziggi → ostali gredo v osnutek;
 * - RAW Rolls in FARO Hemp 2 se izbrišeta (izrecno);
 * - Throwie vrečke ni na zalogi → deaktivirana;
 * - črn grinder Zvij.si: zaloga 9.
 *
 * Zagon: docker compose --profile tools run --rm wp-cli wp eval-file scripts/wp-catalog-ziggi-only.php
 */

if (! defined('ABSPATH')) {
    exit;
}

/** Trajen izbris (izrecna zahteva). */
$delete = [
    332 => 'RAW Rolls',
    214 => 'FARO Hemp 2 vžigalnik',
];

/** Umik iz prodaje, podatki ostanejo. */
$draft = [
    219 => 'IRIE XTRA Light King Size Slim — ne-Ziggi papir',
    220 => 'JaJa Noir Black — ne-Ziggi papir',
    221 => 'SmK Gold Papers + Filter Tips — ne-Ziggi papir',
    225 => 'SmK Gold Rolls — ne-Ziggi rolce',
    222 => 'Smoking Black Rolls — ne-Ziggi rolce',
    224 => 'Smoking Brown Rolls — ne-Ziggi rolce',
    223 => 'Smoking Silver Rolls — ne-Ziggi rolce',
    226 => 'Throwie vrečka / setup pouch — ni na zalogi',
];

/** Potrjene zaloge. */
$stock = [
    218 => 9,  // Grinder Zvij.si črn
];

foreach ($draft as $id => $why) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}: ni izdelka\n";
        continue;
    }
    if ($product->get_status() !== 'draft') {
        $product->set_status('draft');
        $product->save();
    }
    printf("osnutek #%d %s — %s\n", $id, $product->get_name(), $why);
}

foreach ($stock as $id => $qty) {
    $product = wc_get_product($id);
    if (! $product) {
        continue;
    }
    $product->set_manage_stock(true);
    $product->set_stock_quantity($qty);
    $product->set_backorders('no');
    $product->save();
    printf("zaloga #%d %s = %d\n", $id, $product->get_name(), $qty);
}

foreach ($delete as $id => $name) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "ze izbrisan: {$name}\n";
        continue;
    }
    $product->delete(true);
    printf("IZBRISAN #%d %s\n", $id, $name);
}
