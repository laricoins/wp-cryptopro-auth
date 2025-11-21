<?php
if (!defined('ABSPATH')) {
    exit;
}

class CryptoPro_Shortcodes {
    
    /**
     * РљРѕРЅСЃС‚СЂСѓРєС‚РѕСЂ РєР»Р°СЃСЃР°.
     * 
     * @author @ddnitecry
     */
    public function __construct() {
        add_shortcode('cryptopro_login', array($this, 'login_shortcode'));
        add_shortcode('cryptopro_auth', array($this, 'auth_shortcode'));
        add_shortcode('cryptopro_debug', array($this, 'debug_shortcode'));
    }
    
    /**
     * РЁРѕСЂС‚РєРѕРґ РґР»СЏ РєРЅРѕРїРєРё РІС…РѕРґР° [cryptopro_login].
     * 
     * @param mixed $atts РђС‚СЂРёР±СѓС‚С‹ С€РѕСЂС‚РєРѕРґР°.
     * @return string HTML РєРѕРґ.
     * @author @ddnitecry
     */
    public function login_shortcode(mixed $atts): string {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            return sprintf(
                '<div class="cryptopro-logged-in-message">%s, %s</div>',
                esc_html($current_user->display_name),
                __('РІС‹ СѓР¶Рµ Р·Р°Р»РѕРіРёРЅРµРЅС‹', 'wp-cryptopro-auth')
            );
        }

        $atts = shortcode_atts(array(
            'button_text' => __('Р’РѕР№С‚Рё СЃ РїРѕРјРѕС‰СЊСЋ РљСЂРёРїС‚РѕРџСЂРѕ', 'wp-cryptopro-auth'),
            'show_guide' => 'true',
            'show_debug' => $this->is_debug_enabled() ? 'true' : 'false',
            'class' => 'cryptopro-login-form'
        ), $atts);
        
        ob_start();
        ?>
        <div class="cryptopro-auth-container <?php echo esc_attr($atts['class']); ?>">
            <button class="cryptopro-auth-btn" type="button">
                <?php echo esc_html($atts['button_text']); ?>
            </button>
            <div class="cryptopro-auth-status"></div>
            
            <?php if ($atts['show_debug'] === 'true'): ?>
            <div class="cryptopro-debug-container" style="margin-top: 15px;"></div>
            <?php endif; ?>
            
            <?php if ($atts['show_guide'] === 'true'): ?>
            <div class="cryptopro-auth-guide" style="display: none;">
                <h4><?php esc_html_e('Р”Р»СЏ СЂР°Р±РѕС‚С‹ РЅРµРѕР±С…РѕРґРёРјС‹:', 'wp-cryptopro-auth'); ?></h4>
                <ol>
                    <li><?php esc_html_e('РЈСЃС‚Р°РЅРѕРІРёС‚СЊ РљСЂРёРїС‚РѕРџСЂРѕ CSP', 'wp-cryptopro-auth'); ?></li>
                    <li><?php esc_html_e('РЈСЃС‚Р°РЅРѕРІРёС‚СЊ РљСЂРёРїС‚РѕРџСЂРѕ Browser Plugin', 'wp-cryptopro-auth'); ?></li>
                    <li><?php esc_html_e('РќР°СЃС‚СЂРѕРёС‚СЊ СЃРµСЂС‚РёС„РёРєР°С‚ Р­Р¦Рџ', 'wp-cryptopro-auth'); ?></li>
                </ol>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * РЁРѕСЂС‚РєРѕРґ РґР»СЏ Р±Р»РѕРєР° Р°РІС‚РѕСЂРёР·Р°С†РёРё [cryptopro_auth].
     * 
     * @param mixed $atts РђС‚СЂРёР±СѓС‚С‹ С€РѕСЂС‚РєРѕРґР°.
     * @return string HTML РєРѕРґ.
     * @author @ddnitecry
     */
    public function auth_shortcode(mixed $atts): string {
        // Р•СЃР»Рё РїРѕР»СЊР·РѕРІР°С‚РµР»СЊ СѓР¶Рµ Р·Р°Р»РѕРіРёРЅРµРЅ, РїСЂРѕСЃС‚Рѕ РІС‹Р·С‹РІР°РµРј login_shortcode, 
        // РєРѕС‚РѕСЂС‹Р№ РІРµСЂРЅРµС‚ СЃРѕРѕР±С‰РµРЅРёРµ Рѕ С‚РѕРј, С‡С‚Рѕ РїРѕР»СЊР·РѕРІР°С‚РµР»СЊ Р·Р°Р»РѕРіРёРЅРµРЅ.
        // Р­С‚Рѕ РїСЂРµРґРѕС‚РІСЂР°С‚РёС‚ РїРѕРєР°Р· Р·Р°РіРѕР»РѕРІРєР° Рё РѕРїРёСЃР°РЅРёСЏ С„РѕСЂРјС‹ РІС…РѕРґР°.
        if (is_user_logged_in()) {
            return $this->login_shortcode($atts);
        }

        $atts = shortcode_atts(array(
            'title' => __('РђРІС‚РѕСЂРёР·Р°С†РёСЏ С‡РµСЂРµР· Р­Р¦Рџ', 'wp-cryptopro-auth'),
            'description' => __('РСЃРїРѕР»СЊР·СѓР№С‚Рµ СЌР»РµРєС‚СЂРѕРЅРЅСѓСЋ РїРѕРґРїРёСЃСЊ РґР»СЏ РІС…РѕРґР° РІ СЃРёСЃС‚РµРјСѓ', 'wp-cryptopro-auth'),
            'show_debug' => $this->is_debug_enabled() ? 'true' : 'false',
            'redirect' => '',
            'class' => 'cryptopro-auth-block'
        ), $atts);
        
        ob_start();
        ?>
        <div class="cryptopro-auth-block <?php echo esc_attr($atts['class']); ?>">
            <?php if (!empty($atts['title'])): ?>
                <h3><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <?php if (!empty($atts['description'])): ?>
                <p><?php echo esc_html($atts['description']); ?></p>
            <?php endif; ?>
            
            <?php 
            echo wp_kses_post($this->login_shortcode(array(
                'show_debug' => $atts['show_debug']
            ))); 
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * РЁРѕСЂС‚РєРѕРґ РґР»СЏ РґРёР°РіРЅРѕСЃС‚РёРєРё [cryptopro_debug].
     * 
     * @param mixed $atts РђС‚СЂРёР±СѓС‚С‹ С€РѕСЂС‚РєРѕРґР°.
     * @return string HTML РєРѕРґ.
     * @author @ddnitecry
     */
    public function debug_shortcode(mixed $atts): string {
        if (!current_user_can('manage_options')) {
            return __('РўРѕР»СЊРєРѕ Р°РґРјРёРЅРёСЃС‚СЂР°С‚РѕСЂС‹ РјРѕРіСѓС‚ РёСЃРїРѕР»СЊР·РѕРІР°С‚СЊ СЌС‚РѕС‚ С€РѕСЂС‚РєРѕРґ', 'wp-cryptopro-auth');
        }
        
        $atts = shortcode_atts(array(
            'title' => __('Р”РёР°РіРЅРѕСЃС‚РёРєР° CryptoPro Auth', 'wp-cryptopro-auth'),
            'class' => 'cryptopro-debug-info'
        ), $atts);
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($atts['class']); ?>">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <div class="debug-info">
                <h4><?php esc_html_e('РРЅС„РѕСЂРјР°С†РёСЏ Рѕ СЃРёСЃС‚РµРјРµ:', 'wp-cryptopro-auth'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Р’РµСЂСЃРёСЏ PHP:', 'wp-cryptopro-auth'); ?> <?php echo esc_html(phpversion()); ?></li>
                    <li><?php esc_html_e('Р’РµСЂСЃРёСЏ WordPress:', 'wp-cryptopro-auth'); ?> <?php echo esc_html(get_bloginfo('version')); ?></li>
                    <li><?php esc_html_e('Режим отладки:', 'wp-cryptopro-auth'); ?> <?php echo $this->is_debug_enabled() ? esc_html(__('Включен', 'wp-cryptopro-auth')) : esc_html(__('Выключен', 'wp-cryptopro-auth')); ?></li>
                    <li><?php esc_html_e('Р’РµСЂСЃРёСЏ РїР»Р°РіРёРЅР°:', 'wp-cryptopro-auth'); ?> <?php echo defined('CRYPTOPRO_AUTH_VERSION') ? esc_html(CRYPTOPRO_AUTH_VERSION) : 'Unknown'; ?></li>
                </ul>
                
                <h4><?php esc_html_e('РџСЂРѕРІРµСЂРєР° РїР»Р°РіРёРЅР°:', 'wp-cryptopro-auth'); ?></h4>
                <button id="test-plugin-detection" class="button button-primary"><?php esc_html_e('Р—Р°РїСѓСЃС‚РёС‚СЊ С‚РµСЃС‚ РѕР±РЅР°СЂСѓР¶РµРЅРёСЏ', 'wp-cryptopro-auth'); ?></button>
                <div id="test-results" style="margin-top: 15px;"></div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-plugin-detection').on('click', function() {
                const results = $('#test-results');
                results.html('<p><?php esc_html_e('РџСЂРѕРІРµСЂРєР° РѕР±СЉРµРєС‚РѕРІ...', 'wp-cryptopro-auth'); ?></p>');
                
                const objectsToCheck = [
                    'CryptoPro', 'cryptoPro', 'cadesplugin', 'CAdESCOM',
                    'kontur', 'Kontur', 'crypto', 'Crypto'
                ];
                
                let foundObjects = [];
                
                objectsToCheck.forEach(objName => {
                    if (window[objName]) {
                        foundObjects.push('<li><strong>' + objName + ':</strong> <?php esc_html_e('РЅР°Р№РґРµРЅ', 'wp-cryptopro-auth'); ?></li>');
                    } else {
                        foundObjects.push('<li><strong>' + objName + ':</strong> <?php esc_html_e('РЅРµ РЅР°Р№РґРµРЅ', 'wp-cryptopro-auth'); ?></li>');
                    }
                });
                
                results.html('<ul>' + foundObjects.join('') + '</ul>');
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * РџСЂРѕРІРµСЂРєР° СЂРµР¶РёРјР° РѕС‚Р»Р°РґРєРё.
     * 
     * @return bool
     * @author @ddnitecry
     */
    private function is_debug_enabled(): bool {
        $settings = get_option('cryptopro_auth_settings', array());
        return !empty($settings['debug_mode']);
    }
}
