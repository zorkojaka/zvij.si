<?php
/**
 * ZVIJ — izjave (slider), gradnik za katerokoli stran.
 *
 * Urejanje: Zvij.si → Izjave. Naslov = avtor, vsebina = izjava, izvleček =
 * vloga (npr. »Glasbenik«), naslovna slika = fotografija, vrstni red = »Vrstni red«.
 *
 * Uporaba na strani: blok »Kratka koda« z [zvij_izjave]
 *   [zvij_izjave naslov="Kaj pravijo drugi" stevilo="3" interval="7"]
 * Brez JavaScripta se izjave prikažejo kot vodoravni trak z drsenjem.
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('init', function (): void {
    register_post_type('zvij_quote', [
        'labels'       => [
            'name'          => __('Izjave', 'zvij-core'),
            'singular_name' => __('Izjava', 'zvij-core'),
            'add_new_item'  => __('Dodaj izjavo', 'zvij-core'),
            'edit_item'     => __('Uredi izjavo', 'zvij-core'),
            'featured_image'     => __('Fotografija', 'zvij-core'),
            'set_featured_image' => __('Nastavi fotografijo', 'zvij-core'),
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'zvij-dashboard',
        'supports'     => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
        'capability_type' => 'page',
    ]);
});

/** Obrazec v adminu govori v jeziku izjav, ne prispevkov. */
add_filter('enter_title_here', static fn ($text, $post) => $post->post_type === 'zvij_quote' ? __('Avtor izjave', 'zvij-core') : $text, 10, 2);

add_shortcode('zvij_izjave', function ($atts): string {
    $atts = shortcode_atts(['naslov' => '', 'stevilo' => 10, 'interval' => 7], $atts, 'zvij_izjave');
    $quotes = get_posts([
        'post_type'   => 'zvij_quote',
        'post_status' => 'publish',
        'numberposts' => max(1, (int) $atts['stevilo']),
        'orderby'     => ['menu_order' => 'ASC', 'date' => 'ASC'],
    ]);
    if (! $quotes) {
        return '';
    }
    static $instance = 0;
    $instance++;
    $id = 'zv-quotes-' . $instance;

    ob_start();
    ?>
    <section class="zv-quotes" id="<?php echo esc_attr($id); ?>" data-interval="<?php echo esc_attr((string) max(0, (int) $atts['interval'])); ?>" aria-roledescription="<?php esc_attr_e('vrtiljak', 'zvij-core'); ?>" aria-label="<?php echo esc_attr($atts['naslov'] !== '' ? $atts['naslov'] : __('Izjave', 'zvij-core')); ?>">
      <?php if ($atts['naslov'] !== '') : ?><h2 class="zv-quotes__title"><?php echo esc_html($atts['naslov']); ?></h2><?php endif; ?>
      <div class="zv-quotes__track" tabindex="0">
        <?php foreach ($quotes as $i => $quote) : ?>
          <figure class="zv-quotes__slide" aria-roledescription="<?php esc_attr_e('izjava', 'zvij-core'); ?>" aria-label="<?php echo esc_attr(sprintf('%d / %d', $i + 1, count($quotes))); ?>">
            <?php if (has_post_thumbnail($quote)) : ?>
              <?php echo get_the_post_thumbnail($quote, 'thumbnail', ['class' => 'zv-quotes__photo', 'loading' => 'lazy', 'alt' => '']); ?>
            <?php endif; ?>
            <blockquote><?php echo wp_kses_post(wpautop($quote->post_content)); ?></blockquote>
            <figcaption>
              <strong><?php echo esc_html(get_the_title($quote)); ?></strong>
              <?php if ($quote->post_excerpt !== '') : ?><span><?php echo esc_html($quote->post_excerpt); ?></span><?php endif; ?>
            </figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
      <?php if (count($quotes) > 1) : ?>
        <div class="zv-quotes__dots" role="group" aria-label="<?php esc_attr_e('Izberi izjavo', 'zvij-core'); ?>">
          <?php foreach ($quotes as $i => $quote) : ?>
            <button type="button" data-quote="<?php echo esc_attr((string) $i); ?>" aria-label="<?php echo esc_attr(sprintf(__('Izjava %d', 'zvij-core'), $i + 1)); ?>"<?php echo $i === 0 ? ' aria-current="true"' : ''; ?>></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
    <?php
    zvij_quotes_assets();
    return (string) ob_get_clean();
});

/** CSS in JS samo na straneh, kjer je gradnik — enkrat na stran. */
function zvij_quotes_assets(): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    ?>
    <style>
      .zv-quotes{margin:2rem 0;text-align:center}
      .zv-quotes__title{margin:0 0 1rem}
      .zv-quotes__track{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;scroll-behavior:smooth;scrollbar-width:none;gap:1rem;outline-offset:4px}
      .zv-quotes__track::-webkit-scrollbar{display:none}
      .zv-quotes__slide{flex:0 0 100%;scroll-snap-align:center;margin:0;padding:1.5rem 1.25rem;display:grid;justify-items:center;gap:.9rem;background:var(--zvij-panel,#fff);border:1px solid var(--zvij-line,#e3e1d9);border-radius:var(--zvij-radius,16px)}
      .zv-quotes__photo{width:120px;height:120px;border-radius:50%;object-fit:cover}
      .zv-quotes__slide blockquote{margin:0;max-width:40rem;font-size:clamp(1.1rem,2.4vw,1.45rem);line-height:1.45;font-weight:600}
      .zv-quotes__slide blockquote p{margin:0}
      .zv-quotes__slide figcaption{display:grid;gap:.15rem}
      .zv-quotes__slide figcaption span{color:var(--zvij-muted,#6f7268);font-size:.9rem}
      .zv-quotes__dots{display:flex;justify-content:center;gap:.5rem;margin-top:.9rem}
      .zv-quotes__dots button{width:.7rem;height:.7rem;padding:0;border-radius:50%;border:0;background:var(--zvij-line,#e3e1d9);cursor:pointer}
      .zv-quotes__dots button[aria-current="true"]{background:var(--zvij-text,#14160f)}
      @media (prefers-reduced-motion:reduce){.zv-quotes__track{scroll-behavior:auto}}
    </style>
    <script>
    (function(){
      function init(root){
        var track=root.querySelector('.zv-quotes__track'),dots=root.querySelectorAll('[data-quote]');
        if(!track||dots.length<2){return;}
        var index=0,timer=null,seconds=parseInt(root.getAttribute('data-interval'),10)||0;
        var still=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        function mark(i){index=i;dots.forEach(function(d,n){if(n===i){d.setAttribute('aria-current','true');}else{d.removeAttribute('aria-current');}});}
        function go(i){track.scrollTo({left:i*track.clientWidth,behavior:still?'auto':'smooth'});mark(i);}
        dots.forEach(function(d){d.addEventListener('click',function(){go(+d.getAttribute('data-quote'));restart();});});
        track.addEventListener('scroll',function(){var i=Math.round(track.scrollLeft/track.clientWidth);if(i!==index){mark(i);}},{passive:true});
        function restart(){if(timer){clearInterval(timer);}if(seconds>0&&!still){timer=setInterval(function(){go((index+1)%dots.length);},seconds*1000);}}
        ['mouseenter','focusin','touchstart'].forEach(function(e){root.addEventListener(e,function(){if(timer){clearInterval(timer);timer=null;}},{passive:true});});
        ['mouseleave','focusout'].forEach(function(e){root.addEventListener(e,restart);});
        restart();
      }
      function all(){document.querySelectorAll('.zv-quotes').forEach(init);}
      if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',all);}else{all();}
    })();
    </script>
    <?php
}
