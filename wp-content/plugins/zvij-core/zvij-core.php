<?php
/**
 * Plugin Name: Zvij Core
 * Plugin URI: https://dev.inteligent.si
 * Description: Harmless bootstrap plugin for the clean Zvij.si development app.
 * Version: 0.1.0
 * Author: Zvij.si
 * Requires at least: 6.5
 * Requires PHP: 8.2
 * Text Domain: zvij-core
 */

if (! defined('ABSPATH')) {
    exit;
}

define('ZVIJ_CORE_VERSION', '0.1.0');

add_action('admin_notices', function (): void {
    if (! current_user_can('manage_options')) {
        return;
    }

    echo '<div class="notice notice-info"><p>';
    echo esc_html__('Zvij Core loaded for the clean dev environment.', 'zvij-core');
    echo '</p></div>';
});
