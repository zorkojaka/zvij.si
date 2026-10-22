<?php
if (PHP_SAPI !== 'cli') { exit; }
require '/var/www/html/wp-load.php';
$labels = ['pozdrav', 'stopnja članstva', 'kristali', 'izziv', 'povabi prijatelje', 'članstvo', 'zadnje naročilo', 'Zvij koda'];
foreach ($labels as $label) {
    $html = zvij_account_placeholder($label);
    if (! str_contains($html, '<img ') || str_contains($html, 'Slika ·')) { throw new Exception('Image slot failed: ' . $label); }
    preg_match('/src="([^"]+)"/', $html, $match);
    $path = str_replace(plugins_url(), WP_PLUGIN_DIR, $match[1]);
    if (! is_file($path)) { throw new Exception('Missing asset: ' . $label); }
    echo 'PASS ' . $label . "\n";
}
if (! str_contains(zvij_account_placeholder('kristali'), '100 kristalov') || ! str_contains(zvij_account_placeholder('kristali'), '= 1 €')) { throw new Exception('Missing exchange rate'); }
echo "PASS readable exchange rate\n";
// Synthetic visual fixture contains no real customer names, orders or balances.
$base = get_template_directory_uri();
$css = plugins_url('assets/account.css', WP_PLUGIN_DIR . '/zvij-core/zvij-core.php');
$html = '<!doctype html><html lang="sl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="' . $base . '/style.css"><link rel="stylesheet" href="' . $css . '?v=1.1"><body><main style="max-width:1100px;margin:24px auto;padding:16px"><div class="zv-acc-grid">';
foreach (array_slice($labels, 0, 5) as $label) {
    $hello = $label === 'pozdrav';
    $html .= '<section class="zv-acc-card' . ($hello ? ' zv-acc-card--wide zv-acc-hello' : '') . '">' . zvij_account_placeholder($label) . '<div class="zv-acc-body"><h2>' . ($hello ? 'Živijo, član' : $label) . '</h2><p>' . ($hello ? 'Tvoja stopnja, kristali in povabila na enem mestu.' : 'Pregled članstva in ugodnosti.') . '</p>' . ($hello ? '<nav class="zv-acc-links"><a>Naročila</a><a>Naslovi</a><a>Podrobnosti računa</a><a>Odjava</a></nav>' : '') . '</div></section>';
}
file_put_contents('/tmp/account-art-fixture.html', $html . '</div></main></body></html>');
