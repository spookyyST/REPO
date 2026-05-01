<?php
/**
 * Static design data for the first visual sprint.
 *
 * @package SvarkaBlue
 */

if (!defined('ABSPATH')) {
    exit;
}

function svarka_blue_catalog_categories() {
    return array(
        array(
            'title' => 'СВАРКА MIG/MAG',
            'text'  => '',
            'count' => '',
            'image' => svarka_blue_asset_url('assets/images/cat-migmag.png'),
            'url'   => home_url('/catalog/svarka-mig-mag/'),
            'tags'  => array(),
        ),
        array(
            'title' => 'СВАРКА TIG',
            'text'  => '',
            'count' => '',
            'image' => svarka_blue_asset_url('assets/images/cat-tig.png'),
            'url'   => home_url('/catalog/svarka-tig/'),
            'tags'  => array(),
        ),
        array(
            'title' => 'ЛАЗЕРНАЯ СВАРКА, РЕЗКА И ОЧИСТКА',
            'text'  => '',
            'count' => '',
            'image' => svarka_blue_asset_url('assets/images/cat-laser.png'),
            'url'   => home_url('/catalog/lazernaya-svarka/'),
            'tags'  => array(),
        ),
        array(
            'title' => 'ПЛАЗМЕННАЯ РЕЗКА CUT',
            'text'  => '',
            'count' => '',
            'image' => svarka_blue_asset_url('assets/images/cat-plasma.png'),
            'url'   => home_url('/catalog/plazmennaya-rezka/'),
            'tags'  => array(),
        ),
        array(
            'title' => 'СВАРОЧНЫЕ ТРАКТОРЫ',
            'text'  => '',
            'count' => '',
            'image' => svarka_blue_asset_url('assets/images/cat-tractor.png'),
            'url'   => home_url('/catalog/svarochnye-traktory/'),
            'tags'  => array(),
        ),
        array(
            'title' => 'СВАРКА MMA',
            'text'  => '',
            'count' => '',
            'image' => svarka_blue_asset_url('assets/images/cat-mma.png'),
            'url'   => home_url('/catalog/svarka-mma/'),
            'tags'  => array(),
        ),
    );
}

function svarka_blue_home_equipment_cards() {
    $categories = svarka_blue_catalog_categories();
    $images = array(
        'assets/images/ChatGPT Image 30 апр. 2026 г., 13_24_54.png',
        'assets/images/ChatGPT Image 30 апр. 2026 г., 13_26_31 (1).png',
        'assets/images/ChatGPT Image 30 апр. 2026 г., 13_26_32 (2).png',
        'assets/images/ChatGPT Image 30 апр. 2026 г., 13_27_00.png',
    );
    $cards = array();

    for ($index = 0; $index < 9; $index++) {
        $category = $categories[$index % count($categories)];
        $category['image'] = svarka_blue_asset_url($images[$index % count($images)]);
        $cards[] = $category;
    }

    return $cards;
}

function svarka_blue_partner_logos() {
    $labels = array('КУРГАНХИММАШ', 'ЮТ', 'ОСК', 'УРАЛХИММАШ', 'НАКС', 'БТС-МОСТ', 'АНЕКО', 'КЭВРЗ', 'ПТПА', 'ГЕЙЗЕР', 'ЕКС', 'ЗМК МАМИ');
    $images = array(
        'assets/images/a5f8imvx3p06i1jt17r0yeqlh8amv4k9.jpg',
        'assets/images/h9xhqpunk1obk6jma2d93o0labg4c57r.jpg',
    );

    return array_map(
        static function ($label, $index) use ($images) {
            return array(
                'label' => $label,
                'image' => svarka_blue_asset_url($images[$index % count($images)]),
            );
        },
        $labels,
        array_keys($labels)
    );
}

function svarka_blue_news_items() {
    return array(
        array(
            'date'  => '01.11.2025',
            'title' => 'Демозал HUGONG - открылся демонстрационный зал сварочного оборудования в Екатеринбурге',
            'image' => svarka_blue_asset_url('assets/images/news-demozal.png'),
        ),
        array(
            'date'  => '21.03.2025',
            'title' => 'Hugong на выставке Металлообработка.Сварка 2025',
            'image' => svarka_blue_asset_url('assets/images/news-expo.png'),
        ),
        array(
            'date'  => '14.10.2024',
            'title' => 'Hugong на Международной выставке Weldex',
            'image' => svarka_blue_asset_url('assets/images/news-weldex.png'),
        ),
        array(
            'date'  => '02.10.2024',
            'title' => 'Hugong на специализированной выставке для сварки и резки ПРОФСВАРКА в Минске',
            'image' => svarka_blue_asset_url('assets/images/news-profsvar.png'),
        ),
    );
}

function svarka_blue_featured_products() {
    return array(
        array(
            'title' => 'Промышленный полуавтомат EXTREMIG 200 III LCD',
            'sku'   => '029650',
            'image' => svarka_blue_asset_url('assets/images/cat-migmag.png'),
            'category' => 'MIG/MAG',
            'url'   => home_url('/catalog/svarka-mig-mag/extremig-200-iii-lcd/'),
            'specs' => array('200 А', '220 В', 'LCD', '2/4T'),
            'status' => 'В наличии',
        ),
        array(
            'title' => 'Аргонодуговой аппарат TIG 200 PULSE AC/DC',
            'sku'   => '031184',
            'image' => svarka_blue_asset_url('assets/images/cat-tig.png'),
            'category' => 'TIG',
            'url'   => home_url('/catalog/svarka-tig/tig-200-pulse-acdc/'),
            'specs' => array('200 А', 'AC/DC', 'Pulse', 'HF'),
            'status' => 'Под заказ',
        ),
        array(
            'title' => 'Установка лазерной очистки LASERCLEAN 1500',
            'sku'   => '040512',
            'image' => svarka_blue_asset_url('assets/images/cat-laser.png'),
            'category' => 'Laser',
            'url'   => home_url('/catalog/lazernaya-svarka/laserclean-1500/'),
            'specs' => array('1500 Вт', 'CW', 'ручная', '3 фазы'),
            'status' => 'В наличии',
        ),
        array(
            'title' => 'Аппарат плазменной резки CUT 100 INDUSTRIAL',
            'sku'   => '018430',
            'image' => svarka_blue_asset_url('assets/images/cat-plasma.png'),
            'category' => 'CUT',
            'url'   => home_url('/catalog/plazmennaya-rezka/cut-100-industrial/'),
            'specs' => array('100 А', '380 В', 'CNC', 'Pilot Arc'),
            'status' => 'В наличии',
        ),
    );
}

function svarka_blue_filter_groups() {
    return array(
        'Процесс' => array('MIG/MAG', 'TIG', 'MMA', 'CUT', 'Laser'),
        'Питание' => array('220 В', '380 В', '220/380 В'),
        'Назначение' => array('Сервис', 'Производство', 'Монтаж', 'Роботизация'),
    );
}

function svarka_blue_footer_links() {
    return array(
        'ПРОДУКЦИЯ' => array(
            'СВАРКА MMA' => home_url('/catalog/svarka-mma/'),
            'СВАРКА MIG/MAG' => home_url('/catalog/svarka-mig-mag/'),
            'СВАРКА TIG' => home_url('/catalog/svarka-tig/'),
            'ПЛАЗМЕННАЯ РЕЗКА' => home_url('/catalog/plazmennaya-rezka/'),
            'СВАРОЧНЫЕ ТРАКТОРЫ' => home_url('/catalog/svarochnye-traktory/'),
            'ЛАЗЕРНАЯ СВАРКА' => home_url('/catalog/lazernaya-svarka/'),
            'ДОП. ОБОРУДОВАНИЕ' => home_url('/catalog/dopolnitelnoe-oborudovanie/'),
        ),
        'НОВОСТИ' => array(
            'БЛОГ' => home_url('/news/'),
            'ВИДЕО ОБЗОРЫ' => home_url('/video/'),
            'КАТАЛОГИ ДЛЯ СКАЧИВАНИЯ' => home_url('/downloads/'),
        ),
        'О КОМПАНИИ' => array(
            'СТАТЬ ДИЛЕРОМ HUGONG' => home_url('/dealer/'),
            'ГДЕ КУПИТЬ' => home_url('/where-to-buy/'),
            'ДЕМОЗАЛ МОСКВА' => home_url('/showroom-moscow/'),
            'ДЕМОЗАЛ ЕКАТЕРИНБУРГ' => home_url('/showroom-ekaterinburg/'),
            'ВАКАНСИИ' => home_url('/jobs/'),
            'ДЕКЛАРАЦИИ И СЕРТИФИКАТЫ' => home_url('/certificates/'),
            'ГАРАНТИИ' => home_url('/warranty/'),
            'ОПЛАТА И ДОСТАВКА' => home_url('/delivery/'),
            'ВОЗВРАТ ИЛИ ЗАМЕНА ОБОРУДОВАНИЯ' => home_url('/returns/'),
            'КАРТА САЙТА' => home_url('/sitemap/'),
        ),
    );
}
