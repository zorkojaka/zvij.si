<?php
/**
 * Uvoz produktnih fotografij iz razpakirane mape.
 *
 * Vir: uradne Ziggijeve fotografije (mapa "ZiGGi PRODUCTS" na Drivu, lastnik
 * jure@ziggipapers.com) in slike izdelkov s Knistermannove B2B strani.
 *
 * Ujemanje je po IMENU MAPE oz. datoteke, ne po vrstnem redu — Ziggijeve mape
 * so ostevilcene (1., 2., 5. ...), stevilke pa ne pomenijo nicesar pri nas.
 * Prva slika postane glavna, ostale gredo v galerijo.
 *
 * Idempotentno: izdelek, ki ze ima pravo fotografijo, se preskoci (razen z
 * ZVIJ_FORCE=1). Placeholderji se povozijo in pobrisejo.
 *
 * Zagon:
 *   docker compose --profile tools run --rm wp-cli \
 *     wp eval-file scripts/wp-import-product-images.php /var/www/html/data/slike-uvoz
 */

if (! defined('ABSPATH')) {
    exit;
}

$dir = $args[0] ?? '/var/www/html/data/slike-uvoz';
if (! is_dir($dir)) {
    echo "Mape ni: {$dir}\n";
    return;
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$force = getenv('ZVIJ_FORCE') === '1';

/**
 * Vzorec v imenu mape/datoteke => ID izdelka. Vzorci se preverjajo po vrsti,
 * zato morajo bolj specificni (npr. "roll basic") stati PRED splosnejsimi
 * ("roll"), sicer bi jih splosnejsi pobral prvi.
 */
$map = [
    // Knistermann — posamezne datoteke
    'clipper-black'        => 211,
    'clipper-plin'         => 445,
    'grinder-zlat'         => 216,
    'grinder-srebrn'       => 217,

    // Ziggi — imena map
    'original special edition' => [438, 439, 440],
    'original wide extra'  => 441,
    'original double'      => 444,
    'original roll basic'  => null,   // te razlicice ne prodajamo
    'natural roll basic'   => null,
    'original roll'        => 442,
    'natural roll'         => 443,
    'original extra'       => null,
    'natural extra'        => null,
    'original 1_1'         => null,
    'natural 1_1'          => null,
    'original basic'       => null,
    'natural basic'        => null,
    'filter tips'          => null,
    'hemp'                 => 436,
    'original'             => 435,
    'natural'              => 437,
];

/** Normalizira ime za ujemanje: brez vodilne stevilke, malo, brez locil. */
function zvij_img_key(string $name): string {
    $n = preg_replace('/^\s*\d+\s*[.\-_)]\s*/u', '', $name);
    $n = mb_strtolower($n);
    $n = str_replace(['ziggi', 'ziggi', '_', '-', '.'], ' ', $n);
    return trim(preg_replace('/\s+/', ' ', $n));
}

function zvij_img_match(string $name, array $map) {
    $key = zvij_img_key($name);
    foreach ($map as $pattern => $target) {
        if (str_contains($key, $pattern)) {
            return [$pattern, $target];
        }
    }
    return [null, false];
}

/** Pomanjsa in ponovno stisne, da v Mediateko ne gredo 5 MB izvirniki. */
function zvij_img_prepare(string $src, string $dest, int $max = 1600): bool {
    $info = @getimagesize($src);
    if (! $info) {
        return false;
    }
    [$w, $h] = $info;
    $img = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
        IMAGETYPE_PNG  => @imagecreatefrompng($src),
        IMAGETYPE_WEBP => @imagecreatefromwebp($src),
        default        => null,
    };
    if (! $img) {
        return false;
    }
    $scale = min(1, $max / max($w, $h));
    $nw = (int) round($w * $scale);
    $nh = (int) round($h * $scale);
    $out = imagecreatetruecolor($nw, $nh);
    imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
    imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $ok = imagejpeg($out, $dest, 86);
    imagedestroy($img);
    imagedestroy($out);
    return $ok;
}

/** Pripne slike na izdelek: prva je glavna, ostale galerija. */
function zvij_img_attach(int $product_id, array $files, bool $force): string {
    $product = wc_get_product($product_id);
    if (! $product) {
        return "ni izdelka #{$product_id}";
    }

    $current = (int) get_post_thumbnail_id($product_id);
    $is_placeholder = $current && get_post_meta($current, '_zvij_placeholder', true) !== '';
    if ($current && ! $is_placeholder && ! $force) {
        return 'preskocim — ze ima pravo fotografijo';
    }

    sort($files, SORT_NATURAL | SORT_FLAG_CASE);
    $ids = [];
    foreach (array_slice($files, 0, 5) as $file) {
        $tmp = sys_get_temp_dir() . '/' . sanitize_file_name(basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION))) . '.jpg';
        if (! zvij_img_prepare($file, $tmp)) {
            continue;
        }
        $slug = sanitize_title($product->get_name());
        $name = $slug . (count($ids) ? '-' . (count($ids) + 1) : '') . '.jpg';
        $target = ['name' => $name, 'tmp_name' => $tmp];
        $id = media_handle_sideload($target, $product_id, $product->get_name());
        if (is_wp_error($id)) {
            @unlink($tmp);
            continue;
        }
        $ids[] = (int) $id;
    }

    if (! $ids) {
        return 'nobena slika se ni uvozila';
    }

    $product->set_image_id($ids[0]);
    $product->set_gallery_image_ids(array_slice($ids, 1));
    $product->save();

    if ($is_placeholder) {
        wp_delete_attachment($current, true);
    }

    return sprintf('glavna + %d v galeriji', count($ids) - 1);
}

/* ------------------------------------------------------------------ */

$entries = array_values(array_diff(scandir($dir) ?: [], ['.', '..', '__MACOSX']));
$unmatched = [];
$done = [];

foreach ($entries as $entry) {
    $path = $dir . '/' . $entry;

    // Mapa izdelka ali posamezna datoteka
    $files = is_dir($path)
        ? array_values(array_filter(glob($path . '/*'), fn ($f) => preg_match('/\.(jpe?g|png|webp)$/i', $f)))
        : (preg_match('/\.(jpe?g|png|webp)$/i', $path) ? [$path] : []);

    if (! $files) {
        continue;
    }

    [$pattern, $target] = zvij_img_match($entry, $map);

    if ($target === false) {
        $unmatched[] = $entry;
        continue;
    }
    if ($target === null) {
        printf("%-46s → preskok (te razlicice ne prodajamo)\n", mb_substr($entry, 0, 45));
        continue;
    }

    foreach ((array) $target as $pid) {
        $result = zvij_img_attach((int) $pid, $files, $force);
        $p = wc_get_product((int) $pid);
        printf("%-46s → #%d %-34s %s\n", mb_substr($entry, 0, 45), $pid,
            $p ? mb_substr($p->get_name(), 0, 33) : '?', $result);
        $done[] = $pid;
    }
}

if ($unmatched) {
    echo "\nNEUJEMAJOCE (preveri rocno):\n";
    foreach ($unmatched as $u) {
        echo "  {$u}\n";
    }
}
printf("\nobdelanih izdelkov: %d\n", count(array_unique($done)));
