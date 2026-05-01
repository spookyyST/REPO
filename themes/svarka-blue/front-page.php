<?php
/**
 * Front page template.
 *
 * @package SvarkaBlue
 */

if (!defined('ABSPATH')) {
    exit;
}

$equipment_cards = svarka_blue_home_equipment_cards();
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

    <section class="reference-section reference-catalog" id="equipment">
        <div class="container">
            <h1 class="reference-title">КАТАЛОГ ОБОРУДОВАНИЯ</h1>
            <div class="reference-category-grid">
                <?php foreach ($equipment_cards as $category) : ?>
                    <article class="reference-category-card">
                        <a href="<?php echo esc_url($category['url']); ?>" class="reference-category-card__link">
                            <span class="reference-category-card__image">
                                <img src="<?php echo esc_url($category['image']); ?>" alt="<?php echo esc_attr($category['title']); ?>">
                            </span>
                            <span class="reference-category-card__content">
                                <h2><?php echo esc_html($category['title']); ?></h2>
                                <span class="reference-category-card__more">
                                    Подробнее
                                    <i data-lucide="arrow-up-right"></i>
                                </span>
                            </span>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="reference-category-more">
                <a class="button button--primary reference-category-more__button" href="<?php echo esc_url(home_url('/catalog/')); ?>">
                    Показать все
                    <i data-lucide="arrow-up-right"></i>
                </a>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
