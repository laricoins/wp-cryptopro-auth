(function ($) {
    'use strict';

    $(document).ready(function () {
        // Проверка CPStore
        $('#check-cpstore').on('click', function () {
            const $button = $(this);
            const $result = $('#cpstore-check-result');

            $button.prop('disabled', true).text(criptapro_admin.strings.checking);
            $result.hide();

            $.ajax({
                url: criptapro_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'criptapro_check_cpstore',
                    nonce: criptapro_admin.nonce
                },
                success: function (response) {
                    $button.prop('disabled', false).text('Проверить CPStore');

                    if (response.success && response.data) {
                        const data = response.data;
                        let html = '';

                        if (data.available && data.php_extension) {
                            html = '<div class="notice notice-success inline"><p><strong>✓ ' + data.message + '</strong></p>';
                            if (data.details.classes) {
                                html += '<p><strong>Доступные классы:</strong> ' + data.details.classes + '</p>';
                            }
                            if (data.details.instance) {
                                html += '<p>' + data.details.instance + '</p>';
                            }
                            if (data.details.verification) {
                                html += '<p><strong>' + data.details.verification + '</strong></p>';
                            }
                            if (data.details.instance_error) {
                                html += '<p class="error">Ошибка создания экземпляра: ' + data.details.instance_error + '</p>';
                            }
                            html += '</div>';
                            $result.removeClass('notice-error').addClass('notice-success');
                        } else if (data.php_extension === false) {
                            html = '<div class="notice notice-warning inline"><p><strong>⚠ ' + data.message + '</strong></p>';
                            if (data.details.note) {
                                html += '<p>' + data.details.note + '</p>';
                            }
                            html += '</div>';
                            $result.removeClass('notice-error notice-success').addClass('notice-warning');
                        } else {
                            html = '<div class="notice notice-error inline"><p><strong>✗ ' + data.message + '</strong></p></div>';
                            $result.removeClass('notice-success notice-warning').addClass('notice-error');
                        }

                        $result.html(html).show();
                    } else {
                        $result.html('<div class="notice notice-error inline"><p>Ошибка проверки</p></div>').show();
                    }
                },
                error: function () {
                    $button.prop('disabled', false).text('Проверить CPStore');
                    $('#cpstore-check-result').html('<div class="notice notice-error inline"><p>Ошибка запроса</p></div>').show();
                }
            });
        });
    });
})(jQuery);
