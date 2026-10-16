<?php
/**
 * Blog: posamezen članek.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    ?>
    <article <?php post_class('page-layout zv-article'); ?>>
      <header class="page-hero">
        <p class="eyebrow"><a href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Blog', 'zvij-theme'); ?></a> · <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j. n. Y')); ?></time></p>
        <h1><?php the_title(); ?></h1>
      </header>
      <?php if (has_post_thumbnail()) : ?>
        <figure class="zv-article__media"><?php the_post_thumbnail('large'); ?></figure>
      <?php endif; ?>
      <div class="content-card entry-content"><?php the_content(); ?></div>
      <p class="zv-article__note"><?php esc_html_e('Članek je informativne narave in ni zdravstveni nasvet. Naši vršički so namenjeni pripravi čaja, ne kajenju. Samo za polnoletne.', 'zvij-theme'); ?></p>
      <div class="page-actions">
        <a class="button" href="<?php echo esc_url(home_url('/cbd-vrsicki/')); ?>"><?php esc_html_e('Vršički za čaj', 'zvij-theme'); ?></a>
        <a class="button button--ghost" href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Vsi članki', 'zvij-theme'); ?></a>
      </div>
    </article>
<?php endwhile; ?>
<?php
get_footer();
