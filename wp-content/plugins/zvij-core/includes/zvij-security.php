<?php
/**
 * ZVIJ — utrditev pred lansiranjem (16. 10. 2026).
 *
 * Trgovina ima enega skrbnika in nobenih javnih avtorjev, zato uporabniška
 * imena ne smejo biti javno vidna (REST /wp/v2/users, ?author=N, oEmbed),
 * ker napadalcu dajo polovico prijave. Datoteke, ki ne smejo biti dosegljive
 * prek spleta (scripts/, data/), zapre nginx — glej .deploy/nginx/zvij-hardening.conf.
 */

if (! defined('ABSPATH')) {
    exit;
}

/** REST: seznam in profili uporabnikov samo za prijavljene urednike. */
add_filter('rest_endpoints', function (array $endpoints): array {
    if (current_user_can('list_users')) {
        return $endpoints;
    }
    foreach (array_keys($endpoints) as $route) {
        if (str_starts_with($route, '/wp/v2/users')) {
            unset($endpoints[$route]);
        }
    }
    return $endpoints;
});

/** ?author=N in /author/ime/ ne izdata imena — preusmeritev na domačo stran. */
add_action('template_redirect', function (): void {
    if (is_author() || (isset($_GET['author']) && ! is_admin())) { // phpcs:ignore WordPress.Security.NonceVerification
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}, 1);

/** oEmbed ne razkrije avtorja. */
add_filter('oembed_response_data', function (array $data): array {
    unset($data['author_name'], $data['author_url']);
    return $data;
});

/** Avtorske povezave v sitemapu niso potrebne. */
add_filter('wp_sitemaps_add_provider', function ($provider, string $name) {
    return $name === 'users' ? false : $provider;
}, 10, 2);

/** Napaka ob prijavi ne pove, ali uporabnik obstaja. */
add_filter('login_errors', static function (): string {
    return (int) get_transient(zvij_security_login_key()) >= 5
        ? __('Preveč neuspešnih poskusov. Poskusi znova čez 15 minut.', 'zvij-core')
        : __('Prijava ni uspela. Preveri podatke in poskusi znova.', 'zvij-core');
});

/** XML-RPC ni v uporabi (nginx ga tudi zapre). */
add_filter('xmlrpc_enabled', '__return_false');

/** Brez razkrivanja različice WordPressa v glavi strani. */
remove_action('wp_head', 'wp_generator');

/**
 * Omejitev poskusov prijave: 5 napačnih poskusov z istega IP-ja v 15 minutah
 * zaklene prijavo za ta IP za 15 minut. IP pride od nginxa (X-Real-IP).
 */
function zvij_security_client_ip(): string {
    $ip = (string) ($_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function zvij_security_login_key(): string {
    return 'zvij_login_fail_' . md5(zvij_security_client_ip());
}

add_filter('authenticate', function ($user) {
    if ((int) get_transient(zvij_security_login_key()) >= 5) {
        return new WP_Error('zvij_locked', __('Preveč neuspešnih poskusov. Poskusi znova čez 15 minut.', 'zvij-core'));
    }
    return $user;
}, 30);

add_action('wp_login_failed', function (): void {
    $key = zvij_security_login_key();
    set_transient($key, (int) get_transient($key) + 1, 15 * MINUTE_IN_SECONDS);
});

add_action('wp_login', function (): void {
    delete_transient(zvij_security_login_key());
});
