<?php
if (!defined('ABSPATH')) exit;

add_action('admin_init', 'cpo_register_settings_and_fields');

function cpo_register_settings_and_fields() {
    register_setting('cpo_general_settings_grp', 'cpo_disable_base_price');
    register_setting('cpo_general_settings_grp', 'cpo_products_per_page');
    register_setting('cpo_general_settings_grp', 'cpo_admin_capability', array('type' => 'array', 'sanitize_callback' => 'cpo_sanitize_roles'));

    register_setting('cpo_shortcode_settings_grp', 'cpo_default_product_image');
    register_setting('cpo_shortcode_settings_grp', 'cpo_grid_with_date_show_image');
    register_setting('cpo_shortcode_settings_grp', 'cpo_grid_no_date_show_image');
    register_setting('cpo_shortcode_settings_grp', 'cpo_grid_with_date_button_color');
    register_setting('cpo_shortcode_settings_grp', 'cpo_grid_no_date_button_color');

    register_setting('cpo_notification_settings_grp', 'cpo_enable_email');
    register_setting('cpo_notification_settings_grp', 'cpo_admin_email');
    register_setting('cpo_notification_settings_grp', 'cpo_email_subject_template');
    register_setting('cpo_notification_settings_grp', 'cpo_email_body_template');

    register_setting('cpo_notification_settings_grp', 'cpo_sms_service');
    register_setting('cpo_notification_settings_grp', 'cpo_sms_api_key');
    register_setting('cpo_notification_settings_grp', 'cpo_sms_sender');
    register_setting('cpo_notification_settings_grp', 'cpo_admin_phone');
    register_setting('cpo_notification_settings_grp', 'cpo_sms_pattern_code');
    register_setting('cpo_notification_settings_grp', 'cpo_sms_customer_enable');
    register_setting('cpo_notification_settings_grp', 'cpo_sms_customer_pattern_code');

    add_settings_section('cpo_general_section', null, null, 'cpo_general_settings_page');
    add_settings_section('cpo_shortcode_section', null, null, 'cpo_shortcode_settings_page');
    add_settings_section('cpo_email_test_section', __('تست ارسال ایمیل', 'cpo-full'), null, 'cpo_notification_settings_page');

    add_settings_field('cpo_disable_base_price', __('غیرفعال کردن قیمت پایه', 'cpo-full'), 'cpo_disable_base_price_callback', 'cpo_general_settings_page', 'cpo_general_section');
    add_settings_field('cpo_products_per_page', __('تعداد محصولات در هر بار بارگذاری', 'cpo-full'), 'cpo_products_per_page_callback', 'cpo_general_settings_page', 'cpo_general_section');
    add_settings_field('cpo_admin_capability', __('نقش‌های مجاز دسترسی', 'cpo-full'), 'cpo_admin_capability_callback', 'cpo_general_settings_page', 'cpo_general_section');

    add_settings_field('cpo_default_product_image', __('لوگوی پیش‌فرض محصولات', 'cpo-full'), 'cpo_default_product_image_callback', 'cpo_shortcode_settings_page', 'cpo_shortcode_section');
    add_settings_field('cpo_grid_with_date_show_image', __('نمایش تصویر (شورت‌کد با تاریخ)', 'cpo-full'), 'cpo_grid_with_date_show_image_callback', 'cpo_shortcode_settings_page', 'cpo_shortcode_section');
    add_settings_field('cpo_grid_no_date_show_image', __('نمایش تصویر (شورت‌کد بدون تاریخ)', 'cpo-full'), 'cpo_grid_no_date_show_image_callback', 'cpo_shortcode_settings_page', 'cpo_shortcode_section');
    add_settings_field('cpo_grid_with_date_button_color', __('رنگ دکمه (شورت‌کد با تاریخ)', 'cpo-full'), 'cpo_grid_with_date_button_color_callback', 'cpo_shortcode_settings_page', 'cpo_shortcode_section');
    add_settings_field('cpo_grid_no_date_button_color', __('رنگ دکمه (شورت‌کد بدون تاریخ)', 'cpo-full'), 'cpo_grid_no_date_button_color_callback', 'cpo_shortcode_settings_page', 'cpo_shortcode_section');

    add_settings_field('cpo_email_test', __('ارسال ایمیل آزمایشی', 'cpo-full'), 'cpo_email_test_callback', 'cpo_notification_settings_page', 'cpo_email_test_section');
}

function cpo_sanitize_roles($input) {
    if (is_array($input)) return array_map('sanitize_text_field', $input);
    return [];
}

function cpo_disable_base_price_callback() {
    echo '<input type="checkbox" name="cpo_disable_base_price" value="1" ' . checked(1, get_option('cpo_disable_base_price'), false) . ' />';
    echo '<p class="description">' . __('با فعال کردن این گزینه، فیلد "قیمت پایه" در تمام بخش‌های افزونه مخفی می‌شود.', 'cpo-full') . '</p>';
}

function cpo_products_per_page_callback() {
    echo '<input type="number" name="cpo_products_per_page" value="' . esc_attr(get_option('cpo_products_per_page', 5)) . '" class="small-text" min="1" />';
    echo '<p class="description">' . __('این تعداد محصول در شورت‌کد گرید در ابتدا نمایش داده می‌شود.', 'cpo-full') . '</p>';
}

function cpo_admin_capability_callback() {
    $roles = get_editable_roles();
    $saved_roles = get_option('cpo_admin_capability');
    if (empty($saved_roles)) $saved_roles = ['administrator'];
    elseif (is_string($saved_roles)) $saved_roles = ['administrator']; 

    echo '<fieldset><legend class="screen-reader-text"><span>نقش‌های مجاز</span></legend>';
    foreach ($roles as $role_slug => $role_info) {
        $checked = in_array($role_slug, $saved_roles) ? 'checked="checked"' : '';
        echo '<label style="display:inline-block; margin-left: 15px; margin-bottom: 5px;">';
        echo '<input type="checkbox" name="cpo_admin_capability[]" value="' . esc_attr($role_slug) . '" ' . $checked . ' /> ';
        echo esc_html($role_info['name']);
        echo '</label><br>';
    }
    echo '</fieldset>';
    echo '<p class="description">' . __('نقش‌هایی که اجازه دسترسی به منوهای مدیریت این افزونه را دارند انتخاب کنید.', 'cpo-full') . '</p>';
}

function cpo_default_product_image_callback() {
    $image_url = get_option('cpo_default_product_image', '');
    echo '<div class="cpo-image-uploader-wrapper">';
    echo '<input type="text" name="cpo_default_product_image" value="' . esc_url($image_url) . '" class="regular-text" id="cpo-default-image-url"/>';
    echo '<button type="button" class="button cpo-upload-btn" data-input-id="cpo-default-image-url">' . __('انتخاب تصویر', 'cpo-full') . '</button>';
    echo '<div class="cpo-image-preview">';
    if ($image_url) echo '<img src="' . esc_url($image_url) . '" style="max-width: 100px; height: auto; margin-top: 10px;">';
    echo '</div></div>';
}

function cpo_grid_with_date_show_image_callback() {
    echo '<input type="checkbox" name="cpo_grid_with_date_show_image" value="1" ' . checked(1, get_option('cpo_grid_with_date_show_image', 1), false) . ' />';
}

function cpo_grid_no_date_show_image_callback() {
    echo '<input type="checkbox" name="cpo_grid_no_date_show_image" value="1" ' . checked(1, get_option('cpo_grid_no_date_show_image', 1), false) . ' />';
}

function cpo_grid_with_date_button_color_callback() {
    echo '<input type="text" name="cpo_grid_with_date_button_color" value="' . esc_attr(get_option('cpo_grid_with_date_button_color', '#ffc107')) . '" class="cpo-color-picker" />';
}

function cpo_grid_no_date_button_color_callback() {
    echo '<input type="text" name="cpo_grid_no_date_button_color" value="' . esc_attr(get_option('cpo_grid_no_date_button_color', '#0073aa')) . '" class="cpo-color-picker" />';
}

function cpo_email_test_callback() {
    echo '<button type="button" class="button button-secondary" id="cpo-test-email-btn">' . __('ارسال ایمیل تست', 'cpo-full') . '</button>';
    echo '<p class="description">' . __('یک ایمیل آزمایشی به ایمیل مدیر ارسال می‌کند.', 'cpo-full') . '</p>';
    echo '<textarea id="cpo-email-log" readonly style="width: 100%; height: 150px; margin-top: 10px; background-color: #f0f0f0; font-family: monospace; direction: ltr; text-align: left;"></textarea>';
}
?>