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
    <div class="site-footer__member">
      <p class="site-footer__eyebrow"><?php esc_html_e('Član Zvij.si', 'zvij-theme'); ?></p>
      <h2><?php esc_html_e('Postani član', 'zvij-theme'); ?></h2>
      <p><?php esc_html_e('Novi izdelki, članske ponudbe in občasne Zvijače za zvijače.', 'zvij-theme'); ?></p>
      <?php echo function_exists('zvij_membership_form') ? zvij_membership_form(['source' => 'footer']) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>

    <div class="site-footer__brand">
      <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/logo-zvij.svg'); ?>" alt="<?php esc_attr_e('zvij.si', 'zvij-theme'); ?>" loading="lazy">
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
</footer>
<?php wp_footer(); ?>
</body>
</html>
