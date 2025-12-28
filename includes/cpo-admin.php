<?php
if (!defined('ABSPATH')) exit;

add_action('admin_enqueue_scripts', 'cpo_admin_assets');
function cpo_admin_assets($hook) {
    if (!CPO_Core::has_access()) return;

    $allowed = ['custom-prices-products', 'custom-prices-categories', 'custom-prices-orders', 'custom-prices-shortcodes', 'custom-prices-settings', 'custom-prices-product-edit'];
    $is_cpo = false; foreach($allowed as $a) if(strpos($hook, $a)!==false) $is_cpo=true;
    if(!$is_cpo && strpos($hook,'custom-prices')===false) return;

    wp_enqueue_media();
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', [], null, true);
    
    // --- افزودن کتابخانه‌های تقویم شمسی (CDN) ---
    wp_enqueue_style('persian-datepicker-css', 'https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css');
    wp_enqueue_script('persian-date-js', 'https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js', [], null, true);
    wp_enqueue_script('persian-datepicker-js', 'https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js', ['jquery', 'persian-date-js'], null, true);
    // ------------------------------------------

    // توجه: نام فایل JS همان admin.js باقی مانده است
    wp_enqueue_script('cpo-admin-js', CPO_ASSETS_URL . 'js/admin.js', ['jquery', 'wp-i18n', 'chart-js', 'wp-util'], CPO_VERSION, true);

    if (strpos($hook, 'settings') !== false) {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('cpo-color-picker-init', CPO_ASSETS_URL . 'js/admin-color-picker.js', ['wp-color-picker', 'jquery'], CPO_VERSION, true);
    }

    $logo = get_option('cpo_default_product_image');

    wp_localize_script('cpo-admin-js', 'cpo_admin_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cpo_admin_nonce'),
        'logo_url' => $logo ? esc_url($logo) : '',
        'order_statuses' => ['new_order'=>__('سفارش جدید','cpo-full'), 'negotiating'=>__('در حال مذاکره','cpo-full'), 'cancelled'=>__('کنسل شد','cpo-full'), 'completed'=>__('خرید انجام شد','cpo-full')],
        'product_statuses' => ['1'=>__('فعال','cpo-full'), '0'=>__('غیرفعال','cpo-full')],
        'i18n' => [ 'saving'=>__('ذخیره...','cpo-full'), 'save'=>__('ذخیره','cpo-full'), 'cancel'=>__('لغو','cpo-full'), 'error'=>__('خطا','cpo-full'), 'serverError'=>__('خطای سرور','cpo-full'), 'loadingForm'=>__('بارگذاری...','cpo-full') ]
    ]);

    wp_enqueue_style('cpo-admin-css', CPO_ASSETS_URL . 'css/admin.css', [], CPO_VERSION);
}

add_action('admin_menu', 'cpo_admin_menu');
function cpo_admin_menu() {
    if (!CPO_Core::has_access()) return;
    $cap = 'read'; $slug = 'custom-prices-products';
    add_menu_page( __('مدیریت قیمت', 'cpo-full'), __('مدیریت قیمت', 'cpo-full'), $cap, $slug, 'cpo_products_page', 'dashicons-tag', 30 );
    add_submenu_page($slug, __('محصولات', 'cpo-full'), __('محصولات', 'cpo-full'), $cap, $slug, 'cpo_products_page'); 
    add_submenu_page($slug, __('دسته‌بندی‌ها', 'cpo-full'), __('دسته‌بندی‌ها', 'cpo-full'), $cap, 'custom-prices-categories', 'cpo_categories_page');
    add_submenu_page($slug, __('سفارشات', 'cpo-full'), __('سفارشات', 'cpo-full'), $cap, 'custom-prices-orders', 'cpo_orders_page');
    add_submenu_page($slug, __('شورت‌کدها', 'cpo-full'), __('شورت‌کدها', 'cpo-full'), $cap, 'custom-prices-shortcodes', 'cpo_shortcodes_page');
    add_submenu_page($slug, __('تنظیمات', 'cpo-full'), __('تنظیمات', 'cpo-full'), $cap, 'custom-prices-settings', 'cpo_settings_page');
    add_submenu_page( null, __('ویرایش', 'cpo-full'), __('ویرایش', 'cpo-full'), $cap, 'custom-prices-product-edit', 'cpo_product_edit_page' );
}

add_action('admin_menu', 'cpo_add_order_count_bubble', 99);
function cpo_add_order_count_bubble() {
    global $wpdb, $menu; if (!CPO_Core::has_access()) return;
    
    $count = $wpdb->get_var("SELECT COUNT(id) FROM " . CPO_DB_ORDERS . " WHERE status = 'new_order'");
    
    if ($count > 0) {
        foreach ($menu as $key => $value) {
            if ($menu[$key][2] == 'custom-prices-products') {
                $menu[$key][0] .= ' <span class="update-plugins count-' . intval($count) . '"><span class="plugin-count">' . intval($count) . '</span></span>';
                return;
            }
        }
    }
}

function cpo_products_page() { include CPO_TEMPLATES_DIR . 'products.php'; }
function cpo_categories_page() { include CPO_TEMPLATES_DIR . 'categories.php'; }
function cpo_orders_page() { include CPO_TEMPLATES_DIR . 'orders.php'; }
function cpo_settings_page() { include CPO_TEMPLATES_DIR . 'settings.php'; }
function cpo_shortcodes_page() { include CPO_TEMPLATES_DIR . 'shortcodes.php'; }
function cpo_product_edit_page() { include CPO_TEMPLATES_DIR . 'product-edit.php'; }

add_action('admin_init', 'cpo_handle_admin_actions');
function cpo_handle_admin_actions() {
    global $wpdb; $page = isset($_GET['page']) ? $_GET['page'] : '';

    if (isset($_POST['cpo_add_category']) && $page === 'custom-prices-categories') {
        check_admin_referer('cpo_add_cat_action', 'cpo_add_cat_nonce');
        $wpdb->insert(CPO_DB_CATEGORIES, ['name'=>$_POST['name'], 'slug'=>sanitize_title($_POST['slug']?:$_POST['name']), 'image_url'=>$_POST['image_url'], 'created'=>current_time('mysql',1)]);
        wp_redirect(add_query_arg('cpo_message', 'category_added', admin_url('admin.php?page=custom-prices-categories'))); exit;
    }

    if (isset($_POST['cpo_add_product']) && $page === 'custom-prices-products') {
        check_admin_referer('cpo_add_product_action', 'cpo_add_product_nonce');
        $data = ['cat_id'=>$_POST['cat_id'], 'name'=>$_POST['name'], 'price'=>$_POST['price'], 'min_price'=>$_POST['min_price'], 'max_price'=>$_POST['max_price'], 'product_type'=>$_POST['product_type'], 'unit'=>$_POST['unit'], 'load_location'=>$_POST['load_location'], 'is_active'=>$_POST['is_active'], 'description'=>$_POST['description'], 'image_url'=>$_POST['image_url'], 'created'=>current_time('mysql',1), 'last_updated_at'=>current_time('mysql',1)];
        $wpdb->insert(CPO_DB_PRODUCTS, $data);
        $pid = $wpdb->insert_id;
        CPO_Core::save_price_history($pid, $data['price'], 'price');
        wp_redirect(add_query_arg('cpo_message', 'product_added', admin_url('admin.php?page=custom-prices-products'))); exit;
    }

    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $map = ['custom-prices-categories'=>[CPO_DB_CATEGORIES,'cpo_delete_cat_'.$id], 'custom-prices-products'=>[CPO_DB_PRODUCTS,'cpo_delete_product_'.$id], 'custom-prices-orders'=>[CPO_DB_ORDERS,'cpo_delete_order_'.$id]];
        if(isset($map[$page]) && check_admin_referer($map[$page][1])) {
            $wpdb->delete($map[$page][0], ['id'=>$id]);
            wp_redirect(add_query_arg('cpo_message', 'deleted', admin_url('admin.php?page='.$page))); exit;
        }
    }
}

add_action('wp_ajax_cpo_fetch_product_edit_form', 'cpo_fetch_product_edit_form');
function cpo_fetch_product_edit_form() {
    check_ajax_referer('cpo_admin_nonce', 'security');
    if (!CPO_Core::has_access()) wp_send_json_error(['message'=>'عدم دسترسی'], 403);
    $product_id = intval($_GET['id']);
    ob_start(); include CPO_TEMPLATES_DIR . 'product-edit.php'; wp_send_json_success(['html'=>ob_get_clean()]);
}

add_action('wp_ajax_cpo_handle_edit_product_ajax', 'cpo_handle_edit_product_ajax');
function cpo_handle_edit_product_ajax() {
    check_ajax_referer('cpo_edit_product_action', 'cpo_edit_product_nonce');
    if (!CPO_Core::has_access()) wp_send_json_error(['message'=>'عدم دسترسی'], 403);
    global $wpdb; $pid = intval($_POST['product_id']);
    $data = ['cat_id'=>$_POST['cat_id'], 'name'=>$_POST['name'], 'price'=>$_POST['price'], 'min_price'=>$_POST['min_price'], 'max_price'=>$_POST['max_price'], 'product_type'=>$_POST['product_type'], 'unit'=>$_POST['unit'], 'load_location'=>$_POST['load_location'], 'is_active'=>$_POST['is_active'], 'description'=>$_POST['description'], 'image_url'=>$_POST['image_url'], 'last_updated_at'=>current_time('mysql',1)];
    $wpdb->update(CPO_DB_PRODUCTS, $data, ['id'=>$pid]);
    CPO_Core::save_price_history($pid, $data['price'], 'price');
    wp_send_json_success(['message'=>'بروزرسانی شد.']);
}

add_action('wp_ajax_cpo_fetch_category_edit_form', 'cpo_fetch_category_edit_form');
function cpo_fetch_category_edit_form() {
    check_ajax_referer('cpo_admin_nonce', 'security');
    if (!CPO_Core::has_access()) wp_send_json_error(['message'=>'عدم دسترسی'], 403);
    $cat_id = intval($_GET['id']);
    ob_start(); include CPO_TEMPLATES_DIR . 'category-edit.php'; wp_send_json_success(['html'=>ob_get_clean()]);
}

add_action('wp_ajax_cpo_handle_edit_category_ajax', 'cpo_handle_edit_category_ajax');
function cpo_handle_edit_category_ajax() {
    check_ajax_referer('cpo_edit_cat_action', 'cpo_edit_cat_nonce');
    if (!CPO_Core::has_access()) wp_send_json_error(['message'=>'عدم دسترسی'], 403);
    global $wpdb;
    $wpdb->update(CPO_DB_CATEGORIES, ['name'=>$_POST['name'], 'slug'=>sanitize_title($_POST['slug']?:$_POST['name']), 'image_url'=>$_POST['image_url']], ['id'=>intval($_POST['category_id'])]);
    wp_send_json_success(['message'=>'بروزرسانی شد.']);
}

add_action('wp_ajax_cpo_quick_update', 'cpo_quick_update');
function cpo_quick_update() {
    check_ajax_referer('cpo_admin_nonce', 'security');
    if (!CPO_Core::has_access()) wp_send_json_error(['message'=>'عدم دسترسی'], 403);
    global $wpdb; $id = intval($_POST['id']); $field = $_POST['field']; $val = $_POST['value'];
    $table = ($_POST['table_type']=='products') ? CPO_DB_PRODUCTS : (($_POST['table_type']=='orders') ? CPO_DB_ORDERS : CPO_DB_CATEGORIES);
    
    $data = [$field => $val];
    if ($_POST['table_type']=='products') {
        $data['last_updated_at'] = current_time('mysql', 1);
        if(in_array($field, ['price','min_price','max_price'])) CPO_Core::save_price_history($id, $val, $field);
    }
    $wpdb->update($table, $data, ['id'=>$id]);
    wp_send_json_success(['message'=>'بروز شد', 'new_time'=>date_i18n('Y/m/d H:i:s', current_time('timestamp'))]);
}

add_action('wp_ajax_cpo_test_email', 'cpo_ajax_test_email');
function cpo_ajax_test_email() {
    check_ajax_referer('cpo_admin_nonce', 'security');
    if(!CPO_Core::has_access()) wp_send_json_error(['log'=>'عدم دسترسی'], 403);
    $sent = wp_mail(get_option('cpo_admin_email'), 'Test', 'Test Body');
    wp_send_json_success(['log'=>$sent?'Success':'Failed']);
}

add_action('wp_ajax_cpo_test_sms', 'cpo_ajax_test_sms');
function cpo_ajax_test_sms() {
    check_ajax_referer('cpo_admin_nonce', 'security');
    if(!CPO_Core::has_access()) wp_send_json_error(['log'=>'عدم دسترسی'], 403);
    
    if (!class_exists('CPO_Full_SMS')) {
         wp_send_json_error(['log'=>'کلاس پیامک (CPO_Full_SMS) یافت نشد.']);
    }

    $vars = [
        '{product_name}' => 'محصول تستی', 
        '{customer_name}' => 'مدیر سایت', 
        '{phone}' => get_option('cpo_admin_phone'), 
        '{qty}' => '1', 
        '{unit}' => 'عدد', 
        '{load_location}' => 'تست', 
        '{note}' => 'پیامک آزمایشی سیستم CPO'
    ];
    
    $result = CPO_Full_SMS::send_notification($vars);
    
    if ($result) {
        wp_send_json_success(['log'=>'پیامک تست با موفقیت به وب‌سرویس ارسال شد.']);
    } else {
        wp_send_json_error(['log'=>'ارسال ناموفق بود. لطفاً بررسی کنید: 1. کلید API صحیح باشد. 2. کد پترن در IPPanel تایید شده باشد. 3. شماره فرستنده و گیرنده صحیح باشد.']);
    }
}

// ----------------------------------------------------
// تغییرات جدید: مدیریت اصلاح تاریخچه قیمت (Price History Correction)
// ----------------------------------------------------

// 1. دریافت فرم و لیست تاریخچه
add_action('wp_ajax_cpo_fetch_price_history', 'cpo_fetch_price_history');
function cpo_fetch_price_history() {
    check_ajax_referer('cpo_admin_nonce', 'security');
    if (!CPO_Core::has_access()) wp_send_json_error(['message'=>'عدم دسترسی'], 403);
    
    global $wpdb;
    $pid = intval($_GET['product_id']);
    $product = $wpdb->get_row($wpdb->prepare("SELECT name FROM ".CPO_DB_PRODUCTS." WHERE id=%d", $pid));
    
    // دریافت تاریخچه به ترتیب نزولی (جدیدترین اول)
    $history = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".CPO_DB_PRICE_HISTORY." WHERE product_id=%d ORDER BY change_time DESC", $pid));
    
    ob_start();
    ?>
    <div class="cpo-history-manager" style="padding: 10px;">
        <h3><?php echo sprintf(__('تاریخچه قیمت: %s', 'cpo-full'), esc_html($product->name)); ?></h3>
        
        <div class="cpo-add-history-box" style="background:#f1f1f1; padding:15px; border-radius:5px; margin-bottom:15px; border:1px solid #ccc;">
            <h4 style="margin-top:0;"><?php _e('افزودن قیمت جا افتاده / جدید', 'cpo-full'); ?></h4>
            <form id="cpo-add-history-form" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
                <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                
                <div style="width: 180px;">
                    <label style="font-size:12px;"><?php _e('تاریخ و زمان', 'cpo-full'); ?></label><br>
                    <input type="text" class="cpo-persian-date-input regular-text" style="width:100%; text-align:center;" autocomplete="off" placeholder="انتخاب تاریخ">
                    <input type="hidden" name="change_time" id="cpo-real-date-input">
                </div>
                <div>
                    <label style="font-size:12px;"><?php _e('قیمت پایه', 'cpo-full'); ?></label><br>
                    <input type="text" name="price" class="small-text" placeholder="0">
                </div>
                <div>
                    <label style="font-size:12px;"><?php _e('حداقل', 'cpo-full'); ?></label><br>
                    <input type="text" name="min_price" class="small-text" placeholder="Min">
                </div>
                <div>
                    <label style="font-size:12px;"><?php _e('حداکثر', 'cpo-full'); ?></label><br>
                    <input type="text" name="max_price" class="small-text" placeholder="Max">
                </div>
                <div>
                    <button type="submit" class="button button-primary"><?php _e('افزودن', 'cpo-full'); ?></button>
                </div>
            </form>
        </div>

        <div class="notice notice-info inline" style="margin-bottom: 10px;"><p><?php _e('برای ویرایش مقادیر، روی سلول‌ها **دوبار کلیک** کنید.', 'cpo-full'); ?></p></div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 25%;"><?php _e('تاریخ ثبت', 'cpo-full'); ?></th>
                    <th><?php _e('قیمت پایه', 'cpo-full'); ?></th>
                    <th><?php _e('حداقل', 'cpo-full'); ?></th>
                    <th><?php _e('حداکثر', 'cpo-full'); ?></th>
                    <th style="width:60px;"><?php _e('حذف', 'cpo-full'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if($history): foreach($history as $row): ?>
                <tr data-history-id="<?php echo $row->id; ?>">
                    <td class="cpo-history-editable" data-field="change_time" data-type="datetime" style="direction:ltr; text-align:left;">
                        <?php echo $row->change_time; ?>
                    </td>
                    <td class="cpo-history-editable" data-field="price">
                        <?php echo number_format((float)str_replace(',','',$row->price)); ?>
                    </td>
                    <td class="cpo-history-editable" data-field="min_price">
                        <?php echo number_format((float)str_replace(',','',$row->min_price)); ?>
                    </td>
                    <td class="cpo-history-editable" data-field="max_price">
                        <?php echo number_format((float)str_replace(',','',$row->max_price)); ?>
                    </td>
                    <td>
                        <button type="button" class="button-link-delete cpo-delete-history" style="color:red; text-decoration:none;">&times; حذف</button>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5"><?php _e('هیچ تاریخچه‌ای یافت نشد.', 'cpo-full'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    wp_send_json_success(['html' => ob_get_clean()]);
}

// 2. افزودن رکورد جدید
add_action('wp_ajax_cpo_add_history_record', 'cpo_add_history_record');
function cpo_add_history_record() {
    check_ajax_referer('cpo_admin_nonce', 'security');
    if (!CPO_Core::has_access()) wp_send_json_error(['message'=>'عدم دسترسی'], 403);
    
    global $wpdb;
    $pid = intval($_POST['product_id']);
    
    // دریافت تاریخ از فیلد مخفی (که توسط دیت‌پیکر به میلادی تبدیل شده است)
    $time = sanitize_text_field($_POST['change_time']); 
    
    // اگر کاربر تاریخی انتخاب نکرده باشد، زمان فعلی درج شود
    if (empty($time)) {
        $time = current_time('mysql', 1);
    }

    $wpdb->insert(CPO_DB_PRICE_HISTORY, [
        'product_id' => $pid,
        'change_time' => $time, // فرمت صحیح: YYYY-MM-DD HH:MM:SS
        'price' => sanitize_text_field($_POST['price']),
        'min_price' => sanitize_text_field($_POST['min_price']),
        'max_price' => sanitize_text_field($_POST['max_price'])
    ]);
    
    wp_send_json_success(['message' => 'رکورد اضافه شد.']);
}

// 3. ویرایش سریع (Double Click) روی تاریخچه
add_action('wp_ajax_cpo_update_history_cell', 'cpo_update_history_cell');
function cpo_update_history_cell() {
    check_ajax_referer('cpo_admin_nonce', 'security');
    if (!CPO_Core::has_access()) wp_send_json_error(['message'=>'عدم دسترسی'], 403);
    
    global $wpdb;
    $id = intval($_POST['id']);
    $field = sanitize_text_field($_POST['field']);
    $value = sanitize_text_field($_POST['value']);
    
    // لیست فیلدهای مجاز
    if(!in_array($field, ['price', 'min_price', 'max_price', 'change_time'])) wp_send_json_error();

    // حذف کاما از قیمت‌ها برای ذخیره صحیح
    if($field !== 'change_time') {
        $value = str_replace(',', '', $value);
    }

    $wpdb->update(CPO_DB_PRICE_HISTORY, [$field => $value], ['id' => $id]);
    wp_send_json_success(['message' => 'بروز شد']);
}

// 4. حذف رکورد تاریخچه
add_action('wp_ajax_cpo_delete_history_record', 'cpo_delete_history_record');
function cpo_delete_history_record() {
    check_ajax_referer('cpo_admin_nonce', 'security');
    if (!CPO_Core::has_access()) wp_send_json_error(['message'=>'عدم دسترسی'], 403);
    
    global $wpdb;
    $id = intval($_POST['id']);
    $wpdb->delete(CPO_DB_PRICE_HISTORY, ['id' => $id]);
    wp_send_json_success();
}
