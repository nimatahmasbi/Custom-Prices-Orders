<?php if (!defined('ABSPATH')) exit; ?>

<div id="cpo-order-modal" class="cpo-modal-overlay" style="display:none;">
    <div class="cpo-modal-container">
        <button class="cpo-modal-close">&times;</button>
        <h3><?php _e('ثبت سفارش برای:', 'cpo-full'); ?> <span class="cpo-modal-product-name"></span><span class="cpo-modal-product-location"></span></h3>
        <form id="cpo-order-form">
            <input type="hidden" name="product_id" id="cpo-order-product-id" value="">
            
            <input type="hidden" name="captcha_key" id="cpo_captcha_key" value="">

            <div class="cpo-form-field">
                <label for="customer_name"><?php _e('نام و نام خانوادگی', 'cpo-full'); ?> <span class="required">*</span></label>
                <input type="text" name="customer_name" id="customer_name" required>
            </div>
            <div class="cpo-form-field">
                <label for="phone"><?php _e('شماره تماس', 'cpo-full'); ?> <span class="required">*</span></label>
                <input type="tel" name="phone" id="phone" required class="ltr" style="direction:ltr; text-align:left;">
            </div>
            <div class="cpo-form-field">
                <label for="qty"><?php _e('مقدار/تعداد درخواستی', 'cpo-full'); ?> <span class="cpo-modal-product-unit"></span> <span class="required">*</span></label>
                <input type="text" name="qty" id="qty" required>
            </div>
            <div class="cpo-form-field">
                <label for="note"><?php _e('توضیحات (اختیاری)', 'cpo-full'); ?></label>
                <textarea name="note" id="note" rows="3"></textarea>
            </div>

            <div class="cpo-form-field cpo-captcha-field">
                 <label for="captcha_input"><?php _e('کد امنیتی را وارد کنید:', 'cpo-full'); ?> <span class="required">*</span></label>
                 <div class="cpo-captcha-wrap">
                     <span class="cpo-captcha-code">----</span>
                     <button type="button" class="cpo-refresh-captcha" title="<?php esc_attr_e('کد جدید', 'cpo-full'); ?>">↺</button>
                     <input type="text" name="captcha_input" id="captcha_input" required maxlength="4" autocomplete="off" class="ltr" style="direction:ltr; text-align:center;">
                 </div>
            </div>

            <div class="cpo-form-field">
                <button type="submit"><?php _e('ثبت درخواست', 'cpo-full'); ?></button>
            </div>
             <div class="cpo-form-message-placeholder" style="margin-top: 15px;"></div> 
        </form>
    </div>
</div>

<div id="cpo-front-chart-modal" class="cpo-modal-overlay" style="display:none;">
     <div class="cpo-modal-container cpo-chart-container">
        <button class="cpo-modal-close">&times;</button>
        <h3><?php _e('نمودار تغییرات قیمت', 'cpo-full'); ?></h3>
        
        <div class="cpo-chart-toolbar">
            <button class="button cpo-chart-filter active" data-range="all"><?php _e('همه', 'cpo-full'); ?></button>
            <button class="button cpo-chart-filter" data-range="12"><?php _e('۱ سال', 'cpo-full'); ?></button>
            <button class="button cpo-chart-filter" data-range="6"><?php _e('۶ ماه', 'cpo-full'); ?></button>
            <button class="button cpo-chart-filter" data-range="3"><?php _e('۳ ماه', 'cpo-full'); ?></button>
            <button class="button cpo-chart-filter" data-range="1"><?php _e('۱ ماه', 'cpo-full'); ?></button>
            <button class="button cpo-chart-filter" data-range="0.25"><?php _e('۱ هفته', 'cpo-full'); ?></button>
            <button class="button button-primary cpo-chart-download"><?php _e('دانلود نمودار', 'cpo-full'); ?></button>
        </div>

        <div class="cpo-chart-inner">
            <div class="cpo-chart-bg"></div> <canvas id="cpoFrontPriceChart"></canvas>
        </div>
    </div>
</div>
