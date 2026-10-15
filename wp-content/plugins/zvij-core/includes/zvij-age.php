<?php
/**
 * ZVIJ — starostna omejitev 18+.
 *
 * Dve ravni:
 *  1. Vstopno okno ob prvem obisku (»Si star vsaj 18 let?«). Odgovor se
 *     zapomni v piškotku `zvij_age` (nujen za izpolnitev zakonske omejitve,
 *     zato ni vezan na privolitev za piškotke). Pravne strani (pogoji,
 *     zasebnost, vračila) so dostopne brez potrditve.
 *  2. Obvezna potrditev na blagajni. Brez kljukice naročila ni mogoče oddati;
 *     čas potrditve se shrani na naročilo (`_zvij_age_confirmed`) kot dokaz.
 */

if (! defined('ABSPATH')) {
    exit;
}

/** Strani, ki morajo biti berljive brez potrditve starosti. */
function zvij_age_is_exempt_page(): bool {
    $ids = array_filter([
        (int) get_option('woocommerce_terms_page_id'),
        (int) get_option('wp_page_for_privacy_policy'),
        (int) get_option('woocommerce_refund_returns_page_id'),
    ]);
    return $ids && is_page($ids);
}

function zvij_age_gate_render(): void {
    if (is_admin() || zvij_age_is_exempt_page()) {
        return;
    }
    $terms = get_permalink((int) get_option('woocommerce_terms_page_id'));
    ?>
    <style>
      .zv-age{position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;padding:1.25rem;background:rgba(17,19,16,.82);backdrop-filter:blur(6px)}
      .zv-age[hidden]{display:none}
      .zv-age__box{max-width:26rem;width:100%;background:var(--zvij-panel,#fff);color:var(--zvij-text,#14160f);border-radius:var(--zvij-radius,16px);padding:1.75rem 1.5rem;text-align:center;box-shadow:0 24px 70px rgba(0,0,0,.35)}
      .zv-age__badge{display:inline-block;font-weight:800;font-size:1.6rem;line-height:1;padding:.55rem .8rem;border-radius:999px;background:var(--zvij-lime,#c8f24c);color:#14160f;margin-bottom:.9rem}
      .zv-age__box h2{margin:0 0 .5rem;font-size:1.3rem}
      .zv-age__box p{margin:0 0 1.2rem;color:var(--zvij-muted,#6f7268);font-size:.95rem;line-height:1.5}
      .zv-age__actions{display:flex;gap:.6rem}
      .zv-age__actions button{flex:1;cursor:pointer;border-radius:999px;font-weight:700;font-size:.95rem;padding:.75rem 1rem;border:1px solid var(--zvij-line,#e3e1d9);background:transparent;color:inherit}
      .zv-age__actions .zv-age__yes{background:var(--zvij-text,#14160f);border-color:var(--zvij-text,#14160f);color:var(--zvij-bg,#f5f4f0)}
      .zv-age__no-msg{margin-top:1rem!important;font-weight:600;color:inherit!important}
      .zv-age__small{font-size:.8rem!important;margin:1rem 0 0!important}
      .zv-age__small a{color:inherit}
      html.zv-age-open,html.zv-age-open body{overflow:hidden}
    </style>
    <div class="zv-age" id="zvij-age" role="dialog" aria-modal="true" aria-labelledby="zvij-age-title" hidden>
      <div class="zv-age__box">
        <span class="zv-age__badge">18+</span>
        <h2 id="zvij-age-title"><?php esc_html_e('Si star vsaj 18 let?', 'zvij-core'); ?></h2>
        <p><?php esc_html_e('Zvij.si prodaja kadilski pribor in vršičke za čaj izključno polnoletnim osebam.', 'zvij-core'); ?></p>
        <div class="zv-age__actions">
          <button type="button" data-age="no"><?php esc_html_e('Ne', 'zvij-core'); ?></button>
          <button type="button" data-age="yes" class="zv-age__yes"><?php esc_html_e('Da, star sem 18+', 'zvij-core'); ?></button>
        </div>
        <p class="zv-age__no-msg" data-age-no hidden><?php esc_html_e('Žal ti strani ne smemo prikazati. Vrni se, ko boš polnoleten.', 'zvij-core'); ?></p>
        <?php if ($terms) : ?>
          <p class="zv-age__small"><a href="<?php echo esc_url($terms); ?>"><?php esc_html_e('Pogoji poslovanja', 'zvij-core'); ?></a></p>
        <?php endif; ?>
      </div>
    </div>
    <script>
    (function(){
      var el=document.getElementById('zvij-age');
      if(!el||document.cookie.split('; ').indexOf('zvij_age=18')!==-1){return;}
      el.hidden=false;document.documentElement.classList.add('zv-age-open');
      var yes=el.querySelector('[data-age="yes"]');if(yes){yes.focus();}
      el.addEventListener('click',function(e){
        var b=e.target.closest('[data-age]');if(!b){return;}
        if(b.getAttribute('data-age')==='yes'){
          var d=new Date();d.setTime(d.getTime()+365*864e5);
          document.cookie='zvij_age=18;expires='+d.toUTCString()+';path=/;SameSite=Lax';
          el.hidden=true;document.documentElement.classList.remove('zv-age-open');
        }else{
          el.querySelector('.zv-age__actions').hidden=true;
          el.querySelector('[data-age-no]').hidden=false;
        }
      });
    })();
    </script>
    <?php
}
add_action('wp_footer', 'zvij_age_gate_render', 90);

/** Obvezna kljukica na blagajni, tik pred gumbom za oddajo. */
add_action('woocommerce_review_order_before_submit', function (): void {
    $checked = ! empty($_POST['zvij_age_confirm']); // phpcs:ignore WordPress.Security.NonceVerification
    ?>
    <p class="form-row validate-required zvij-age-confirm" id="zvij_age_confirm_field">
      <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
        <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="zvij_age_confirm" id="zvij_age_confirm" value="1" <?php checked($checked); ?>>
        <span><?php esc_html_e('Potrjujem, da sem star vsaj 18 let.', 'zvij-core'); ?></span>&nbsp;<abbr class="required" title="<?php esc_attr_e('obvezno', 'zvij-core'); ?>">*</abbr>
      </label>
    </p>
    <?php
}, 5);

add_action('woocommerce_checkout_process', function (): void {
    if (empty($_POST['zvij_age_confirm'])) { // phpcs:ignore WordPress.Security.NonceVerification
        wc_add_notice(__('Za oddajo naročila potrdi, da si star vsaj 18 let.', 'zvij-core'), 'error');
    }
});

add_action('woocommerce_checkout_create_order', function ($order): void {
    if (! empty($_POST['zvij_age_confirm'])) { // phpcs:ignore WordPress.Security.NonceVerification
        $order->update_meta_data('_zvij_age_confirmed', current_time('mysql'));
    }
});

add_action('woocommerce_admin_order_data_after_billing_address', function ($order): void {
    $at = (string) $order->get_meta('_zvij_age_confirmed');
    echo '<p><strong>' . esc_html__('Starost 18+', 'zvij-core') . ':</strong> '
        . ($at !== '' ? esc_html(sprintf(__('potrjeno %s', 'zvij-core'), mysql2date('j. n. Y H:i', $at))) : esc_html__('ni potrditve', 'zvij-core'))
        . '</p>';
});
