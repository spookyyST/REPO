<?php
/**
 * Product catalog and Excel import for Svarka.
 *
 * @package SvarkaProductCatalog
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SVARKA_PM_VERSION', '0.1.4');

add_action('init', 'svarka_pm_register_content');
add_action('admin_menu', 'svarka_pm_admin_menu');
add_action('admin_post_svarka_pm_import', 'svarka_pm_handle_import');
add_action('wp_ajax_svarka_pm_import_step', 'svarka_pm_ajax_import_step');
add_action('template_redirect', 'svarka_pm_redirect_catalog_query_params', 1);
add_filter('query_vars', 'svarka_pm_query_vars');
add_filter('template_include', 'svarka_pm_template_include');
add_filter('redirect_canonical', 'svarka_pm_catalog_redirect_canonical', 10, 2);
add_filter('pre_handle_404', 'svarka_pm_pre_handle_catalog_404', 10, 2);

function svarka_pm_register_content() {
    register_post_type('svarka_product', array(
        'labels' => array(
            'name'          => 'Товары',
            'singular_name' => 'Товар',
            'add_new_item'  => 'Добавить товар',
            'edit_item'     => 'Редактировать товар',
        ),
        'public'       => true,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-products',
        'supports'     => array('title', 'editor', 'thumbnail'),
        'has_archive'  => false,
        'rewrite'      => array('slug' => 'catalog/product', 'with_front' => false),
        'show_in_rest' => true,
    ));

    register_taxonomy('svarka_product_category', array('svarka_product'), array(
        'labels' => array(
            'name'          => 'Категории товаров',
            'singular_name' => 'Категория товара',
        ),
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'hierarchical'      => true,
        'rewrite'           => array('slug' => 'catalog/category', 'with_front' => false),
        'show_in_rest'      => true,
    ));

    add_rewrite_rule('^catalog/?$', 'index.php?svarka_catalog=1', 'top');

    if (get_option('svarka_pm_rewrite_version') !== SVARKA_PM_VERSION) {
        flush_rewrite_rules(false);
        update_option('svarka_pm_rewrite_version', SVARKA_PM_VERSION, false);
    }
}

function svarka_pm_query_vars($vars) {
    $vars[] = 'svarka_catalog';
    return $vars;
}

function svarka_pm_template_include($template) {
    $is_catalog = (string) get_query_var('svarka_catalog') === '1' || svarka_pm_request_path() === 'catalog';

    if (!$is_catalog) {
        return $template;
    }

    $catalog_template = get_stylesheet_directory() . '/page-catalog.php';
    return file_exists($catalog_template) ? $catalog_template : $template;
}

function svarka_pm_catalog_url() {
    return home_url('/catalog/');
}

function svarka_pm_request_path() {
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
    $path = trim((string) wp_parse_url($uri, PHP_URL_PATH), '/');
    $home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

    if ($home_path !== '' && ($path === $home_path || strpos($path, $home_path . '/') === 0)) {
        $path = trim(substr($path, strlen($home_path)), '/');
    }

    return $path;
}

function svarka_pm_redirect_catalog_query_params() {
    if (is_admin() || wp_doing_ajax() || svarka_pm_request_path() === 'catalog') {
        return;
    }

    $catalog_keys = array('product_category', 'catalog_search', 'filter', 'sort', 'catalog_page');
    $has_catalog_query = false;
    foreach ($catalog_keys as $key) {
        if (isset($_GET[$key])) {
            $has_catalog_query = true;
            break;
        }
    }

    if (!$has_catalog_query) {
        return;
    }

    $query = array();
    foreach ($catalog_keys as $key) {
        if (isset($_GET[$key])) {
            $query[$key] = svarka_pm_clean_query_value(wp_unslash($_GET[$key]));
        }
    }

    wp_safe_redirect(add_query_arg($query, svarka_pm_catalog_url()), 302);
    exit;
}

function svarka_pm_clean_query_value($value) {
    if (is_array($value)) {
        $clean = array();
        foreach ($value as $key => $item) {
            $clean[sanitize_key((string) $key)] = svarka_pm_clean_query_value($item);
        }
        return $clean;
    }

    return sanitize_text_field((string) $value);
}

function svarka_pm_catalog_redirect_canonical($redirect_url, $requested_url) {
    if (svarka_pm_request_path() === 'catalog') {
        return false;
    }

    return $redirect_url;
}

function svarka_pm_pre_handle_catalog_404($preempt, $wp_query) {
    if (svarka_pm_request_path() !== 'catalog') {
        return $preempt;
    }

    if ($wp_query instanceof WP_Query) {
        $wp_query->is_404 = false;
    }
    status_header(200);

    return true;
}

function svarka_pm_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=svarka_product',
        'Загрузка товаров',
        'Загрузка Excel',
        'manage_options',
        'svarka-product-import',
        'svarka_pm_render_import_page'
    );
}

function svarka_pm_render_import_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $result = get_transient('svarka_pm_last_import_' . get_current_user_id());
    delete_transient('svarka_pm_last_import_' . get_current_user_id());
    $job_id = isset($_GET['svarka_pm_job']) ? sanitize_key((string) $_GET['svarka_pm_job']) : '';
    $job = $job_id ? svarka_pm_load_import_job($job_id) : null;
    ?>
    <div class="wrap">
        <h1>Загрузка товаров из Excel</h1>
        <p>Загружайте файлы отдельно: компрессоры своим форматом, чиллеры своим форматом. Повторная загрузка обновляет товары по названию и типу файла.</p>

        <?php if (is_array($result)) : ?>
            <div class="notice notice-<?php echo empty($result['error']) ? 'success' : 'error'; ?> is-dismissible">
                <p><?php echo esc_html($result['message']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (is_array($job)) : ?>
            <div id="svarka-pm-import-progress" class="notice notice-info" style="padding:16px 18px;margin:18px 0;max-width:1100px;">
                <h2 style="margin:0 0 8px;">Идёт импорт: <?php echo esc_html($job['label']); ?></h2>
                <p style="margin:0 0 12px;">Не закрывайте эту страницу до завершения.</p>
                <progress id="svarka-pm-progress-bar" value="<?php echo esc_attr((string) $job['offset']); ?>" max="<?php echo esc_attr((string) $job['total']); ?>" style="width:100%;height:20px;"></progress>
                <p id="svarka-pm-progress-text" style="margin:10px 0 0;">
                    Обработано <?php echo esc_html((string) $job['offset']); ?> из <?php echo esc_html((string) $job['total']); ?>.
                </p>
            </div>
            <script>
                (function () {
                    var box = document.getElementById('svarka-pm-import-progress');
                    var bar = document.getElementById('svarka-pm-progress-bar');
                    var text = document.getElementById('svarka-pm-progress-text');
                    var done = false;

                    function step() {
                        if (done) {
                            return;
                        }

                        var data = new FormData();
                        data.append('action', 'svarka_pm_import_step');
                        data.append('job', <?php echo wp_json_encode($job_id); ?>);
                        data.append('_ajax_nonce', <?php echo wp_json_encode(wp_create_nonce('svarka_pm_import_step_' . $job_id)); ?>);

                        fetch(ajaxurl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: data
                        })
                            .then(function (response) {
                                return response.json();
                            })
                            .then(function (response) {
                                if (!response || !response.success) {
                                    throw new Error(response && response.data && response.data.message ? response.data.message : 'Ошибка импорта');
                                }

                                var payload = response.data;
                                bar.value = payload.offset;
                                bar.max = payload.total;
                                text.textContent = 'Обработано ' + payload.offset + ' из ' + payload.total + '. Создано: ' + payload.created + ', обновлено: ' + payload.updated + ', пропущено: ' + payload.skipped + '.';

                                if (payload.done) {
                                    done = true;
                                    box.className = 'notice notice-success';
                                    text.textContent = 'Импорт завершён. Создано: ' + payload.created + ', обновлено: ' + payload.updated + ', пропущено: ' + payload.skipped + '.';
                                    return;
                                }

                                window.setTimeout(step, 120);
                            })
                            .catch(function (error) {
                                done = true;
                                box.className = 'notice notice-error';
                                text.textContent = error.message;
                            });
                    }

                    step();
                }());
            </script>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(2,minmax(280px,1fr));gap:24px;max-width:1100px;">
            <?php svarka_pm_render_import_box('compressors', 'Компрессоры EXELUTE', 'Формат: price, название во 2-й колонке, давление, производительность, мощность и т.д.'); ?>
            <?php svarka_pm_render_import_box('chillers', 'Чиллеры ATS', 'Формат: Наименование элемента, Цена "Розничная цена", мощность охлаждения, насос, компрессор и т.д.'); ?>
        </div>
    </div>
    <?php
}

function svarka_pm_render_import_box($type, $title, $description) {
    ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="padding:20px;background:#fff;border:1px solid #dcdcde;border-radius:8px;">
        <h2 style="margin-top:0;"><?php echo esc_html($title); ?></h2>
        <p><?php echo esc_html($description); ?></p>
        <input type="hidden" name="action" value="svarka_pm_import">
        <input type="hidden" name="import_type" value="<?php echo esc_attr($type); ?>">
        <?php wp_nonce_field('svarka_pm_import_' . $type); ?>
        <p><input type="file" name="products_file" accept=".xlsx" required></p>
        <p><button class="button button-primary" type="submit">Загрузить</button></p>
    </form>
    <?php
}

function svarka_pm_handle_import() {
    if (!current_user_can('manage_options')) {
        wp_die('Недостаточно прав.');
    }

    $type = isset($_POST['import_type']) ? sanitize_key((string) $_POST['import_type']) : '';
    if (!in_array($type, array('compressors', 'chillers'), true)) {
        wp_die('Неизвестный тип импорта.');
    }

    check_admin_referer('svarka_pm_import_' . $type);

    $redirect = admin_url('edit.php?post_type=svarka_product&page=svarka-product-import');

    if (empty($_FILES['products_file']['tmp_name']) || !is_uploaded_file($_FILES['products_file']['tmp_name'])) {
        svarka_pm_import_notice('Файл не загружен.', true);
        wp_safe_redirect($redirect);
        exit;
    }

    $name = isset($_FILES['products_file']['name']) ? (string) $_FILES['products_file']['name'] : '';
    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'xlsx') {
        svarka_pm_import_notice('Нужен файл .xlsx.', true);
        wp_safe_redirect($redirect);
        exit;
    }

    $result = svarka_pm_create_import_job((string) $_FILES['products_file']['tmp_name'], $type);
    if ($result['ok']) {
        wp_safe_redirect(add_query_arg('svarka_pm_job', $result['job'], $redirect));
        exit;
    }

    svarka_pm_import_notice($result['message'], true);
    wp_safe_redirect($redirect);
    exit;
}

function svarka_pm_import_notice($message, $error = false) {
    set_transient('svarka_pm_last_import_' . get_current_user_id(), array(
        'message' => $message,
        'error'   => (bool) $error,
    ), 60);
}

function svarka_pm_create_import_job($path, $type) {
    $table = svarka_pm_read_xlsx($path);
    if (is_wp_error($table)) {
        return array('ok' => false, 'message' => $table->get_error_message());
    }

    if (empty($table['rows'])) {
        return array('ok' => false, 'message' => 'В файле нет товарных строк.');
    }

    $config = svarka_pm_import_config($type);
    $job_id = strtolower(wp_generate_password(16, false, false));
    $job = array(
        'id'      => $job_id,
        'type'    => $type,
        'label'   => $config['category'],
        'rows'    => $table['rows'],
        'total'   => count($table['rows']),
        'offset'  => 0,
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'time'    => time(),
    );

    $saved = svarka_pm_save_import_job($job_id, $job);
    if (is_wp_error($saved)) {
        return array('ok' => false, 'message' => $saved->get_error_message());
    }

    return array('ok' => true, 'job' => $job_id, 'message' => 'Файл принят, начинается импорт.');
}

function svarka_pm_import_job_dir() {
    $upload = wp_upload_dir();
    if (!empty($upload['error'])) {
        return new WP_Error('upload_dir_failed', $upload['error']);
    }

    return trailingslashit($upload['basedir']) . 'svarka-import-jobs';
}

function svarka_pm_import_job_path($job_id) {
    $dir = svarka_pm_import_job_dir();
    if (is_wp_error($dir)) {
        return $dir;
    }

    return trailingslashit($dir) . sanitize_key($job_id) . '.json';
}

function svarka_pm_save_import_job($job_id, array $job) {
    $dir = svarka_pm_import_job_dir();
    if (is_wp_error($dir)) {
        return $dir;
    }

    if (!wp_mkdir_p($dir)) {
        return new WP_Error('job_dir_failed', 'Не удалось создать папку для временного файла импорта.');
    }

    $path = svarka_pm_import_job_path($job_id);
    if (is_wp_error($path)) {
        return $path;
    }

    $json = wp_json_encode($job);
    if (!$json || file_put_contents($path, $json, LOCK_EX) === false) {
        return new WP_Error('job_write_failed', 'Не удалось сохранить временный файл импорта.');
    }

    return true;
}

function svarka_pm_load_import_job($job_id) {
    $path = svarka_pm_import_job_path($job_id);
    if (is_wp_error($path) || !file_exists($path)) {
        return null;
    }

    $json = file_get_contents($path);
    $job = $json ? json_decode($json, true) : null;
    return is_array($job) ? $job : null;
}

function svarka_pm_ajax_import_step() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Недостаточно прав.'), 403);
    }

    $job_id = isset($_POST['job']) ? sanitize_key((string) $_POST['job']) : '';
    if ($job_id === '') {
        wp_send_json_error(array('message' => 'Не найден ID импорта.'), 400);
    }

    check_ajax_referer('svarka_pm_import_step_' . $job_id);

    $job = svarka_pm_load_import_job($job_id);
    if (!$job) {
        wp_send_json_error(array('message' => 'Временный файл импорта не найден. Загрузите файл заново.'), 404);
    }

    $config = svarka_pm_import_config((string) $job['type']);
    $batch = 75;
    $start = (int) $job['offset'];
    $end = min((int) $job['total'], $start + $batch);

    for ($i = $start; $i < $end; $i++) {
        $row = isset($job['rows'][$i]) && is_array($job['rows'][$i]) ? $job['rows'][$i] : array();
        $result = svarka_pm_import_row($row, $config, (string) $job['type']);
        $job[$result]++;
    }

    $job['offset'] = $end;
    $done = $end >= (int) $job['total'];

    if ($done) {
        $path = svarka_pm_import_job_path($job_id);
        if (!is_wp_error($path) && file_exists($path)) {
            unlink($path);
        }
    } else {
        $saved = svarka_pm_save_import_job($job_id, $job);
        if (is_wp_error($saved)) {
            wp_send_json_error(array('message' => $saved->get_error_message()), 500);
        }
    }

    wp_send_json_success(array(
        'done'    => $done,
        'offset'  => (int) $job['offset'],
        'total'   => (int) $job['total'],
        'created' => (int) $job['created'],
        'updated' => (int) $job['updated'],
        'skipped' => (int) $job['skipped'],
    ));
}

function svarka_pm_import_xlsx($path, $type) {
    $table = svarka_pm_read_xlsx($path);
    if (is_wp_error($table)) {
        return array('ok' => false, 'message' => $table->get_error_message());
    }

    $config = svarka_pm_import_config($type);
    $created = 0;
    $updated = 0;
    $skipped = 0;

    foreach ($table['rows'] as $row) {
        $result = svarka_pm_import_row($row, $config, $type);
        ${$result}++;
    }

    return array(
        'ok'      => true,
        'message' => sprintf('Импорт завершен: создано %d, обновлено %d, пропущено %d.', $created, $updated, $skipped),
    );
}

function svarka_pm_import_row(array $row, array $config, $type) {
    $title = svarka_pm_row_value($row, $config['title_keys']);
    if ($title === '') {
        return 'skipped';
    }

    $price_raw = svarka_pm_row_value($row, $config['price_keys']);
    $price = svarka_pm_parse_price($price_raw);
    $attrs = svarka_pm_extract_attributes($row, $config['skip_keys'], $type);
    $post_id = svarka_pm_upsert_product($title, $config['category'], $type, $price, $attrs);

    if (!$post_id) {
        return 'skipped';
    }

    if (get_post_meta($post_id, '_svarka_import_was_created', true) === '1') {
        delete_post_meta($post_id, '_svarka_import_was_created');
        return 'created';
    }

    return 'updated';
}

function svarka_pm_import_config($type) {
    if ($type === 'chillers') {
        return array(
            'category'   => 'Чиллеры',
            'title_keys' => array('Наименование элемента'),
            'price_keys' => array('Цена "Розничная цена"'),
            'skip_keys'  => array('Наименование элемента', 'Цена "Розничная цена"'),
        );
    }

    return array(
        'category'   => 'Компрессоры',
        'title_keys' => array('Unnamed: 1', 'column_2', 'Наименование', 'Наименование элемента', 'Название', 'name', 'title'),
        'price_keys' => array('price', 'Цена', 'Цена "Розничная цена"'),
        'skip_keys'  => array('price', 'Цена', 'Цена "Розничная цена"', 'Unnamed: 1', 'column_2', 'Наименование', 'Наименование элемента', 'Название', 'name', 'title'),
    );
}

function svarka_pm_upsert_product($title, $category_name, $source_type, array $price, array $attrs) {
    $import_key = $source_type . ':' . md5(wp_strip_all_tags($title));
    $existing = get_posts(array(
        'post_type'      => 'svarka_product',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_svarka_import_key',
        'meta_value'     => $import_key,
    ));

    $post_data = array(
        'post_type'    => 'svarka_product',
        'post_status'  => 'publish',
        'post_title'   => $title,
        'post_content' => '',
    );

    if ($existing) {
        $post_data['ID'] = (int) $existing[0];
        $post_id = wp_update_post($post_data, true);
    } else {
        $post_id = wp_insert_post($post_data, true);
        if (!is_wp_error($post_id)) {
            update_post_meta((int) $post_id, '_svarka_import_was_created', '1');
        }
    }

    if (is_wp_error($post_id) || !$post_id) {
        return 0;
    }

    $term = term_exists($category_name, 'svarka_product_category');
    if (!$term) {
        $term = wp_insert_term($category_name, 'svarka_product_category');
    }
    if (!is_wp_error($term)) {
        wp_set_object_terms((int) $post_id, array((int) $term['term_id']), 'svarka_product_category');
    }

    update_post_meta((int) $post_id, '_svarka_import_key', $import_key);
    update_post_meta((int) $post_id, '_svarka_source_type', $source_type);
    update_post_meta((int) $post_id, '_svarka_product_price', $price['value']);
    update_post_meta((int) $post_id, '_svarka_product_price_text', $price['text']);
    update_post_meta((int) $post_id, '_svarka_product_attrs', $attrs);

    svarka_pm_replace_attr_meta((int) $post_id, $attrs);

    return (int) $post_id;
}

function svarka_pm_replace_attr_meta($post_id, array $attrs) {
    $source_type = get_post_meta($post_id, '_svarka_source_type', true);
    $attrs = svarka_pm_normalize_attributes($attrs, (string) $source_type);

    $all_meta = get_post_meta($post_id);
    foreach ($all_meta as $key => $_value) {
        if (strpos($key, '_svarka_attr_') === 0) {
            delete_post_meta($post_id, $key);
        }
    }

    $registry = get_option('svarka_pm_attr_labels', array());
    if (!is_array($registry)) {
        $registry = array();
    }

    $keys = array();
    foreach ($attrs as $label => $value) {
        $meta_key = svarka_pm_attr_meta_key($label);
        $keys[] = $meta_key;
        $registry[$meta_key] = $label;
        update_post_meta($post_id, $meta_key, $value);
    }

    update_post_meta($post_id, '_svarka_attr_keys', $keys);
    update_option('svarka_pm_attr_labels', $registry, false);
}

function svarka_pm_extract_attributes(array $row, array $skip_keys, $source_type = '') {
    $skip = array();
    foreach ($skip_keys as $key) {
        $skip[svarka_pm_norm_key($key)] = true;
    }

    $attrs = array();
    foreach ($row as $key => $value) {
        if (isset($skip[svarka_pm_norm_key($key)])) {
            continue;
        }
        $value = svarka_pm_clean_value($value);
        if ($value === '') {
            continue;
        }
        $attrs[$key] = $value;
    }

    return svarka_pm_normalize_attributes($attrs, (string) $source_type);
}

function svarka_pm_row_value(array $row, array $keys) {
    foreach ($keys as $key) {
        foreach ($row as $row_key => $value) {
            if (svarka_pm_norm_key($row_key) === svarka_pm_norm_key($key)) {
                return svarka_pm_clean_value($value);
            }
        }
    }

    return '';
}

function svarka_pm_parse_price($raw) {
    $raw = svarka_pm_clean_value($raw);
    $number = preg_replace('/[^\d.,]/u', '', $raw);
    $number = str_replace(array(' ', ','), array('', '.'), $number);

    if ($number !== '' && is_numeric($number)) {
        return array(
            'value' => (float) $number,
            'text'  => '',
        );
    }

    return array(
        'value' => '',
        'text'  => $raw !== '' ? $raw : 'По запросу',
    );
}

function svarka_pm_attr_meta_key($label) {
    return '_svarka_attr_' . md5((string) $label);
}

function svarka_pm_norm_key($key) {
    $key = (string) $key;
    return trim(function_exists('mb_strtolower') ? mb_strtolower($key) : strtolower($key));
}

function svarka_pm_clean_value($value) {
    if ($value === null) {
        return '';
    }
    if (is_float($value) && floor($value) === $value) {
        $value = (string) (int) $value;
    }
    return trim(wp_strip_all_tags((string) $value));
}

function svarka_pm_product_attrs($post_id) {
    $attrs = get_post_meta($post_id, '_svarka_product_attrs', true);
    $attrs = is_array($attrs) ? $attrs : array();
    $source_type = get_post_meta($post_id, '_svarka_source_type', true);

    return svarka_pm_normalize_attributes($attrs, (string) $source_type);
}

function svarka_pm_normalize_attributes(array $attrs, $source_type = '') {
    $normalized = array();

    foreach ($attrs as $label => $value) {
        $value = svarka_pm_clean_value($value);
        if ($value === '') {
            continue;
        }

        $label = svarka_pm_canonical_attr_label((string) $label, (string) $source_type);
        if (isset($normalized[$label])) {
            $normalized[$label] = svarka_pm_better_attr_value($normalized[$label], $value);
        } else {
            $normalized[$label] = $value;
        }
    }

    return svarka_pm_sort_attributes($normalized, (string) $source_type);
}

function svarka_pm_canonical_attr_label($label, $source_type = '') {
    $key = svarka_pm_attr_signature($label);
    $aliases = svarka_pm_attr_aliases((string) $source_type);

    return isset($aliases[$key]) ? $aliases[$key] : svarka_pm_clean_attr_label($label);
}

function svarka_pm_attr_aliases($source_type = '') {
    $aliases = array(
        'chastotnyjpreobrazovatel' => 'Частотный преобразователь',
        'частотныйпреобразователь' => 'Частотный преобразователь',
        'dvestupeniszhatiya' => 'Две ступени сжатия',
        'двеступенисжатия' => 'Две ступени сжатия',
        'gabaritymm' => 'Габариты (ДхШхВ), мм',
        'габаритыдхшхвмм' => 'Габариты (ДхШхВ), мм',
        'massakg' => 'Масса, кг',
        'массакг' => 'Масса, кг',
        'moshhnostkw' => 'Мощность, кВт',
        'мощностьквт' => 'Мощность, кВт',
        'obemresiveral' => 'Объем ресивера, л',
        'объемресиверал' => 'Объем ресивера, л',
        'resiver' => 'Объем ресивера, л',
        'ресивер' => 'Объем ресивера, л',
        'osushitel' => 'Осушитель',
        'осушитель' => 'Осушитель',
        'prisoedinenie' => 'Присоединение',
        'присоединение' => 'Присоединение',
        'proizvoditelnostm3min' => 'Производительность, м³/мин',
        'производительностьм3мин' => 'Производительность, м³/мин',
        'rabocheedavleniebar' => 'Рабочее давление, бар',
        'рабочеедавлениебар' => 'Рабочее давление, бар',
        'stepenzashhity' => 'Степень защиты',
        'степеньзащиты' => 'Степень защиты',
        'stranaproizvodstva' => 'Страна производства',
        'странапроизводства' => 'Страна производства',
        'temperaturaekspluatacziic' => 'Температура эксплуатации, °C',
        'температураэксплуатацииc' => 'Температура эксплуатации, °C',
        'температураэксплуатациис' => 'Температура эксплуатации, °C',
        'tipelektrodvigatelya' => 'Тип электродвигателя',
        'типэлектродвигателя' => 'Тип электродвигателя',
        'tipohlazhdeniya' => 'Тип охлаждения',
        'типохлаждения' => 'Тип охлаждения',
        'tipprivoda' => 'Тип привода',
        'типпривода' => 'Тип привода',
        'tipszhatiya' => 'Тип сжатия',
        'типсжатия' => 'Тип сжатия',
        'type' => 'Тип',
        'тип' => 'Тип',
        'емкостьбакалитров' => 'Ёмкость бака, л',
        'емкостьбакал' => 'Ёмкость бака, л',
        'встроенныйнасос' => 'Встроенный насос',
        'гарантиямесяцев' => 'Гарантия, месяцев',
        'маркаиспарителя' => 'Марка испарителя',
        'маркакомпрессора' => 'Марка компрессора',
        'марканасоса' => 'Марка насоса',
        'мощностьохлажденияквт' => 'Мощность охлаждения, кВт',
        'напряжениепитанияв' => 'Напряжение питания, В',
        'присоединительныеразмерыдюйм' => 'Присоединительные размеры, дюйм',
        'температураокружающейсредывстандартномисполнении' => 'Температура окружающей среды в стандартном исполнении',
        'температурарабочейжидкостивстандартномисполнении' => 'Температура рабочей жидкости в стандартном исполнении',
        'типиспарителя' => 'Тип испарителя',
        'типкомпрессора' => 'Тип компрессора',
        'хладагент' => 'Хладагент',
        'рабочаяжидкость' => 'Рабочая жидкость',
    );

    if ($source_type === 'chillers') {
        $aliases['moshhnostkw'] = 'Мощность охлаждения, кВт';
    }

    return $aliases;
}

function svarka_pm_clean_attr_label($label) {
    $label = svarka_pm_clean_value($label);
    $label = str_replace(array('mm.', 'kw.', 'kg.', 'l.'), array('мм', 'кВт', 'кг', 'л'), $label);
    return trim($label);
}

function svarka_pm_attr_signature($label) {
    $label = svarka_pm_norm_key($label);
    $label = str_replace(array('ё', '³'), array('е', '3'), $label);
    return (string) preg_replace('/[^\p{L}\p{N}]+/u', '', $label);
}

function svarka_pm_better_attr_value($current, $candidate) {
    $current_has_number = preg_match('/\d/u', (string) $current);
    $candidate_has_number = preg_match('/\d/u', (string) $candidate);

    if (!$current_has_number && $candidate_has_number) {
        return $candidate;
    }

    return $current;
}

function svarka_pm_sort_attributes(array $attrs, $source_type = '') {
    uksort($attrs, function ($a, $b) use ($source_type) {
        $a_order = svarka_pm_attr_order($a, $source_type);
        $b_order = svarka_pm_attr_order($b, $source_type);

        if ($a_order === $b_order) {
            return strnatcasecmp($a, $b);
        }

        return $a_order <=> $b_order;
    });

    return $attrs;
}

function svarka_pm_attr_order($label, $source_type = '') {
    $compressors = array(
        'Частотный преобразователь',
        'Две ступени сжатия',
        'Габариты (ДхШхВ), мм',
        'Масса, кг',
        'Мощность, кВт',
        'Объем ресивера, л',
        'Осушитель',
        'Присоединение',
        'Производительность, м³/мин',
        'Рабочее давление, бар',
        'Степень защиты',
        'Страна производства',
        'Температура эксплуатации, °C',
        'Тип электродвигателя',
        'Тип охлаждения',
        'Тип привода',
        'Тип сжатия',
        'Тип',
    );
    $chillers = array(
        'Ёмкость бака, л',
        'Встроенный насос',
        'Габариты (ДхШхВ), мм',
        'Гарантия, месяцев',
        'Марка испарителя',
        'Марка компрессора',
        'Марка насоса',
        'Масса, кг',
        'Мощность охлаждения, кВт',
        'Напряжение питания, В',
        'Присоединительные размеры, дюйм',
        'Страна производства',
        'Температура окружающей среды в стандартном исполнении',
        'Температура рабочей жидкости в стандартном исполнении',
        'Тип испарителя',
        'Тип компрессора',
        'Тип охлаждения',
        'Хладагент',
        'Рабочая жидкость',
    );
    $order = $source_type === 'chillers' ? $chillers : array_merge($compressors, $chillers);
    $index = array_search($label, array_values(array_unique($order)), true);

    return $index === false ? 1000 : (int) $index;
}

function svarka_pm_read_xlsx($path) {
    if (!class_exists('ZipArchive')) {
        return new WP_Error('xlsx_zip_missing', 'На сервере недоступен ZipArchive, XLSX прочитать нельзя.');
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        return new WP_Error('xlsx_open_failed', 'Не удалось открыть XLSX.');
    }

    $shared = svarka_pm_xlsx_shared_strings($zip);
    $sheet_xml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if (!$sheet_xml) {
        return new WP_Error('xlsx_sheet_missing', 'Не найден первый лист XLSX.');
    }

    $xml = simplexml_load_string($sheet_xml);
    if (!$xml) {
        return new WP_Error('xlsx_xml_failed', 'Не удалось прочитать XML листа.');
    }

    $raw_rows = array();
    foreach ($xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') as $row) {
        $values = array();
        foreach ($row->xpath('./*[local-name()="c"]') as $cell) {
            $ref = (string) $cell['r'];
            $col = svarka_pm_xlsx_col_index($ref);
            $values[$col] = svarka_pm_xlsx_cell_value($cell, $shared);
        }
        if ($values) {
            ksort($values);
            $raw_rows[] = $values;
        }
    }

    if (!$raw_rows) {
        return new WP_Error('xlsx_empty', 'В файле нет строк.');
    }

    $header_row = array_shift($raw_rows);
    $max_col = max(array_keys($header_row));
    $headers = array();
    for ($i = 1; $i <= $max_col; $i++) {
        $header = isset($header_row[$i]) ? svarka_pm_clean_value($header_row[$i]) : '';
        $headers[$i] = $header !== '' ? $header : 'column_' . $i;
    }

    $rows = array();
    foreach ($raw_rows as $raw) {
        $assoc = array();
        $has_value = false;
        foreach ($headers as $i => $header) {
            $value = isset($raw[$i]) ? svarka_pm_clean_value($raw[$i]) : '';
            $assoc[$header] = $value;
            if ($value !== '') {
                $has_value = true;
            }
        }
        if ($has_value) {
            $rows[] = $assoc;
        }
    }

    return array(
        'headers' => array_values($headers),
        'rows'    => $rows,
    );
}

function svarka_pm_xlsx_shared_strings(ZipArchive $zip) {
    $xml_string = $zip->getFromName('xl/sharedStrings.xml');
    if (!$xml_string) {
        return array();
    }

    $xml = simplexml_load_string($xml_string);
    if (!$xml) {
        return array();
    }

    $strings = array();
    foreach ($xml->xpath('//*[local-name()="si"]') as $si) {
        $parts = array();
        foreach ($si->xpath('.//*[local-name()="t"]') as $text) {
            $parts[] = (string) $text;
        }
        $strings[] = implode('', $parts);
    }

    return $strings;
}

function svarka_pm_xlsx_cell_value(SimpleXMLElement $cell, array $shared) {
    $type = (string) $cell['t'];
    $value = svarka_pm_xlsx_child_text($cell, 'v');

    if ($type === 's') {
        $index = $value !== '' ? (int) $value : -1;
        return isset($shared[$index]) ? $shared[$index] : '';
    }

    if ($type === 'inlineStr') {
        $parts = array();
        foreach ($cell->xpath('.//*[local-name()="t"]') as $text) {
            $parts[] = (string) $text;
        }
        return implode('', $parts);
    }

    return $value;
}

function svarka_pm_xlsx_child_text(SimpleXMLElement $node, $name) {
    $children = $node->xpath('./*[local-name()="' . $name . '"]');
    return isset($children[0]) ? (string) $children[0] : '';
}

function svarka_pm_xlsx_col_index($cell_ref) {
    $letters = preg_replace('/[^A-Z]/', '', strtoupper((string) $cell_ref));
    $number = 0;
    for ($i = 0, $len = strlen($letters); $i < $len; $i++) {
        $number = $number * 26 + (ord($letters[$i]) - 64);
    }
    return max(1, $number);
}

function svarka_pm_get_catalog_context() {
    $category = isset($_GET['product_category']) ? sanitize_title((string) $_GET['product_category']) : '';
    $search = isset($_GET['catalog_search']) ? sanitize_text_field((string) $_GET['catalog_search']) : '';
    $filters = isset($_GET['filter']) && is_array($_GET['filter']) ? wp_unslash($_GET['filter']) : array();
    $sort = isset($_GET['sort']) ? sanitize_key((string) $_GET['sort']) : 'title';
    $per_page = 20;
    $page = isset($_GET['catalog_page']) ? max(1, absint($_GET['catalog_page'])) : 1;

    $base_ids = svarka_pm_query_product_ids($category, $search, array(), $sort);
    $filter_groups = svarka_pm_build_filter_groups($base_ids);
    $active_filters = svarka_pm_normalize_filters($filters, $filter_groups);
    $product_ids = svarka_pm_query_product_ids($category, $search, $active_filters, $sort);
    $total = count($product_ids);
    $total_pages = max(1, (int) ceil($total / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;
    $page_ids = array_slice($product_ids, $offset, $per_page);

    return array(
        'categories'     => svarka_pm_catalog_categories(),
        'category'       => $category,
        'search'         => $search,
        'sort'           => $sort,
        'filter_groups'  => $filter_groups,
        'active_filters' => $active_filters,
        'products'       => svarka_pm_products_from_ids($page_ids),
        'total'          => $total,
        'page'           => $page,
        'per_page'       => $per_page,
        'total_pages'    => $total_pages,
        'showing_from'   => $total > 0 ? $offset + 1 : 0,
        'showing_to'     => $total > 0 ? min($total, $offset + count($page_ids)) : 0,
    );
}

function svarka_pm_query_product_ids($category_slug, $search, array $filters, $sort) {
    $args = array(
        'post_type'      => 'svarka_product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        's'              => $search,
    );

    if ($category_slug !== '') {
        $args['tax_query'] = array(array(
            'taxonomy' => 'svarka_product_category',
            'field'    => 'slug',
            'terms'    => $category_slug,
        ));
    }

    if ($sort === 'price_asc' || $sort === 'price_desc') {
        $args['meta_key'] = '_svarka_product_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = $sort === 'price_asc' ? 'ASC' : 'DESC';
    } else {
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
    }

    $ids = array_map('intval', get_posts($args));

    return $filters ? svarka_pm_filter_product_ids_by_attrs($ids, $filters) : $ids;
}

function svarka_pm_normalize_filters(array $raw_filters, array $filter_groups) {
    $allowed = array();
    foreach ($filter_groups as $group) {
        $allowed[$group['key']] = array_fill_keys($group['values'], true);
    }

    $filters = array();
    foreach ($raw_filters as $key => $values) {
        $key = sanitize_text_field((string) $key);
        if (!isset($allowed[$key])) {
            continue;
        }
        $values = is_array($values) ? $values : array($values);
        foreach ($values as $value) {
            $value = svarka_pm_clean_value($value);
            if ($value !== '' && isset($allowed[$key][$value])) {
                $filters[$key][] = $value;
            }
        }
    }

    return $filters;
}

function svarka_pm_build_filter_groups(array $product_ids) {
    $groups = array();
    foreach ($product_ids as $post_id) {
        $source_type = get_post_meta($post_id, '_svarka_source_type', true);
        $attrs = svarka_pm_product_attrs($post_id);
        foreach ($attrs as $label => $value) {
            $key = svarka_pm_attr_meta_key($label);
            if (!isset($groups[$key])) {
                $groups[$key] = array(
                    'key'    => $key,
                    'label'  => $label,
                    'source' => array(),
                    'values' => array(),
                );
            }
            if ($source_type !== '') {
                $groups[$key]['source'][(string) $source_type] = true;
            }
            $groups[$key]['values'][$value] = true;
        }
    }

    foreach ($groups as $key => $group) {
        $values = array_keys($group['values']);
        natcasesort($values);
        $groups[$key]['values'] = array_values($values);
    }

    uasort($groups, function ($a, $b) {
        $a_source = isset($a['source']['chillers']) && count($a['source']) === 1 ? 'chillers' : '';
        $b_source = isset($b['source']['chillers']) && count($b['source']) === 1 ? 'chillers' : '';
        $a_order = svarka_pm_attr_order($a['label'], $a_source);
        $b_order = svarka_pm_attr_order($b['label'], $b_source);

        if ($a_order === $b_order) {
            return strnatcasecmp($a['label'], $b['label']);
        }

        return $a_order <=> $b_order;
    });

    return array_values($groups);
}

function svarka_pm_filter_product_ids_by_attrs(array $ids, array $filters) {
    $matched = array();
    foreach ($ids as $post_id) {
        $attrs = svarka_pm_product_attrs($post_id);
        $attr_values = array();

        foreach ($attrs as $label => $value) {
            $attr_values[svarka_pm_attr_meta_key($label)] = svarka_pm_clean_value($value);
        }

        foreach ($filters as $filter_key => $values) {
            $values = array_map('svarka_pm_clean_value', (array) $values);
            if (!isset($attr_values[$filter_key]) || !in_array($attr_values[$filter_key], $values, true)) {
                continue 2;
            }
        }

        $matched[] = $post_id;
    }

    return $matched;
}

function svarka_pm_products_from_ids(array $ids) {
    $products = array();
    foreach ($ids as $post_id) {
        $attrs = svarka_pm_product_attrs($post_id);
        $terms = get_the_terms($post_id, 'svarka_product_category');
        $category = $terms && !is_wp_error($terms) ? $terms[0]->name : '';
        $price = get_post_meta($post_id, '_svarka_product_price', true);
        $price_text = get_post_meta($post_id, '_svarka_product_price_text', true);

        $products[] = array(
            'id'         => $post_id,
            'title'      => get_the_title($post_id),
            'url'        => get_permalink($post_id),
            'category'   => $category,
            'price'      => $price !== '' ? number_format_i18n((float) $price, 0) . ' ₽' : ($price_text ?: 'По запросу'),
            'attrs'      => $attrs,
            'specs'      => array_slice($attrs, 0, 4, true),
            'image'      => svarka_pm_catalog_placeholder_image(),
        );
    }
    return $products;
}

function svarka_pm_catalog_placeholder_image() {
    $relative = 'assets/images/ChatGPT Image 30 апр. 2026 г., 13_24_54.png';
    $path = get_stylesheet_directory() . '/' . $relative;

    if (file_exists($path)) {
        return get_stylesheet_directory_uri() . '/' . $relative;
    }

    return '';
}

function svarka_pm_catalog_categories() {
    $terms = get_terms(array(
        'taxonomy'   => 'svarka_product_category',
        'hide_empty' => false,
    ));
    if (is_wp_error($terms)) {
        return array();
    }
    return $terms;
}
