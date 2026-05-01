<?php
/**
 * Page template.
 *
 * @package SvarkaBlue
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="site-main">
    <?php while (have_posts()) : the_post(); ?>
        <section class="page-hero">
            <div class="container">
                <span class="eyebrow">Информация</span>
                <h1><?php the_title(); ?></h1>
            </div>
        </section>

        <section class="section">
            <div class="container content-wrap">
                <?php the_content(); ?>
            </div>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
