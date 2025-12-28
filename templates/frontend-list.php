<?php
// frontend list template used by shortcode
?>
<div class="cpo-front">
    <?php if(!empty($a['title'])) echo '<h3>'.esc_html($a['title']).'</h3>'; ?>
    <table class="cpo-table">
        <thead><tr><th><?php _e('نام','cpo'); ?></th><th><?php _e('دسته','cpo'); ?></th><th><?php _e('قیمت','cpo'); ?></th><th><?php _e('آخرین بروزرسانی','cpo'); ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach($products as $p): ?>
            <tr data-id="<?php echo $p->id; ?>">
                <td><?php echo esc_html($p->product_name); ?></td>
                <td><?php echo esc_html($p->category_name); ?></td>
                <td><?php echo $p->min_price ? esc_html($p->min_price).' – '.esc_html($p->max_price) : __('تماس بگیرید','cpo'); ?></td>
                <td><?php echo $p->last_modified ? cpo_format_date_local($p->last_modified) : '-'; ?></td>
                <td><button class="cpo-btn cpo-chart-btn" data-id="<?php echo $p->id; ?>"><?php _e('نمودار قیمت','cpo'); ?></button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
