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
        $kits = [];
        $kit_roles = [];
        foreach ((array) get_option('zvij_kits', []) as $kd) {
            $key = $kd['key'] ?? '';
            if (! isset($tone_map[$key])) {
                continue;
            }
            $kits[] = ['key' => $key, 'name' => ($kd['name'] ?? ucfirst($key) . ' Kit') . ' Zvij.si', 'tone' => $tone_map[$key]];
            if (! $kit_roles) {
                foreach (($kd['items'] ?? []) as $it) {
                    $kit_roles[] = (string) ($it['label'] ?? '');
                }
            }
        }
        if (! $kit_roles) {
            $kit_roles = ['Tulec', 'Vžigalnik', 'Grinder', 'Rolice', 'DUBI 42'];
        }
        ?>
        <section class="zv-page-head">
          <h1>Kiti Zvij.si</h1>
          <p>En kit, tvoj stil.<br>Izberi barvo.<br>Ostalo je že sestavljeno.</p>
        </section>
        <section class="zv-kit-page-grid">
          <?php foreach ($kits as $kit) : ?>
            <?php $img = zvij_kit_flatlay_url($kit['key']); ?>
            <article class="zv-kit-pick zv-kit-tab--<?php echo esc_attr($kit['tone']); ?>">
              <?php if ($img !== '') : ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($kit['name']); ?>" loading="lazy"><?php endif; ?>
              <h2><?php echo esc_html($kit['name']); ?></h2>
              <p>Cena kmalu.</p>
              <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>">Izberi <?php echo esc_html(ucfirst($kit['key'])); ?></a>
            </article>
          <?php endforeach; ?>
        </section>
        <section class="zv-benefits">
          <div><b>Vse na enem mestu</b><span>Brez iskanja po trgovinah.</span></div>
          <div><b>Usklajeno</b><span>Tvoj stil. Tvoja izbira.</span></div>
          <div><b>Pripravljeno za akcijo</b><span>Odpri in uživaj.</span></div>
        </section>
        <section class="zv-card zv-card--kit-list">
          <h2>Kaj je v kitu?</h2>
          <div class="zv-mini-list">
            <?php foreach ($kit_roles as $role_label) : ?><span><?php echo esc_html($role_label); ?></span><?php endforeach; ?>
          </div>
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
          <div><b>Hitro pri tebi</b><span>1-2 dni</span></div>
          <div><b>Diskretno pakiranje</b><span>Brez oznak</span></div>
          <div><b>Domača podpora</b><span>Tukaj smo zate.</span></div>
          <div><b>Zate. Vedno.</b><span>Domača podpora</span></div>
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
          <div><b>Domače</b><span>Smo iz Slovenije.</span></div>
          <div><b>Pošteno</b><span>Fer cene. Brez presenečenj.</span></div>
          <div><b>Diskretno</b><span>Tvoja zasebnost je naša stvar.</span></div>
          <div><b>Za skupnost</b><span>Podpiramo kulturo.</span></div>
        </section>
        <section class="zv-about-stats" id="ekipa">
          <div><b>Domače</b><span>Iz Slovenije.</span></div>
          <div><b>Diskretno</b><span>Brez oznak.</span></div>
          <div><b>Fer</b><span>Poštene cene.</span></div>
          <div><b>@zvij.si</b><span>Sledi nam</span></div>
        </section>
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
