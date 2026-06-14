<?php
/**
 * Page template.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class('page-layout'); ?>>
    <header class="page-hero">
      <p class="eyebrow"><?php esc_html_e('Zvij.si', 'zvij-theme'); ?></p>
      <h1><?php the_title(); ?></h1>
      <div class="page-hero__intro">
        <?php the_excerpt(); ?>
      </div>
    </header>
    <div class="content-card entry-content">
      <?php the_content(); ?>
    </div>
    <div class="page-actions">
      <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Poglej trgovino', 'zvij-theme'); ?></a>
      <a class="button button--ghost" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>"><?php esc_html_e('Član Zvij.si', 'zvij-theme'); ?></a>
    </div>
  </article>
<?php endwhile; ?>
<?php
get_footer();
