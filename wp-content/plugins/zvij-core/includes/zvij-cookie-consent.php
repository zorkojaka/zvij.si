<?php
/**
 * ZVIJ — obvestilo o piškotkih (GDPR/ePrivacy).
 *
 * Nujni piškotki (WooCommerce košarica/seja, WordPress) se naložijo vedno.
 * Nenujni (analitika/trženje) se NE naložijo brez izrecne privolitve — okno je
 * ločeno od članstva/nakupa, zavrnitev je enako preprosta kot privolitev.
 *
 * Stanje privolitve: piškotek `zvij_consent` = "all" | "necessary" (180 dni).
 * JS izpostavi `window.zvijConsent = {necessary:true, analytics:bool}` in sproži
 * dogodek `zvij:consent` — morebitni analitični skripti (npr. GA) naj se naložijo
 * šele, ko je `analytics` true (ali ob tem dogodku). Ponovni prikaz:
 * `window.zvijOpenCookieSettings()` (povezava »Nastavitve piškotkov« v nogi).
 */

if (! defined('ABSPATH')) {
    exit;
}

function zvij_cookie_consent_render(): void {
    if (is_admin()) {
        return;
    }
    $policy = get_privacy_policy_url();
    $more = $policy ? $policy . '#piskotki' : '';
    ?>
    <style>
      .zv-cookie{position:fixed;left:0;right:0;bottom:0;z-index:9999;background:#fbf8f3;border-top:1px solid #e0d9cf;box-shadow:0 -8px 30px rgba(25,18,10,.12)}
      .zv-cookie[hidden]{display:none}
      .zv-cookie__inner{max-width:1100px;margin:0 auto;padding:1rem 1.25rem;display:flex;gap:1rem;flex-wrap:wrap;align-items:center;justify-content:space-between}
      .zv-cookie__text{margin:0;flex:1 1 320px;font-size:.9rem;line-height:1.5;color:#4a4335}
      .zv-cookie__text a{color:#ad8650;text-decoration:underline}
      .zv-cookie__actions{display:flex;gap:.6rem;flex-wrap:wrap}
      .zv-cookie__actions button{cursor:pointer;border-radius:6px;font-size:.9rem;font-weight:700;padding:.6rem 1.1rem;border:1px solid #c7b194;background:#fff;color:#1e1a15}
      .zv-cookie__actions .zv-cookie__accept{background:#c8934e;border-color:#c8934e;color:#17120f}
      @media (max-width:600px){.zv-cookie__actions{width:100%}.zv-cookie__actions button{flex:1}}
    </style>
    <section class="zv-cookie" id="zvij-cookie" aria-label="<?php esc_attr_e('Obvestilo o piškotkih', 'zvij-core'); ?>" hidden>
      <div class="zv-cookie__inner">
        <p class="zv-cookie__text">
          <?php esc_html_e('Uporabljamo nujne piškotke za delovanje trgovine. Z vašo privolitvijo bomo uporabljali tudi analitične piškotke.', 'zvij-core'); ?>
          <?php if ($more) : ?><a href="<?php echo esc_url($more); ?>"><?php esc_html_e('Več o piškotkih', 'zvij-core'); ?></a><?php endif; ?>
        </p>
        <div class="zv-cookie__actions">
          <button type="button" data-consent="necessary"><?php esc_html_e('Samo nujni', 'zvij-core'); ?></button>
          <button type="button" data-consent="all" class="zv-cookie__accept"><?php esc_html_e('Sprejmi vse', 'zvij-core'); ?></button>
        </div>
      </div>
    </section>
    <script>
    (function(){
      var NAME='zvij_consent', el=document.getElementById('zvij-cookie');
      if(!el){return;}
      function get(n){return document.cookie.split('; ').reduce(function(a,c){var p=c.split('=');return p[0]===n?decodeURIComponent(p[1]||''):a;},'');}
      function set(n,v,days){var d=new Date();d.setTime(d.getTime()+days*864e5);document.cookie=n+'='+encodeURIComponent(v)+';expires='+d.toUTCString()+';path=/;SameSite=Lax';}
      function apply(v){window.zvijConsent={necessary:true,analytics:v==='all'};try{document.dispatchEvent(new CustomEvent('zvij:consent',{detail:window.zvijConsent}));}catch(e){}}
      var cur=get(NAME);
      if(cur==='all'||cur==='necessary'){apply(cur);}else{el.hidden=false;}
      el.addEventListener('click',function(e){var b=e.target.closest('[data-consent]');if(!b){return;}var v=b.getAttribute('data-consent');set(NAME,v,180);apply(v);el.hidden=true;});
      window.zvijOpenCookieSettings=function(){el.hidden=false;};
    })();
    </script>
    <?php
}
add_action('wp_footer', 'zvij_cookie_consent_render', 100);
