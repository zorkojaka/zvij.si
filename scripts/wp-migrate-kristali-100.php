<?php
/**
 * Prehod tečaja kristalov z 10:1 na 100:1 (Jaka, 4. 9. 2026).
 *
 * En kristal je odslej natanko en cent. Vrednosti na izdelkih se pomnožijo
 * z 10, da evrska vrednost ostane nespremenjena — razen Ziggi izdelkov, ki
 * dobijo Jakove številke (Double 20 kristalov = 0,20 €), ostali sorazmerno.
 *
 * VARNOSTNA ZAPORA: skripta zavrne izvedbo, če v ledgerju obstaja kakšna
 * vrstica. Ob spremembi tečaja bi bilo treba preračunati še vsa stanja
 * članov, tega pa ne smemo narediti na slepo.
 */

if (! defined('ABSPATH')) {
    exit;
}

global $wpdb;

/**
 * ZAPORA PROTI PONOVNEMU ZAGONU: skripta množi z 10, drugi zagon bi torej
 * vrednosti podeseteril. Označimo, da je prehod opravljen.
 */
if (get_option('zvij_kristali_migrated_100') === '1') {
    echo "Prehod je bil že opravljen (opcija zvij_kristali_migrated_100). Preskakujem.\n";
    return;
}

$ledger = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}zvij_credit_ledger");
if ($ledger > 0) {
    printf("USTAVLJENO: ledger ima %d vrstic — stanja članov bi bilo treba preračunati ročno.\n", $ledger);
    return;
}
echo "ledger je prazen — varno za prehod\n\n";

if (ZVIJ_KRISTALI_PER_EUR !== 100) {
    printf("USTAVLJENO: konstanta je %d, pričakovano 100. Najprej posodobi kodo.\n", ZVIJ_KRISTALI_PER_EUR);
    return;
}

/** Ziggi po Jakovi specifikaciji: Double 20 kristalov, ostali sorazmerno. */
$ziggi = [
    435 => 10, 436 => 10, 437 => 10, // 2,30 €
    438 => 12, 439 => 12, 440 => 12, // 2,50 € posebne izdaje
    441 => 12,                        // 2,50 € Wide Extra
    442 => 15, 443 => 15,             // 2,90 € rolce
    444 => 20,                        // 4,20 € Double
];

$rows = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_zvij_kristali' ORDER BY post_id");

printf("%-46s %8s %8s %9s\n", 'Izdelek', 'prej', 'zdaj', 'vrednost');
foreach ($rows as $row) {
    $id  = (int) $row->post_id;
    $old = (int) $row->meta_value;
    $new = $ziggi[$id] ?? $old * 10;

    // Prek WooCommerce API, ne update_post_meta: slednji ne razveljavi
    // predpomnilnika izdelkov in stran bi se se naprej kazala stare vrednosti.
    $product = wc_get_product($id);
    if ($product) {
        $product->update_meta_data('_zvij_kristali', (string) $new);
        $product->save();
    } else {
        update_post_meta($id, '_zvij_kristali', (string) $new);
    }

    printf(
        "%-46s %8d %8d %8.2f €%s\n",
        $product ? mb_substr($product->get_name(), 0, 45) : ('#' . $id),
        $old, $new, $new / ZVIJ_KRISTALI_PER_EUR,
        isset($ziggi[$id]) ? '   ← Ziggi, po specifikaciji' : ''
    );
}

/** Nagrada za Zvij kodo: 30 → 300 kristalov (ostane 3 €). */
$old_ref = (int) get_option('zvij_referral_owner_kristali', 30);
if ($old_ref < 100) {
    update_option('zvij_referral_owner_kristali', $old_ref * 10);
    printf("\nnagrada za Zvij kodo: %d → %d kristalov (%.2f €)\n", $old_ref, $old_ref * 10, $old_ref * 10 / ZVIJ_KRISTALI_PER_EUR);
}

update_option('zvij_kristali_migrated_100', '1');
echo "\nprehod označen kot opravljen\n";
