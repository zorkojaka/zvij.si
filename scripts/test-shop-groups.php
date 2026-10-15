<?php
/** Standalone shop-group regression checks; no WordPress database writes. */
define('ABSPATH', __DIR__);
$actions = [];
$filters = [];
$group_value = '';
$shop = true;
function add_action($name, $callback): void { global $actions; $actions[$name] = $callback; }
function add_filter($name, $callback): void { global $filters; $filters[$name] = $callback; }
function get_query_var($name, $default = '') { global $group_value; return $group_value; }
function is_shop(): bool { global $shop; return $shop; }
require (getenv('ZVIJ_THEME_DIR') ?: dirname(__DIR__) . '/wp-content/themes/zvij-theme') . '/inc/shop-groups.php';
function check($condition, string $message): void {
    if (! $condition) { throw new RuntimeException($message); }
    echo "PASS: $message\n";
}
class ShopGroupQuery {
    public array $data = [];
    public bool $main = true;
    public function is_main_query(): bool { return $this->main; }
    public function get($key) { return $this->data[$key] ?? null; }
    public function set($key, $value): void { $this->data[$key] = $value; }
}
check(array_keys(zvij_shop_groups()) === ['ziggi', 'cbd', 'pripomocki'], 'Three requested navigation groups');
check(in_array('zvij_group', $filters['query_vars']([]), true), 'Group query variable registered');
foreach (zvij_shop_groups() as $key => $group) {
    $group_value = $key;
    $query = new ShopGroupQuery();
    $existing = ['relation' => 'OR', ['taxonomy' => 'product_visibility', 'terms' => [1], 'operator' => 'NOT IN']];
    $query->data['tax_query'] = $existing;
    $actions['woocommerce_product_query']($query);
    check($query->data['tax_query'][1]['terms'] === $group['categories'], "$key filters all its categories");
    check($query->data['tax_query']['relation'] === 'AND' && $query->data['tax_query'][0] === $existing, "$key preserves existing visibility constraints");
}
foreach (['', 'unknown', ['ziggi']] as $value) {
    $group_value = $value;
    $query = new ShopGroupQuery();
    $actions['woocommerce_product_query']($query);
    check($query->data === [], 'Empty or invalid group leaves catalog unchanged');
}
$group_value = 'cbd';
$query = new ShopGroupQuery();
$query->main = false;
$actions['woocommerce_product_query']($query);
check($query->data === [], 'Secondary product queries untouched');
$query = new ShopGroupQuery();
$shop = false;
$actions['woocommerce_product_query']($query);
check($query->data === [], 'Other product archives untouched');
