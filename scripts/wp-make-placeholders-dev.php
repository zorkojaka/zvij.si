<?php
/**
 * Ustvari OZNAČENE placeholder slike za izdelke brez fotografije in jih
 * nastavi kot featured image (wp eval-file, idempotentno — preskoči izdelke,
 * ki featured sliko že imajo, razen če je obstoječa naš placeholder).
 *
 * Placeholder: 1200×1200 JPEG v brand tonih s črtkanim okvirjem, imenom
 * izdelka in oznako »SLIKA V PRIPRAVI« — da je na strani takoj jasno, kaj je
 * začasno. Prave/AI slike po docs/SLIKE_PRODUKTOV.md in
 * data/slike/NAVODILA-ZA-AI-SLIKE.md te placeholderje nadomestijo.
 */

if (! defined('ABSPATH')) {
    exit;
}

$font = ABSPATH . 'wp-content/uploads/DMSans-Bold.ttf';
if (! file_exists($font)) {
    echo "NAPAKA: pisava ni najdena\n";
    return;
}

/** Izdelki, ki potrebujejo placeholder (ID => vloga za podnapis). */
$targets = [
    217 => 'Grinder',
    216 => 'Grinder',
    332 => 'Rolice',
    333 => 'Rolice',
    226 => 'Setup dodatek',
];

require_once ABSPATH . 'wp-admin/includes/image.php';

$upload = wp_upload_dir();
$dir = trailingslashit($upload['basedir']) . 'zvij-placeholderji';
wp_mkdir_p($dir);

foreach ($targets as $product_id => $role) {
    $product = wc_get_product($product_id);
    if (! $product) {
        echo "preskocim {$product_id}: ni izdelka\n";
        continue;
    }

    $existing_thumb = (int) get_post_thumbnail_id($product_id);
    if ($existing_thumb && get_post_meta($existing_thumb, '_zvij_placeholder', true) === '') {
        echo "preskocim {$product_id}: ima pravo sliko\n";
        continue;
    }

    $title = $product->get_name();

    $img = imagecreatetruecolor(1200, 1200);

    // navpicni gradient med brand kremastima tonoma
    for ($y = 0; $y < 1200; $y++) {
        $t = $y / 1200;
        $r = (int) round(0xF4 + (0xFF - 0xF4) * $t);
        $g = (int) round(0xEA + (0xF8 - 0xEA) * $t);
        $b = (int) round(0xDB + (0xEC - 0xDB) * $t);
        imagefilledrectangle($img, 0, $y, 1200, $y + 1, imagecolorallocate($img, $r, $g, $b));
    }

    $text = imagecolorallocate($img, 0x27, 0x1E, 0x14);
    $accent = imagecolorallocate($img, 0xC8, 0x93, 0x4E);
    $muted = imagecolorallocate($img, 0x8A, 0x7F, 0x6E);

    // crtkan okvir — vizualna oznaka placeholderja
    imagesetthickness($img, 5);
    $dash = 26;
    for ($x = 60; $x < 1140; $x += $dash * 2) {
        imageline($img, $x, 60, min($x + $dash, 1140), 60, $accent);
        imageline($img, $x, 1140, min($x + $dash, 1140), 1140, $accent);
    }
    for ($y = 60; $y < 1140; $y += $dash * 2) {
        imageline($img, 60, $y, 60, min($y + $dash, 1140), $accent);
        imageline($img, 1140, $y, 1140, min($y + $dash, 1140), $accent);
    }

    // vloga (kicker)
    $kicker = mb_strtoupper($role);
    $bb = imagettfbbox(30, 0, $font, $kicker);
    imagettftext($img, 30, 0, (int) ((1200 - ($bb[2] - $bb[0])) / 2), 470, $accent, $font, $kicker);

    // ime izdelka, lomljeno na ~18 znakov
    $lines = explode("\n", wordwrap($title, 18, "\n", true));
    $size = count($lines) > 2 ? 56 : 68;
    $line_h = (int) ($size * 1.35);
    $y0 = 580 - (int) ((count($lines) - 1) * $line_h / 2);
    foreach ($lines as $i => $line) {
        $bb = imagettfbbox($size, 0, $font, $line);
        imagettftext($img, $size, 0, (int) ((1200 - ($bb[2] - $bb[0])) / 2), $y0 + $i * $line_h, $text, $font, $line);
    }

    // znacka SLIKA V PRIPRAVI
    $badge = 'SLIKA V PRIPRAVI';
    $bb = imagettfbbox(34, 0, $font, $badge);
    $bw = $bb[2] - $bb[0];
    $bx = (int) ((1200 - $bw) / 2);
    imagefilledrectangle($img, $bx - 40, 760, $bx + $bw + 40, 850, $accent);
    imagettftext($img, 34, 0, $bx, 818, imagecolorallocate($img, 0xFF, 0xF8, 0xEC), $font, $badge);

    $note = 'AI placeholder - prava slika po NAVODILA-ZA-AI-SLIKE.md';
    $bb = imagettfbbox(20, 0, $font, $note);
    imagettftext($img, 20, 0, (int) ((1200 - ($bb[2] - $bb[0])) / 2), 1095, $muted, $font, $note);

    $file = $dir . '/' . $product->get_slug() . '-placeholder.jpg';
    imagejpeg($img, $file, 85);
    imagedestroy($img);

    // pripni kot featured image
    $attachment_id = wp_insert_attachment([
        'post_mime_type' => 'image/jpeg',
        'post_title' => $title . ' (placeholder)',
        'post_status' => 'inherit',
    ], $file);
    if (is_wp_error($attachment_id) || ! $attachment_id) {
        echo "NAPAKA pri {$product_id}\n";
        continue;
    }
    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file));
    update_post_meta($attachment_id, '_zvij_placeholder', '1');

    if ($existing_thumb) {
        wp_delete_attachment($existing_thumb, true);
    }
    set_post_thumbnail($product_id, $attachment_id);
    update_post_meta($product_id, '_zvij_image_kind', 'temporary_mockup');
    update_post_meta($product_id, '_zvij_final_photo_pending', 'yes');

    echo "OK {$product_id} {$product->get_slug()} -> attachment {$attachment_id}\n";
}
