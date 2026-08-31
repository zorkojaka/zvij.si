<?php
/**
 * Predlansirno čiščenje trgovine (wp eval-file, idempotentno).
 *
 * 1. Prepiše javno besedilo izdelkov, ki so nosili interne planerske zapiske
 *    (»Draft komponenta«, »Incoming«, »Ne komunicirati kot …«) — to bi bilo
 *    ob objavi vidno kupcu.
 * 2. Umakne podvojene/privzete strani (WooCommerce »Shop«, WP »Sample Page«).
 * 3. Pobriše testna naročila in testne kupone iz razvoja.
 *
 * Zagon: docker compose --profile tools run --rm wp-cli wp eval-file scripts/wp-prelaunch-cleanup.php
 */

if (! defined('ABSPATH')) {
    exit;
}

/** 1. Javno besedilo namesto internih zapiskov. */
$copy = [
    226 => [
        'name'  => 'Throwie vrečka / setup pouch',
        'short' => 'Vrečka z vrvico, v katero gre cel setup.',
        'desc'  => 'Preprosta vrečka z vrvico, dovolj velika za vžigalnik, rizle, grinder in filtre. Vse na enem mestu, nič se ne izgublja po žepih in predalih — osnova vsakega kita.',
    ],
    332 => [
        'name'  => 'RAW Rolls',
        'short' => 'Papir v zvitku — odviješ na svojo mero.',
        'desc'  => 'RAW Rolls: nebeljen papir v zvitku, ki ga odviješ na dolžino, kakršno rabiš. Brez fiksnega formata, en zvitek traja dolgo.',
    ],
    214 => [
        'name'  => 'FARO Hemp 2 vžigalnik',
        'short' => 'Preprost vsakdanji vžigalnik za v žep ali kit.',
        'desc'  => 'Osnovni vžigalnik za vsak dan — tisti, ki ga daš v vrečko, posodiš in ne obžaluješ, če ostane pri nekom drugem. Za setup, ki ga hraniš, poglej Clipper.',
    ],
];

foreach ($copy as $id => $data) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}: ni izdelka\n";
        continue;
    }
    $product->set_name($data['name']);
    $product->set_short_description($data['short']);
    $product->set_description($data['desc']);
    $product->save();
    echo "besedilo popravljeno #{$id} — {$data['name']}\n";
}

/** 2. Podvojene / privzete strani v koš. */
$shop_page_id = (int) get_option('woocommerce_shop_page_id');
foreach ([ 'shop' => 'privzeta WooCommerce stran, trgovina je /trgovina/', 'sample-page' => 'privzeta WordPress vzorčna stran' ] as $slug => $why) {
    $page = get_page_by_path($slug);
    if (! $page || $page->post_status === 'trash') {
        echo "preskocim /{$slug}/: ne obstaja ali je ze v kosu\n";
        continue;
    }
    if ((int) $page->ID === $shop_page_id) {
        echo "POZOR: /{$slug}/ je nastavljena kot WooCommerce stran trgovine — pustim\n";
        continue;
    }
    wp_trash_post($page->ID);
    echo "v kos: /{$slug}/ (#{$page->ID}) — {$why}\n";
}

/** 3. Testna naročila in kuponi iz razvoja. */
$deleted = 0;
foreach (wc_get_orders([ 'limit' => -1, 'status' => 'any' ]) as $order) {
    $email = strtolower((string) $order->get_billing_email());
    $is_test = $email === ''
        || str_contains($email, 'example.com')
        || str_starts_with($email, 'test-')
        || str_contains($email, 'test@')
        || in_array($email, [ 'zorkojaka@gmail.com', 'jaka@inteligent.si' ], true);
    if (! $is_test) {
        echo "PUSTIM naročilo #" . $order->get_id() . " ({$email}) — ni videti testno\n";
        continue;
    }
    echo "brisem testno naročilo #" . $order->get_id() . " ({$email}, " . $order->get_status() . ")\n";
    $order->delete(true);
    $deleted++;
}
echo "izbrisanih testnih naročil: {$deleted}\n";

$coupons = get_posts([ 'post_type' => 'shop_coupon', 'numberposts' => -1, 'post_status' => 'any' ]);
foreach ($coupons as $coupon) {
    if (! str_starts_with(strtoupper($coupon->post_title), 'ZVIJ-')) {
        continue;
    }
    echo "brisem testni kupon {$coupon->post_title}\n";
    wp_delete_post($coupon->ID, true);
}

/** Števec računov nazaj na 1 — testna naročila ga niso smela porabiti. */
update_option('zvij_invoice_next_seq', 1);
echo "zvij_invoice_next_seq ponastavljen na 1\n";
