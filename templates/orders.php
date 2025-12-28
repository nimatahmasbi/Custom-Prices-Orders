<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

$order_statuses = [
    'new_order'     => __('سفارش جدید', 'cpo-full'),
    'negotiating'   => __('در حال مذاکره', 'cpo-full'),
    'cancelled'     => __('کنسل شد', 'cpo-full'),
    'completed'     => __('خرید انجام شد', 'cpo-full'),
];

$search_term = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$where_clause = '';
if (!empty($search_term)) {
    $like = '%' . $wpdb->esc_like($search_term) . '%';
    $where_clause = $wpdb->prepare( " WHERE product_name LIKE %s OR customer_name LIKE %s OR phone LIKE %s OR qty LIKE %s OR unit LIKE %s OR load_location LIKE %s OR note LIKE %s OR admin_note LIKE %s OR status LIKE %s OR id LIKE %s", $like, $like, $like, $like, $like, $like, $like, $like, $like, $like );
}

$orders = $wpdb->get_results("SELECT * FROM " . CPO_DB_ORDERS . $where_clause . " ORDER BY id ASC");
?>

<div class="wrap">
    <h1><?php _e('سفارشات مشتریان', 'cpo-full'); ?></h1>

    <div class="notice notice-info">
        <p><?php _e('برای ویرایش وضعیت سفارش یا ثبت یادداشت مدیر، روی سلول مورد نظر **دوبار کلیک (Double Click)** کنید.', 'cpo-full'); ?></p>
    </div>

    <form method="get">
        <input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? esc_attr($_REQUEST['page']) : ''; ?>">
        <p class="search-box">
	        <label class="screen-reader-text" for="cpo-orders-search-input"><?php _e('جستجوی سفارشات:', 'cpo-full'); ?></label>
	        <input type="search" id="cpo-orders-search-input" name="s" value="<?php echo esc_attr($search_term); ?>">
	        <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('جستجوی سفارشات', 'cpo-full'); ?>">
        </p>
    </form>

    <div class="cpo-orders-table-wrapper">
        <table class="wp-list-table widefat fixed striped cpo-orders-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-id" style="width: 5%;"><?php _e('ID', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('نام محصول', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('محل بارگیری', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('نام مشتری', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('تلفن', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('مقدار', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('واحد', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column column-note"><?php _e('یادداشت مشتری', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e('وضعیت', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column column-admin_note"><?php _e('یادداشت مدیر', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php _e('تاریخ ثبت', 'cpo-full'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('عملیات', 'cpo-full'); ?></th>
                </tr>
            </thead>
            <tbody id="the-list">
            <?php if ($orders) : foreach ($orders as $order) : ?>
                <tr id="order-<?php echo $order->id; ?>">
                    <td class="column-id" data-colname="<?php esc_attr_e('ID', 'cpo-full'); ?>"><?php echo $order->id; ?></td>
                    <td data-colname="<?php esc_attr_e('نام محصول', 'cpo-full'); ?>"><?php echo esc_html($order->product_name); ?></td>
                    <td data-colname="<?php esc_attr_e('محل بارگیری', 'cpo-full'); ?>"><?php echo esc_html($order->load_location); ?></td>
                    <td data-colname="<?php esc_attr_e('نام مشتری', 'cpo-full'); ?>"><?php echo esc_html($order->customer_name); ?></td>
                    <td data-colname="<?php esc_attr_e('تلفن', 'cpo-full'); ?>"><?php echo esc_html($order->phone); ?></td>
                    <td data-colname="<?php esc_attr_e('مقدار', 'cpo-full'); ?>"><?php echo esc_html($order->qty); ?></td>
                    <td data-colname="<?php esc_attr_e('واحد', 'cpo-full'); ?>"><?php echo esc_html($order->unit); ?></td>
                    <td class="column-note" data-colname="<?php esc_attr_e('یادداشت مشتری', 'cpo-full'); ?>">
                        <?php
                        $full_note = esc_html($order->note);
                        if (mb_strlen($full_note) > 40) { 
                            echo '<span title="' . esc_attr($full_note) . '">' . esc_html(mb_substr($full_note, 0, 40)) . '...</span>';
                        } else {
                            echo nl2br($full_note); 
                        }
                        ?>
                    </td>
                    <td class="cpo-quick-edit-select column-status" data-colname="<?php esc_attr_e('وضعیت', 'cpo-full'); ?>" data-id="<?php echo $order->id; ?>" data-field="status" data-table-type="orders" data-current="<?php echo esc_attr($order->status); ?>">
                        <?php echo isset($order_statuses[$order->status]) ? $order_statuses[$order->status] : esc_html($order->status); ?>
                    </td>
                     <td class="cpo-quick-edit column-admin_note" data-colname="<?php esc_attr_e('یادداشت مدیر', 'cpo-full'); ?>" data-id="<?php echo $order->id; ?>" data-field="admin_note" data-table-type="orders">
                        <?php
                        $full_admin_note = esc_html($order->admin_note);
                        if (mb_strlen($full_admin_note) > 40) { 
                            echo '<span title="' . esc_attr($full_admin_note) . '">' . esc_html(mb_substr($full_admin_note, 0, 40)) . '...</span>';
                        } else {
                            echo nl2br($full_admin_note);
                        }
                        ?>
                     </td>
                    <td class="column-date" data-colname="<?php esc_attr_e('تاریخ ثبت', 'cpo-full'); ?>"><?php echo date_i18n('Y/m/d H:i', strtotime(get_date_from_gmt($order->created))); ?></td>
                    <td class="column-actions" data-colname="<?php esc_attr_e('عملیات', 'cpo-full'); ?>">
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=custom-prices-orders&action=delete&id=' . $order->id), 'cpo_delete_order_' . $order->id); ?>" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e('آیا مطمئنید؟', 'cpo-full'); ?>')"><?php _e('حذف', 'cpo-full'); ?></a>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="12"><?php _e('سفارشی یافت نشد.', 'cpo-full'); ?></td></tr>
            <?php endif; ?>
            </tbody>
             <tfoot>
                 <tr>
                     <th scope="col"><?php _e('ID', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('نام محصول', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('محل بارگیری', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('نام مشتری', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('تلفن', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('مقدار', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('واحد', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('یادداشت مشتری', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('وضعیت', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('یادداشت مدیر', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('تاریخ ثبت', 'cpo-full'); ?></th>
                    <th scope="col"><?php _e('عملیات', 'cpo-full'); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>