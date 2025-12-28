jQuery(document).ready(function ($) {

    // --- مدیریت آکاردئون ---
    $('.cpo-accordion-header').on('click', function () {
        $(this).toggleClass('active').next('.cpo-accordion-content').slideToggle(300);
    });

    if ($('.cpo-accordion-content').length && !$('.cpo-accordion-content').find('.notice-error, .error').length && !window.location.hash) {
        $('.cpo-accordion-content').hide();
        $('.cpo-accordion-header').removeClass('active');
    }

    if (window.location.hash) {
        var targetAccordion = $(window.location.hash);
        if (targetAccordion.hasClass('cpo-accordion-content')) {
            targetAccordion.show();
            targetAccordion.prev('.cpo-accordion-header').addClass('active');
        }
    }

    // --- مدیریت آپلودر رسانه وردپرس ---
    var mediaUploader;
    $(document).on('click', '.cpo-upload-btn', function (e) {
        e.preventDefault();
        var button = $(this);
        var inputId = button.data("input-id");
        var input_field = inputId ? jQuery("#" + inputId) : button.siblings('input[type="text"]');

        if (!input_field.length) {
            input_field = button.closest('td').find('input[type="text"]');
        }
        var preview_img_container = button.closest('td, .cpo-image-uploader-wrapper, .form-table tr').find(".cpo-image-preview");

        if (!input_field.length) return;

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'انتخاب تصویر محصول/دسته‌بندی',
            button: { text: 'استفاده از این تصویر' },
            multiple: false
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            input_field.val(attachment.url).trigger('change');
            if (preview_img_container.length) {
                preview_img_container.html('<img src="' + attachment.url + '" style="max-width: 100px; margin-top: 10px; border:1px solid #ddd; padding:2px;">');
            }
        });
        mediaUploader.open();
    });

    // --- ویرایش سریع (Quick Edit) - لیست محصولات اصلی ---
    $(document).on('dblclick', '.cpo-quick-edit, .cpo-quick-edit-select', function () {
        var cell = $(this);
        if (cell.hasClass('editing') || cell.closest('td').hasClass('editing-td')) return;

        var id = cell.data('id');
        var field = cell.data('field');
        var table_type = cell.data('table-type');
        var original_content = cell.html();
        var original_text = cell.text().trim();
        var input_element;

        if (cell.hasClass('cpo-quick-edit-select')) {
            cell.addClass('editing');
            input_element = $('<select>').addClass('cpo-quick-edit-input');
            var options = (table_type === 'orders') ? cpo_admin_vars.order_statuses : cpo_admin_vars.product_statuses;

            $.each(options, function (val, text) {
                var isSelected = (val == cell.data('current')) ? 'selected' : '';
                input_element.append('<option value="' + val + '" ' + isSelected + '>' + text + '</option>');
            });

        } else if (field === 'min_price' || field === 'max_price') {
            var td = cell.closest('td');
            td.addClass('editing-td');
            var min_val = td.find('[data-field="min_price"]').text().trim();
            var max_val = td.find('[data-field="max_price"]').text().trim();
            var container = $('<div>').css({ display: 'flex', gap: '5px', alignItems: 'center' });
            var input_min = $('<input>').attr({ type: 'text', class: 'cpo-quick-edit-input small-text', 'data-field': 'min_price', value: min_val, placeholder: 'حداقل' });
            var input_max = $('<input>').attr({ type: 'text', class: 'cpo-quick-edit-input small-text', 'data-field': 'max_price', value: max_val, placeholder: 'حداکثر' });
            container.append(input_min).append('<span>-</span>').append(input_max);
            input_element = container;
            cell = td;
            original_content = td.html();

        } else {
            cell.addClass('editing');
            if (field === 'admin_note' || field === 'description') {
                input_element = $('<textarea>').addClass('cpo-quick-edit-input').val(original_text).css('min-height', '60px');
            } else {
                input_element = $('<input>').attr('type', 'text').addClass('cpo-quick-edit-input').val(original_text);
            }
        }

        var btn_save = $('<button>').addClass('button button-primary button-small').text(cpo_admin_vars.i18n.save).css('margin-right', '5px');
        var btn_cancel = $('<button>').addClass('button button-secondary button-small').text(cpo_admin_vars.i18n.cancel);
        var btn_wrap = $('<div>').addClass('cpo-quick-edit-buttons').css('margin-top', '5px').append(btn_save).append(btn_cancel);

        cell.empty().append(input_element).append(btn_wrap);
        cell.find('input, select, textarea').first().focus();

        btn_save.on('click', function () {
            // ... (Logic remains same for main table save) ...
            if (field === 'min_price' || field === 'max_price') {
                var new_min = cell.find('input[data-field="min_price"]').val();
                var new_max = cell.find('input[data-field="max_price"]').val();
                cell.text(cpo_admin_vars.i18n.saving);
                $.when(
                    $.post(cpo_admin_vars.ajax_url, { action: 'cpo_quick_update', security: cpo_admin_vars.nonce, id: id, field: 'min_price', value: new_min, table_type: table_type }),
                    $.post(cpo_admin_vars.ajax_url, { action: 'cpo_quick_update', security: cpo_admin_vars.nonce, id: id, field: 'max_price', value: new_max, table_type: table_type })
                ).done(function () {
                    window.location.reload();
                }).fail(function () {
                    alert(cpo_admin_vars.i18n.serverError);
                    window.location.reload();
                });
            } else {
                var new_val = cell.find('.cpo-quick-edit-input').val();
                cell.text(cpo_admin_vars.i18n.saving);
                $.post(cpo_admin_vars.ajax_url, {
                    action: 'cpo_quick_update', security: cpo_admin_vars.nonce, id: id, field: field, value: new_val, table_type: table_type
                }, function (res) {
                    if (res.success) {
                        window.location.reload();
                    } else {
                        alert(res.data.message || cpo_admin_vars.i18n.error);
                        cell.html(original_content);
                        cell.removeClass('editing editing-td');
                    }
                }).fail(function () {
                    alert(cpo_admin_vars.i18n.serverError);
                    cell.html(original_content);
                    cell.removeClass('editing editing-td');
                });
            }
        });

        btn_cancel.on('click', function () {
            cell.html(original_content);
            cell.removeClass('editing editing-td');
        });
    });

    // --- مدیریت نمودار پیشرفته (Chart.js) ---
    var chartInstance = null;
    var fullChartData = null;

    $(document).on('click', '.cpo-show-chart', function (e) {
        e.preventDefault();
        var product_id = $(this).data('product-id');

        if ($('#cpo-chart-modal').length === 0) {
             var modalHTML = '<div id="cpo-chart-modal" class="cpo-modal-overlay" style="display:none;">' +
                '<div class="cpo-modal-container cpo-chart-background">' +
                '<span class="cpo-close-modal">×</span>' +
                '<h2>نمودار تغییرات قیمت</h2>' +
                '<div class="cpo-chart-toolbar">' +
                '<button class="button cpo-chart-filter active" data-range="all">همه</button> ' +
                // ... (Buttons) ...
                '<button class="button button-primary cpo-chart-download">دانلود نمودار</button>' +
                '</div>' +
                '<div class="cpo-chart-modal-content">' +
                '<div class="cpo-chart-bg"></div>' +
                '<canvas id="cpoPriceChart"></canvas>' +
                '</div>' +
                '</div>' +
                '</div>';
            $('body').append(modalHTML);
        }

        var modal = $('#cpo-chart-modal');
        var canvas = modal.find('#cpoPriceChart');
        var bg_layer = modal.find('.cpo-chart-bg');

        if (cpo_admin_vars.logo_url) {
            bg_layer.css({
                'background-image': 'url(' + cpo_admin_vars.logo_url + ')',
                'background-repeat': 'no-repeat',
                'background-position': 'center center',
                'background-size': '200px',
                'opacity': '0.1',
                'position': 'absolute', 'top': 0, 'left': 0, 'width': '100%', 'height': '100%', 'z-index': 0
            });
            canvas.css({ 'position': 'relative', 'z-index': 1 });
        }

        modal.css('display', 'flex');

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        $.get(cpo_admin_vars.ajax_url, {
            action: 'cpo_get_chart_data',
            product_id: product_id,
            security: cpo_admin_vars.nonce
        }).done(function (response) {
            if (response.success && response.data.labels && response.data.labels.length > 0) {
                fullChartData = response.data;
                renderChart(fullChartData, canvas, 'all');
            } else {
                alert(response.data.message || 'داده‌ای برای نمایش وجود ندارد.');
                modal.hide();
            }
        }).fail(function () {
            alert('خطا در دریافت اطلاعات نمودار.');
            modal.hide();
        });
    });
    
    // ... (Chart Filter and Download functions - kept same) ...
    $(document).on('click', '.cpo-chart-filter', function () {
        $('.cpo-chart-filter').removeClass('active');
        $(this).addClass('active');
        var range = $(this).data('range');
        var canvas = $('#cpoPriceChart');
        if (chartInstance) chartInstance.destroy();
        renderChart(fullChartData, canvas, range);
    });

    $(document).on('click', '.cpo-chart-download', function () {
        if (!chartInstance) return;
        var link = document.createElement('a');
        link.href = chartInstance.toBase64Image();
        link.download = 'price-history-chart.png';
        link.click();
    });

    function renderChart(data, ctx, range) {
        var labels = data.labels; var prices = data.prices; var min_prices = data.min_prices; var max_prices = data.max_prices;
        if (range !== 'all') {
            var totalPoints = labels.length;
            var pointsToShow = Math.floor(parseFloat(range) * 30);
            if (totalPoints > pointsToShow) {
                var start = totalPoints - pointsToShow;
                labels = labels.slice(start); prices = prices.slice(start); min_prices = min_prices.slice(start); max_prices = max_prices.slice(start);
            }
        }
        // ... (Chart Dataset Logic - kept same) ...
        var isSinglePrice = true;
        if (min_prices && max_prices && min_prices.length > 0) {
             for (var i = 0; i < min_prices.length; i++) {
                 if (min_prices[i] !== max_prices[i] && min_prices[i] !== null && max_prices[i] !== null) { isSinglePrice = false; break; }
             }
        } else { isSinglePrice = false; }

        var datasets = [];
        if (isSinglePrice) {
            datasets.push({ label: 'قیمت', data: prices, borderColor: 'rgb(75, 192, 192)', tension: 0.1, borderWidth: 3, fill: false });
        } else {
            if (min_prices) datasets.push({ label: 'حداقل', data: min_prices, borderColor: 'rgba(54, 162, 235, 0.8)', borderDash: [5, 5], pointRadius: 0, fill: false });
            if (prices) datasets.push({ label: 'قیمت پایه', data: prices, borderColor: 'rgb(75, 192, 192)', tension: 0.1, borderWidth: 3, fill: { target: 0, above: 'rgba(54, 162, 235, 0.15)' } });
            if (max_prices) datasets.push({ label: 'حداکثر', data: max_prices, borderColor: 'rgba(255, 99, 132, 0.8)', borderDash: [5, 5], pointRadius: 0, fill: { target: 1, above: 'rgba(255, 99, 132, 0.15)' } });
        }

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: true, position: 'top' }, filler: { propagate: false } },
                scales: { y: { beginAtZero: false } }
            }
        });
    }


    // --- سایر پاپ‌آپ‌ها ---
    $(document).on('click', '.cpo-close-modal, .cpo-modal-overlay', function (e) {
        if (e.target === this || $(this).hasClass('cpo-close-modal')) {
            $('.cpo-modal-overlay').hide();
            if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
        }
    });

    // ... (Edit modals - kept same) ...
    $(document).on('click', '.cpo-edit-button, .cpo-edit-cat-button', function (e) {
        e.preventDefault();
        var btn = $(this);
        var ajax_data = { security: cpo_admin_vars.nonce };

        if (btn.hasClass('cpo-edit-button')) {
            ajax_data.action = 'cpo_fetch_product_edit_form';
            ajax_data.id = btn.data('product-id');
        } else {
            ajax_data.action = 'cpo_fetch_category_edit_form';
            ajax_data.id = btn.data('cat-id');
        }

        if ($('#cpo-edit-modal').length === 0) {
            $('body').append('<div id="cpo-edit-modal" class="cpo-modal-overlay" style="display: none;"><div class="cpo-modal-container"><span class="cpo-close-modal">×</span><div class="cpo-edit-modal-content"></div></div></div>');
        }
        var modal = $('#cpo-edit-modal');
        modal.css('display', 'flex').addClass('loading').find('.cpo-edit-modal-content').html('<p style="text-align:center;padding:20px;">' + cpo_admin_vars.i18n.loadingForm + '</p>');

        $.get(cpo_admin_vars.ajax_url, ajax_data).done(function (res) {
            modal.removeClass('loading');
            if (res.success) {
                modal.find('.cpo-edit-modal-content').html(res.data.html);
                if (window.cpo_init_media_uploader) window.cpo_init_media_uploader();
                if (modal.find('.cpo-color-picker').length) modal.find('.cpo-color-picker').wpColorPicker();
            } else {
                modal.find('.cpo-edit-modal-content').html('<p style="color:red;text-align:center;">' + (res.data.message || 'خطا') + '</p>');
            }
        });
    });

    $(document).on('submit', '#cpo-edit-product-form, #cpo-edit-category-form', function (e) {
        e.preventDefault();
        var form = $(this);
        var action_name = (form.attr('id') === 'cpo-edit-product-form') ? 'cpo_handle_edit_product_ajax' : 'cpo_handle_edit_category_ajax';
        var btn = form.find('input[type="submit"]');

        btn.prop('disabled', true).val(cpo_admin_vars.i18n.saving);

        $.post(cpo_admin_vars.ajax_url, form.serialize() + '&action=' + action_name, function (res) {
            if (res.success) {
                alert('تغییرات با موفقیت ذخیره شد.');
                $('#cpo-edit-modal').hide();
                window.location.reload();
            } else {
                alert(res.data.message || cpo_admin_vars.i18n.error);
                btn.prop('disabled', false).val(cpo_admin_vars.i18n.save);
            }
        }).fail(function () {
            alert(cpo_admin_vars.i18n.serverError);
            btn.prop('disabled', false).val(cpo_admin_vars.i18n.save);
        });
    });
    
    $('#cpo-test-email-btn, #cpo-test-sms-btn').click(function () {
        var btn = $(this);
        var log_area = btn.siblings('textarea');
        var action_name = (btn.attr('id') === 'cpo-test-email-btn') ? 'cpo_test_email' : 'cpo_test_sms';

        btn.prop('disabled', true).text('در حال ارسال...');

        $.post(cpo_admin_vars.ajax_url, {
            action: action_name,
            security: cpo_admin_vars.nonce
        }, function (res) {
            log_area.val(res.data.log || JSON.stringify(res));
            btn.prop('disabled', false).text('ارسال تست مجدد');
        }).fail(function (x) {
            log_area.val('خطا: ' + x.responseText);
            btn.prop('disabled', false).text('ارسال تست مجدد');
        });
    });

    // ===============================================
    // --- تغییرات جدید: مدیریت مدال اصلاح قیمت ---
    // ===============================================

    // تابع کمکی برای بارگذاری مجدد جدول تاریخچه (برای صفحه‌بندی و رفرش)
    function loadHistory(pid, page = 1, perPage = 10) {
        var modal = $('#cpo-history-modal');
        // فقط محتوای جدول را تار کن یا لودینگ نشان بده، کل مودال را دوباره نساز
        modal.find('.cpo-history-content').css('opacity', '0.5');

        $.get(cpo_admin_vars.ajax_url, {
            action: 'cpo_fetch_price_history',
            product_id: pid,
            paged: page,
            per_page: perPage,
            security: cpo_admin_vars.nonce
        }).done(function (res) {
            modal.find('.cpo-history-content').css('opacity', '1');
            if (res.success) {
                modal.find('.cpo-history-content').html(res.data.html);

                // --- فعال‌سازی مجدد دیت پیکر شمسی ---
                if ($.fn.pDatepicker) {
                    $('.cpo-persian-date-input').pDatepicker({
                        format: 'YYYY/MM/DD HH:mm', 
                        timePicker: { enabled: true, step: 1 },
                        altField: '#cpo-real-date-input', 
                        altFormat: 'YYYY-MM-DD HH:mm:ss', 
                        initialValue: true,
                        autoClose: true,
                        observer: true // مشاهده تغییرات DOM
                    });
                }
            } else {
                alert('خطا در بارگذاری');
            }
        });
    }

    // باز کردن مدال با کلیک روی دکمه "اصلاح قیمت"
    $(document).on('click', '.cpo-history-btn', function (e) {
        e.preventDefault();
        var pid = $(this).data('product-id');

        if ($('#cpo-history-modal').length === 0) {
            $('body').append(
                '<div id="cpo-history-modal" class="cpo-modal-overlay" style="display: none;">' +
                '<div class="cpo-modal-container" style="max-width:900px;">' +
                '<span class="cpo-close-modal">×</span>' +
                '<div class="cpo-history-content"></div>' +
                '</div></div>'
            );
        }

        var modal = $('#cpo-history-modal');
        modal.css('display', 'flex').find('.cpo-history-content').html('<p style="text-align:center;padding:30px;">' + cpo_admin_vars.i18n.loadingForm + '</p>');

        // بارگذاری اولیه (صفحه ۱، ۱۰ آیتم)
        loadHistory(pid, 1, 10);
    });

    // مدیریت کلیک روی دکمه‌های صفحه‌بندی
    $(document).on('click', '.cpo-pagination-link', function(e) {
        e.preventDefault();
        var btn = $(this);
        var pid = btn.data('product-id');
        var page = btn.data('page');
        var perPage = $('.cpo-per-page-select').val(); // مقدار فعلی سلکتور
        loadHistory(pid, page, perPage);
    });

    // مدیریت تغییر تعداد نمایش در صفحه
    $(document).on('change', '.cpo-per-page-select', function() {
        var select = $(this);
        var pid = select.data('product-id');
        var perPage = select.val();
        loadHistory(pid, 1, perPage); // بازگشت به صفحه ۱
    });

    // سابمیت فرم افزودن رکورد جدید
    $(document).on('submit', '#cpo-add-history-form', function (e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var originalText = btn.text();
        var pid = form.find('input[name="product_id"]').val();
        var perPage = $('.cpo-per-page-select').val() || 10;

        btn.prop('disabled', true).text('...');

        $.post(cpo_admin_vars.ajax_url, form.serialize() + '&action=cpo_add_history_record&security=' + cpo_admin_vars.nonce, function (res) {
            if (res.success) {
                // رفرش جدول
                loadHistory(pid, 1, perPage);
            } else {
                alert(res.data.message || 'خطا در افزودن رکورد');
                btn.prop('disabled', false).text(originalText);
            }
        }).fail(function () {
            alert('خطای سرور');
            btn.prop('disabled', false).text(originalText);
        });
    });

    // حذف رکورد تاریخچه
    $(document).on('click', '.cpo-delete-history', function () {
        if (!confirm('آیا مطمئنید؟')) return;
        var row = $(this).closest('tr');
        var id = row.data('history-id');

        $.post(cpo_admin_vars.ajax_url, {
            action: 'cpo_delete_history_record',
            id: id,
            security: cpo_admin_vars.nonce
        }, function (res) {
            if (res.success) row.fadeOut(300, function () { $(this).remove(); });
            else alert('خطا در حذف');
        });
    });

    // ویرایش سریع با دبل کلیک (مخصوص جدول تاریخچه)
    $(document).on('dblclick', '.cpo-history-editable', function () {
        var cell = $(this);
        if (cell.hasClass('editing')) return;

        var originalContent = cell.text().trim();
        var rawValue = originalContent.replace(/,/g, '');
        var field = cell.data('field');
        var type = cell.data('type') || 'text';

        cell.addClass('editing');

        var inputHtml = '';
        if (type === 'datetime') {
            inputHtml = '<input type="text" class="cpo-hist-input" value="' + rawValue + '" style="width:100%; direction:ltr;">';
        } else {
            inputHtml = '<input type="text" class="cpo-hist-input" value="' + rawValue + '" style="width:100%; text-align:center;">';
        }

        cell.html(inputHtml);
        var input = cell.find('input');
        input.focus();

        input.on('blur keypress', function (e) {
            if (e.type === 'keypress' && e.which !== 13) return;

            var newVal = $(this).val();
            if (newVal === rawValue) {
                cell.html(originalContent);
                cell.removeClass('editing');
                return;
            }

            cell.text('Saving...');
            var rowId = cell.closest('tr').data('history-id');

            $.post(cpo_admin_vars.ajax_url, {
                action: 'cpo_update_history_cell',
                id: rowId,
                field: field,
                value: newVal,
                security: cpo_admin_vars.nonce
            }, function (res) {
                if (res.success) {
                    // برای سادگی، فعلاً مقدار جدید را نشان می‌دهیم.
                    // برای تبدیل دقیق مجدد به شمسی در سمت کلاینت نیاز به تابع مبدل است
                    // اما چون صفحه رفرش نمی‌شود، کاربر مقدار عددی/متنی وارد شده را می‌بیند.
                    cell.text(newVal);
                } else {
                    cell.text(originalContent);
                    alert('خطا در ذخیره');
                }
                cell.removeClass('editing');
            });
        });
    });

});
