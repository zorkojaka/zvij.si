<?php
/**
 * Site footer.
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
</main>
<footer class="site-footer">
  <div class="site-footer__inner">
    <?php
    // Na domaci strani ima clanstvo svojo sekcijo (zv-member), zato bi bil
    // obrazec v nogi drugi enak obrazec v istem drsu. Drugje ostane.
    if (! is_front_page()) :
    ?>
    <div class="site-footer__member" id="postani-clan">
      <p class="site-footer__eyebrow"><?php esc_html_e('Član Zvij.si', 'zvij-theme'); ?></p>
      <h2><?php echo is_user_logged_in() ? 'Tvoje članstvo je aktivno' : 'Postani član'; ?></h2>
      <p><?php esc_html_e('Novi izdelki, članske ponudbe in občasne Zvijače za zvijače.', 'zvij-theme'); ?></p>
      <?php echo function_exists('zvij_membership_form') ? zvij_membership_form(['source' => 'footer']) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
    <?php else : ?>
    <div class="site-footer__info">
      <nav class="site-footer__links" aria-label="<?php esc_attr_e('Povezave v nogi', 'zvij-theme'); ?>">
        <div>
          <p class="site-footer__eyebrow"><?php esc_html_e('Trgovina', 'zvij-theme'); ?></p>
          <a href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Vsi izdelki', 'zvij-theme'); ?></a>
          <a href="<?php echo esc_url(home_url('/cbd-vrsicki/')); ?>"><?php esc_html_e('Vršički za čaj', 'zvij-theme'); ?></a>
          <a href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>"><?php esc_html_e('Član Zvij.si', 'zvij-theme'); ?></a>
          <a href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Blog', 'zvij-theme'); ?></a>
        </div>
        <div>
          <p class="site-footer__eyebrow"><?php esc_html_e('Pomoč', 'zvij-theme'); ?></p>
          <a href="<?php echo esc_url(home_url('/faq/')); ?>"><?php esc_html_e('Pogosta vprašanja', 'zvij-theme'); ?></a>
          <a href="<?php echo esc_url(home_url('/kontakt/')); ?>"><?php esc_html_e('Kontakt', 'zvij-theme'); ?></a>
          <a href="<?php echo esc_url(home_url('/pogoji-poslovanja/')); ?>"><?php esc_html_e('Pogoji poslovanja', 'zvij-theme'); ?></a>
          <a href="<?php echo esc_url(home_url('/refund_returns/')); ?>"><?php esc_html_e('Vračila in reklamacije', 'zvij-theme'); ?></a>
        </div>
      </nav>
      <address class="site-footer__company">
        <strong>Zvij.si d.o.o.</strong><br>
        Rjava cesta 26A, 1260 Ljubljana-Polje<br>
        <?php esc_html_e('Matična št.', 'zvij-theme'); ?> 9378294000 · <?php esc_html_e('Davčna št.', 'zvij-theme'); ?> 68449763<br>
        <a href="mailto:info@zvij.si">info@zvij.si</a>
      </address>
    </div>
    <?php endif; ?>

    <div class="site-footer__brand">
      <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/logo-zvij-outline.svg'); ?>" width="96" height="87" alt="<?php esc_attr_e('zvij.si', 'zvij-theme'); ?>" loading="lazy">
      <address>
        <strong>Zvij.si</strong><br>
        <?php esc_html_e('Tvoj vajb. Tvoja rutina. Tvoj lajf. Tvoja pravila.', 'zvij-theme'); ?>
      </address>
      <div class="site-footer__social" aria-label="<?php esc_attr_e('Družbena omrežja', 'zvij-theme'); ?>">
        <a href="https://www.instagram.com/zvij.si" aria-label="Instagram">IG</a>
        <a href="https://www.tiktok.com/@zvij.si" aria-label="TikTok">TT</a>
      </div>
    </div>
  </div>
  <div class="site-footer__legal">
    <a href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Blog', 'zvij-theme'); ?></a>
    <a href="<?php echo esc_url(home_url('/kontakt/')); ?>"><?php esc_html_e('Kontakt', 'zvij-theme'); ?></a>
    <a href="<?php echo esc_url(home_url('/pogoji-poslovanja/')); ?>"><?php esc_html_e('Pogoji poslovanja', 'zvij-theme'); ?></a>
    <a href="<?php echo esc_url(home_url('/refund_returns/')); ?>"><?php esc_html_e('Vračila', 'zvij-theme'); ?></a>
    <?php if ($privacy = get_privacy_policy_url()) : ?>
      <a href="<?php echo esc_url($privacy); ?>"><?php esc_html_e('Politika zasebnosti', 'zvij-theme'); ?></a>
    <?php endif; ?>
    <button type="button" class="site-footer__cookies" onclick="if(window.zvijOpenCookieSettings)window.zvijOpenCookieSettings()"><?php esc_html_e('Nastavitve piškotkov', 'zvij-theme'); ?></button>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
