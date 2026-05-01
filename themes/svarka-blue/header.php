<?php
/**
 * Theme header.
 *
 * @package SvarkaBlue
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" id="site-header">
    <div class="mainbar">
        <div class="container mainbar__inner">
            <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('На главную', 'svarka-blue'); ?>">
                <?php svarka_blue_render_brand(); ?>
            </a>

            <nav class="site-nav" aria-label="<?php esc_attr_e('Основная навигация', 'svarka-blue'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'nav-menu',
                    'fallback_cb'    => 'svarka_blue_primary_fallback_menu',
                ));
                ?>
            </nav>

            <div class="header-actions">
                <div class="header-contacts">
                    <a href="mailto:<?php echo esc_attr(svarka_blue_contact_email()); ?>"><?php echo esc_html(svarka_blue_contact_email()); ?></a>
                    <a href="tel:<?php echo esc_attr(preg_replace('/\D+/', '', svarka_blue_contact_phone())); ?>"><?php echo esc_html(svarka_blue_contact_phone()); ?></a>
                </div>
                <a class="button button--primary header-cta" href="#request">ОБРАТНЫЙ ЗВОНОК</a>
                <button class="mobile-toggle" type="button" id="mobile-toggle" aria-controls="mobile-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Открыть меню', 'svarka-blue'); ?>">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </div>
</header>

<div class="mobile-menu" id="mobile-menu" aria-hidden="true">
    <div class="mobile-menu__head">
        <span><?php echo esc_html(svarka_blue_brand_title()); ?></span>
        <button class="mobile-menu__close" type="button" id="mobile-close" aria-label="<?php esc_attr_e('Закрыть меню', 'svarka-blue'); ?>">
            <i data-lucide="x"></i>
        </button>
    </div>
    <nav class="mobile-nav" aria-label="<?php esc_attr_e('Мобильная навигация', 'svarka-blue'); ?>">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'nav-menu',
            'fallback_cb'    => 'svarka_blue_primary_fallback_menu',
        ));
        ?>
    </nav>
    <form class="mobile-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <i data-lucide="search"></i>
        <input type="search" name="s" placeholder="Артикул или название" value="<?php echo esc_attr(get_search_query()); ?>">
    </form>
    <a class="button button--primary button--full" href="#request">Оставить заявку</a>
</div>
<div class="mobile-backdrop" id="mobile-backdrop" aria-hidden="true"></div>
