<?php
/**
 * ZVIJ — UPN QR ("slikaj in plačaj") za plačilo z nakazilom na TRR.
 *
 * Za naročila, ki čakajo na nakazilo (plačilni način BACS, status on-hold/pending),
 * prikaže standardni slovenski UPN QR nalog s podatki prejemnika (Zvij.si, TRR),
 * zneskom in sklicem. Kupec ga s telefonom skenira in plača. QR se pokaže na
 * strani »naročilo prejeto«, v pregledu naročila in v predračunskem emailu.
 *
 * QR sestavi in izriše preverjena knjižnica datalinx/php-upn-qr-generator
 * (bacon/bacon-qr-code) — ta poskrbi za pravilen format polj, ISO-8859-2
 * kodiranje in kontrolno vsoto po ZBS specifikaciji. PNG se generira prek Imagick
 * in shrani v uploads (regenerira se, če se znesek/sklic spremenita).
 */

if (! defined('ABSPATH')) {
    exit;
}

$zvij_upnqr_autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_readable($zvij_upnqr_autoload)) {
    require_once $zvij_upnqr_autoload;
}

/** Ali je UPN QR knjižnica na voljo. */
function zvij_upnqr_enabled(): bool {
    return class_exists('DataLinx\\PhpUpnQrGenerator\\UPNQR');
}

/** Ali za to naročilo prikažemo UPN QR (nakazilo na TRR, še neplačano). */
function zvij_upnqr_applies(WC_Order $order): bool {
    $applies = zvij_upnqr_enabled()
        && (float) $order->get_total() > 0
        && $order->get_payment_method() === 'bacs'
        && $order->has_status(['on-hold', 'pending'])
        && trim((string) get_option('zvij_invoice_trr', '')) !== '';

    return (bool) apply_filters('zvij_upnqr_applies', $applies, $order);
}

/** Sklic za nakazilo (model SI00 + št. naročila). */
function zvij_upnqr_reference(WC_Order $order): string {
    return 'SI00 ' . $order->get_order_number();
}

/**
 * Sestavi UPN QR objekt za naročilo. Polja plačnika ostanejo prazna — izpolni
 * jih kupčeva banka. Namen je ASCII (brez šumnikov) za maksimalno združljivost.
 *
 * @return \DataLinx\PhpUpnQrGenerator\UPNQR
 */
function zvij_upnqr_build(WC_Order $order) {
    $city = trim((string) get_option('woocommerce_store_postcode', '') . ' ' . (string) get_option('woocommerce_store_city', ''));

    $qr = new \DataLinx\PhpUpnQrGenerator\UPNQR();
    $qr->setRecipientIban((string) get_option('zvij_invoice_trr', ''))
        ->setRecipientName(mb_substr((string) get_option('zvij_invoice_seller_name', 'ZVIJ.si d.o.o.'), 0, 33))
        ->setRecipientStreetAddress(mb_substr(trim((string) get_option('woocommerce_store_address', '')), 0, 33))
        ->setRecipientCity(mb_substr($city, 0, 33))
        ->setRecipientReference(zvij_upnqr_reference($order))
        ->setAmount(round((float) $order->get_total(), 2))
        ->setPurposeCode('OTHR')
        ->setPaymentPurpose(mb_substr('Nakup Zvij.si - narocilo ' . $order->get_order_number(), 0, 42));

    return $qr;
}

/** Pot in URL do PNG datoteke QR kode za naročilo (ključ vključuje znesek/sklic/TRR). */
function zvij_upnqr_png_paths(WC_Order $order): array {
    $uploads = wp_upload_dir();
    $key = substr(md5($order->get_total() . '|' . zvij_upnqr_reference($order) . '|' . get_option('zvij_invoice_trr', '')), 0, 8);
    $name = 'order-' . $order->get_id() . '-' . $key . '.png';

    return [
        trailingslashit($uploads['basedir']) . 'zvij-upnqr/' . $name,
        trailingslashit($uploads['baseurl']) . 'zvij-upnqr/' . $name,
    ];
}

/**
 * Izriše QR niz v PNG prek GD. (Imagick v tem okolju nima format-delegatov, zato
 * ne uporabljamo knjižničnega generateQrCode(), ampak matriko iz bacon Encoderja
 * sami narišemo z GD.) Nivo M, ISO-8859-2, brez ECI — po ZBS UPN specifikaciji.
 */
function zvij_upnqr_render_png(string $content, string $path, int $size = 480): bool {
    $qr = \BaconQrCode\Encoder\Encoder::encode(
        $content,
        \BaconQrCode\Common\ErrorCorrectionLevel::M(),
        'ISO-8859-2',
        null,
        false
    );
    $matrix = $qr->getMatrix();
    $count = $matrix->getWidth();
    if ($count < 1) {
        return false;
    }

    $margin = 4; // tiha cona (modulov)
    $modules = $count + 2 * $margin;
    $scale = max(1, (int) floor($size / $modules));
    $px = $modules * $scale;

    $img = imagecreatetruecolor($px, $px);
    $white = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 0, 0, 0);
    imagefilledrectangle($img, 0, 0, $px - 1, $px - 1, $white);

    for ($y = 0; $y < $count; $y++) {
        for ($x = 0; $x < $count; $x++) {
            if ($matrix->get($x, $y)) {
                $x0 = ($x + $margin) * $scale;
                $y0 = ($y + $margin) * $scale;
                imagefilledrectangle($img, $x0, $y0, $x0 + $scale - 1, $y0 + $scale - 1, $black);
            }
        }
    }

    $ok = imagepng($img, $path);
    imagedestroy($img);

    return (bool) $ok;
}

/** Vrne URL do PNG QR kode (jo generira, če še ne obstaja). Prazen niz ob napaki. */
function zvij_upnqr_url(WC_Order $order): string {
    if (! zvij_upnqr_enabled()) {
        return '';
    }
    [$path, $url] = zvij_upnqr_png_paths($order);

    if (! file_exists($path)) {
        $dir = dirname($path);
        if (! file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        try {
            $content = zvij_upnqr_build($order)->serializeContents();
            if (! zvij_upnqr_render_png($content, $path, 480)) {
                return '';
            }
        } catch (\Throwable $e) {
            if (function_exists('wc_get_logger')) {
                wc_get_logger()->error('UPN QR: ' . $e->getMessage(), ['source' => 'zvij-upnqr']);
            }
            return '';
        }
    }

    return $url;
}

/** Podatki za nakazilo kot polja (za izpis pod QR). */
function zvij_upnqr_details(WC_Order $order): array {
    return [
        'Prejemnik' => (string) get_option('zvij_invoice_seller_name', 'ZVIJ.si d.o.o.'),
        'IBAN'      => (string) get_option('zvij_invoice_trr', ''),
        'Sklic'     => zvij_upnqr_reference($order),
        'Znesek'    => html_entity_decode(wp_strip_all_tags(wc_price($order->get_total())), ENT_QUOTES, 'UTF-8'),
    ];
}

/** Prikaz QR bloka na spletni strani (naročilo prejeto / pregled naročila). */
function zvij_upnqr_render_page($order_id): void {
    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order || ! zvij_upnqr_applies($order)) {
        return;
    }
    $url = zvij_upnqr_url($order);
    if ($url === '') {
        return;
    }

    echo '<section class="zvij-upnqr" style="margin:1.5rem 0;padding:1.25rem;border:1px solid #e0d9cf;border-radius:8px;background:#fbf8f3;display:flex;gap:1.25rem;flex-wrap:wrap;align-items:center;">';
    echo '<img src="' . esc_url($url) . '" alt="' . esc_attr__('UPN QR koda za plačilo', 'zvij-core') . '" width="200" height="200" style="width:200px;height:200px;flex:0 0 auto;background:#fff;border-radius:6px;">';
    echo '<div style="min-width:220px;flex:1;">';
    echo '<h3 style="margin:0 0 .5rem;font-size:1.05rem;">' . esc_html__('Plačaj z nakazilom — skeniraj UPN QR', 'zvij-core') . '</h3>';
    echo '<p style="margin:0 0 .75rem;color:#6b6250;font-size:.9rem;">' . esc_html__('Odpri mobilno banko, izberi »Skeniraj QR« in potrdi plačilo. Polja se izpolnijo samodejno.', 'zvij-core') . '</p>';
    echo '<table style="border-collapse:collapse;font-size:.92rem;">';
    foreach (zvij_upnqr_details($order) as $label => $value) {
        echo '<tr><td style="padding:2px 12px 2px 0;color:#8a7f6e;">' . esc_html($label) . '</td><td style="padding:2px 0;font-weight:600;color:#1e1a15;">' . esc_html($value) . '</td></tr>';
    }
    echo '</table></div></section>';
}
add_action('woocommerce_thankyou', 'zvij_upnqr_render_page', 15);
add_action('woocommerce_view_order', 'zvij_upnqr_render_page', 15);

/** Prikaz QR / podatkov za nakazilo v emailu kupcu (predračun). */
function zvij_upnqr_render_email($order, bool $sent_to_admin, bool $plain_text, $email = null): void {
    if ($sent_to_admin || ! $order instanceof WC_Order || ! zvij_upnqr_applies($order)) {
        return;
    }
    $details = zvij_upnqr_details($order);

    if ($plain_text) {
        echo "\n" . 'ZA PLAČILO (UPN QR / nakazilo):' . "\n";
        foreach ($details as $label => $value) {
            echo $label . ': ' . $value . "\n";
        }
        return;
    }

    $url = zvij_upnqr_url($order);

    echo '<div style="margin:0 0 24px;padding:16px 20px;border:1px solid #e0d9cf;border-radius:6px;background:#fbf8f3;">';
    echo '<div style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#8a7f6e;margin-bottom:8px;">' . esc_html__('Za plačilo — skeniraj UPN QR', 'zvij-core') . '</div>';
    echo '<table cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;"><tr>';
    if ($url !== '') {
        echo '<td style="vertical-align:top;padding:0 18px 0 0;"><img src="' . esc_url($url) . '" alt="UPN QR" width="150" height="150" style="display:block;width:150px;height:150px;background:#fff;border-radius:6px;"></td>';
    }
    echo '<td style="vertical-align:top;font-size:13px;line-height:1.6;color:#4a4335;">';
    foreach ($details as $label => $value) {
        echo '<div><span style="color:#8a7f6e;">' . esc_html($label) . ':</span> <strong style="color:#1e1a15;">' . esc_html($value) . '</strong></div>';
    }
    echo '</td></tr></table></div>';
}
add_action('woocommerce_email_before_order_table', 'zvij_upnqr_render_email', 25, 4);
