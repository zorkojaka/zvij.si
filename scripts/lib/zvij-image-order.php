<?php
/**
 * Vrstni red produktnih fotografij — katera je glavna.
 *
 * Ziggijeve mape imajo slike razvrscene od prodajne skatle proti posameznemu
 * kosu: 1. skatla spredaj, 2. skatla postrani, 3. odprta skatla s knjizicami,
 * 4. posamezen kos. V trgovini prodajamo KOS, ne skatle, zato mora biti glavna
 * slika zadnja, ostale pa gredo v galerijo v izvornem vrstnem redu.
 *
 * Tu je ta odlocitev zapisana enkrat; uporabljata jo uvoznik slik
 * (wp-import-product-images.php) in popravek ze uvozenih izdelkov
 * (wp-featured-single-unit.php), da se ne razideta.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Izdelki, kjer je posamezen kos na ZADNJI sliki niza.
 *
 * Kdor ni na seznamu, obdrzi izvorni vrstni red (glavna = prva). Tak je npr.
 * Mystery Mix, ki skatle na slikah sploh nima.
 */
const ZVIJ_SINGLE_UNIT_LAST = [
    'ziggi-original-classic-slim',
    'ziggi-natural-classic-slim',
    'ziggi-hemp-classic-slim',
    'ziggi-wide-extra',
    'ziggi-original-double',
    'ziggi-original-roll-tips-tray',
    'ziggi-natural-roll-tips-tray',
];

/**
 * Indeks (0-based) slike, ki naj bo glavna, znotraj niza v IZVORNEM vrstnem
 * redu.
 */
function zvij_featured_index(string $slug, int $count): int {
    if ($count < 2) {
        return 0;
    }
    return in_array($slug, ZVIJ_SINGLE_UNIT_LAST, true) ? $count - 1 : 0;
}

/**
 * Zaporedna stevilka slike v izvornem nizu, prebrana iz imena datoteke.
 *
 * Uvoznik poimenuje slike <slug>.jpg, <slug>-2.jpg, <slug>-3.jpg ... Ime je
 * trajno, zato je iz njega izpeljan vrstni red stabilen: popravek vrstnega
 * reda lahko tece veckrat in vedno da isti rezultat.
 *
 * Priponka -N se steje le, ce je pred njo tocno slug izdelka — sicer bi pri
 * izdelku s stevilko v slugu (npr. dubi-42) stevilko brali kot zaporedje.
 */
function zvij_image_source_index(int $attachment_id, string $slug): int {
    $file = (string) get_attached_file($attachment_id);
    if (! $file) {
        return PHP_INT_MAX;
    }
    $base = pathinfo($file, PATHINFO_FILENAME);
    if ($base === $slug) {
        return 1;
    }
    if (preg_match('/^' . preg_quote($slug, '/') . '-(\d+)$/', $base, $m)) {
        return (int) $m[1];
    }
    return PHP_INT_MAX;
}

/** Razvrsti priponke v izvorni vrstni red (po imenu datoteke). */
function zvij_sort_by_source(array $attachment_ids, string $slug): array {
    usort($attachment_ids, fn ($a, $b) =>
        zvij_image_source_index($a, $slug) <=> zvij_image_source_index($b, $slug));
    return $attachment_ids;
}

/**
 * Osnova imena datotek za ta izdelek.
 *
 * Uvoznik poimenuje po sanitize_title(ime izdelka), ta pa se lahko razlikuje
 * od sluga (npr. ce je bil slug rocno spremenjen). Vzamemo tisto osnovo, ki
 * se dejansko ujema s katero od priponk.
 */
function zvij_image_base(WC_Product $product, array $attachment_ids): string {
    foreach ([sanitize_title($product->get_name()), $product->get_slug()] as $base) {
        foreach ($attachment_ids as $id) {
            if (zvij_image_source_index($id, $base) !== PHP_INT_MAX) {
                return $base;
            }
        }
    }
    return $product->get_slug();
}
