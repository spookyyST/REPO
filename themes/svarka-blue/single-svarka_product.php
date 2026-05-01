<?php
/**
 * Single imported product template.
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
        <?php
        $attrs = function_exists('svarka_pm_product_attrs') ? svarka_pm_product_attrs(get_the_ID()) : get_post_meta(get_the_ID(), '_svarka_product_attrs', true);
        $attrs = is_array($attrs) ? $attrs : array();
        $price = get_post_meta(get_the_ID(), '_svarka_product_price', true);
        $price_text = get_post_meta(get_the_ID(), '_svarka_product_price_text', true);
        $price_display = $price !== '' ? number_format_i18n((float) $price, 0) . ' ₽' : ($price_text ?: 'По запросу');
        $terms = get_the_terms(get_the_ID(), 'svarka_product_category');
        $category = $terms && !is_wp_error($terms) ? $terms[0]->name : 'Каталог';
        $placeholder_image = function_exists('svarka_pm_catalog_placeholder_image') ? svarka_pm_catalog_placeholder_image() : '';
        ?>
        <section class="page-hero">
            <div class="container page-hero__grid">
                <div>
                    <span class="eyebrow"><?php echo esc_html($category); ?></span>
                    <h1><?php the_title(); ?></h1>
                    <p>Технические характеристики и заявка на подбор оборудования.</p>
                </div>
                <div class="product-single-price">
                    <span>Цена</span>
                    <strong><?php echo esc_html($price_display); ?></strong>
                    <a class="button button--primary" href="<?php echo esc_url(add_query_arg('product_id', get_the_ID(), home_url('/contacts/'))); ?>">Оставить заявку</a>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container product-single-layout">
                <div class="product-single-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('large'); ?>
                    <?php elseif ($placeholder_image) : ?>
                        <img src="<?php echo esc_url($placeholder_image); ?>" alt="<?php the_title_attribute(); ?>">
                    <?php else : ?>
                        <div class="product-single-placeholder" aria-hidden="true"><?php echo esc_html(function_exists('mb_substr') ? mb_substr(get_the_title(), 0, 2) : substr(get_the_title(), 0, 2)); ?></div>
                    <?php endif; ?>
                </div>
                <div class="product-single-specs">
                    <h2>Характеристики</h2>
                    <?php if ($attrs) : ?>
                        <dl>
                            <?php foreach ($attrs as $label => $value) : ?>
                                <div>
                                    <dt><?php echo esc_html($label); ?></dt>
                                    <dd><?php echo esc_html($value); ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    <?php else : ?>
                        <p>Характеристики пока не заполнены.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
