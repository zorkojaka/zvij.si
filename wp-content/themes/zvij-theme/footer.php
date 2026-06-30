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
  <a class="button button--light" href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Začni s svojim setupom', 'zvij-theme'); ?></a>
</footer>
<?php wp_footer(); ?>
</body>
</html>
