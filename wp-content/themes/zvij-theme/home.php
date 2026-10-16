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
      <article <?php post_class('zv-blog-card'); ?>>
        <a class="zv-blog-card__link" href="<?php the_permalink(); ?>">
          <?php if (has_post_thumbnail()) : ?>
            <span class="zv-blog-card__media"><?php the_post_thumbnail('medium_large', ['loading' => 'lazy']); ?></span>
          <?php endif; ?>
          <span class="zv-blog-card__body">
            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j. n. Y')); ?></time>
            <h2><?php the_title(); ?></h2>
            <p><?php echo esc_html(wp_strip_all_tags(get_the_excerpt())); ?></p>
            <span class="zv-blog-card__more"><?php esc_html_e('Preberi', 'zvij-theme'); ?> →</span>
          </span>
        </a>
      </article>
    <?php endwhile; ?>
  <?php else : ?>
    <p><?php esc_html_e('Članki prihajajo kmalu.', 'zvij-theme'); ?></p>
  <?php endif; ?>
</section>
<?php
get_footer();
