<?php
/**
 * Catalog page template.
 *
 * @package SvarkaBlue
 */

if (!defined('ABSPATH')) {
    exit;
}

$catalog = function_exists('svarka_pm_get_catalog_context') ? svarka_pm_get_catalog_context() : array(
    'categories'     => array(),
    'category'       => '',
    'search'         => '',
    'sort'           => 'title',
    'filter_groups'  => array(),
    'active_filters' => array(),
    'products'       => array(),
    'total'          => 0,
    'page'           => 1,
    'per_page'       => 20,
    'total_pages'    => 1,
    'showing_from'   => 0,
    'showing_to'     => 0,
);
$catalog_url = function_exists('svarka_pm_catalog_url') ? svarka_pm_catalog_url() : home_url('/catalog/');
$catalog_query_args = array();
if ($catalog['category']) {
    $catalog_query_args['product_category'] = $catalog['category'];
}
if ($catalog['search']) {
    $catalog_query_args['catalog_search'] = $catalog['search'];
}
if ($catalog['sort'] && $catalog['sort'] !== 'title') {
    $catalog_query_args['sort'] = $catalog['sort'];
}
if ($catalog['active_filters']) {
    $catalog_query_args['filter'] = $catalog['active_filters'];
}

get_header();
?>

<main class="site-main">
    <section class="page-hero page-hero--catalog">
        <div class="container page-hero__grid">
            <div>
                <span class="eyebrow">Каталог HUGONG</span>
                <h1>Оборудование для сварки, охлаждения и производства</h1>
            </div>
        </div>
    </section>

    <section class="section section--workbench">
        <div class="container">
            <div class="catalog-tabs" aria-label="Категории каталога">
                <a class="<?php echo $catalog['category'] === '' ? 'is-active' : ''; ?>" href="<?php echo esc_url($catalog_url); ?>">Все товары</a>
                <?php foreach ($catalog['categories'] as $category) : ?>
                    <?php
                    $category_url = add_query_arg('product_category', $category->slug, $catalog_url);
                    ?>
                    <a class="<?php echo $catalog['category'] === $category->slug ? 'is-active' : ''; ?>" href="<?php echo esc_url($category_url); ?>">
                        <?php echo esc_html($category->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="catalog-layout">
                <div class="product-area__bar">
                    <div class="product-area__summary">
                        <strong>Найдено: <?php echo esc_html((string) $catalog['total']); ?></strong>
                        <span>
                            <?php if ($catalog['total']) : ?>
                                <?php echo esc_html('показано ' . $catalog['showing_from'] . '-' . $catalog['showing_to'] . ' из ' . $catalog['total']); ?>
                            <?php else : ?>
                                <?php echo $catalog['category'] ? 'товары выбранной категории' : 'все загруженные товары'; ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <form class="catalog-toolbar__search" role="search" method="get" action="<?php echo esc_url($catalog_url); ?>">
                        <?php if ($catalog['category']) : ?>
                            <input type="hidden" name="product_category" value="<?php echo esc_attr($catalog['category']); ?>">
                        <?php endif; ?>
                        <?php if ($catalog['sort'] && $catalog['sort'] !== 'title') : ?>
                            <input type="hidden" name="sort" value="<?php echo esc_attr($catalog['sort']); ?>">
                        <?php endif; ?>
                        <?php foreach ($catalog['active_filters'] as $filter_key => $values) : ?>
                            <?php foreach ($values as $value) : ?>
                                <input type="hidden" name="filter[<?php echo esc_attr($filter_key); ?>][]" value="<?php echo esc_attr($value); ?>">
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <label class="screen-reader-text" for="catalog-search-inline">Поиск по каталогу</label>
                        <div class="catalog-toolbar__search-row">
                            <i data-lucide="search"></i>
                            <input id="catalog-search-inline" type="search" name="catalog_search" placeholder="Название или модель" value="<?php echo esc_attr($catalog['search']); ?>">
                            <button class="button button--primary" type="submit">Найти</button>
                        </div>
                    </form>

                    <form class="catalog-toolbar__sort" method="get" action="<?php echo esc_url($catalog_url); ?>">
                        <?php if ($catalog['category']) : ?>
                            <input type="hidden" name="product_category" value="<?php echo esc_attr($catalog['category']); ?>">
                        <?php endif; ?>
                        <?php if ($catalog['search']) : ?>
                            <input type="hidden" name="catalog_search" value="<?php echo esc_attr($catalog['search']); ?>">
                        <?php endif; ?>
                        <?php foreach ($catalog['active_filters'] as $filter_key => $values) : ?>
                            <?php foreach ($values as $value) : ?>
                                <input type="hidden" name="filter[<?php echo esc_attr($filter_key); ?>][]" value="<?php echo esc_attr($value); ?>">
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <label>
                            Сортировка
                            <select name="sort">
                                <option value="title" <?php selected($catalog['sort'], 'title'); ?>>По названию</option>
                                <option value="price_asc" <?php selected($catalog['sort'], 'price_asc'); ?>>Цена по возрастанию</option>
                                <option value="price_desc" <?php selected($catalog['sort'], 'price_desc'); ?>>Цена по убыванию</option>
                            </select>
                        </label>
                    </form>
                </div>

                <aside class="filter-panel" aria-label="Фильтры каталога">
                    <form class="catalog-filter-form" method="get" action="<?php echo esc_url($catalog_url); ?>">
                        <?php if ($catalog['category']) : ?>
                            <input type="hidden" name="product_category" value="<?php echo esc_attr($catalog['category']); ?>">
                        <?php endif; ?>
                        <?php if ($catalog['search']) : ?>
                            <input type="hidden" name="catalog_search" value="<?php echo esc_attr($catalog['search']); ?>">
                        <?php endif; ?>
                        <?php if ($catalog['sort'] && $catalog['sort'] !== 'title') : ?>
                            <input type="hidden" name="sort" value="<?php echo esc_attr($catalog['sort']); ?>">
                        <?php endif; ?>

                        <div class="filter-panel__head">
                            <h3>Фильтр</h3>
                            <a href="<?php echo esc_url($catalog['category'] ? add_query_arg('product_category', $catalog['category'], $catalog_url) : $catalog_url); ?>">Сбросить</a>
                        </div>

                        <?php if ($catalog['filter_groups']) : ?>
                            <?php foreach ($catalog['filter_groups'] as $group) : ?>
                                <?php
                                $active_values = isset($catalog['active_filters'][$group['key']]) ? $catalog['active_filters'][$group['key']] : array();
                                ?>
                                <details class="filter-group" <?php echo $active_values ? 'open' : ''; ?>>
                                    <summary><?php echo esc_html($group['label']); ?></summary>
                                    <div class="filter-options filter-options--checks">
                                        <?php foreach ($group['values'] as $value) : ?>
                                            <label>
                                                <input
                                                    type="checkbox"
                                                    name="filter[<?php echo esc_attr($group['key']); ?>][]"
                                                    value="<?php echo esc_attr($value); ?>"
                                                    <?php checked(in_array($value, $active_values, true)); ?>
                                                >
                                                <span></span>
                                                <?php echo esc_html($value); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </details>
                            <?php endforeach; ?>
                            <button class="button button--primary button--full catalog-filter-form__submit" type="submit">Показать</button>
                        <?php else : ?>
                            <p class="catalog-empty-note">Загрузите товары через админку, чтобы появились фильтры.</p>
                        <?php endif; ?>
                    </form>
                </aside>

                <div class="product-area">
                    <?php if ($catalog['products']) : ?>
                        <div class="product-grid">
                            <?php
                            $home_images = array(
                                'assets/images/ChatGPT Image 30 апр. 2026 г., 13_24_54.png',
                                'assets/images/ChatGPT Image 30 апр. 2026 г., 13_26_31 (1).png',
                                'assets/images/ChatGPT Image 30 апр. 2026 г., 13_26_32 (2).png',
                                'assets/images/ChatGPT Image 30 апр. 2026 г., 13_27_00.png',
                            );
                            ?>
                            <?php foreach ($catalog['products'] as $index => $product) : ?>
                                <?php $product['image'] = svarka_blue_asset_url($home_images[$index % count($home_images)]); ?>
                                <article class="product-card">
                                    <a href="<?php echo esc_url($product['url']); ?>" class="product-card__image <?php echo $product['image'] ? '' : 'product-card__image--placeholder'; ?>">
                                        <?php if ($product['image']) : ?>
                                            <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['title']); ?>">
                                        <?php else : ?>
                                            <span><?php echo esc_html(function_exists('mb_substr') ? mb_substr($product['title'], 0, 2) : substr($product['title'], 0, 2)); ?></span>
                                        <?php endif; ?>
                                    </a>
                                    <div class="product-card__body">
                                        <div class="product-card__meta">
                                            <span><?php echo esc_html($product['category']); ?></span>
                                            <span><?php echo esc_html($product['price']); ?></span>
                                        </div>
                                        <h3><a href="<?php echo esc_url($product['url']); ?>"><?php echo esc_html($product['title']); ?></a></h3>
                                        <?php if ($product['specs']) : ?>
                                            <div class="spec-row spec-row--pairs">
                                                <?php foreach ($product['specs'] as $label => $value) : ?>
                                                    <span><?php echo esc_html($label . ': ' . $value); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="product-card__actions">
                                            <a class="button button--secondary" href="<?php echo esc_url($product['url']); ?>">Подробнее</a>
                                            <a class="button button--primary" href="<?php echo esc_url(add_query_arg('product_id', $product['id'], home_url('/contacts/'))); ?>">Заявка</a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($catalog['total_pages'] > 1) : ?>
                            <?php
                            $current_page = (int) $catalog['page'];
                            $total_pages = (int) $catalog['total_pages'];
                            $page_numbers = array_values(array_unique(array_filter(array(
                                1,
                                $current_page - 1,
                                $current_page,
                                $current_page + 1,
                                $total_pages,
                            ), function ($page_number) use ($total_pages) {
                                return $page_number >= 1 && $page_number <= $total_pages;
                            })));
                            sort($page_numbers);
                            $previous_page = max(1, $current_page - 1);
                            $next_page = min($total_pages, $current_page + 1);
                            ?>
                            <nav class="catalog-pagination" aria-label="Навигация по каталогу">
                                <?php if ($current_page > 1) : ?>
                                    <a class="catalog-pagination__arrow" href="<?php echo esc_url(add_query_arg(array_merge($catalog_query_args, array('catalog_page' => $previous_page)), $catalog_url)); ?>" aria-label="Предыдущая страница">Назад</a>
                                <?php else : ?>
                                    <span class="catalog-pagination__arrow is-disabled">Назад</span>
                                <?php endif; ?>

                                <div class="catalog-pagination__pages">
                                    <?php $last_page = 0; ?>
                                    <?php foreach ($page_numbers as $page_number) : ?>
                                        <?php if ($last_page && $page_number > $last_page + 1) : ?>
                                            <span class="catalog-pagination__dots">...</span>
                                        <?php endif; ?>
                                        <?php if ($page_number === $current_page) : ?>
                                            <span class="is-active"><?php echo esc_html((string) $page_number); ?></span>
                                        <?php else : ?>
                                            <a href="<?php echo esc_url(add_query_arg(array_merge($catalog_query_args, array('catalog_page' => $page_number)), $catalog_url)); ?>"><?php echo esc_html((string) $page_number); ?></a>
                                        <?php endif; ?>
                                        <?php $last_page = $page_number; ?>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ($current_page < $total_pages) : ?>
                                    <a class="catalog-pagination__arrow" href="<?php echo esc_url(add_query_arg(array_merge($catalog_query_args, array('catalog_page' => $next_page)), $catalog_url)); ?>" aria-label="Следующая страница">Вперед</a>
                                <?php else : ?>
                                    <span class="catalog-pagination__arrow is-disabled">Вперед</span>
                                <?php endif; ?>
                            </nav>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="catalog-empty">
                            <h2>Товары не найдены</h2>
                            <p>Проверьте фильтры или загрузите Excel-файлы через админку.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
