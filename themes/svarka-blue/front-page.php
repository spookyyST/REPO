<?php
/**
 * Front page template.
 *
 * @package SvarkaBlue
 */

if (!defined('ABSPATH')) {
    exit;
}

$hero_banner = content_url('/uploads/2026/04/Generated Image April 30, 2026 - 11_19AM.jpg');

get_header();
?>

<main class="site-main reference-home">
    <section class="reference-hero" aria-label="Каталог оборудования" style="--hero-banner: url('<?php echo esc_url($hero_banner); ?>');">
        <button class="hero-arrow hero-arrow--prev" type="button" aria-label="Предыдущий слайд">
            <i data-lucide="chevron-left"></i>
        </button>
        <button class="hero-arrow hero-arrow--next" type="button" aria-label="Следующий слайд">
            <i data-lucide="chevron-right"></i>
        </button>
    </section>

    <?php get_template_part('template-parts/catalog-workbench'); ?>
</main>

<?php
get_footer();
