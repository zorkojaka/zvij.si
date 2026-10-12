<?php
/** Invitation analytics; only shop managers can see customer identities. */
if (! defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function (): void {
    add_submenu_page('zvij-dashboard', 'Povabila in kristali', 'Povabila in kristali', 'manage_woocommerce', 'zvij-invitations', 'zvij_invitation_admin');
});

function zvij_invitation_type_label(string $type): string {
    $labels = [
        'earn' => 'Nakup', 'redeem' => 'Poraba', 'refund' => 'Vračilo porabe',
        'adjust' => 'Popravek / storno', 'expire' => 'Potek', 'referral' => 'Staro povabilo (fiksna nagrada)',
        'referral_bonus' => 'Povabilo: 10 %', 'referral_reversal' => 'Storno bonusa povabila',
    ];
    return $labels[$type] ?? $type;
}

function zvij_invitation_admin_url(array $args = []): string {
    return add_query_arg(array_merge(['page' => 'zvij-invitations'], $args), admin_url('admin.php'));
}

function zvij_invitation_admin(): void {
    if (! current_user_can('manage_woocommerce')) {
        return;
    }
    global $wpdb;
    $members = zvij_membership_table();
    $invitations = zvij_invitation_table();
    $ledger = zvij_credit_table();
    $period = in_array((string) ($_GET['period'] ?? '30'), ['7', '30', 'all'], true) ? (string) ($_GET['period'] ?? '30') : '30';
    $since = $period === 'all' ? '1970-01-01 00:00:00' : wp_date('Y-m-d H:i:s', time() - (int) $period * DAY_IN_SECONDS);
    $email = sanitize_email(wp_unslash((string) ($_GET['member'] ?? '')));
    $type = sanitize_key((string) ($_GET['kind'] ?? ''));
    if (! in_array($type, ['earn', 'redeem', 'refund', 'adjust', 'expire', 'referral', 'referral_bonus', 'referral_reversal'], true)) {
        $type = '';
    }
    $page = max(1, absint($_GET['journal_page'] ?? 1));
    $rank_page = max(1, absint($_GET['rank_page'] ?? 1));
    $friend_page = max(1, absint($_GET['friend_page'] ?? 1));
    $base = ['period' => $period, 'member' => $email, 'kind' => $type];
    $new_friends = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$invitations} WHERE created_at >= %s", $since));
    $totals = $wpdb->get_row($wpdb->prepare("SELECT COALESCE(SUM(CASE WHEN type = 'referral_bonus' THEN amount ELSE 0 END),0) AS awarded, COALESCE(SUM(CASE WHEN type = 'referral_reversal' THEN amount ELSE 0 END),0) AS reversed, COUNT(DISTINCT CASE WHEN type = 'referral_bonus' THEN order_id END) AS orders FROM {$ledger} WHERE created_at >= %s", $since), ARRAY_A);
    $sources = $wpdb->get_results($wpdb->prepare("SELECT type, COUNT(*) AS entries, COALESCE(SUM(amount),0) AS amount FROM {$ledger} WHERE created_at >= %s GROUP BY type ORDER BY type", $since), ARRAY_A);
    $channels = $wpdb->get_results($wpdb->prepare("SELECT channel, COUNT(*) AS friends FROM {$invitations} WHERE created_at >= %s GROUP BY channel ORDER BY friends DESC", $since), ARRAY_A);
    $rank_count = (int) $wpdb->get_var("SELECT COUNT(DISTINCT owner_member_id) FROM {$invitations}");
    $rank = $wpdb->get_results($wpdb->prepare("SELECT m.id, m.name, m.email,
        (SELECT COUNT(*) FROM {$invitations} i WHERE i.owner_member_id = m.id) AS friends,
        (SELECT COUNT(DISTINCT i.friend_email) FROM {$invitations} i JOIN {$ledger} e ON e.member_email = i.friend_email AND e.type = 'earn' AND e.created_at >= i.created_at WHERE i.owner_member_id = m.id) AS buyers,
        (SELECT COALESCE(SUM(amount),0) FROM {$ledger} e WHERE e.member_email = m.email AND e.type = 'referral_bonus' AND e.created_at >= %s) AS awarded,
        (SELECT COALESCE(SUM(amount),0) FROM {$ledger} e WHERE e.member_email = m.email AND e.type = 'referral_reversal' AND e.created_at >= %s) AS reversed,
        (SELECT COALESCE(SUM(amount),0) FROM {$ledger} e WHERE e.member_email = m.email) AS balance
        FROM {$members} m WHERE EXISTS (SELECT 1 FROM {$invitations} i WHERE i.owner_member_id = m.id)
        ORDER BY friends DESC, m.id ASC LIMIT 20 OFFSET %d", $since, $since, ($rank_page - 1) * 20), ARRAY_A);
    $where = $wpdb->prepare('created_at >= %s', $since);
    if ($email !== '') {
        $where .= $wpdb->prepare(' AND member_email = %s', $email);
    }
    if ($type !== '') {
        $where .= $wpdb->prepare(' AND type = %s', $type);
    }
    $journal_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$ledger} WHERE {$where}");
    $journal = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$ledger} WHERE {$where} ORDER BY id DESC LIMIT 30 OFFSET %d", ($page - 1) * 30), ARRAY_A);
    $selected = $email !== '' ? zvij_membership_find_by_email($email) : null;
    $friends = [];
    $friend_count = 0;
    if ($selected) {
        $friend_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$invitations} WHERE owner_member_id = %d", $selected['id']));
        $friends = $wpdb->get_results($wpdb->prepare("SELECT i.*,
            (SELECT COUNT(DISTINCT e.order_id) FROM {$ledger} e WHERE e.type = 'referral_bonus' AND e.related_email = i.friend_email AND e.member_email = %s) AS orders,
            (SELECT COALESCE(SUM(e.amount),0) FROM {$ledger} e WHERE e.type IN ('referral_bonus','referral_reversal') AND e.related_email = i.friend_email AND e.member_email = %s) AS bonus
            FROM {$invitations} i WHERE owner_member_id = %d ORDER BY i.id DESC LIMIT 20 OFFSET %d", $email, $email, $selected['id'], ($friend_page - 1) * 20), ARRAY_A);
    }
    ?>
    <div class="wrap zv-invite-admin">
      <h1>Povabila in kristali</h1>
      <p>10 % kristalov prijatelja iz plačanega nakupa se dodatno pripiše povabitelju. Prijatelju se nič ne odšteje. Zgodovinske fiksne nagrade ostanejo ločene.</p>
      <style>.zv-invite-admin .stats{display:flex;flex-wrap:wrap;gap:24px;padding:20px 0}.zv-invite-admin .stats strong{display:block;font-size:24px}.zv-invite-admin .table-scroll{overflow:auto}.zv-invite-admin td,.zv-invite-admin th{vertical-align:top}.zv-invite-admin form{display:flex;flex-wrap:wrap;gap:12px;align-items:end}.zv-invite-admin label{display:grid;gap:4px}.zv-invite-admin .pagination{display:flex;gap:12px;margin:12px 0 28px}</style>
      <form method="get">
        <input type="hidden" name="page" value="zvij-invitations">
        <label>Obdobje<select name="period"><option value="7" <?php selected($period, '7'); ?>>7 dni</option><option value="30" <?php selected($period, '30'); ?>>30 dni</option><option value="all" <?php selected($period, 'all'); ?>>Ves čas</option></select></label>
        <label>Član (e-pošta)<input name="member" type="email" value="<?php echo esc_attr($email); ?>" placeholder="Vsi člani"></label>
        <label>Vir kristalov<select name="kind"><option value="">Vsi viri</option><?php foreach (['earn', 'redeem', 'refund', 'adjust', 'expire', 'referral', 'referral_bonus', 'referral_reversal'] as $kind) : ?><option value="<?php echo esc_attr($kind); ?>" <?php selected($type, $kind); ?>><?php echo esc_html(zvij_invitation_type_label($kind)); ?></option><?php endforeach; ?></select></label>
        <button class="button button-primary">Prikaži</button>
      </form>
      <div class="stats">
        <div>Novi računi iz povabil<strong><?php echo $new_friends; ?></strong></div>
        <div>Naročila z bonusom<strong><?php echo (int) $totals['orders']; ?></strong></div>
        <div>Pripisani bonusi<strong>+<?php echo (int) $totals['awarded']; ?></strong></div>
        <div>Stornirani bonusi<strong><?php echo (int) $totals['reversed']; ?></strong></div>
        <div>Neto bonus (kristali)<strong><?php echo (int) $totals['awarded'] + (int) $totals['reversed']; ?></strong></div>
      </div>
      <p>Števila zgoraj veljajo za izbrano obdobje in vse člane. Filter člana in vira zoži dnevnik spodaj.</p>
      <h2>Kdo je povabil prijatelje</h2>
      <p>Prijatelji in kupci: ves čas. Bonusi: izbrano obdobje. Stanje: trenutno dobroimetje iz vseh virov.</p>
      <div class="table-scroll"><table class="widefat striped"><thead><tr><th>Član</th><th>Prijatelji z računom</th><th>Že kupili</th><th>Bonus</th><th>Storno</th><th>Neto bonus</th><th>Stanje kristalov</th></tr></thead><tbody>
      <?php foreach ($rank as $row) : ?><tr><td><a href="<?php echo esc_url(zvij_invitation_admin_url(['period' => $period, 'member' => $row['email']])); ?>"><?php echo esc_html($row['name'] ?: $row['email']); ?></a><br><?php echo esc_html($row['email']); ?></td><td><?php echo (int) $row['friends']; ?></td><td><?php echo (int) $row['buyers']; ?></td><td>+<?php echo (int) $row['awarded']; ?></td><td><?php echo (int) $row['reversed']; ?></td><td><?php echo (int) $row['awarded'] + (int) $row['reversed']; ?></td><td><?php echo (int) $row['balance']; ?></td></tr><?php endforeach; ?>
      <?php if (! $rank) : ?><tr><td colspan="7">Še ni registriranih prijateljev iz povabil.</td></tr><?php endif; ?>
      </tbody></table></div>
      <?php zvij_invitation_admin_pagination($rank_page, $rank_count, 20, 'rank_page', $base); ?>
      <?php if ($selected) : ?>
      <h2>Prijatelji člana <?php echo esc_html($email); ?></h2>
      <div class="table-scroll"><table class="widefat striped"><thead><tr><th>Prijatelj</th><th>Račun</th><th>Vir povabila</th><th>Registracija</th><th>Naročila z bonusom</th><th>Neto bonus (ves čas)</th></tr></thead><tbody>
      <?php foreach ($friends as $friend) : ?><tr><td><?php echo esc_html($friend['friend_email']); ?></td><td>#<?php echo (int) $friend['friend_user_id']; ?></td><td><?php echo esc_html($friend['channel']); ?></td><td><?php echo esc_html($friend['created_at']); ?></td><td><?php echo (int) $friend['orders']; ?></td><td><?php echo (int) $friend['bonus']; ?></td></tr><?php endforeach; ?>
      <?php if (! $friends) : ?><tr><td colspan="6">Ta član še nima prijateljev z računom iz povabil.</td></tr><?php endif; ?>
      </tbody></table></div>
      <?php zvij_invitation_admin_pagination($friend_page, $friend_count, 20, 'friend_page', $base); ?>
      <?php endif; ?>
      <h2>Od kod prihajajo kristali (izbrano obdobje, vsi člani)</h2>
      <table class="widefat striped"><thead><tr><th>Vir</th><th>Zapisov</th><th>Kristali (neto)</th></tr></thead><tbody><?php foreach ($sources as $source) : ?><tr><td><?php echo esc_html(zvij_invitation_type_label($source['type'])); ?></td><td><?php echo (int) $source['entries']; ?></td><td><?php echo (int) $source['amount']; ?></td></tr><?php endforeach; ?><?php if (! $sources) : ?><tr><td colspan="3">V tem obdobju ni premikov kristalov.</td></tr><?php endif; ?></tbody></table>
      <h2>Od kod so prišli novi prijatelji</h2>
      <table class="widefat striped"><thead><tr><th>Vir povezave</th><th>Registrirani računi</th></tr></thead><tbody><?php foreach ($channels as $channel) : ?><tr><td><?php echo esc_html($channel['channel']); ?></td><td><?php echo (int) $channel['friends']; ?></td></tr><?php endforeach; ?><?php if (! $channels) : ?><tr><td colspan="2">V tem obdobju ni novih prijateljev.</td></tr><?php endif; ?></tbody></table>
      <h2>Dnevnik kristalov</h2>
      <p>Vsak pripis, poraba in storno. Pri bonusu je naveden prijatelj in naročilo, iz katerega izhaja.</p>
      <div class="table-scroll"><table class="widefat striped"><thead><tr><th>Datum</th><th>Prejemnik</th><th>Vir</th><th>Prijatelj</th><th>Naročilo</th><th>Kristali</th><th>Opis</th></tr></thead><tbody>
      <?php foreach ($journal as $entry) : $order = $entry['order_id'] ? wc_get_order((int) $entry['order_id']) : null; ?><tr><td><?php echo esc_html($entry['created_at']); ?></td><td><a href="<?php echo esc_url(zvij_invitation_admin_url(['period' => $period, 'member' => $entry['member_email']])); ?>"><?php echo esc_html($entry['member_email']); ?></a></td><td><?php echo esc_html(zvij_invitation_type_label($entry['type'])); ?></td><td><?php echo esc_html($entry['related_email'] ?: '—'); ?></td><td><?php if ($order) : ?><a href="<?php echo esc_url($order->get_edit_order_url()); ?>">#<?php echo esc_html($order->get_order_number()); ?></a><?php else : ?><?php echo $entry['order_id'] ? '#' . (int) $entry['order_id'] : '—'; ?><?php endif; ?></td><td><?php echo esc_html(((int) $entry['amount'] > 0 ? '+' : '') . (int) $entry['amount']); ?></td><td><?php echo esc_html($entry['note']); ?></td></tr><?php endforeach; ?>
      <?php if (! $journal) : ?><tr><td colspan="7">Ni premikov za izbrane filtre.</td></tr><?php endif; ?>
      </tbody></table></div>
      <?php zvij_invitation_admin_pagination($page, $journal_count, 30, 'journal_page', $base); ?>
    </div>
    <?php
}

function zvij_invitation_admin_pagination(int $page, int $count, int $size, string $key, array $args): void {
    $pages = max(1, (int) ceil($count / $size));
    echo '<div class="pagination">';
    if ($page > 1) {
        echo '<a class="button" href="' . esc_url(zvij_invitation_admin_url(array_merge($args, [$key => $page - 1]))) . '">Prejšnja</a>';
    }
    echo '<span>' . esc_html(sprintf('Stran %d / %d · %d zapisov', $page, $pages, $count)) . '</span>';
    if ($page < $pages) {
        echo '<a class="button" href="' . esc_url(zvij_invitation_admin_url(array_merge($args, [$key => $page + 1]))) . '">Naslednja</a>';
    }
    echo '</div>';
}
