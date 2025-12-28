<?php
/**
 * Plugin Name: Custom Prices & Orders
 * Description: افزونه مدیریت هوشمند قیمت و سفارشات (CPO).
 * Version: 3.9.5
 * Author: Mr.NT
 * Author URI: https://21s.ir
 * Update URI: https://21s.ir/updates/cpo/info.json
 */

if (!defined('ABSPATH')) exit;

global $wpdb;
define('CPO_VERSION', '3.9.5');
define('CPO_PATH', plugin_dir_path(__FILE__));
define('CPO_URL', plugin_dir_url(__FILE__));
define('CPO_TEMPLATES_DIR', CPO_PATH . 'templates/');
define('CPO_ASSETS_URL', CPO_URL . 'assets/');

// تغییر نام جداول دیتابیس
define('CPO_DB_PRODUCTS', $wpdb->prefix . 'cpo_products');
define('CPO_DB_ORDERS', $wpdb->prefix . 'cpo_orders');
define('CPO_DB_CATEGORIES', $wpdb->prefix . 'cpo_categories');
define('CPO_DB_PRICE_HISTORY', $wpdb->prefix . 'cpo_price_history');

// تغییر نام فایل‌های اینکلود شده (باید نام فایل‌های واقعی را هم در پوشه تغییر دهید)
require_once(CPO_PATH . 'includes/cpo-core.php');
require_once(CPO_PATH . 'includes/cpo-admin.php');
require_once(CPO_PATH . 'includes/cpo-settings.php');
if (file_exists(CPO_PATH . 'includes/cpo-email.php')) require_once(CPO_PATH . 'includes/cpo-email.php');
if (file_exists(CPO_PATH . 'includes/cpo-sms.php')) require_once(CPO_PATH . 'includes/cpo-sms.php');
if (file_exists(CPO_PATH . 'includes/cpo-updater.php')) require_once(CPO_PATH . 'includes/cpo-updater.php');

register_activation_hook(__FILE__, 'cpo_activate');
function cpo_activate() {
    CPO_Core::create_db_tables();
    if (get_option('cpo_products_per_page') === false) update_option('cpo_products_per_page', 5);
    if (get_option('cpo_admin_capability') === false) update_option('cpo_admin_capability', ['administrator']);
}

add_action('init', 'cpo_init_auto_updater');
function cpo_init_auto_updater() {
    if (class_exists('CPO_Auto_Updater')) {
        new CPO_Auto_Updater(__FILE__, CPO_VERSION, 'https://21s.ir/updates/cpo/info.json');
    }
}

// تغییر شورت‌کدها
add_shortcode('cpo_products_list', 'cpo_products_list_shortcode');
function cpo_products_list_shortcode($atts) {
    ob_start(); echo '<div class="cpo-table-responsive-wrapper cpo-products-list-wrapper">'; include CPO_TEMPLATES_DIR . 'shortcode-list.php'; echo '</div>'; return ob_get_clean();
}

add_shortcode('cpo_products_grid_view', 'cpo_products_grid_view_shortcode');
function cpo_products_grid_view_shortcode($atts) {
    global $wpdb;
    $a = shortcode_atts(array('cat_id' => ''), $atts);
    $cat_ids = [];
    $where_clause = "WHERE is_active = 1";

    if (!empty($a['cat_id'])) {
        $cat_ids = array_map('intval', explode(',', $a['cat_id']));
        if (!empty($cat_ids)) {
            $ids_str = implode(',', $cat_ids);
            $where_clause .= " AND cat_id IN ($ids_str)";
        }
    }

    $products_per_page = max(1, (int) get_option('cpo_products_per_page', 5));
    $products = $wpdb->get_results("SELECT * FROM " . CPO_DB_PRODUCTS . " $where_clause ORDER BY id ASC LIMIT $products_per_page");
    $total_products = $wpdb->get_var("SELECT COUNT(id) FROM " . CPO_DB_PRODUCTS . " $where_clause");
    $categories = CPO_Core::get_all_categories();

    ob_start();
    echo '<div class="cpo-table-responsive-wrapper cpo-grid-view-date-wrapper">';
    include CPO_TEMPLATES_DIR . 'shortcode-grid-view.php';
    echo '</div>';
    return ob_get_clean();
}

add_shortcode('cpo_products_grid_view_no_date', 'cpo_products_grid_view_no_date_shortcode');
function cpo_products_grid_view_no_date_shortcode($atts) {
    global $wpdb;
    $a = shortcode_atts(array('cat_id' => ''), $atts);
    $cat_ids = [];
    $where_clause = "WHERE is_active = 1";

    if (!empty($a['cat_id'])) {
        $cat_ids = array_map('intval', explode(',', $a['cat_id']));
        if (!empty($cat_ids)) {
            $ids_str = implode(',', $cat_ids);
            $where_clause .= " AND cat_id IN ($ids_str)";
        }
    }

    $products_per_page = max(1, (int) get_option('cpo_products_per_page', 5));
    $products = $wpdb->get_results("SELECT * FROM " . CPO_DB_PRODUCTS . " $where_clause ORDER BY id ASC LIMIT $products_per_page");
    $total_products = $wpdb->get_var("SELECT COUNT(id) FROM " . CPO_DB_PRODUCTS . " $where_clause");
    $last_updated_time = $wpdb->get_var("SELECT MAX(last_updated_at) FROM " . CPO_DB_PRODUCTS . " WHERE is_active = 1");
    $categories = CPO_Core::get_all_categories();

    ob_start();
    echo '<div class="cpo-table-responsive-wrapper cpo-grid-view-nodate-wrapper">';
    include CPO_TEMPLATES_DIR . 'shortcode-grid-view-no-date.php';
    echo '</div>';
    return ob_get_clean();
}

add_action('wp_enqueue_scripts', 'cpo_front_assets');
function cpo_front_assets() {
    global $post; $load = false;
    if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'cpo_products_list') || has_shortcode($post->post_content, 'cpo_products_grid_view') || has_shortcode($post->post_content, 'cpo_products_grid_view_no_date'))) $load = true;
    if (isset($_GET['elementor-preview'])) $load = true;

    if ($load) {
        wp_enqueue_style('cpo-front-css', CPO_ASSETS_URL . 'css/front.css', [], CPO_VERSION);
        wp_enqueue_style('cpo-grid-view-css', CPO_ASSETS_URL . 'css/grid-view.css', [], CPO_VERSION);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', [], null, true);
        
        // توجه: نام فایل JS هم باید به front.js یا cpo-front.js تغییر کند (اینجا cpo-front.js فرض شده)
        wp_enqueue_script('cpo-front-js', CPO_ASSETS_URL . 'js/front.js', ['jquery', 'chart-js'], CPO_VERSION, true);

        $logo_url = get_option('cpo_default_product_image');
        wp_localize_script('cpo-front-js', 'cpo_front_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cpo_front_nonce'),
            'logo_url' => $logo_url ? esc_url($logo_url) : '',
            'i18n' => [ 'sending' => 'در حال ارسال...', 'server_error' => 'خطای سرور.', 'view_more' => 'مشاهده بیشتر', 'loading' => 'بارگذاری...', 'no_more_products' => 'محصول دیگری نیست.' ]
        ));
    }
}

add_action('wp_footer', 'cpo_add_modals_to_footer');
function cpo_add_modals_to_footer() {
    if (wp_script_is('cpo-front-js', 'enqueued')) {
        include CPO_TEMPLATES_DIR . 'modals-frontend.php';
        $c1 = get_option('cpo_grid_with_date_button_color', '#ffc107'); $c2 = get_option('cpo_grid_no_date_button_color', '#0073aa');
        echo "<style>.cpo-grid-view-wrapper.with-date-shortcode .filter-btn.active { background-color: $c1 !important; border-color: $c1 !important; color: #fff !important; } .cpo-grid-view-wrapper.no-date-shortcode .filter-btn.active { background-color: $c2 !important; border-color: $c2 !important; color: #fff !important; }</style>";
    }
}

add_action('wp_ajax_cpo_load_more_products', 'cpo_load_more_products');
add_action('wp_ajax_nopriv_cpo_load_more_products', 'cpo_load_more_products');
function cpo_load_more_products() {
    check_ajax_referer('cpo_front_nonce', 'nonce'); global $wpdb;
    $page = max(1, intval($_POST['page'])); $per_page = max(1, (int) get_option('cpo_products_per_page', 5)); $offset = ($page - 1) * $per_page;
    $products = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . CPO_DB_PRODUCTS . " WHERE is_active=1 ORDER BY id ASC LIMIT %d OFFSET %d", $per_page, $offset));
    
    if ($products) {
        ob_start();
        $disable_base = get_option('cpo_disable_base_price', 0);
        $show_img = ($_POST['shortcode_type'] === 'with_date') ? get_option('cpo_grid_with_date_show_image', 1) : get_option('cpo_grid_no_date_show_image', 1);
        $show_date = ($_POST['shortcode_type'] === 'with_date');
        $cart = CPO_ASSETS_URL . 'images/cart-icon.png'; $chart = CPO_ASSETS_URL . 'images/chart-icon.png'; $def_img = get_option('cpo_default_product_image', CPO_ASSETS_URL . 'images/default-product.png');

        foreach ($products as $p) {
            $img = $p->image_url ?: $def_img;
            $min_c = str_replace(',', '', $p->min_price); $max_c = str_replace(',', '', $p->max_price);
            $single = ($min_c == $max_c && is_numeric($min_c));
            ?>
            <tr class="product-row" data-cat-id="<?php echo $p->cat_id; ?>">
                <td class="col-product-name" data-colname="محصول"><?php if($show_img): ?><img src="<?php echo esc_url($img); ?>"><?php endif; ?><span><?php echo esc_html($p->name); ?></span></td>
                <td data-colname="نوع"><?php echo esc_html($p->product_type); ?></td>
                <td data-colname="واحد"><?php echo esc_html($p->unit); ?></td>
                <td data-colname="محل"><?php echo esc_html($p->load_location); ?></td>
                <?php if($show_date): ?><td data-colname="تاریخ"><?php echo date_i18n('Y/m/d H:i', strtotime($p->last_updated_at)); ?></td><?php endif; ?>
                <?php if(!$disable_base): ?><td class="col-price" data-colname="قیمت"><?php echo number_format((float)$p->price); ?></td><?php endif; ?>
                <td class="col-price-range" data-colname="بازه"><?php if($single) echo number_format((float)$min_c); else echo ($p->min_price && $p->max_price) ? number_format((float)$p->min_price).' - '.number_format((float)$p->max_price) : 'تماس بگیرید'; ?></td>
                <td class="col-actions">
                    <button class="cpo-icon-btn cpo-order-btn" data-product-id="<?php echo $p->id; ?>" data-product-name="<?php echo $p->name; ?>" data-product-unit="<?php echo $p->unit; ?>" data-product-location="<?php echo $p->load_location; ?>"><img src="<?php echo $cart; ?>"></button>
                    <button class="cpo-icon-btn cpo-chart-btn" data-product-id="<?php echo $p->id; ?>"><img src="<?php echo $chart; ?>"></button>
                </td>
            </tr>
            <?php
        }
        wp_send_json_success(['html' => ob_get_clean(), 'has_more' => true]);
    } else wp_send_json_success(['html' => '', 'has_more' => false]);
    wp_die();
}
?>
