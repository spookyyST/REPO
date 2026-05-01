<?php
/**
 * Theme footer.
 *
 * @package SvarkaBlue
 */

if (!defined('ABSPATH')) {
    exit;
}

$footer_links = svarka_blue_footer_links();
?>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="site-brand site-brand--footer" href="<?php echo esc_url(home_url('/')); ?>">
                <?php svarka_blue_render_brand(); ?>
            </a>
            <p>Каталог сварочного оборудования для производства, монтажа и сервисных мастерских.</p>
            <div class="footer-social">
                <a href="#" aria-label="Telegram"><i data-lucide="send"></i></a>
                <a href="#" aria-label="YouTube"><i data-lucide="play"></i></a>
                <a href="#" aria-label="Email"><i data-lucide="mail"></i></a>
            </div>
        </div>

        <?php foreach ($footer_links as $title => $links) : ?>
            <div class="footer-column">
                <h2><?php echo esc_html($title); ?></h2>
                <ul>
                    <?php foreach ($links as $label => $url) : ?>
                        <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <div class="footer-column footer-contacts">
            <h2>НАШИ КОНТАКТЫ</h2>
            <a class="footer-phone" href="tel:<?php echo esc_attr(preg_replace('/\D+/', '', svarka_blue_contact_phone())); ?>"><?php echo esc_html(svarka_blue_contact_phone()); ?></a>
            <a href="mailto:<?php echo esc_attr(svarka_blue_contact_email()); ?>"><?php echo esc_html(svarka_blue_contact_email()); ?></a>
        </div>
    </div>

    <div class="container footer-bottom">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'footer',
            'container'      => false,
            'menu_class'     => 'footer-mini-menu',
            'fallback_cb'    => 'svarka_blue_footer_fallback_menu',
        ));
        ?>
        <span>© <?php echo esc_html(date_i18n('Y')); ?> <?php echo esc_html(svarka_blue_brand_title()); ?></span>
    </div>
</footer>

<div class="cookie-banner" data-cookie-banner aria-hidden="true">
    <div>
        <strong>Cookies</strong>
        <p>Используем технические cookies для корректной работы сайта и аналитики. Настройки можно изменить в браузере.</p>
    </div>
    <div class="cookie-banner__actions">
        <a href="<?php echo esc_url(home_url('/cookies/')); ?>">Подробнее</a>
        <button class="button button--primary" type="button" data-cookie-accept>Ок</button>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
