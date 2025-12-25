<?php if (!defined('ABSPATH')) exit; ?>
<div class="cpo-grid-wrapper">
    <?php if (!empty($categories)) : ?>
    <aside class="cpo-grid-sidebar">
        <h3>دسته‌بندی‌ها</h3>
        <ul>
            <li class="active"><a href="#" data-cat-id="all">همه دسته‌ها</a></li>
            <?php foreach ($categories as $cat) : ?>
                <li><a href="#" data-cat-id="<?php echo $cat->id; ?>"><?php echo esc_html($cat->name); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </aside>
    <?php endif; ?>

    <main class="cpo-grid-container">
        <div class="cpo-grid-header">
            <div class="col-name">نام</div>
            <div class="col-type">نوع</div>
            <div class="col-unit">واحد</div>
            <div class="col-location">محل بارگیری</div>
            <div class="col-price">قیمت</div>
            <div class="col-change">تغییر</div>
            <div class="col-actions">عملیات</div>
        </div>
        <div class="cpo-grid-body">
            <?php foreach ($products as $product) : 
                $change = $product->percentage_change;
                $change_class = '';
                if (is_numeric($change)) {
                    if ($change > 0) $change_class = 'positive';
                    if ($change < 0) $change_class = 'negative';
                    $change_text = number_format($change, 2) . '%';
                } else {
                    $change_text = '-';
                }
            ?>
            <div class="cpo-grid-row" data-cat-id="<?php echo $product->cat_id; ?>">
                <div class="col-name"><?php echo esc_html($product->name); ?></div>
                <div class="col-type"><?php echo esc_html($product->product_type); ?></div>
                <div class="col-unit"><?php echo esc_html($product->unit); ?></div>
                <div class="col-location"><?php echo esc_html($product->load_location); ?></div>
                <div class="col-price"><?php echo esc_html(number_format((float)str_replace(',', '', $product->price))); ?> تومان</div>
                <div class="col-change <?php echo $change_class; ?>"><?php echo $change_text; ?></div>
                <div class="col-actions">
                    <button class="cpo-grid-btn order-btn" data-product-id="<?php echo $product->id; ?>" data-product-name="<?php echo esc_attr($product->name); ?>">خرید</button>
                    <button class="cpo-grid-btn chart-btn" data-product-id="<?php echo $product->id; ?>">→</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>
<div style="text-align:center; margin-top: 20px;">
    <a href="#" class="cpo-grid-full-list-btn">مشاهده لیست کامل قیمت</a>
</div>