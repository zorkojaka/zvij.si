<?php
/**
 * Stari članki s prejšnje strani, prepisani v varnem tonu (Jaka, 16. 10. 2026).
 * Idempotentno.
 *
 * Pravila (docs/LEGAL_CBD_COPY_RULES.md): brez zdravstvenih trditev in obljub
 * učinkov, vršički samo za čaj, nič o kajenju. HHC članek ostane neobjavljen —
 * HHC ne prodajamo in ga ne promoviramo; njegov naslov nginx preusmeri na /blog/.
 * Naslovne slike brez zvitkov in brez angleških zdravstvenih napisov.
 *
 * Zagon: docker exec -u 33 zvij-dev-wordpress php /var/www/html/scripts/wp-blog-relaunch.php
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/** Stari naslovi /ime-clanka/ morajo delovati naprej. */
if (get_option('permalink_structure') !== '/%postname%/') {
    update_option('permalink_structure', '/%postname%/');
    echo "permalinki prispevkov: /%postname%/\n";
}

/** Stran /blog/ kot seznam člankov. */
$blog = get_page_by_path('blog');
$blog_id = $blog ? $blog->ID : wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Blog', 'post_name' => 'blog']);
update_option('page_for_posts', (int) $blog_id);

function zvij_blog_image(string $file, string $title): int {
    $existing = get_posts(['post_type' => 'attachment', 'meta_key' => '_zvij_blog_source', 'meta_value' => basename($file), 'numberposts' => 1, 'fields' => 'ids']);
    if ($existing) {
        return (int) $existing[0];
    }
    $upload = wp_upload_bits('blog-' . basename($file), null, file_get_contents($file));
    if ($upload['error']) {
        throw new RuntimeException($upload['error']);
    }
    $id = wp_insert_attachment(['post_mime_type' => 'image/jpeg', 'post_title' => $title, 'post_status' => 'inherit'], $upload['file']);
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
    update_post_meta($id, '_wp_attachment_image_alt', $title);
    update_post_meta($id, '_zvij_blog_source', basename($file));
    return (int) $id;
}

$images = get_template_directory() . '/assets/images/';
$shop = static fn (string $slug): string => esc_url(home_url('/izdelek/' . $slug . '/'));

$posts = [
    84 => [
        'title'   => 'Kaj je CBD?',
        'slug'    => 'raziskovanje-cbd-od-prvinske-rastline-do-sodobnih-moznosti',
        'image'   => zvij_blog_image($images . 'blog/cbd-caj.jpg', 'Skodelica čaja in posušeni konopljini vršički'),
        'excerpt' => 'CBD je ena od spojin v konoplji, ki ne omamlja. Kaj je, od kod prihaja in kako ga pri nas pripraviš — kot čaj.',
        'content' => '<p>CBD ali kanabidiol je ena od več kot sto spojin, ki jih najdemo v konoplji. Skupaj jim rečemo kanabinoidi. Največ jih je v cvetovih, ki jim pravimo vršički.</p>
<h2>CBD ni THC</h2>
<p>Najbolj znan kanabinoid je THC — tisti, zaradi katerega je človek »zadet«. CBD tega učinka nima: ne omamlja in ne spreminja zaznavanja. Zato je CBD konoplja nekaj povsem drugega kot konoplja, ki jo poznaš iz filmov.</p>
<h2>Od kod prihaja</h2>
<p>CBD vršički prihajajo iz industrijske konoplje — sort, ki so vzgojene tako, da vsebujejo zelo malo THC. Gojijo jih po Evropi, tudi v Sloveniji. Po obiranju se vršički posušijo in pakirajo, tako kot drugi zeliščni čaji.</p>
<h2>Kaj je res in kaj ne</h2>
<p>Na spletu boš o CBD našel ogromno obljub. Raziskave še potekajo in za večino trditev o zdravju dokazov ni dovolj — zato jih ne ponavljamo. CBD ni zdravilo in ne nadomešča zdravljenja. Če jemlješ zdravila, si noseča ali dojiš, se pred uporabo posvetuj z zdravnikom.</p>
<h2>Pri nas: vršički za čaj</h2>
<p>Naši vršički so namenjeni pripravi čaja. Žličko vršičkov prelij z vročo vodo in pusti stati 10–15 minut. Za polnejši okus dodaj kanček mleka ali masla.</p>
<p>Poglej <a href="' . $shop('smokey-cbd-vrsicki') . '">SMOKEY CBD</a> in <a href="' . $shop('frutty-cbd-vrsicki') . '">FRUTTY CBD</a>.</p>',
    ],
    82 => [
        'title'   => 'Kaj je CBG?',
        'slug'    => 'cbg-skrivnostni-kanabinoid-z-velikim-potencialom',
        'image'   => 83,
        'excerpt' => 'CBG je manj znan kanabinoid, iz katerega rastlina med rastjo naredi druge. Kaj je in zakaj ga je v večini konoplje tako malo.',
        'content' => '<p>CBG ali kanabigerol je kanabinoid, o katerem se govori manj kot o CBD in THC. Zanimiv je predvsem zaradi svoje vloge v rastlini.</p>
<h2>»Matični« kanabinoid</h2>
<p>Mlada rastlina konoplje najprej tvori kislo obliko CBG. Ko rastlina zori, se ta postopoma pretvarja v druge kanabinoide, med njimi v CBD in THC. Zato je v večini zrelih rastlin CBG zelo malo — ostane ga le nekaj odstotkov.</p>
<h2>Sorte z več CBG</h2>
<p>Pridelovalci so vzgojili sorte industrijske konoplje, v katerih se CBG ne pretvori naprej in ga ostane več. Iz takih rastlin so CBG vršički.</p>
<h2>Ali omamlja?</h2>
<p>Ne. Tako kot CBD tudi CBG ne povzroča »zadetosti«. Raziskave o CBG so še na začetku, zato o učinkih ne obljubljamo ničesar. CBG ni zdravilo.</p>
<h2>Pri nas</h2>
<p><a href="' . $shop('chilly-cbg-vrsicki') . '">CHILLY CBG</a> so posušeni vršički za čaj. Žličko prelij z vročo vodo in pusti stati 10–15 minut.</p>',
    ],
    78 => [
        'title'   => 'CBD, CBG in THC: kaj jih loči',
        'slug'    => 'cbd-cbg-in-thc-kaj-jih-loci',
        'image'   => zvij_blog_image($images . 'blog/kanabinoidi-caj.jpg', 'Čaj iz konopljinih vršičkov'),
        'excerpt' => 'Trije kanabinoidi iz iste rastline, a zelo različni. Kratek pregled, kaj je kaj.',
        'content' => '<p>Vsi trije prihajajo iz konoplje in vsem trem rečemo kanabinoidi. Tu se podobnost konča.</p>
<figure class="wp-block-table"><table>
<thead><tr><th></th><th>Kaj je</th><th>Omamlja?</th></tr></thead>
<tbody>
<tr><td><strong>THC</strong></td><td>Tetrahidrokanabinol, najbolj znan kanabinoid.</td><td>Da.</td></tr>
<tr><td><strong>CBD</strong></td><td>Kanabidiol, glavni kanabinoid industrijske konoplje.</td><td>Ne.</td></tr>
<tr><td><strong>CBG</strong></td><td>Kanabigerol, »matični« kanabinoid, iz katerega nastanejo drugi.</td><td>Ne.</td></tr>
</tbody></table></figure>
<h2>THC</h2>
<p>THC je spojina, zaradi katere je konoplja znana kot droga. Konoplja z visoko vsebnostjo THC v Sloveniji ni dovoljena. Industrijska konoplja ga vsebuje zelo malo.</p>
<h2>CBD</h2>
<p>CBD je v industrijski konoplji običajno najpogostejši kanabinoid. Ne omamlja. Več v članku <a href="' . esc_url(home_url('/raziskovanje-cbd-od-prvinske-rastline-do-sodobnih-moznosti/')) . '">Kaj je CBD?</a></p>
<h2>CBG</h2>
<p>CBG rastlina tvori prvi in ga nato pretvarja v druge kanabinoide. Ne omamlja. Več v članku <a href="' . esc_url(home_url('/cbg-skrivnostni-kanabinoid-z-velikim-potencialom/')) . '">Kaj je CBG?</a></p>
<h2>Kaj prodajamo mi</h2>
<p>Pri nas dobiš samo CBD in CBG vršički iz industrijske konoplje, namenjene pripravi čaja. O zdravstvenih učinkih ne obljubljamo ničesar — raziskave o kanabinoidih še potekajo.</p>',
    ],
    86 => [
        'title'   => 'Zgodba Zvij.si',
        'slug'    => 'zgodba-zvij-si',
        'image'   => zvij_blog_image($images . 'home/hero.jpg', 'Zvij.si JollySafe tulci v črni, zlati in srebrni'),
        'excerpt' => 'Zvij.si smo prvič odprli 20. aprila 2023. Zdaj je tu nova trgovina: pribor, vršički za čaj in Član Zvij.si.',
        'content' => '<p>Zvij.si smo prvič odprli 20. aprila 2023. Od takrat smo se veliko naučili — predvsem to, kaj ljudje res rabijo in kaj je samo hrup.</p>
<h2>Kaj je novo</h2>
<p>Nova trgovina je preprostejša. Na enem mestu dobiš pribor, ki ga sami uporabljamo: <a href="' . esc_url(home_url('/dubi-filtri/')) . '">DUBI filtre</a>, Ziggi papirčke, vžigalnike Clipper, grinderje in tulce JollySafe. Poleg tega imamo <a href="' . esc_url(home_url('/cbd-vrsicki/')) . '">CBD in CBG vršičke za čaj</a>.</p>
<h2>Član Zvij.si</h2>
<p>Člani ob vsakem plačanem naročilu zberejo kristale in z njimi naslednjič plačajo manj. Prijatelja lahko povabiš s svojo kodo in oba dobita nekaj zase. <a href="' . esc_url(home_url('/clan-zvij-si/')) . '">Več o članstvu</a>.</p>
<h2>Kako poteka nakup</h2>
<p>Plačaš z nakazilom — po naročilu dobiš UPN QR kodo, ki jo skeniraš z mobilno banko. Paket odpremimo v 1–2 delovnih dneh po plačilu, diskretno pakiran in brez oznak vsebine.</p>
<p>Zvij.si je namenjen samo polnoletnim. Tvoj vajb, tvoja pravila.</p>',
    ],
];

foreach ($posts as $id => $data) {
    if (! get_post($id)) {
        echo "preskocim #{$id}: ni prispevka\n";
        continue;
    }
    $args = [
        'ID'           => $id,
        'post_title'   => $data['title'],
        'post_name'    => $data['slug'],
        'post_excerpt' => $data['excerpt'],
        'post_content' => $data['content'],
        'post_status'  => 'publish',
    ];
    // Zgodba je nov članek (ob ponovnem odprtju), ne zgodovinski — enkrat dobi današnji datum.
    if ($id === 86 && get_post_status($id) !== 'publish') {
        $args['post_date'] = current_time('mysql');
        $args['post_date_gmt'] = current_time('mysql', true);
    }
    wp_update_post(wp_slash($args));
    set_post_thumbnail($id, (int) $data['image']);
    echo "objavljen #{$id}: {$data['title']} → " . get_permalink($id) . "\n";
}

/** HHC ostane neobjavljen, »Hello world« v koš. */
if (get_post(80) && get_post_status(80) !== 'draft') {
    wp_update_post(['ID' => 80, 'post_status' => 'draft']);
}
if (get_post(1) && get_post_status(1) !== 'trash') {
    wp_trash_post(1);
    echo "Hello world v koš\n";
}

flush_rewrite_rules(true);
echo "OK\n";
