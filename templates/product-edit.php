<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$product_id) {
    wp_die(__('شناسه محصول نامعتبر است.', 'cpo-full'));
}

// واکشی محصول فعلی
$product = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . CPO_DB_PRODUCTS . " WHERE id = %d", $product_id));
if (!$product) {
    wp_die(__('محصول مورد نظر یافت نشد.', 'cpo-full'));
}

// دریافت دسته‌بندی‌ها
$categories = CPO_Core::get_all_categories(); 
$default_image_url = get_site_icon_url(100) ? get_site_icon_url(100) : CPO_ASSETS_URL . 'images/default-product.png';
$img_src = esc_url($product->image_url) ? esc_url($product->image_url) : $default_image_url;

$disable_base_price = get_option('cpo_disable_base_price', 0);
?>

<div class="wrap">
    <h1><?php _e('ویرایش محصول: ', 'cpo-full'); echo esc_html($product->name); ?></h1>

    <?php 
    if (isset($_GET['cpo_message']) && $_GET['cpo_message'] == 'product_updated') {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('محصول با موفقیت به‌روزرسانی شد.', 'cpo-full') . '</p></div>';
    }
    if (isset($_GET['cpo_message']) && $_GET['cpo_message'] == 'product_update_failed') {
        echo '<div class="notice notice-error is-dismissible"><p>' . __('خطا در به‌روزرسانی محصول. لطفاً دوباره امتحان کنید.', 'cpo-full') . '</p></div>';
    }
    ?>

    <p><a href="<?php echo admin_url('admin.php?page=custom-prices-products'); ?>" class="button button-secondary"><?php _e('بازگشت به لیست محصولات', 'cpo-full'); ?></a></p>

    <form method="post" id="cpo-edit-product-form">
        <?php wp_nonce_field('cpo_edit_product_action', 'cpo_edit_product_nonce'); ?>
        <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
        <input type="hidden" name="cpo_edit_product" value="1">

        <table class="form-table">
            <tr>
                <th><?php _e('نام محصول', 'cpo-full'); ?></th>
                <td><input type="text" name="name" required class="regular-text" value="<?php echo esc_attr($product->name); ?>"></td>
                <th><?php _e('دسته‌بندی', 'cpo-full'); ?></th>
                <td>
                    <select name="cat_id" required>
                        <option value=""><?php _e('انتخاب کنید', 'cpo-full'); ?></option>
                        <?php foreach ($categories as $cat) : ?>
                            <option value="<?php echo $cat->id; ?>" <?php selected($product->cat_id, $cat->id); ?>><?php echo $cat->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php _e('نوع', 'cpo-full'); ?></th>
                <td><input type="text" name="product_type" class="regular-text" value="<?php echo esc_attr($product->product_type); ?>"></td>
                <th><?php _e('واحد', 'cpo-full'); ?></th>
                <td><input type="text" name="unit" class="regular-text" value="<?php echo esc_attr($product->unit); ?>"></td>
            </tr>
            <tr>
                <th><?php _e('محل بارگیری', 'cpo-full'); ?></th>
                <td><input type="text" name="load_location" class="regular-text" value="<?php echo esc_attr($product->load_location); ?>"></td>
                <th><?php _e('وضعیت', 'cpo-full'); ?></th>
                <td>
                    <select name="is_active">
                        <option value="1" <?php selected($product->is_active, 1); ?>><?php _e('فعال', 'cpo-full'); ?></option>
                        <option value="0" <?php selected($product->is_active, 0); ?>><?php _e('غیرفعال', 'cpo-full'); ?></option>
                    </select>
                </td>
            </tr>

            <?php if (!$disable_base_price) : ?>
            <tr>
                <th><?php _e('قیمت پایه/استاندارد', 'cpo-full'); ?></th>
                <td><input type="text" name="price" required class="regular-text" value="<?php echo esc_attr($product->price); ?>"></td>
                <th><?php _e('بازه قیمت (حداقل - حداکثر)', 'cpo-full'); ?></th>
                <td>
                    <input type="text" name="min_price" class="small-text" value="<?php echo esc_attr($product->min_price); ?>">
                    -
                    <input type="text" name="max_price" class="small-text" value="<?php echo esc_attr($product->max_price); ?>">
                </td>
            </tr>
            <?php else: ?>
            <input type="hidden" name="price" value="<?php echo esc_attr($product->price); ?>">
             <tr>
                <th><?php _e('بازه قیمت (حداقل - حداکثر)', 'cpo-full'); ?></th>
                <td colspan="3">
                    <input type="text" name="min_price" class="small-text" value="<?php echo esc_attr($product->min_price); ?>">
                    -
                    <input type="text" name="max_price" class="small-text" value="<?php echo esc_attr($product->max_price); ?>">
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th><?php _e('عکس محصول', 'cpo-full'); ?></th>
                <td colspan="3">
                    <input type="text" name="image_url" id="product_image_url" class="regular-text" value="<?php echo esc_url($product->image_url); ?>">
                    <button type="button" class="button cpo-upload-btn"><?php _e('انتخاب تصویر', 'cpo-full'); ?></button>
                    <div class="cpo-image-preview">
                        <img src="<?php echo $img_src; ?>" style="max-width: 100px; height: auto; margin-top: 10px;">
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php _e('توضیحات', 'cpo-full'); ?></th>
                <td colspan="3"><textarea name="description" rows="5" class="large-text"><?php echo esc_textarea($product->description); ?></textarea></td>
            </tr>
        </table>
        <p class="submit"><input type="submit" name="cpo_update_product" id="submit" class="button button-primary" value="<?php _e('ذخیره تغییرات', 'cpo-full'); ?>"></p>
    </form>
</div>