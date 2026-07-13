<?php
/**
 * Preklop dev email načina: Mailpit (lokalni zajem) ali živi SMTP (mail.zvij.si).
 *
 *   docker compose run --rm wp-cli wp eval-file scripts/wp-mail-mode.php mailpit
 *   docker compose run --rm wp-cli wp eval-file scripts/wp-mail-mode.php live
 *   docker compose run --rm wp-cli wp eval-file scripts/wp-mail-mode.php status
 *
 * Žive SMTP nastavitve se ob preklopu na mailpit shranijo v opcijo
 * zvij_smtp_live_settings in se ob preklopu nazaj obnovijo — gesla ni
 * treba nikoli ponovno vpisovati. Mailpit UI: http://127.0.0.1:8101
 * (oz. MAILPIT_PORT iz .env).
 */

$mode = $args[0] ?? 'status';
$settings = (array) get_option('wp_mail_smtp', []);
$current_host = (string) ($settings['smtp']['host'] ?? '');

if ($mode === 'status') {
    WP_CLI::log('Trenutni SMTP: ' . ($current_host === 'mailpit' ? 'mailpit (lokalni zajem)' : $current_host . ' (živi)'));
    exit;
}

if (! in_array($mode, ['mailpit', 'live'], true)) {
    WP_CLI::error('Uporaba: wp eval-file scripts/wp-mail-mode.php mailpit|live|status');
}

if ($mode === 'mailpit') {
    if ($current_host !== 'mailpit') {
        update_option('zvij_smtp_live_settings', $settings['smtp'], false);
    }
    $settings['smtp'] = [
        'host' => 'mailpit',
        'port' => 1025,
        'encryption' => 'none',
        'autotls' => false,
        'auth' => false,
        'user' => '',
        'pass' => '',
    ];
    update_option('wp_mail_smtp', $settings);
    WP_CLI::success('SMTP preklopljen na Mailpit (mailpit:1025). UI: http://127.0.0.1:8101');
    exit;
}

$live = (array) get_option('zvij_smtp_live_settings', []);
if ($live === [] || empty($live['host'])) {
    WP_CLI::error('Shranjenih živih SMTP nastavitev ni (zvij_smtp_live_settings) — vpiši jih ročno v WP Mail SMTP.');
}
$settings['smtp'] = $live;
update_option('wp_mail_smtp', $settings);
WP_CLI::success('SMTP preklopljen nazaj na živi strežnik: ' . $live['host']);
