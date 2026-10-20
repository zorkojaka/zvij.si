<?php
/** Authenticated order workspace and read-only print documents. */
if (! defined('ABSPATH')) { exit; }

function zvij_order_workspace_url(int $id, string $document = ''): string {
    return add_query_arg(['page' => 'zvij-order-documents', 'order_id' => $id, 'document' => $document], admin_url('admin.php'));
}

function zvij_order_admin_email_links($order, bool $admin, bool $plain, $email = null): void {
    if (! $admin || ! $order instanceof WC_Order || ! $email || $email->id !== 'new_order') { return; }
    $links = [
        'Odpri naročilo in tisk dokumentov' => zvij_order_workspace_url($order->get_id()),
        'Uredi naročilo' => $order->get_edit_order_url(),
        'Pregled naročil' => admin_url('admin.php?page=zvij-dashboard'),
    ];
    if ($plain) {
        foreach ($links as $label => $url) { echo "\n" . $label . ': ' . esc_url_raw($url) . "\n"; }
        return;
    }
    echo '<div style="margin:24px 0;padding:16px;border:1px solid #ddd"><strong>Obdelava naročila</strong>';
    foreach ($links as $label => $url) {
        echo '<p><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></p>';
    }
    echo '<p>Za dostop se prijavi v administracijo Zvij.si.</p></div>';
}
add_action('woocommerce_email_before_order_table', 'zvij_order_admin_email_links', 5, 4);

add_action('admin_menu', function (): void {
    add_submenu_page(null, 'Dokumenti naročila', 'Dokumenti naročila', 'manage_woocommerce', 'zvij-order-documents', 'zvij_order_workspace');
});

add_action('admin_post_zvij_issue_invoice', function (): void {
    if (! current_user_can('manage_woocommerce')) { wp_die('Dostop ni dovoljen.', '', ['response' => 403]); }
    $id = absint($_POST['order_id'] ?? 0);
    check_admin_referer('zvij_issue_invoice_' . $id);
    $order = wc_get_order($id);
    if (! $order instanceof WC_Order || $order->has_status(['cancelled', 'failed', 'refunded', 'checkout-draft'])) {
        wp_die('Za to naročilo računa ni mogoče izdati.');
    }
    zvij_invoice_number($order);
    wp_safe_redirect(zvij_order_workspace_url($id, 'invoice'));
    exit;
});

add_action('woocommerce_admin_order_data_after_order_details', function ($order): void {
    if ($order instanceof WC_Order) {
        echo '<p class="form-field form-field-wide"><a class="button" href="' . esc_url(zvij_order_workspace_url($order->get_id())) . '">Dokumenti in tisk</a></p>';
    }
});

function zvij_order_workspace(): void {
    if (! current_user_can('manage_woocommerce')) { wp_die('Dostop ni dovoljen.', '', ['response' => 403]); }
    $order = wc_get_order(absint($_GET['order_id'] ?? 0));
    if (! $order instanceof WC_Order) { wp_die('Naročilo ne obstaja.', '', ['response' => 404]); }
    nocache_headers();
    $document = sanitize_key($_GET['document'] ?? '');
    $cod = $order->get_payment_method() === 'cod';
    $invoice = (string) $order->get_meta(ZVIJ_INVOICE_NUMBER_META);
    $title = $invoice !== '' ? 'Račun ' . $invoice : 'Predračun ' . $order->get_order_number();
    echo '<div class="wrap zv-order-workspace"><div class="zv-doc-toolbar"><h1>Naročilo #' . esc_html($order->get_order_number()) . '</h1>';
    $links = ['' => 'Pregled', 'invoice' => 'Račun', 'label' => 'Naslovna nalepka'];
    if ($cod) { $links['upn'] = 'UPN za povzetje'; }
    foreach ($links as $key => $label) {
        echo '<a class="button" href="' . esc_url(zvij_order_workspace_url($order->get_id(), $key)) . '">' . esc_html($label) . '</a> ';
    }
    echo '<a class="button" href="' . esc_url(zvij_order_print_url($order->get_id())) . '">Seznam za pakiranje</a> ';
    echo '<a class="button" href="' . esc_url($order->get_edit_order_url()) . '">Uredi naročilo</a> ';
    if ($document !== '') { echo '<button class="button button-primary" onclick="window.print()">Natisni</button>'; }
    if ($document === 'invoice' && $invoice === '' && ! $order->has_status(['cancelled', 'failed', 'refunded', 'checkout-draft'])) {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="zvij_issue_invoice"><input type="hidden" name="order_id" value="' . esc_attr((string) $order->get_id()) . '">';
        wp_nonce_field('zvij_issue_invoice_' . $order->get_id());
        echo '<p>To naročilo še nima številke računa. Ob izdaji prejme naslednjo zaporedno številko. Izdaja ne spremeni stanja plačila.</p><button class="button button-primary">Izdaj račun</button></form>';
    }
    echo '</div>';
    ?>
    <style>
    .zv-doc-toolbar .button{margin:8px 4px 16px 0}.zv-order-document{background:white;color:#111;max-width:850px;padding:32px;margin:20px 0;font-size:15px;line-height:1.6}.zv-order-document table{width:100%;border-collapse:collapse}.zv-order-document th,.zv-order-document td{text-align:left;border-bottom:1px solid #ddd;padding:10px}.zv-addresses{display:grid;grid-template-columns:1fr 1.5fr;gap:32px}.zv-addresses address{font-style:normal}.zv-label{max-width:140mm;overflow-wrap:anywhere}.zv-label .recipient{font-size:20px}.zv-order-document img{width:200px;height:200px}
    @media(max-width:600px){.zv-addresses{grid-template-columns:1fr}.zv-order-document{padding:16px}}
    @media print{@page{size:A4;margin:15mm}#adminmenumain,#wpadminbar,#wpfooter,.zv-doc-toolbar,.notice,.update-nag{display:none!important}html.wp-toolbar{padding-top:0!important}#wpcontent,#wpbody-content{margin:0!important;padding:0!important}.wrap{margin:0!important}.zv-order-document{margin:0;padding:0;max-width:none;box-shadow:none}.zv-label{width:140mm;max-width:100%;border:1px solid #111;padding:8mm}.zv-addresses{grid-template-columns:1fr 1.5fr}}
    </style>
    <style>
    .zv-invoice-head{display:flex;justify-content:space-between;align-items:center;gap:32px;border-bottom:2px solid #111;padding-bottom:24px;margin-bottom:28px}.zv-invoice-logo{width:110px;height:100px;object-fit:contain}.zv-invoice-head h2{font-size:28px;margin:0 0 8px;line-height:1.2}.zv-invoice-head p{margin:4px 0}.zv-order-document .zv-addresses{margin-bottom:28px}.zv-invoice-foot{border-top:1px solid #ddd;padding-top:16px;margin-top:28px;color:#555;font-size:12px}.zv-order-document table td:last-child{white-space:nowrap}.zv-order-document table tbody tr:last-child{font-weight:700}
    @media(max-width:600px){.zv-invoice-head{gap:16px}.zv-invoice-head h2{font-size:22px}}
    </style>
    <?php
    echo '<section class="zv-order-document' . ($document === 'label' ? ' zv-label' : '') . '">';
    if ($document === '') {
        echo '<h2>' . esc_html(wc_get_order_status_name($order->get_status())) . '</h2>';
        echo '<p>Plačilo: ' . esc_html($order->get_payment_method_title()) . ' | Skupaj: ' . wp_kses_post($order->get_formatted_order_total()) . '</p>';
        echo '<p>Izberi dokument zgoraj in pritisni Natisni. Povezave v e-pošti ostanejo uporabne tudi pozneje, po prijavi.</p>';
        echo '<p>Naslovna nalepka vsebuje oba naslova, ne nadomešča prevoznikove nalepke za sledenje. UPN je plačilni list s QR kodo, ne obrazec za predtiskan UPN.</p>';
    } elseif ($document === 'label') {
        zvij_order_document_addresses($order, true);
        echo '<p>Naročilo #' . esc_html($order->get_order_number()) . '</p>';
        if ($cod) { echo '<p><strong>Odkupnina: ' . wp_kses_post($order->get_formatted_order_total()) . '</strong></p>'; }
    } elseif ($document === 'invoice') {
        echo '<header class="zv-invoice-head"><img class="zv-invoice-logo" src="' . esc_url(get_template_directory_uri() . '/assets/images/brand/logo-zvij-outline.svg') . '" alt="Zvij.si"><div><h2>' . esc_html($title) . '</h2><p>Datum: ' . esc_html(zvij_invoice_date($order)) . '</p><p>Naročilo #' . esc_html($order->get_order_number()) . '</p></div></header>';
        zvij_order_document_addresses($order, false);
        echo '<table><thead><tr><th>Izdelek</th><th>Količina</th><th>Znesek</th></tr></thead><tbody>';
        foreach ($order->get_items() as $item) {
            echo '<tr><td>' . esc_html($item->get_name()) . '</td><td>' . esc_html((string) $item->get_quantity()) . '</td><td>' . wp_kses_post($order->get_formatted_line_subtotal($item)) . '</td></tr>';
        }
        foreach ($order->get_order_item_totals() as $total) {
            echo '<tr><th colspan="2">' . esc_html($total['label']) . '</th><td>' . wp_kses_post($total['value']) . '</td></tr>';
        }
        echo '</tbody></table><p>' . esc_html(zvij_invoice_vat_note()) . '</p><p>Način plačila: ' . esc_html($order->get_payment_method_title()) . '</p><footer class="zv-invoice-foot">Hvala za naročilo. Zvij.si</footer>';
    } elseif ($document === 'upn' && $cod) {
        echo '<h2>UPN / podatki za odkupnino</h2>';
        zvij_order_document_addresses($order, false);
        echo '<p>Znesek: <strong>' . wp_kses_post($order->get_formatted_order_total()) . '</strong><br>IBAN: ' . esc_html(get_option('zvij_invoice_trr', '')) . '<br>Sklic: ' . esc_html(zvij_upnqr_reference($order)) . '<br>Namen: Nakup Zvij.si - narocilo ' . esc_html($order->get_order_number()) . '</p>';
        if (trim((string) get_option('zvij_invoice_trr', '')) === '' || ! zvij_upnqr_enabled()) {
            echo '<p><strong>UPN ni pripravljen: preveri IBAN trgovine in knjižnico za UPN QR.</strong></p>';
        } else {
            $path = wp_tempnam('zvij-upn');
            try {
                $qr = zvij_upnqr_build($order);
                if ($path && zvij_upnqr_render_png($qr->serializeContents(), $path, 480)) {
                    echo '<img alt="UPN QR" src="data:image/png;base64,' . esc_attr(base64_encode(file_get_contents($path))) . '">';
                } else { echo '<p>QR kode ni mogoče pripraviti.</p>'; }
            } catch (Throwable $error) { echo '<p>UPN QR ni mogoče pripraviti. Preveri podatke prejemnika.</p>'; }
            finally { if ($path && is_file($path)) { unlink($path); } }
        }
        echo '<p>Za plačilo po povzetju. Ne plačuj dodatno, če je prevoznik odkupnino že prevzel.</p>';
    } else { echo '<p>Dokument ni na voljo za to naročilo.</p>'; }
    echo '</section></div>';
}

function zvij_order_document_addresses(WC_Order $order, bool $shipping): void {
    $seller = zvij_invoice_seller();
    $address = $shipping ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address();
    if (! $address) { $address = $order->get_formatted_billing_address(); }
    echo '<div class="zv-addresses"><address><strong>' . ($shipping ? 'Pošiljatelj' : 'Izdajatelj') . '</strong><br>' . esc_html($seller['name']) . '<br>';
    $lines = $shipping ? [get_option('woocommerce_store_address', ''), get_option('woocommerce_store_address_2', ''), trim(get_option('woocommerce_store_postcode', '') . ' ' . get_option('woocommerce_store_city', ''))] : $seller['lines'];
    echo implode('<br>', array_map('esc_html', array_filter($lines)));
    echo '</address><address class="recipient"><strong>' . ($shipping ? 'Prejemnik' : 'Kupec / plačnik') . '</strong><br>' . wp_kses_post($address) . '</address></div>';
}
