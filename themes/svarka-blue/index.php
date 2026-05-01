<?php
/**
 * Generic template.
 *
 * @package SvarkaBlue
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="site-main">
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Раздел сайта</span>
            <h1><?php single_post_title(); ?></h1>
        </div>
    </section>

    <section class="section">
        <div class="container content-wrap">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article <?php post_class('content-entry'); ?>>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <div class="entry-content">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php the_posts_pagination(); ?>
            <?php else : ?>
                <p>Материалы пока не добавлены.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
