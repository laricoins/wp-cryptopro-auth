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
    <h1><?php esc_html_e('Р СњР В°РЎРѓРЎвЂљРЎР‚Р С•Р в„–Р С”Р С‘ CryptoPro Auth', 'wp-cryptopro-auth'); ?></h1>
    
    <div class="cryptopro-settings-section">
        <h3><?php esc_html_e('Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° РЎРѓР С‘РЎРѓРЎвЂљР ВµР СРЎвЂ№', 'wp-cryptopro-auth'); ?></h3>
        <div class="cryptopro-system-check">
            <p>
                <button type="button" id="check-cpstore" class="button button-secondary">
                    <?php esc_html_e('Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С‘РЎвЂљРЎРЉ CPStore', 'wp-cryptopro-auth'); ?>
                </button>
            </p>
            <div id="cpstore-check-result" class="cryptopro-check-result" style="display: none; margin-top: 10px; padding: 10px; border-radius: 4px;"></div>
        </div>
    </div>
    
    <div class="cryptopro-settings-section">
        <h3><?php esc_html_e('Р С›РЎРѓР Р…Р С•Р Р†Р Р…РЎвЂ№Р Вµ Р Р…Р В°РЎРѓРЎвЂљРЎР‚Р С•Р в„–Р С”Р С‘', 'wp-cryptopro-auth'); ?></h3>
        <form method="post" action="options.php">
            <?php
            settings_fields('cryptopro_auth_settings');
            do_settings_sections('cryptopro_auth_settings');
            ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="test_mode"><?php esc_html_e('Р СћР ВµРЎРѓРЎвЂљ-РЎР‚Р ВµР В¶Р С‘Р С', 'wp-cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="test_mode" name="cryptopro_auth_settings[test_mode]" 
                               value="1" <?php checked($cryptopro_settings['test_mode'] ?? false, true); ?> />
                        <label for="test_mode"><?php esc_html_e('Р вЂ™Р С”Р В»РЎР‹РЎвЂЎР С‘РЎвЂљРЎРЉ РЎвЂљР ВµРЎРѓРЎвЂљ-РЎР‚Р ВµР В¶Р С‘Р С (Р С—РЎР‚Р С•Р С—РЎС“РЎРѓР С”Р В°РЎвЂљРЎРЉ Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚Р С”РЎС“ Р С—Р С•Р Т‘Р С—Р С‘РЎРѓР С‘ Р Р…Р В° РЎРѓР ВµРЎР‚Р Р†Р ВµРЎР‚Р Вµ)', 'wp-cryptopro-auth'); ?></label>
                        <p class="description">
                            <?php esc_html_e('Р вЂ™ РЎвЂљР ВµРЎРѓРЎвЂљ-РЎР‚Р ВµР В¶Р С‘Р СР Вµ Р В°Р Р†РЎвЂљР С•РЎР‚Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ Р С—РЎР‚Р С•РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљ Р В±Р ВµР В· Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р С‘ Р С—Р С•Р Т‘Р С—Р С‘РЎРѓР С‘ Р Р…Р В° РЎРѓР ВµРЎР‚Р Р†Р ВµРЎР‚Р Вµ. Р ВРЎРѓР С—Р С•Р В»РЎРЉР В·РЎС“Р в„–РЎвЂљР Вµ РЎвЂљР С•Р В»РЎРЉР С”Р С• Р Т‘Р В»РЎРЏ РЎР‚Р В°Р В·РЎР‚Р В°Р В±Р С•РЎвЂљР С”Р С‘ Р С‘ РЎвЂљР ВµРЎРѓРЎвЂљР С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С‘РЎРЏ!', 'wp-cryptopro-auth'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="api_endpoint"><?php esc_html_e('API Endpoint', 'wp-cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="api_endpoint" name="cryptopro_auth_settings[api_endpoint]" 
                               value="<?php echo esc_attr($cryptopro_settings['api_endpoint'] ?? ''); ?>" 
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e('URL endpoint Р Т‘Р В»РЎРЏ Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р С‘ Р С—Р С•Р Т‘Р С—Р С‘РЎРѓР ВµР в„–', 'wp-cryptopro-auth'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="auto_create_users"><?php esc_html_e('Р С’Р Р†РЎвЂљР С•РЎРѓР С•Р В·Р Т‘Р В°Р Р…Р С‘Р Вµ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»Р ВµР в„–', 'wp-cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="auto_create_users" name="cryptopro_auth_settings[auto_create_users]" 
                               value="1" <?php checked($cryptopro_settings['auto_create_users'] ?? true, true); ?> />
                        <label for="auto_create_users"><?php esc_html_e('Р РЋР С•Р В·Р Т‘Р В°Р Р†Р В°РЎвЂљРЎРЉ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»Р ВµР в„– Р В°Р Р†РЎвЂљР С•Р СР В°РЎвЂљР С‘РЎвЂЎР ВµРЎРѓР С”Р С‘', 'wp-cryptopro-auth'); ?></label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default_user_role"><?php esc_html_e('Р В Р С•Р В»РЎРЉ Р С—Р С• РЎС“Р СР С•Р В»РЎвЂЎР В°Р Р…Р С‘РЎР‹', 'wp-cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <select id="default_user_role" name="cryptopro_auth_settings[default_user_role]">
                            <?php wp_dropdown_roles($cryptopro_settings['default_user_role'] ?? 'subscriber'); ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="debug_mode"><?php esc_html_e('Р В Р ВµР В¶Р С‘Р С Р С•РЎвЂљР В»Р В°Р Т‘Р С”Р С‘', 'wp-cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="debug_mode" name="cryptopro_auth_settings[debug_mode]" 
                               value="1" <?php checked($cryptopro_settings['debug_mode'] ?? false, true); ?> />
                        <label for="debug_mode"><?php esc_html_e('Р вЂ™Р С”Р В»РЎР‹РЎвЂЎР С‘РЎвЂљРЎРЉ Р С•РЎвЂљР В»Р В°Р Т‘Р С•РЎвЂЎР Р…РЎвЂ№Р Вµ РЎРѓР С•Р С•Р В±РЎвЂ°Р ВµР Р…Р С‘РЎРЏ Р Р…Р В° РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р Вµ', 'wp-cryptopro-auth'); ?></label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="redirect_url"><?php esc_html_e('URL РЎР‚Р ВµР Т‘Р С‘РЎР‚Р ВµР С”РЎвЂљР В° Р С—Р С•РЎРѓР В»Р Вµ Р В°Р Р†РЎвЂљР С•РЎР‚Р С‘Р В·Р В°РЎвЂ Р С‘Р С‘', 'wp-cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="redirect_url" name="cryptopro_auth_settings[redirect_url]" 
                               value="<?php echo esc_attr($cryptopro_settings['redirect_url'] ?? ''); ?>" 
                               class="regular-text" placeholder="<?php echo esc_attr(home_url()); ?>" />
                        <p class="description">
                            <?php esc_html_e('URL Р Т‘Р В»РЎРЏ Р С—Р ВµРЎР‚Р ВµР Р…Р В°Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘РЎРЏ Р С—Р С•РЎРѓР В»Р Вµ РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С•Р в„– Р В°Р Р†РЎвЂљР С•РЎР‚Р С‘Р В·Р В°РЎвЂ Р С‘Р С‘. Р С›РЎРѓРЎвЂљР В°Р Р†РЎРЉРЎвЂљР Вµ Р С—РЎС“РЎРѓРЎвЂљРЎвЂ№Р С Р Т‘Р В»РЎРЏ Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°Р Р…Р С‘РЎРЏ Р С–Р В»Р В°Р Р†Р Р…Р С•Р в„– РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ РЎвЂ№.', 'wp-cryptopro-auth'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Р вЂР ВµР В·Р С•Р С—Р В°РЎРѓР Р…Р С•РЎРѓРЎвЂљРЎРЉ', 'wp-cryptopro-auth'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="trusted_issuers"><?php esc_html_e('Р вЂќР С•Р Р†Р ВµРЎР‚Р ВµР Р…Р Р…РЎвЂ№Р Вµ Р Р€Р В¦ (Whitelist)', 'wp-cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <textarea id="trusted_issuers" name="cryptopro_auth_settings[trusted_issuers]" rows="5" class="large-text code"><?php echo esc_textarea($cryptopro_settings['trusted_issuers'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php esc_html_e('Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ РЎвЂЎР В°РЎРѓРЎвЂљР С‘ Р Р…Р В°Р В·Р Р†Р В°Р Р…Р С‘Р в„– Р Т‘Р С•Р Р†Р ВµРЎР‚Р ВµР Р…Р Р…РЎвЂ№РЎвЂ¦ Р С‘Р В·Р Т‘Р В°РЎвЂљР ВµР В»Р ВµР в„– (Issuer), Р С—Р С• Р С•Р Т‘Р Р…Р С•Р СРЎС“ Р Р…Р В° РЎРѓРЎвЂљРЎР‚Р С•Р С”РЎС“. Р СњР В°Р С—РЎР‚Р С‘Р СР ВµРЎР‚: "Р В¤Р ВµР Т‘Р ВµРЎР‚Р В°Р В»РЎРЉР Р…Р В°РЎРЏ Р Р…Р В°Р В»Р С•Р С–Р С•Р Р†Р В°РЎРЏ РЎРѓР В»РЎС“Р В¶Р В±Р В°". Р вЂўРЎРѓР В»Р С‘ Р С—РЎС“РЎРѓРЎвЂљР С• - Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° Р С•РЎвЂљР С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р В°.', 'wp-cryptopro-auth'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="enable_logging"><?php esc_html_e('Р вЂєР С•Р С–Р С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С‘Р Вµ Р С—Р С•Р С—РЎвЂ№РЎвЂљР С•Р С” Р Р†РЎвЂ¦Р С•Р Т‘Р В°', 'wp-cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="enable_logging" name="cryptopro_auth_settings[enable_logging]" 
                               value="1" <?php checked($cryptopro_settings['enable_logging'] ?? false, true); ?> />
                        <label for="enable_logging"><?php esc_html_e('Р вЂ”Р В°Р С—Р С‘РЎРѓРЎвЂ№Р Р†Р В°РЎвЂљРЎРЉ Р С—Р С•Р С—РЎвЂ№РЎвЂљР С”Р С‘ Р Р†РЎвЂ¦Р С•Р Т‘Р В° Р Р† Р В»Р С•Р С–-РЎвЂћР В°Р в„–Р В»', 'wp-cryptopro-auth'); ?></label>
                        <p class="description">
                            <?php 
                            /* translators: %s: path to the log file */
                            echo wp_kses_post(sprintf(__('Р вЂєР С•Р С– РЎвЂћР В°Р в„–Р В»: %s', 'wp-cryptopro-auth'), '<code>/wp-content/uploads/cryptopro-auth-logs/auth.log</code>')); 
                            ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="require_https"><?php esc_html_e('Р СћРЎР‚Р ВµР В±Р С•Р Р†Р В°РЎвЂљРЎРЉ HTTPS', 'wp-cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="require_https" name="cryptopro_auth_settings[require_https]" 
                               value="1" <?php checked($cryptopro_settings['require_https'] ?? false, true); ?> />
                        <label for="require_https"><?php esc_html_e('Р вЂ”Р В°Р С—РЎР‚Р ВµРЎвЂљР С‘РЎвЂљРЎРЉ Р В°Р Р†РЎвЂљР С•РЎР‚Р С‘Р В·Р В°РЎвЂ Р С‘РЎР‹ Р С—Р С• Р Р…Р ВµР В·Р В°РЎвЂ°Р С‘РЎвЂ°Р ВµР Р…Р Р…Р С•Р СРЎС“ Р С—РЎР‚Р С•РЎвЂљР С•Р С”Р С•Р В»РЎС“ HTTP', 'wp-cryptopro-auth'); ?></label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="enable_cors"><?php esc_html_e('Р СњР В°РЎРѓРЎвЂљРЎР‚Р С•Р в„–Р С”Р С‘ CORS', 'wp-cryptopro-auth'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" id="enable_cors" name="cryptopro_auth_settings[enable_cors]" 
                                       value="1" <?php checked($cryptopro_settings['enable_cors'] ?? false, true); ?> />
                                <?php esc_html_e('Р вЂ™Р С”Р В»РЎР‹РЎвЂЎР С‘РЎвЂљРЎРЉ Р В·Р В°Р С–Р С•Р В»Р С•Р Р†Р С”Р С‘ CORS Р Т‘Р В»РЎРЏ API', 'wp-cryptopro-auth'); ?>
                            </label>
                            <br><br>
                            <label for="allowed_origins"><?php esc_html_e('Р В Р В°Р В·РЎР‚Р ВµРЎв‚¬Р ВµР Р…Р Р…РЎвЂ№Р Вµ Р Т‘Р С•Р СР ВµР Р…РЎвЂ№ (Origins):', 'wp-cryptopro-auth'); ?></label>
                            <textarea id="allowed_origins" name="cryptopro_auth_settings[allowed_origins]" rows="3" class="large-text code"><?php echo esc_textarea($cryptopro_settings['allowed_origins'] ?? home_url()); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('Р РЋР С—Р С‘РЎРѓР С•Р С” РЎР‚Р В°Р В·РЎР‚Р ВµРЎв‚¬Р ВµР Р…Р Р…РЎвЂ№РЎвЂ¦ Р Т‘Р С•Р СР ВµР Р…Р С•Р Р† Р Т‘Р В»РЎРЏ Cross-Origin Р В·Р В°Р С—РЎР‚Р С•РЎРѓР С•Р Р†, Р С—Р С• Р С•Р Т‘Р Р…Р С•Р СРЎС“ Р Р…Р В° РЎРѓРЎвЂљРЎР‚Р С•Р С”РЎС“. Р СџР С• РЎС“Р СР С•Р В»РЎвЂЎР В°Р Р…Р С‘РЎР‹ Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р… РЎвЂљР ВµР С”РЎС“РЎвЂ°Р С‘Р в„– Р Т‘Р С•Р СР ВµР Р….', 'wp-cryptopro-auth'); ?>
                            </p>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    
    <div class="cryptopro-settings-section">
        <h3><?php esc_html_e('Р РЃР С•РЎР‚РЎвЂљР С”Р С•Р Т‘РЎвЂ№', 'wp-cryptopro-auth'); ?></h3>
        <p><strong><?php esc_html_e('Р В¤Р С•РЎР‚Р СР В° Р В»Р С•Р С–Р С‘Р Р…Р В°:', 'wp-cryptopro-auth'); ?></strong> <code>[cryptopro_login]</code></p>
        <p><strong><?php esc_html_e('Р вЂР В»Р С•Р С” Р В°Р Р†РЎвЂљР С•РЎР‚Р С‘Р В·Р В°РЎвЂ Р С‘Р С‘:', 'wp-cryptopro-auth'); ?></strong> <code>[cryptopro_auth]</code></p>
        <p><strong><?php esc_html_e('Р С›РЎвЂљР В»Р В°Р Т‘Р С•РЎвЂЎР Р…Р В°РЎРЏ Р С‘Р Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ:', 'wp-cryptopro-auth'); ?></strong> <code>[cryptopro_debug]</code></p>
        
        <h4><?php esc_html_e('Р СџР В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚РЎвЂ№:', 'wp-cryptopro-auth'); ?></h4>
        <ul>
            <li><code>button_text</code> - <?php esc_html_e('Р СћР ВµР С”РЎРѓРЎвЂљ Р С”Р Р…Р С•Р С—Р С”Р С‘', 'wp-cryptopro-auth'); ?></li>
            <li><code>show_guide</code> - <?php esc_html_e('Р СџР С•Р С”Р В°Р В·РЎвЂ№Р Р†Р В°РЎвЂљРЎРЉ Р С‘Р Р…РЎРѓРЎвЂљРЎР‚РЎС“Р С”РЎвЂ Р С‘РЎР‹', 'wp-cryptopro-auth'); ?></li>
            <li><code>show_debug</code> - <?php esc_html_e('Р СџР С•Р С”Р В°Р В·РЎвЂ№Р Р†Р В°РЎвЂљРЎРЉ Р С•РЎвЂљР В»Р В°Р Т‘Р С”РЎС“', 'wp-cryptopro-auth'); ?></li>
            <li><code>title</code> - <?php esc_html_e('Р вЂ”Р В°Р С–Р С•Р В»Р С•Р Р†Р С•Р С” Р В±Р В»Р С•Р С”Р В°', 'wp-cryptopro-auth'); ?></li>
            <li><code>description</code> - <?php esc_html_e('Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ', 'wp-cryptopro-auth'); ?></li>
            <li><code>class</code> - <?php esc_html_e('CSS Р С”Р В»Р В°РЎРѓРЎРѓ', 'wp-cryptopro-auth'); ?></li>
        </ul>
    </div>
</div>
