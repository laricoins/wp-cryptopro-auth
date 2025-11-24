<?php
/**
 * Admin settings template
 */
if (!current_user_can('manage_options')) {
    return;
}

$cryptopro_settings = get_option('cryptopro_auth_settings', array());
?>
<div class="wrap cryptopro-auth-settings">
    <h1><?php esc_html_e('Настройки CryptoPro Auth', 'cryptopro-auth'); ?></h1>
    
    <div class="cryptopro-settings-section">
        <h3><?php esc_html_e('Проверка системы', 'cryptopro-auth'); ?></h3>
        <div class="cryptopro-system-check">
            <p>
                <button type="button" id="check-cpstore" class="button button-secondary">
                    <?php esc_html_e('Проверить CPStore', 'cryptopro-auth'); ?>
                </button>
            </p>
            <div id="cpstore-check-result" class="cryptopro-check-result" style="display: none; margin-top: 10px; padding: 10px; border-radius: 4px;"></div>
        </div>
    </div>
    
    <div class="cryptopro-settings-section">
        <h3><?php esc_html_e('Основные настройки', 'cryptopro-auth'); ?></h3>
        <form method="post" action="options.php">
            <?php
            settings_fields('cryptopro_auth_settings');
            do_settings_sections('cryptopro_auth_settings');
            ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="test_mode"><?php esc_html_e('Тест-режим', 'cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="test_mode" name="cryptopro_auth_settings[test_mode]" 
                               value="1" <?php checked($cryptopro_settings['test_mode'] ?? false, true); ?> />
                        <label for="test_mode"><?php esc_html_e('Включить тест-режим (пропускать проверку подписи на сервере)', 'cryptopro-auth'); ?></label>
                        <p class="description">
                            <?php esc_html_e('В тест-режиме авторизация проходит без проверки подписи на сервере. Используйте только для разработки и тестирования!', 'cryptopro-auth'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="api_endpoint"><?php esc_html_e('API Endpoint', 'cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="api_endpoint" name="cryptopro_auth_settings[api_endpoint]" 
                               value="<?php echo esc_attr($cryptopro_settings['api_endpoint'] ?? ''); ?>" 
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e('URL endpoint для проверки подписей', 'cryptopro-auth'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="auto_create_users"><?php esc_html_e('Автосоздание пользователей', 'cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="auto_create_users" name="cryptopro_auth_settings[auto_create_users]" 
                               value="1" <?php checked($cryptopro_settings['auto_create_users'] ?? true, true); ?> />
                        <label for="auto_create_users"><?php esc_html_e('Создавать пользователей автоматически', 'cryptopro-auth'); ?></label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default_user_role"><?php esc_html_e('Роль по умолчанию', 'cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <select id="default_user_role" name="cryptopro_auth_settings[default_user_role]">
                            <?php wp_dropdown_roles($cryptopro_settings['default_user_role'] ?? 'subscriber'); ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="debug_mode"><?php esc_html_e('Режим отладки', 'cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="debug_mode" name="cryptopro_auth_settings[debug_mode]" 
                               value="1" <?php checked($cryptopro_settings['debug_mode'] ?? false, true); ?> />
                        <label for="debug_mode"><?php esc_html_e('Включить отладочные сообщения на странице', 'cryptopro-auth'); ?></label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="redirect_url"><?php esc_html_e('URL редиректа после авторизации', 'cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="redirect_url" name="cryptopro_auth_settings[redirect_url]" 
                               value="<?php echo esc_attr($cryptopro_settings['redirect_url'] ?? ''); ?>" 
                               class="regular-text" placeholder="<?php echo esc_attr(home_url()); ?>" />
                        <p class="description">
                            <?php esc_html_e('URL для перенаправления после успешной авторизации. Оставьте пустым для использования главной страницы.', 'cryptopro-auth'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Безопасность', 'cryptopro-auth'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="trusted_issuers"><?php esc_html_e('Доверенные УЦ (Whitelist)', 'cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <textarea id="trusted_issuers" name="cryptopro_auth_settings[trusted_issuers]" rows="5" class="large-text code"><?php echo esc_textarea($cryptopro_settings['trusted_issuers'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php esc_html_e('Введите части названий доверенных издателей (Issuer), по одному на строку. Например: "Федеральная налоговая служба". Если пусто - проверка отключена.', 'cryptopro-auth'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="enable_logging"><?php esc_html_e('Логирование попыток входа', 'cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="enable_logging" name="cryptopro_auth_settings[enable_logging]" 
                               value="1" <?php checked($cryptopro_settings['enable_logging'] ?? false, true); ?> />
                        <label for="enable_logging"><?php esc_html_e('Записывать попытки входа в лог-файл', 'cryptopro-auth'); ?></label>
                        <p class="description">
                            <?php 
                            /* translators: %s: path to the log file */
                            echo wp_kses_post(sprintf(__('Лог файл: %s', 'cryptopro-auth'), '<code>/wp-content/uploads/cryptopro-auth-logs/auth.log</code>')); 
                            ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="require_https"><?php esc_html_e('Требовать HTTPS', 'cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="require_https" name="cryptopro_auth_settings[require_https]" 
                               value="1" <?php checked($cryptopro_settings['require_https'] ?? false, true); ?> />
                        <label for="require_https"><?php esc_html_e('Запретить авторизацию по незащищенному протоколу HTTP', 'cryptopro-auth'); ?></label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="enable_cors"><?php esc_html_e('Настройки CORS', 'cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" id="enable_cors" name="cryptopro_auth_settings[enable_cors]" 
                                       value="1" <?php checked($cryptopro_settings['enable_cors'] ?? false, true); ?> />
                                <?php esc_html_e('Включить заголовки CORS для API', 'cryptopro-auth'); ?>
                            </label>
                            <br><br>
                            <label for="allowed_origins"><?php esc_html_e('Разрешенные домены (Origins):', 'cryptopro-auth'); ?></label>
                            <textarea id="allowed_origins" name="cryptopro_auth_settings[allowed_origins]" rows="3" class="large-text code"><?php echo esc_textarea($cryptopro_settings['allowed_origins'] ?? home_url()); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('Список разрешенных доменов для Cross-Origin запросов, по одному на строку. По умолчанию добавлен текущий домен.', 'cryptopro-auth'); ?>
                            </p>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    
    <div class="cryptopro-settings-section">
        <h3><?php esc_html_e('Шорткоды', 'cryptopro-auth'); ?></h3>
        <p><strong><?php esc_html_e('Форма логина:', 'cryptopro-auth'); ?></strong> <code>[cryptopro_login]</code></p>
        <p><strong><?php esc_html_e('Блок авторизации:', 'cryptopro-auth'); ?></strong> <code>[cryptopro_auth]</code></p>
        <p><strong><?php esc_html_e('Отладочная информация:', 'cryptopro-auth'); ?></strong> <code>[cryptopro_debug]</code></p>
        
        <h4><?php esc_html_e('Параметры:', 'cryptopro-auth'); ?></h4>
        <ul>
            <li><code>button_text</code> - <?php esc_html_e('Текст кнопки', 'cryptopro-auth'); ?></li>
            <li><code>show_guide</code> - <?php esc_html_e('Показывать инструкцию', 'cryptopro-auth'); ?></li>
            <li><code>show_debug</code> - <?php esc_html_e('Показывать отладку', 'cryptopro-auth'); ?></li>
            <li><code>title</code> - <?php esc_html_e('Заголовок блока', 'cryptopro-auth'); ?></li>
            <li><code>description</code> - <?php esc_html_e('Описание', 'cryptopro-auth'); ?></li>
            <li><code>class</code> - <?php esc_html_e('CSS класс', 'cryptopro-auth'); ?></li>
        </ul>
    </div>
</div>
