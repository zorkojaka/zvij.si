<?php
/**
 * Kristali po pravilu namesto po ročno vpisanih številkah.
 *
 * Privzeto 10 % cene nazaj, rizle in rolce 5 % (tanjši izdelek). Izjeme na
 * posameznem izdelku (`_zvij_kristali`) ostanejo samo tam, kjer izdelek
 * namenoma odstopa — pri Ziggi jih odstranimo, ker se od pravila razlikujejo
 * za en ali dva kristala, kar je pod dvema centoma.
 */

if (! defined('ABSPATH')) {
    exit;
}

update_option('zvij_credit_reward_percent', 10);
update_option('zvij_credit_reward_by_cat', ['rizle' => 5, 'rolce' => 5]);

$ziggi = [435, 436, 437, 438, 439, 440, 441, 442, 443, 444];

printf("%-44s %8s %9s %8s\n", 'Izdelek', 'prej', 'po pravilu', 'razlika');
foreach ($ziggi as $id) {
    $product = wc_get_product($id);
    if (! $product) {
        continue;
    }
    $before = (int) get_post_meta($id, '_zvij_kristali', true);

    $product->delete_meta_data('_zvij_kristali');
    $product->save();

    $after = zvij_credit_product_kristali($product);
    printf("%-44s %8d %9d %+7.2f €\n", mb_substr($product->get_name(), 0, 43), $before, $after,
        ($after - $before) / ZVIJ_KRISTALI_PER_EUR);
}

echo "\nizjeme, ki ostajajo (namenoma odstopajo od pravila):\n";
global $wpdb;
foreach ($wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_zvij_kristali' ORDER BY post_id") as $id) {
    $p = wc_get_product((int) $id);
    if (! $p) {
        continue;
    }
    $cur  = (int) get_post_meta($id, '_zvij_kristali', true);
    $rule = zvij_credit_kristali_from_price($p);
    printf("  %-42s %4d kr (pravilo bi dalo %d)\n", mb_substr($p->get_name(), 0, 41), $cur, $rule);
}

/**
 * Izjeme, ki se od pravila razlikujejo za en kristal ali manj (torej pod
 * enim centom), niso izjeme — so ostanek ročnega vpisovanja. Odstranimo jih,
 * da ostanejo samo tiste, ki nekaj povedo.
 */
echo "\nodstranjujem izjeme, ki se ujemajo s pravilom:\n";
foreach ($wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_zvij_kristali' ORDER BY post_id") as $id) {
    $p = wc_get_product((int) $id);
    if (! $p) {
        continue;
    }
    $cur  = (int) get_post_meta($id, '_zvij_kristali', true);
    $rule = zvij_credit_kristali_from_price($p);
    if (abs($cur - $rule) > 1) {
        continue;
    }
    $p->delete_meta_data('_zvij_kristali');
    $p->save();
    printf("  %-42s %4d → pravilo %d\n", mb_substr($p->get_name(), 0, 41), $cur, zvij_credit_product_kristali($p));
}

$left = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_zvij_kristali'");
printf("\npreostalih izjem: %d (od 24 na začetku)\n", $left);

/**
 * Pribor po specifikaciji (RELEASE_PLAN, 16. 7. 2026) NE daje kristalov.
 * Pravilo iz cene bi mu jih dalo, zato kategorijam pribora izrecno
 * nastavimo 0 % — sprememba ponudbe ni stvar refaktoriranja.
 */
$by_cat = (array) get_option('zvij_credit_reward_by_cat', []);
$by_cat['vzigalniki']    = 0;
$by_cat['grinderji']     = 0;
$by_cat['setup-dodatki'] = 0;
$by_cat['embalaza']      = 0;
update_option('zvij_credit_reward_by_cat', $by_cat);

echo "\npravila po kategorijah:\n";
foreach ($by_cat as $slug => $pct) {
    printf("  %-16s %3d %%\n", $slug, $pct);
}
