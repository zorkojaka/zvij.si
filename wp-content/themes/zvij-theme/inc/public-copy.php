<?php
/** Normalize public typography without rewriting stored data or machine payloads. */
if (! defined('ABSPATH')) {
    exit;
}

function zvij_public_short_dashes(string $text): string {
    return str_replace(["\u{2013}", "\u{2014}"], '-', $text);
}

function zvij_public_copy_html(string $html): string {
    if (! method_exists('WP_HTML_Tag_Processor', 'set_modifiable_text')) {
        return $html;
    }
    $processor = new WP_HTML_Tag_Processor($html);
    while ($processor->next_token()) {
        $tag = $processor->get_tag();
        if ($processor->get_token_type() === '#text' || $tag === 'TITLE') {
            $text = $processor->get_modifiable_text();
            $normalized = zvij_public_short_dashes($text);
            if ($text !== $normalized) {
                $processor->set_modifiable_text($normalized);
            }
        }
        if ($processor->get_token_type() === '#tag' && ! $processor->is_tag_closer()) {
            foreach (['title', 'alt', 'aria-label', 'placeholder'] as $attribute) {
                $text = $processor->get_attribute($attribute);
                if (is_string($text) && $text !== zvij_public_short_dashes($text)) {
                    $processor->set_attribute($attribute, zvij_public_short_dashes($text));
                }
            }
        }
    }
    return $processor->get_updated_html();
}

add_action('template_redirect', function (): void {
    if (is_admin() || wp_doing_ajax() || is_feed() || is_robots() || is_trackback() || wp_is_json_request()) {
        return;
    }
    ob_start(static function (string $output): string {
        return stripos($output, '<html') === false ? $output : zvij_public_copy_html($output);
    });
}, 99);

// Cart fragments arrive separately from the main HTML document.
add_filter('woocommerce_add_to_cart_fragments', function (array $fragments): array {
    foreach ($fragments as $selector => $html) {
        if (is_string($html)) {
            $fragments[$selector] = zvij_public_copy_html($html);
        }
    }
    return $fragments;
}, 99);
