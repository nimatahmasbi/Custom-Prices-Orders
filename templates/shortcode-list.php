<?php
if (!defined('ABSPATH')) exit;

$disable_base_price = get_option('cpo_disable_base_price', 0);
// حروف بزرگ برای ثابت‌ها
$cart_icon_url = CPO_ASSETS_URL . 'images/cart-icon.png';
$chart_icon_url = CPO_ASSETS_URL . 'images/chart-icon.png';
$default_image = get_option('cpo_default_product_image', CPO_ASSETS_URL . 'images/default-product.png'); 

?>

<div class="cpo-products-list-container">
    <table class="cpo-products-table">
        <thead>
            <tr>
                <th><?php _e('محصول', 'cpo-full'); ?></th>
                <th><?php _e('نوع', 'cpo-full'); ?></th>
                <th><?php _e('واحد', 'cpo-full'); ?></th>
                <th><?php _e('محل بارگیری', 'cpo-full'); ?></th>
                <th><?php _e('آخرین بروزرسانی', 'cpo-full'); ?></th>
                <?php if (!$disable_base_price) : ?>
                    <th><?php _e('قیمت پایه', 'cpo-full'); ?></th>
                <?php endif; ?>
                <th><?php _e('بازه قیمت', 'cpo-full'); ?></th>
                <th><?php _e('عملیات', 'cpo-full'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product) :
            $product_image_url = !empty($product->image_url) ? esc_url($product->image_url) : esc_url($default_image);
            
            $min_clean = str_replace(',', '', $product->min_price);
            $max_clean = str_replace(',', '', $product->max_price);
            $show_single = ($min_clean == $max_clean && is_numeric($min_clean));
        ?>
            <tr data-id="<?php echo $product->id; ?>">
                <td>
                    <div class="cpo-product-info">
                        <img src="<?php echo $product_image_url; ?>" alt="<?php echo esc_attr($product->name); ?>">
                        <div class="cpo-product-details">
                            <span class="cpo-product-name"><?php echo esc_html($product->name); ?></span>
                        </div>
                    </div>
                </td>
                <td><?php echo esc_html($product->product_type); ?></td>
                <td><?php echo esc_html($product->unit); ?></td>
                <td><?php echo esc_html($product->load_location); ?></td>
                <td><?php echo date_i18n('Y/m/d H:i', strtotime(get_date_from_gmt($product->last_updated_at))); ?></td>
                <?php if (!$disable_base_price) : ?>
                    <td class="cpo-base-price">
                         <?php
                            $price_cleaned = str_replace(',', '', $product->price);
                            echo is_numeric($price_cleaned) ? esc_html(number_format_i18n((float)$price_cleaned)) : esc_html($product->price);
                         ?>
                    </td>
                <?php endif; ?>
                <td class="cpo-price-range">
                    <?php if (!empty($product->min_price) && !empty($product->max_price)) : ?>
                        <?php if ($show_single) : ?>
                             <?php echo esc_html(number_format_i18n((float)$min_clean)); ?>
                        <?php else : ?>
                             <?php echo is_numeric($min_clean) ? esc_html(number_format_i18n((float)$min_clean)) : esc_html($product->min_price); ?> - <?php echo is_numeric($max_clean) ? esc_html(number_format_i18n((float)$max_clean)) : esc_html($product->max_price); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="cpo-price-not-set"><?php _e('تماس بگیرید', 'cpo-full'); ?></span>
                    <?php endif; ?>
                </td>
                <td class="cpo-actions-cell">
                    <button class="cpo-icon-btn cpo-order-btn"
                            data-product-id="<?php echo esc_attr($product->id); ?>"
                            data-product-name="<?php echo esc_attr($product->name); ?>"
                            data-product-unit="<?php echo esc_attr($product->unit); ?>"
                            data-product-location="<?php echo esc_attr($product->load_location); ?>"
                            title="<?php esc_attr_e('ثبت سفارش', 'cpo-full'); ?>">
                        <img src="<?php echo esc_url($cart_icon_url); ?>" alt="<?php esc_attr_e('ثبت سفارش', 'cpo-full'); ?>">
                    </button>
                    <button class="cpo-icon-btn cpo-chart-btn" data-product-id="<?php echo esc_attr($product->id); ?>" title="<?php esc_attr_e('نمودار', 'cpo-full'); ?>">
                         <img src="<?php echo esc_url($chart_icon_url); ?>" alt="<?php esc_attr_e('نمودار', 'cpo-full'); ?>">
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>