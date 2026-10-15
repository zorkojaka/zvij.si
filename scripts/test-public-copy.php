<?php
if (PHP_SAPI !== 'cli') { exit(1); }
require dirname(__DIR__) . '/wp-load.php';
$passed = 0;
function copy_check(bool $condition, string $label): void {
    global $passed;
    if (! $condition) { throw new RuntimeException($label); }
    $passed++;
    echo 'PASS ' . $label . PHP_EOL;
}
copy_check(zvij_public_short_dashes('1–2 dni — doma') === '1-2 dni - doma', 'Unicode en and em dash become short hyphens');
copy_check(zvij_public_short_dashes('−3,00 € / -12 / ZK-ABC') === '−3,00 € / -12 / ZK-ABC', 'Math minus, negative values and codes are preserved');
copy_check(zvij_public_copy_html('<p>8,00 € &ndash; 39,90 € &mdash; za člane</p>') === '<p>8,00 € - 39,90 € - za člane</p>', 'HTML entities in visible text are normalized');
copy_check(zvij_public_copy_html('<p>&#8211; &#x2014; &#8212; &#x2013;</p>') === '<p>- - - -</p>', 'Decimal and hexadecimal entities are normalized');
copy_check(zvij_public_copy_html('<title>O nas — Zvij.si</title>') === '<title>O nas - Zvij.si</title>', 'Document title is normalized');
$html = '<a href="/?ref=a—b" data-info="a–b" title="Opis — dodatno" aria-label="Kupi — zdaj">Izdelek — opis</a>';
$out = zvij_public_copy_html($html);
copy_check(str_contains($out, 'href="/?ref=a—b"') && str_contains($out, 'data-info="a–b"'), 'URLs and machine data attributes are not changed');
copy_check(str_contains($out, 'title="Opis - dodatno"') && str_contains($out, 'aria-label="Kupi - zdaj"'), 'Visible and accessible labels are normalized');
$protected = '<script>window.data={"name":"a—b","value":"1–2"};</script><style>.x::after{content:"—"}</style><textarea>moja — opomba</textarea><input value="moja — opomba"><!-- koment — ostane -->';
copy_check(zvij_public_copy_html($protected) === $protected, 'Scripts, styles, form values and comments are unchanged');
copy_check(zvij_public_copy_html('<p>To &amp; ono — &lt;izdelek&gt;</p>') === '<p>To &amp; ono - &lt;izdelek&gt;</p>', 'HTML escaping remains intact');
$fragments = apply_filters('woocommerce_add_to_cart_fragments', ['.notice' => '<p>Dodano — hvala</p>']);
copy_check($fragments['.notice'] === '<p>Dodano - hvala</p>', 'AJAX cart fragments are normalized');
echo 'PASS TOTAL ' . $passed . PHP_EOL;
