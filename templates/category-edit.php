<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

$cat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$cat_id) {
    wp_die(__('شناسه دسته‌بندی نامعتبر است.', 'cpo-full'));
}

$category = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . CPO_DB_CATEGORIES . " WHERE id = %d", $cat_id));
if (!$category) {
    wp_die(__('دسته‌بندی مورد نظر یافت نشد.', 'cpo-full'));
}

$img_src = esc_url($category->image_url);
?>

<div class="wrap">
    <h2><?php _e('ویرایش دسته‌بندی: ', 'cpo-full'); echo esc_html($category->name); ?></h2>

    <form method="post" id="cpo-edit-category-form">
        <?php wp_nonce_field('cpo_edit_cat_action', 'cpo_edit_cat_nonce'); ?>
        <input type="hidden" name="category_id" value="<?php echo $category->id; ?>">
        
        <table class="form-table">
            <tr>
                <th><?php _e('نام دسته‌بندی', 'cpo-full'); ?></th>
                <td><input type="text" name="name" required class="regular-text" value="<?php echo esc_attr($category->name); ?>"></td>
            </tr>
            <tr>
                <th><?php _e('اسلاگ (Slug)', 'cpo-full'); ?></th>
                <td><input type="text" name="slug" class="regular-text" value="<?php echo esc_attr($category->slug); ?>"></td>
            </tr>
            <tr>
                <th><?php _e('عکس دسته‌بندی', 'cpo-full'); ?></th>
                <td>
                    <input type="text" name="image_url" class="regular-text" value="<?php echo esc_url($category->image_url); ?>">
                    <button type="button" class="button cpo-upload-btn"><?php _e('انتخاب تصویر', 'cpo-full'); ?></button>
                    <div class="cpo-image-preview">
                        <?php if ($img_src): ?>
                            <img src="<?php echo $img_src; ?>" style="max-width: 100px; height: auto; margin-top: 10px;">
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="cpo_update_category" class="button button-primary" value="<?php _e('ذخیره تغییرات', 'cpo-full'); ?>">
        </p>
    </form>
</div>