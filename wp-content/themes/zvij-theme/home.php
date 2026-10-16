<?php
/**
 * Blog: seznam člankov (/blog/).
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="zv-page-head">
  <h1><?php esc_html_e('Blog', 'zvij-theme'); ?></h1>
  <p><?php esc_html_e('Kaj je kaj v konoplji in pri priboru. Brez obljub, samo dejstva.', 'zvij-theme'); ?></p>
</section>
<section class="zv-blog-grid">
  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <?php zvij_theme_blog_card(); ?>
    <?php endwhile; ?>
  <?php else : ?>
    <p><?php esc_html_e('Članki prihajajo kmalu.', 'zvij-theme'); ?></p>
  <?php endif; ?>
</section>
<?php
get_footer();
