<div class="wrap cpo-settings-wrap">
    <h1><?php echo __('تنظیمات افزونه مدیریت قیمت‌ها','cpo-full'); ?></h1>

    <?php $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general'; ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=custom-prices-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('عمومی', 'cpo-full'); ?></a>
        <a href="?page=custom-prices-settings&tab=shortcodes" class="nav-tab <?php echo $active_tab == 'shortcodes' ? 'nav-tab-active' : ''; ?>"><?php _e('نمایش شورت‌کدها', 'cpo-full'); ?></a>
        <a href="?page=custom-prices-settings&tab=notifications" class="nav-tab <?php echo $active_tab == 'notifications' ? 'nav-tab-active' : ''; ?>"><?php _e('اعلان‌ها', 'cpo-full'); ?></a>
    </h2>

    <form method="post" action="options.php">
        <?php
        if ($active_tab == 'general') {
            settings_fields('cpo_general_settings_grp');
            do_settings_sections('cpo_general_settings_page');
        } elseif ($active_tab == 'shortcodes') {
            settings_fields('cpo_shortcode_settings_grp');
            do_settings_sections('cpo_shortcode_settings_page');
        } else { // Tab Notifications
            settings_fields('cpo_notification_settings_grp');
            ?>
            <h3><?php _e('تنظیمات ایمیل', 'cpo-full'); ?></h3>
            <table class="form-table">
                 <tr valign="top">
                    <th scope="row"><?php _e('فعال‌سازی ارسال ایمیل','cpo-full'); ?></th>
                    <td>
                        <input type="checkbox" name="cpo_enable_email" value="1" <?php checked(get_option('cpo_enable_email'), 1); ?> />
                        <p class="description"><?php _e('ارسال ایمیل اعلان سفارش جدید به مدیر را فعال می‌کند.','cpo-full'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('ایمیل مدیر','cpo-full'); ?></th>
                    <td>
                        <input type="email" name="cpo_admin_email" value="<?php echo esc_attr(get_option('cpo_admin_email', get_option('admin_email'))); ?>" class="regular-text" />
                        <p class="description"><?php _e('ایمیل گیرنده اعلان‌های سفارش.','cpo-full'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('عنوان ایمیل سفارش مدیر','cpo-full'); ?></th>
                    <td><input type="text" name="cpo_email_subject_template" value="<?php echo esc_attr(get_option('cpo_email_subject_template', 'سفارش جدید: {product_name}')); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('قالب ایمیل سفارش مدیر','cpo-full'); ?></th>
                    <td>
                        <?php
                            $content = get_option('cpo_email_body_template', '<p style="direction:rtl; text-align:right;">سفارش جدیدی از طریق وب‌سایت ثبت شده است:<br><br><strong>محصول:</strong> {product_name} - {load_location}<br><strong>نام مشتری:</strong> {customer_name}<br><strong>شماره تماس:</strong> {phone}<br><strong>تعداد/مقدار:</strong> {qty} ({unit})<br><strong>توضیحات مشتری:</strong> {note}<br></p>');
                            $editor_id = 'cpo_email_body_template';
                            wp_editor(wp_kses_post($content), $editor_id, ['textarea_name' => 'cpo_email_body_template', 'media_buttons' => false, 'textarea_rows' => 15]);
                        ?>
                        <p class="description"><strong><?php _e('متغیرهای مجاز:', 'cpo-full'); ?></strong> <code>{product_name}</code>, <code>{load_location}</code>, <code>{customer_name}</code>, <code>{phone}</code>, <code>{qty}</code>, <code>{unit}</code>, <code>{note}</code></p>
                        <button type="button" id="cpo-load-email-template" class="button" style="margin-top:10px;"><?php _e('بارگذاری قالب پیش‌فرض زیبا', 'cpo-full'); ?></button>
                    </td>
                </tr>
            </table>

            <hr>
            <?php do_settings_sections('cpo_notification_settings_page'); ?>

            <hr>
            <h3><?php _e('تنظیمات پیامک (SMS) با IPPanel (الگو)','cpo-full'); ?></h3>
             <p class="description" style="margin-bottom: 20px;">
                <?php _e('برای ارسال پیامک به مدیر و مشتری، باید از روش "ارسال الگو" (Pattern) در IPPanel استفاده کنید.', 'cpo-full'); ?><br/>
                 <?php _e('لطفاً الگوهای مورد نیاز را در پنل IPPanel خود بسازید.', 'cpo-full'); ?>
            </p>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('فعال‌سازی سرویس IPPanel','cpo-full'); ?></th>
                    <td>
                         <select name="cpo_sms_service">
                            <option value="" <?php selected(get_option('cpo_sms_service'), ''); ?>><?php _e('غیرفعال', 'cpo-full'); ?></option>
                            <option value="ippanel" <?php selected(get_option('cpo_sms_service'), 'ippanel'); ?>>IPPanel (فعال)</option>
                        </select>
                         <p class="description"><?php _e('برای فعال شدن ارسال پیامک، این گزینه را روی IPPanel قرار دهید.', 'cpo-full'); ?></p>
                    </td>
                </tr>
                 <tr valign="top">
                    <th scope="row"><?php _e('کلید API','cpo-full'); ?></th>
                    <td><input type="text" name="cpo_sms_api_key" value="<?php echo esc_attr( get_option('cpo_sms_api_key') ); ?>" class="regular-text ltr" style="direction: ltr; text-align: left;"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('شماره فرستنده','cpo-full'); ?></th>
                    <td><input type="text" name="cpo_sms_sender" value="<?php echo esc_attr( get_option('cpo_sms_sender') ); ?>" class="regular-text ltr" style="direction: ltr; text-align: left;"/></td>
                </tr>

                <tr valign="top"> <td colspan="2"><hr><h4><?php _e('تنظیمات پیامک مدیر', 'cpo-full'); ?></h4></td> </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('کد الگوی مدیر','cpo-full'); ?></th>
                    <td>
                        <input type="text" name="cpo_sms_pattern_code" value="<?php echo esc_attr( get_option('cpo_sms_pattern_code') ); ?>" class="regular-text ltr" style="direction: ltr; text-align: left; width: 200px; display: inline-block; vertical-align: middle;"/>
                        <div style="display: inline-block; vertical-align: middle; margin-right: 15px; padding: 10px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9em;">
                            <strong><?php _e('متغیرهای الگو:', 'cpo-full'); ?></strong><br>
                            <code>product_name</code><br>
                            <code>customer_name</code><br>
                            <code>phone</code><br>
                            <code>qty</code><br>
                            <code>unit</code><br>
                            <code>load_location</code><br>
                            <code>note</code>
                        </div>
                         <p class="description clear" style="clear: both; padding-top: 5px;"><?php _e('کد الگوی اعلان سفارش به مدیر را وارد کنید. الگو باید شامل متغیرهای مورد نیاز شما از لیست بالا باشد.','cpo-full'); ?></p>
                    </td>
                </tr>


                 <tr valign="top">
                    <th scope="row"><?php _e('شماره موبایل مدیر','cpo-full'); ?></th>
                    <td><input type="text" name="cpo_admin_phone" value="<?php echo esc_attr( get_option('cpo_admin_phone') ); ?>" class="regular-text ltr" style="direction: ltr; text-align: left;"/>
                         <p class="description"><?php _e('این شماره، گیرنده پیامک‌های اعلان سفارش و تست می‌باشد.', 'cpo-full'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('تست پیامک مدیر', 'cpo-full'); ?></th>
                    <td>
                        <button type="button" class="button button-secondary" id="cpo-test-sms-btn"><?php _e('ارسال پیامک تست به مدیر', 'cpo-full'); ?></button>
                        <p class="description"><?php _e('یک پیامک آزمایشی با الگوی مدیر به شماره مدیر ارسال می‌کند.', 'cpo-full'); ?></p>
                        <textarea id="cpo-sms-log" readonly style="width: 100%; height: 100px; margin-top: 10px; background-color: #f0f0f0; font-family: monospace; direction: ltr; text-align: left;"></textarea>
                    </td>
                </tr>

                <tr valign="top"> <td colspan="2"><hr><h4><?php _e('تنظیمات پیامک مشتری', 'cpo-full'); ?></h4></td> </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('فعال‌سازی پیامک مشتری','cpo-full'); ?></th>
                    <td>
                        <input type="checkbox" name="cpo_sms_customer_enable" value="1" <?php checked(get_option('cpo_sms_customer_enable'), 1); ?> />
                        <p class="description"><?php _e('ارسال پیامک تایید ثبت سفارش به مشتری را فعال می‌کند.','cpo-full'); ?></p>
                    </td>
                </tr>
                 <tr valign="top">
                    <th scope="row"><?php _e('کد الگوی مشتری','cpo-full'); ?></th>
                    <td>
                        <input type="text" name="cpo_sms_customer_pattern_code" value="<?php echo esc_attr( get_option('cpo_sms_customer_pattern_code') ); ?>" class="regular-text ltr" style="direction: ltr; text-align: left; width: 200px; display: inline-block; vertical-align: middle;"/>
                         <div style="display: inline-block; vertical-align: middle; margin-right: 15px; padding: 10px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9em;">
                             <strong><?php _e('متغیرهای پیشنهادی:', 'cpo-full'); ?></strong><br>
                            <code>customer_name</code><br>
                            <code>product_name</code><br>
                            <code>qty</code><br>
                            <code>unit</code><br>
                            <code>load_location</code><br>
                         </div>
                        <p class="description clear" style="clear: both; padding-top: 5px;">
                            <?php _e('کد الگوی پیامک تایید سفارش برای مشتری را وارد کنید.','cpo-full'); ?><br/>
                        </p>
                    </td>
                </tr>
            </table>
            <?php
        }
        submit_button();
        ?>
    </form>
</div>