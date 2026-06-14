<?php
/**
 * Fallback template.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="content-panel">
  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <article <?php post_class('content-card'); ?>>
        <p class="eyebrow"><?php esc_html_e('Zvij.si', 'zvij-theme'); ?></p>
        <h1><?php the_title(); ?></h1>
        <div class="entry-content">
          <?php the_content(); ?>
        </div>
      </article>
    <?php endwhile; ?>
  <?php endif; ?>
</section>
<?php
get_footer();
