<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin settings template
 */
if (!current_user_can('manage_options')) {
    return;
}

$criptapro_settings = get_option('criptapro_auth_settings', array());
?>
<div class="wrap criptapro-auth-settings">
    <h1><?php esc_html_e('Настройки CriptaPro Auth', 'criptapro-auth'); ?></h1>
    
    <div class="criptapro-settings-section">
        <h3><?php esc_html_e('Проверка системы', 'criptapro-auth'); ?></h3>
        <div class="criptapro-system-check">
            <p>
                <button type="button" id="check-cpstore" class="button button-secondary">
                    <?php esc_html_e('Проверить CPStore', 'criptapro-auth'); ?>
                </button>
            </p>
            <div id="cpstore-check-result" class="criptapro-check-result" style="display: none; margin-top: 10px; padding: 10px; border-radius: 4px;"></div>
        </div>
    </div>
    
    <div class="criptapro-settings-section">
        <h3><?php esc_html_e('Основные настройки', 'criptapro-auth'); ?></h3>
        <form method="post" action="options.php">
            <?php
            settings_fields('criptapro_auth_settings');
            do_settings_sections('criptapro_auth_settings');
            ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="test_mode"><?php esc_html_e('Тест-режим', 'criptapro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="test_mode" name="criptapro_auth_settings[test_mode]" 
                               value="1" <?php checked($criptapro_settings['test_mode'] ?? false, true); ?> />
                        <label for="test_mode"><?php esc_html_e('Включить тест-режим (пропускать проверку подписи на сервере)', 'criptapro-auth'); ?></label>
                        <p class="description">
                            <?php esc_html_e('В тест-режиме авторизация проходит без проверки подписи на сервере. Используйте только для разработки и тестирования!', 'criptapro-auth'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="api_endpoint"><?php esc_html_e('API Endpoint', 'criptapro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="api_endpoint" name="criptapro_auth_settings[api_endpoint]" 
                               value="<?php echo esc_attr($criptapro_settings['api_endpoint'] ?? ''); ?>" 
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e('URL endpoint для проверки подписей', 'criptapro-auth'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="auto_create_users"><?php esc_html_e('Автосоздание пользователей', 'criptapro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="auto_create_users" name="criptapro_auth_settings[auto_create_users]" 
                               value="1" <?php checked($criptapro_settings['auto_create_users'] ?? true, true); ?> />
                        <label for="auto_create_users"><?php esc_html_e('Создавать пользователей автоматически', 'criptapro-auth'); ?></label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default_user_role"><?php esc_html_e('Роль по умолчанию', 'criptapro-auth'); ?></label>
                    </th>
                    <td>
                        <select id="default_user_role" name="criptapro_auth_settings[default_user_role]">
                            <?php wp_dropdown_roles($criptapro_settings['default_user_role'] ?? 'subscriber'); ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="debug_mode"><?php esc_html_e('Режим отладки', 'criptapro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="debug_mode" name="criptapro_auth_settings[debug_mode]" 
                               value="1" <?php checked($criptapro_settings['debug_mode'] ?? false, true); ?> />
                        <label for="debug_mode"><?php esc_html_e('Включить отладочные сообщения на странице', 'criptapro-auth'); ?></label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="redirect_url"><?php esc_html_e('URL редиректа после авторизации', 'criptapro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="redirect_url" name="criptapro_auth_settings[redirect_url]" 
                               value="<?php echo esc_attr($criptapro_settings['redirect_url'] ?? ''); ?>" 
                               class="regular-text" placeholder="<?php echo esc_attr(home_url()); ?>" />
                        <p class="description">
                            <?php esc_html_e('URL для перенаправления после успешной авторизации. Оставьте пустым для использования главной страницы.', 'criptapro-auth'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Безопасность', 'criptapro-auth'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="trusted_issuers"><?php esc_html_e('Доверенные УЦ (Whitelist)', 'criptapro-auth'); ?></label>
                    </th>
                    <td>
                        <textarea id="trusted_issuers" name="criptapro_auth_settings[trusted_issuers]" rows="5" class="large-text code"><?php echo esc_textarea($criptapro_settings['trusted_issuers'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php esc_html_e('Введите части названий доверенных издателей (Issuer), по одному на строку. Например: "Федеральная налоговая служба". Если пусто - проверка отключена.', 'criptapro-auth'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="enable_logging"><?php esc_html_e('Логирование попыток входа', 'criptapro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="enable_logging" name="criptapro_auth_settings[enable_logging]" 
                               value="1" <?php checked($criptapro_settings['enable_logging'] ?? false, true); ?> />
                        <label for="enable_logging"><?php esc_html_e('Записывать попытки входа в лог-файл', 'criptapro-auth'); ?></label>
                        <p class="description">
                            <?php 
                            /* translators: %s: path to the log file */
                            echo wp_kses_post(sprintf(__('Лог файл: %s', 'criptapro-auth'), '<code>/wp-content/uploads/criptapro-auth-logs/auth.log</code>')); 
                            ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="require_https"><?php esc_html_e('Требовать HTTPS', 'criptapro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="require_https" name="criptapro_auth_settings[require_https]" 
                               value="1" <?php checked($criptapro_settings['require_https'] ?? false, true); ?> />
                        <label for="require_https"><?php esc_html_e('Запретить авторизацию по незащищенному протоколу HTTP', 'criptapro-auth'); ?></label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="enable_cors"><?php esc_html_e('Настройки CORS', 'criptapro-auth'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" id="enable_cors" name="criptapro_auth_settings[enable_cors]" 
                                       value="1" <?php checked($criptapro_settings['enable_cors'] ?? false, true); ?> />
                                <?php esc_html_e('Включить заголовки CORS для API', 'criptapro-auth'); ?>
                            </label>
                            <br><br>
                            <label for="allowed_origins"><?php esc_html_e('Разрешенные домены (Origins):', 'criptapro-auth'); ?></label>
                            <textarea id="allowed_origins" name="criptapro_auth_settings[allowed_origins]" rows="3" class="large-text code"><?php echo esc_textarea($criptapro_settings['allowed_origins'] ?? home_url()); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('Список разрешенных доменов для Cross-Origin запросов, по одному на строку. По умолчанию добавлен текущий домен.', 'criptapro-auth'); ?>
                            </p>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    
    <div class="criptapro-settings-section">
        <h3><?php esc_html_e('Шорткоды', 'criptapro-auth'); ?></h3>
        <p><strong><?php esc_html_e('Форма логина:', 'criptapro-auth'); ?></strong> <code>[criptapro_login]</code></p>
        <p><strong><?php esc_html_e('Блок авторизации:', 'criptapro-auth'); ?></strong> <code>[criptapro_auth]</code></p>
        <p><strong><?php esc_html_e('Отладочная информация:', 'criptapro-auth'); ?></strong> <code>[criptapro_debug]</code></p>
        
        <h4><?php esc_html_e('Параметры:', 'criptapro-auth'); ?></h4>
        <ul>
            <li><code>button_text</code> - <?php esc_html_e('Текст кнопки', 'criptapro-auth'); ?></li>
            <li><code>show_guide</code> - <?php esc_html_e('Показывать инструкцию', 'criptapro-auth'); ?></li>
            <li><code>show_debug</code> - <?php esc_html_e('Показывать отладку', 'criptapro-auth'); ?></li>
            <li><code>title</code> - <?php esc_html_e('Заголовок блока', 'criptapro-auth'); ?></li>
            <li><code>description</code> - <?php esc_html_e('Описание', 'criptapro-auth'); ?></li>
            <li><code>class</code> - <?php esc_html_e('CSS класс', 'criptapro-auth'); ?></li>
        </ul>
    </div>
    
    <?php
    /**
     * Хук для добавления дополнительных секций настроек другими плагинами
     * 
     * @since 1.0.0
     * @param array $criptapro_settings Текущие настройки плагина CriptaPro Auth
     */
    do_action('criptapro_auth_settings_page_end', $criptapro_settings);
    ?>
</div>
