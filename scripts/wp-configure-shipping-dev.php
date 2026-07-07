<?php
/**
 * ZVIJ-07 — Slovenija delivery rates (owner-confirmed OWNER-M07, Q01–Q04).
 * Reproducible WooCommerce shipping config, run via:
 *   docker compose run --rm wp-cli wp eval-file scripts/wp-configure-shipping-dev.php
 *
 * Owner model (approved option A): named flat-rate tiers + free "navadna"
 * value over 42 EUR. Idempotent — safe to re-run. Zone 1 = Slovenija; the
 * base navadna rate is instance 1 and free shipping is instance 2 (existing).
 */
if (!class_exists('WC_Shipping_Zones')) { WP_CLI::error('WooCommerce not loaded.'); }
$zone = WC_Shipping_Zones::get_zone(1);
if (!$zone) { WP_CLI::error('Zone 1 (Slovenija) not found.'); }

// Base "navadna" rate — update the existing flat_rate instance 1 in place.
update_option('woocommerce_flat_rate_1_settings', [
  'title' => 'Navadna poštnina', 'tax_status' => 'none', 'cost' => '2.90',
]);
WP_CLI::log('flat_rate #1: Navadna poštnina = 2.90');

// Free "navadna" over 42 EUR — update the existing free_shipping instance 2.
update_option('woocommerce_free_shipping_2_settings', [
  'title' => 'Brezplačna dostava (navadna)', 'requires' => 'min_amount',
  'min_amount' => '42', 'ignore_discounts' => 'no',
]);
WP_CLI::log('free_shipping #2: min_amount = 42');

// Paid upgrade tiers — idempotent by title (create once, then update).
$upgrades = [
  'S sledenjem' => '3.90',
  'S podpisom'  => '3.90',
  'Po povzetju' => '5.90',
  'Paket'       => '7.50',
];
$byTitle = [];
foreach ($zone->get_shipping_methods() as $iid => $m) {
  $byTitle[$m->get_title()] = ['id' => $iid, 'method_id' => $m->id];
}
foreach ($upgrades as $title => $cost) {
  if (isset($byTitle[$title]) && $byTitle[$title]['method_id'] === 'flat_rate') {
    $iid = $byTitle[$title]['id'];
  } else {
    $iid = $zone->add_shipping_method('flat_rate');
  }
  update_option("woocommerce_flat_rate_{$iid}_settings", [
    'title' => $title, 'tax_status' => 'none', 'cost' => $cost,
  ]);
  WP_CLI::log("flat_rate #{$iid}: {$title} = {$cost}");
}

$zone->save();
WP_CLI::success('ZVIJ-07 shipping configured (Slovenija zone).');
