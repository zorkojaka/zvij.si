<?php
/**
 * ZVIJ-08 — Izdaja računov (start).
 *
 * Odločitev iz RELEASE_PLAN.md (MUST LAUNCH #8): za zagon zadošča WooCommerce
 * email z računom; pravi računovodski sistem pride kasneje. Ta modul obstoječi
 * WooCommerce email o naročilu dopolni z računsko glavo (prodajalec + št./datum
 * računa), tako da email kupcu služi kot račun. WooCommerce že izpiše postavke,
 * količine in znesek — tu dodamo le manjkajočo identifikacijo izdajatelja.
 *
 * To NI davčno/fiskalno zaporedno številčenje računov — številka računa je
 * številka naročila; formalni računovodski sistem je ločena, kasnejša naloga.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Statusi naročila, pri katerih email kupcu šteje kot račun.
 * on-hold = predračun (BACS/nakazilo), processing/ready/completed = plačano/v obdelavi.
 */
function zvij_invoice_statuses(): array {
    $statuses = ['processing', 'on-hold', 'completed'];
    if (defined('ZVIJ_ORDER_STATUS_READY')) {
        $statuses[] = ZVIJ_ORDER_STATUS_READY;
    }
    return apply_filters('zvij_invoice_statuses', $statuses);
}

/**
 * Podatki izdajatelja računa. Ime je fiksno (kot na odpremnem dokumentu),
 * naslov se bere iz WooCommerce nastavitev trgovine, TRR/davčna iz opcij
 * (Jaka jih vpiše kasneje — MUST LAUNCH #6). Prazne vrednosti se ne izpišejo.
 *
 * @return array{name:string,lines:string[]}
 */
function zvij_invoice_seller(): array {
    $name = (string) apply_filters('zvij_invoice_seller_name', get_option('zvij_invoice_seller_name', 'Zvij.si d.o.o.'));

    $address = trim((string) get_option('woocommerce_store_address', ''));
    $city = trim(
        trim((string) get_option('woocommerce_store_postcode', '') . ' ' . (string) get_option('woocommerce_store_city', ''))
    );
    $email = (string) apply_filters('zvij_invoice_seller_email', get_option('zvij_invoice_seller_email', 'zvijace@zvij.si'));
    $tax_id = trim((string) get_option('zvij_invoice_tax_id', '')); // davčna/ID za DDV
    $trr = trim((string) get_option('zvij_invoice_trr', ''));       // TRR za predračun/nakazilo

    $lines = [];
    if ($address !== '') {
        $lines[] = $address;
    }
    if ($city !== '') {
        $lines[] = $city;
    }
    if ($email !== '') {
        $lines[] = $email;
    }
    if ($tax_id !== '') {
        $lines[] = 'Davčna št.: ' . $tax_id;
    }
    if ($trr !== '') {
        $lines[] = 'TRR: ' . $trr;
    }

    return apply_filters('zvij_invoice_seller', ['name' => $name, 'lines' => $lines]);
}

/**
 * Opomba glede DDV. Trgovina trenutno nima obračuna davka (calc_taxes = no),
 * zato račun to jasno navede. Prilagodljivo prek opcije/filtra.
 */
function zvij_invoice_vat_note(): string {
    $default = (function_exists('wc_tax_enabled') && wc_tax_enabled()) ? '' : 'Cena je končna. DDV ni obračunan.';
    $note = (string) get_option('zvij_invoice_vat_note', $default);

    return (string) apply_filters('zvij_invoice_vat_note', $note);
}

/**
 * Datum izdaje računa: datum plačila, sicer datum naročila.
 */
function zvij_invoice_date(WC_Order $order): string {
    $date = $order->get_date_paid() ?: $order->get_date_created();

    return $date ? $date->date_i18n('j. n. Y') : date_i18n('j. n. Y');
}

/**
 * V email kupcu doda računsko glavo. Ne izpiše se v admin emaile (nova naročila)
 * niti pri statusih, ki niso račun (cancelled/failed/pending/refunded).
 *
 * @param WC_Order $order
 */
function zvij_invoice_render_email($order, bool $sent_to_admin, bool $plain_text, $email = null): void {
    if ($sent_to_admin || ! $order instanceof WC_Order) {
        return;
    }
    if (! in_array($order->get_status(), zvij_invoice_statuses(), true)) {
        return;
    }

    $seller = zvij_invoice_seller();
    $number = $order->get_order_number();
    $date = zvij_invoice_date($order);
    $vat_note = zvij_invoice_vat_note();

    if ($plain_text) {
        echo "\n" . str_repeat('=', 40) . "\n";
        echo 'RAČUN' . "\n";
        echo 'Račun št.: ' . $number . "\n";
        echo 'Datum izdaje: ' . $date . "\n\n";
        echo 'Izdajatelj: ' . $seller['name'] . "\n";
        foreach ($seller['lines'] as $line) {
            echo $line . "\n";
        }
        if ($vat_note !== '') {
            echo "\n" . $vat_note . "\n";
        }
        echo str_repeat('=', 40) . "\n\n";
        return;
    }

    $lines_html = '';
    foreach ($seller['lines'] as $line) {
        $lines_html .= esc_html($line) . '<br>';
    }

    echo '<div style="margin:0 0 24px;padding:16px 20px;border:1px solid #e0d9cf;border-radius:6px;background:#fbf8f3;font-family:inherit;">'
        . '<table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;">'
        . '<tr>'
        . '<td style="vertical-align:top;padding:0;">'
        . '<div style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#8a7f6e;">Račun</div>'
        . '<div style="font-size:15px;font-weight:700;color:#1e1a15;">Račun št. ' . esc_html($number) . '</div>'
        . '<div style="font-size:13px;color:#4a4335;">Datum izdaje: ' . esc_html($date) . '</div>'
        . '</td>'
        . '<td style="vertical-align:top;padding:0;text-align:right;font-size:12px;line-height:1.5;color:#4a4335;">'
        . '<strong style="color:#1e1a15;">' . esc_html($seller['name']) . '</strong><br>'
        . $lines_html
        . '</td>'
        . '</tr>'
        . '</table>'
        . ($vat_note !== '' ? '<div style="margin-top:10px;font-size:12px;color:#6b6250;">' . esc_html($vat_note) . '</div>' : '')
        . '</div>';
}

// Nad tabelo postavk: računska glava se pokaže pred specifikacijo naročila.
add_action('woocommerce_email_before_order_table', 'zvij_invoice_render_email', 20, 4);
