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
  <div>
    <strong><?php esc_html_e('Zvij.si Dev', 'zvij-theme'); ?></strong>
    <p><?php esc_html_e('Trgovina v nastajanju za ritual, setup, reload in član Zvij.si sistem.', 'zvij-theme'); ?></p>
  </div>
  <div class="site-footer__member">
    <h2><?php esc_html_e('Postani član', 'zvij-theme'); ?></h2>
    <p><?php esc_html_e('Novi izdelki, članske ponudbe in občasne Zvijače za zvijače.', 'zvij-theme'); ?></p>
    <?php echo function_exists('zvij_membership_form') ? zvij_membership_form(['source' => 'footer']) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
