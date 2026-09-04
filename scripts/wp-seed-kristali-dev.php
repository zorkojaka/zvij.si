<?php
/**
 * Seed kristalov iz obstoječih € napisov (wp eval-file, idempotentno).
 *
 * ZGODOVINSKA SKRIPTA — ne poganjaj je več. Nastala je ob uvedbi kristalov
 * (11. 7. 2026) pri tečaju 10 kristalov = 1 €. Od 4. 9. 2026 velja
 * 100 kristalov = 1 € (glej scripts/wp-migrate-kristali-100.php), meta
 * `_zvij_dobroimetje_note` pa je odstranjena — javni napis se izpelje iz
 * `_zvij_kristali` prek zvij_credit_public_note(). Če bi to skripto pognal
 * danes, bi vrednosti podesetkratno podcenil.
 */

if (! defined('ABSPATH')) {
    exit;
}

global $wpdb;

$rows = $wpdb->get_results(
    "SELECT pm.post_id, pm.meta_value, p.post_title
     FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id
     WHERE pm.meta_key = '_zvij_dobroimetje_note'",
    ARRAY_A
);

foreach ($rows as $row) {
    $note = (string) $row['meta_value'];

    if (preg_match('/([0-9]+)\s*kristal/u', $note, $m)) {
        // že v kristalih — samo poskrbi za numerično meto
        $kristali = (int) $m[1];
    } elseif (preg_match('/([0-9]+(?:[.,][0-9]+)?)\s*€/u', $note, $m)) {
        $kristali = (int) round(((float) str_replace(',', '.', $m[1])) * ZVIJ_KRISTALI_PER_EUR);
    } else {
        continue;
    }

    update_post_meta((int) $row['post_id'], '_zvij_kristali', $kristali);
    update_post_meta(
        (int) $row['post_id'],
        '_zvij_dobroimetje_note',
        sprintf('Član prejme %s za naslednji reload.', zvij_kristali_izpis($kristali))
    );

    echo $row['post_id'] . "\t" . $row['post_title'] . "\t" . $kristali . " kristalov\n";
}

echo "Skupaj posodobljenih: " . count($rows) . "\n";
