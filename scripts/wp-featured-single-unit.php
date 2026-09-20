<?php
/**
 * Glavna slika = posamezen kos, ne prodajna skatla.
 *
 * Ziggijeve fotografije prihajajo razvrscene od skatle proti kosu (glej
 * scripts/lib/zvij-image-order.php). Ta skripta popravi vrstni red pri ze
 * uvozenih izdelkih: izbrana slika postane glavna, ostale ostanejo v galeriji
 * v izvornem vrstnem redu.
 *
 * Idempotentno: vrstni red se bere iz imen datotek (<slug>.jpg, <slug>-2.jpg
 * ...), ne iz trenutne razporeditve, zato veckraten zagon ne premika slik
 * naprej.
 *
 * Zagon:
 *   docker compose --profile tools run --rm wp-cli \
 *     wp eval-file scripts/wp-featured-single-unit.php
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/lib/zvij-image-order.php';

$q = new WP_Query([
    'post_type'      => 'product',
    'post_status'    => ['publish', 'private', 'draft'],
    'posts_per_page' => -1,
    'fields'         => 'ids',
]);

foreach ($q->posts as $pid) {
    $product = wc_get_product($pid);
    if (! $product) {
        continue;
    }

    $thumb = (int) $product->get_image_id();
    if (! $thumb) {
        continue;
    }

    $ids = array_values(array_unique(array_merge([$thumb], $product->get_gallery_image_ids())));
    if (count($ids) < 2) {
        continue;
    }

    $base   = zvij_image_base($product, $ids);
    $sorted = zvij_sort_by_source($ids, $base);
    $pick   = zvij_featured_index($product->get_slug(), count($sorted));

    $featured = $sorted[$pick];
    $gallery  = array_values(array_diff($sorted, [$featured]));

    if ($featured === $thumb && $gallery === $product->get_gallery_image_ids()) {
        printf("%-40s ze urejeno\n", $product->get_slug());
        continue;
    }

    $product->set_image_id($featured);
    $product->set_gallery_image_ids($gallery);
    $product->save();

    printf("%-40s glavna -> %s\n", $product->get_slug(),
        basename((string) get_attached_file($featured)));
}
