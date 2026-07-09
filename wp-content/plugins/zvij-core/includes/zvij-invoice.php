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
 * Št. računa: zaporedno številčenje v formatu ZZ/MM/LLLL (zaporedna/mesec/leto).
 * Številka se naročilu dodeli enkrat (ob prvem računskem emailu) in se shrani v
 * meta naročila, tako da se ob ponovnih pošiljanjih ne spremeni. Zaporedni
 * števec se hrani v opciji `zvij_invoice_next_seq` (Jaka ga lahko ročno popravi
 * v WooCommerce → Nastavitve → Splošno, npr. po računu izdanem izven sistema) in
 * se ob prehodu v novo koledarsko leto samodejno vrne na 1.
 *
 * To ni poln fiskalni/računovodski sistem (davčno potrjevanje računov ipd.) —
 * ta ostaja ločena, kasnejša naloga.
 */

if (! defined('ABSPATH')) {
    exit;
}

const ZVIJ_INVOICE_NEXT_SEQ_OPTION = 'zvij_invoice_next_seq';
const ZVIJ_INVOICE_SEQ_YEAR_OPTION = 'zvij_invoice_seq_year';
const ZVIJ_INVOICE_NUMBER_META = '_zvij_invoice_number';
const ZVIJ_INVOICE_SEQ_META = '_zvij_invoice_seq';

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
 * Ali se zaporedni števec ob novem letu vrne na 1 (privzeto da).
 */
function zvij_invoice_reset_yearly(): bool {
    return (bool) apply_filters('zvij_invoice_reset_yearly', true);
}

/**
 * Št. računa v formatu ZZ/MM/LLLL. Če je naročilu že dodeljena, jo vrne;
 * sicer dodeli naslednjo zaporedno in jo shrani (idempotentno).
 */
function zvij_invoice_number(WC_Order $order): string {
    $existing = (string) $order->get_meta(ZVIJ_INVOICE_NUMBER_META);
    if ($existing !== '') {
        return $existing;
    }

    return zvij_invoice_assign_number($order);
}

/**
 * Dodeli in shrani naslednjo zaporedno št. računa za naročilo.
 * Leto/mesec se vzameta iz datuma izdaje (plačilo, sicer nastanek naročila).
 */
function zvij_invoice_assign_number(WC_Order $order): string {
    $date = $order->get_date_paid() ?: $order->get_date_created();
    $year = (int) ($date ? $date->date('Y') : date('Y'));
    $month = (int) ($date ? $date->date('n') : date('n'));

    $seq_year = (int) get_option(ZVIJ_INVOICE_SEQ_YEAR_OPTION, 0);
    $next = (int) get_option(ZVIJ_INVOICE_NEXT_SEQ_OPTION, 1);

    if (zvij_invoice_reset_yearly() && $seq_year !== 0 && $year > $seq_year) {
        $next = 1;
    }

    $seq = max(1, $next);
    $number = sprintf('%d/%02d/%04d', $seq, $month, $year);

    $order->update_meta_data(ZVIJ_INVOICE_NUMBER_META, $number);
    $order->update_meta_data(ZVIJ_INVOICE_SEQ_META, $seq);
    $order->save();

    update_option(ZVIJ_INVOICE_NEXT_SEQ_OPTION, $seq + 1);
    update_option(ZVIJ_INVOICE_SEQ_YEAR_OPTION, $year);

    return $number;
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
    $number = zvij_invoice_number($order);
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

/**
 * WooCommerce → Nastavitve → Splošno: polje za ročni popravek zaporednega
 * števca računov (npr. po računu izdanem izven sistema).
 */
function zvij_invoice_register_settings(array $settings): array {
    $fields = [
        [
            'type' => 'title',
            'name' => __('Zvij.si — računi', 'zvij-core'),
            'desc' => __('Zaporedno številčenje računov v formatu ZZ/MM/LLLL (zaporedna/mesec/leto). Ob prehodu v novo leto se števec samodejno vrne na 1.', 'zvij-core'),
            'id'   => 'zvij_invoice_options',
        ],
        [
            'type'    => 'number',
            'name'    => __('Naslednja zaporedna št. računa', 'zvij-core'),
            'desc'    => __('Zaporedna številka, ki jo dobi naslednji izdani račun. Ročno popravi, če izdaš račun izven sistema, da ostane zaporedje neprekinjeno.', 'zvij-core'),
            'desc_tip' => true,
            'id'      => ZVIJ_INVOICE_NEXT_SEQ_OPTION,
            'default' => 1,
            'css'     => 'width:90px;',
            'custom_attributes' => ['min' => '1', 'step' => '1'],
        ],
        [
            'type' => 'sectionend',
            'id'   => 'zvij_invoice_options',
        ],
    ];

    return array_merge($settings, $fields);
}
add_filter('woocommerce_get_settings_general', 'zvij_invoice_register_settings');

/**
 * Prikaz dodeljene št. računa na strani naročila v adminu (samo za referenco).
 */
function zvij_invoice_show_number_admin($order): void {
    if (! $order instanceof WC_Order) {
        return;
    }
    $number = (string) $order->get_meta(ZVIJ_INVOICE_NUMBER_META);
    if ($number === '') {
        return;
    }
    echo '<p class="form-field form-field-wide"><strong>' . esc_html__('Št. računa', 'zvij-core') . ':</strong> ' . esc_html($number) . '</p>';
}
add_action('woocommerce_admin_order_data_after_order_details', 'zvij_invoice_show_number_admin');
