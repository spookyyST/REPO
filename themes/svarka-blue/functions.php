<?php
/**
 * Svarka Blue theme bootstrap.
 *
 * @package SvarkaBlue
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SVARKA_BLUE_VERSION', '0.6.4');

function svarka_blue_asset_url($path) {
    return get_template_directory_uri() . '/' . ltrim((string) $path, '/');
}

require_once get_template_directory() . '/inc/template-data.php';

function svarka_blue_setup() {
    load_theme_textdomain('svarka-blue', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height'      => 72,
        'width'       => 260,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('woocommerce');

    register_nav_menus(array(
        'primary' => __('Primary menu', 'svarka-blue'),
        'footer'  => __('Footer menu', 'svarka-blue'),
    ));
}
add_action('after_setup_theme', 'svarka_blue_setup');

function svarka_blue_enqueue_assets() {
    wp_enqueue_style(
        'svarka-blue-fonts',
        'https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700;800;900&display=swap',
        array(),
        null
    );
    wp_enqueue_style('svarka-blue-style', get_stylesheet_uri(), array(), SVARKA_BLUE_VERSION);
    wp_enqueue_style(
        'svarka-blue-main',
        svarka_blue_asset_url('assets/css/main.css'),
        array('svarka-blue-style', 'svarka-blue-fonts'),
        SVARKA_BLUE_VERSION
    );

    wp_enqueue_script(
        'lucide-icons',
        'https://unpkg.com/lucide@latest/dist/umd/lucide.min.js',
        array(),
        null,
        true
    );
    wp_enqueue_script(
        'page-flip',
        'https://cdn.jsdelivr.net/npm/page-flip/dist/js/page-flip.browser.min.js',
        array(),
        null,
        true
    );
    wp_enqueue_script(
        'svarka-blue-main',
        svarka_blue_asset_url('assets/js/main.js'),
        array('lucide-icons', 'page-flip'),
        SVARKA_BLUE_VERSION,
        true
    );

    wp_localize_script('svarka-blue-main', 'svarkaBlue', array(
        'cookieConsentKey' => 'svarka_blue_cookie_consent',
        'privacyUrl'       => home_url('/privacy/'),
        'cookiesUrl'       => home_url('/cookies/'),
    ));
}
add_action('wp_enqueue_scripts', 'svarka_blue_enqueue_assets');

function svarka_blue_contact_phone() {
    return get_theme_mod('svarka_blue_phone', '8-495-153-18-04');
}

function svarka_blue_contact_email() {
    $email = sanitize_email(get_theme_mod('svarka_blue_email', get_option('admin_email')));

    if (!$email || strpos($email, '@wpengine.local') !== false) {
        return 'info+622327109@hugongweld.ru';
    }

    return $email;
}

function svarka_blue_brand_title() {
    return get_theme_mod('svarka_blue_brand_title', 'HUGONG');
}

function svarka_blue_brand_subtitle() {
    return get_theme_mod('svarka_blue_brand_subtitle', 'WELD RUSSIA');
}

function svarka_blue_primary_fallback_menu() {
    $items = array(
        'О НАС' => home_url('/about/'),
        'СТАТЬ ДИЛЕРОМ' => home_url('/dealer/'),
        'НОВОСТИ' => home_url('/news/'),
        'ПРОДУКЦИЯ' => home_url('/catalog/'),
        'ГДЕ КУПИТЬ' => home_url('/where-to-buy/'),
        'КОНТАКТЫ' => home_url('/contacts/'),
    );

    echo '<ul class="nav-menu">';
    foreach ($items as $label => $url) {
        printf('<li><a href="%s">%s</a></li>', esc_url($url), esc_html($label));
    }
    echo '</ul>';
}

function svarka_blue_footer_fallback_menu() {
    echo '<ul class="footer-mini-menu">';
    printf('<li><a href="%s">%s</a></li>', esc_url(home_url('/privacy/')), esc_html__('Политика конфиденциальности', 'svarka-blue'));
    printf('<li><a href="%s">%s</a></li>', esc_url(home_url('/cookies/')), esc_html__('Cookies', 'svarka-blue'));
    printf('<li><a href="%s">%s</a></li>', esc_url(home_url('/sitemap/')), esc_html__('Карта сайта', 'svarka-blue'));
    echo '</ul>';
}

function svarka_blue_render_brand() {
    if (has_custom_logo()) {
        $custom_logo_id = (int) get_theme_mod('custom_logo');
        echo wp_get_attachment_image($custom_logo_id, 'full', false, array(
            'class' => 'custom-logo',
            'alt'   => esc_attr(svarka_blue_brand_title()),
        ));
        return;
    }

    ?>
    <span class="brand-mark" aria-hidden="true">
        <span>HG</span>
    </span>
    <span class="brand-copy">
        <strong><?php echo esc_html(svarka_blue_brand_title()); ?></strong>
        <span><?php echo esc_html(svarka_blue_brand_subtitle()); ?></span>
    </span>
    <?php
}

function svarka_blue_customize_register($wp_customize) {
    $wp_customize->add_section('svarka_blue_contacts', array(
        'title'    => __('Контакты и бренд', 'svarka-blue'),
        'priority' => 30,
    ));

    $settings = array(
        'svarka_blue_brand_title' => array('HUGONG', 'Название'),
        'svarka_blue_brand_subtitle' => array('WELD RUSSIA', 'Подпись'),
        'svarka_blue_phone' => array('8-495-153-18-04', 'Телефон'),
        'svarka_blue_email' => array('info+622327109@hugongweld.ru', 'Email'),
    );

    foreach ($settings as $setting_id => $data) {
        $wp_customize->add_setting($setting_id, array(
            'default'           => $data[0],
            'sanitize_callback' => 'sanitize_text_field',
        ));

        $wp_customize->add_control($setting_id, array(
            'label'   => $data[1],
            'section' => 'svarka_blue_contacts',
            'type'    => 'text',
        ));
    }
}
add_action('customize_register', 'svarka_blue_customize_register');

function svarka_blue_ensure_legal_pages() {
    $pages = array(
        'privacy' => array(
            'title' => 'Политика конфиденциальности',
            'content' => '<p>Черновик страницы. Финальный текст можно заменить через админку WordPress.</p>',
        ),
        'cookies' => array(
            'title' => 'Политика cookies',
            'content' => '<p>Черновик страницы. Финальный текст можно заменить через админку WordPress.</p>',
        ),
    );

    foreach ($pages as $slug => $page) {
        $existing = get_page_by_path($slug);
        if ($existing instanceof WP_Post) {
            continue;
        }

        $page_id = wp_insert_post(array(
            'post_title'   => $page['title'],
            'post_name'    => $slug,
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => $page['content'],
        ), true);

        if (!is_wp_error($page_id) && $slug === 'privacy') {
            update_option('wp_page_for_privacy_policy', (int) $page_id);
        }
    }
}
add_action('after_switch_theme', 'svarka_blue_ensure_legal_pages');
