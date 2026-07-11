<?php
/**
 * Page template with visual landing variants.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $slug = get_post_field('post_name', get_the_ID());

    if ($slug === 'zvij-kit' || $slug === 'kiti' || $slug === 'zvij-setup') :
        $tone_map = ['black' => 'dark', 'silver' => 'silver', 'gold' => 'gold'];
        $all_kits = (array) get_option('zvij_kits', []);
        $kits = [];
        foreach ($all_kits as $kd) {
            $key = $kd['key'] ?? '';
            if (! isset($tone_map[$key])) {
                continue;
            }
            $kits[] = ['key' => $key, 'name' => ($kd['name'] ?? ucfirst($key) . ' Kit') . ' Zvij.si', 'tone' => $tone_map[$key]];
        }
        ?>
        <section class="zv-page-head">
          <h1>Kiti Zvij.si</h1>
          <p>En kit, tvoj stil.<br>Izberi barvo.<br>Ostalo je že sestavljeno.</p>
        </section>
        <section class="zv-kit-page-grid">
          <?php foreach ($kits as $i => $kit) : ?>
            <?php $img = zvij_kit_flatlay_url($kit['key']); ?>
            <article class="zv-kit-pick zv-kit-tab--<?php echo esc_attr($kit['tone']); ?><?php echo $i === 0 ? ' is--active' : ''; ?>" data-kit-select="<?php echo esc_attr($kit['key']); ?>">
              <?php if ($img !== '') : ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($kit['name']); ?>" loading="lazy"><?php endif; ?>
              <h2><?php echo esc_html($kit['name']); ?></h2>
              <p class="zv-kit-pick__note">Sestavi spodaj.</p>
              <a class="button" href="#kit-builder" data-kit-select-btn="<?php echo esc_attr($kit['key']); ?>">Izberi <?php echo esc_html(ucfirst($kit['key'])); ?></a>
            </article>
          <?php endforeach; ?>
        </section>
        <?php zvij_kit_builder_render($all_kits); ?>
        <section class="zv-benefits">
          <div data-ico="package"><b>Vse na enem mestu</b><span>Brez iskanja po trgovinah.</span></div>
          <div data-ico="spark"><b>Usklajeno</b><span>Tvoj stil. Tvoja izbira.</span></div>
          <div data-ico="bolt"><b>Pripravljeno za akcijo</b><span>Odpri in uživaj.</span></div>
        </section>
        <?php
        continue;
    endif;

    if ($slug === 'reload') :
        $cats = [
            ['DUBI filtri', 'Poglej filtre'],
            ['CBD/CBG vršički', 'Poglej vse'],
            ['Rolice / papir', 'Poglej vse'],
            ['Vžigalniki', 'Poglej vse'],
            ['Drugi potrošni material', 'Poglej vse'],
        ];
        ?>
        <section class="zv-reload-hero zv-panel">
          <div>
            <h1>Reload<br>ko zmanjka</h1>
            <p>Ne sestavljaš znova. Samo dopolniš. Hitro. Enostavno.</p>
            <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>">Poglej izdelke</a>
          </div>
          <?php $tw = zvij_kit_flatlay_url('throwie'); ?>
          <?php if ($tw !== '') : ?><img src="<?php echo esc_url($tw); ?>" alt="Reload bundle" loading="lazy"><?php endif; ?>
        </section>
        <section class="zv-reload-grid">
          <?php foreach ($cats as $cat) : ?>
            <article class="zv-card">
              <h2><?php echo esc_html($cat[0]); ?></h2>
              <a href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php echo esc_html($cat[1]); ?></a>
            </article>
          <?php endforeach; ?>
        </section>
        <section class="zv-service-strip">
          <div data-ico="truck"><b>Hitro pri tebi</b><span>1-2 dni</span></div>
          <div data-ico="shield"><b>Diskretno pakiranje</b><span>Brez oznak</span></div>
          <div data-ico="heart"><b>Domača podpora</b><span>Tukaj smo zate.</span></div>
        </section>
        <?php
        continue;
    endif;

    if ($slug === 'o-nas') :
        ?>
        <section class="zv-about-hero zv-panel">
          <div>
            <h1>Domač<br>kompanjon<br>ki zrihta robo.</h1>
            <p>Zvij.si ni samo trgovina. Je ekipa, ki sama uporablja to, kar ponuja. Brez bullshita. Samo kvalitetna roba, pošteno in diskretno.</p>
            <a class="button" href="#ekipa">Spoznaj ekipo</a>
          </div>
          <?php $hero = zvij_kit_flatlay_url('black'); ?>
          <?php if ($hero !== '') : ?><img src="<?php echo esc_url($hero); ?>" alt="Zvij.si ekipa in setup" loading="lazy"><?php endif; ?>
        </section>
        <section class="zv-benefits">
          <div data-ico="pin"><b>Domače</b><span>Smo iz Slovenije.</span></div>
          <div data-ico="check"><b>Pošteno</b><span>Fer cene. Brez presenečenj.</span></div>
          <div data-ico="shield"><b>Diskretno</b><span>Tvoja zasebnost je naša stvar.</span></div>
        </section>
        <section class="zv-about-story zv-panel" id="ekipa">
          <div class="zv-about-story__head">
            <p class="eyebrow">Kdo smo</p>
            <h2>Domača ekipa, ki uporablja to, kar ponuja.</h2>
            <div class="page-actions">
              <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>">Poglej trgovino</a>
              <a class="button button--ghost" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>">Postani član</a>
            </div>
          </div>
          <div class="zv-about-story__body">
            <p>Zvij.si je nastal iz preproste ideje: vse za tvoj ritual naj bo na enem mestu — kvalitetno, pošteno in brez iskanja po petih trgovinah.</p>
            <p>DUBI filtri, tulci, papir, vžigalniki in CBD čaj — vsako stvar izberemo z mero in okusom, ker jo sami uporabljamo. Za vsakim izdelkom stoji nekdo, ki ga je preizkusil.</p>
            <p>Verjamemo v diskretnost — pakiranje brez oznak, poštene cene brez presenečenj in skupnost, ki jo gradimo skupaj. Tvoj vajb, tvoja pravila. Mi poskrbimo, da imaš vse pri roki.</p>
            <p class="zv-about-story__note">Zvij.si je namenjen polnoletnim osebam (18+).</p>
          </div>
        </section>
        <?php
        continue;
    endif;
    if (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page())) :
        ?>
        <article <?php post_class('page-layout zv-commerce'); ?>>
          <header class="zv-commerce__head">
            <h1><?php the_title(); ?></h1>
          </header>
          <div class="zv-commerce__body entry-content"><?php the_content(); ?></div>
        </article>
        <?php
        continue;
    endif;
    ?>
    <article <?php post_class('page-layout'); ?>>
      <header class="page-hero">
        <p class="eyebrow">Zvij.si</p>
        <h1><?php the_title(); ?></h1>
        <div class="page-hero__intro"><?php the_excerpt(); ?></div>
      </header>
      <div class="content-card entry-content"><?php the_content(); ?></div>
      <div class="page-actions">
        <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>">Poglej trgovino</a>
        <a class="button button--ghost" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>">Član Zvij.si</a>
      </div>
    </article>
<?php endwhile; ?>
<?php
get_footer();
