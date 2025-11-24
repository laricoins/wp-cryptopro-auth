<?php
if (!defined('ABSPATH')) {
    exit;
}

class CryptoPro_Shortcodes {
    
    /**
     * Конструктор класса.
     * 
     * @author @ddnitecry
     */
    public function __construct() {
        add_shortcode('cryptopro_login', array($this, 'login_shortcode'));
        add_shortcode('cryptopro_auth', array($this, 'auth_shortcode'));
        add_shortcode('cryptopro_debug', array($this, 'debug_shortcode'));
    }
    
    /**
     * Шорткод для кнопки входа [cryptopro_login].
     * 
     * @param mixed $atts Атрибуты шорткода.
     * @return string HTML код.
     * @author @ddnitecry
     */
    public function login_shortcode(mixed $atts): string {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            return sprintf(
                '<div class="cryptopro-logged-in-message">%s, %s</div>',
                esc_html($current_user->display_name),
                __('вы уже залогинены', 'cryptopro-auth')
            );
        }

        $atts = shortcode_atts(array(
            'button_text' => __('Войти с помощью КриптоПро', 'cryptopro-auth'),
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
                <h4><?php esc_html_e('Для работы необходимы:', 'cryptopro-auth'); ?></h4>
                <ol>
                    <li><?php esc_html_e('Установить КриптоПро CSP', 'cryptopro-auth'); ?></li>
                    <li><?php esc_html_e('Установить КриптоПро Browser Plugin', 'cryptopro-auth'); ?></li>
                    <li><?php esc_html_e('Настроить сертификат ЭЦП', 'cryptopro-auth'); ?></li>
                </ol>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Шорткод для блока авторизации [cryptopro_auth].
     * 
     * @param mixed $atts Атрибуты шорткода.
     * @return string HTML код.
     * @author @ddnitecry
     */
    public function auth_shortcode(mixed $atts): string {
        // Если пользователь уже залогинен, просто вызываем login_shortcode, 
        // который вернет сообщение о том, что пользователь залогинен.
        // Это предотвратит показ заголовка и описания формы входа.
        if (is_user_logged_in()) {
            return $this->login_shortcode($atts);
        }

        $atts = shortcode_atts(array(
            'title' => __('Авторизация через ЭЦП', 'cryptopro-auth'),
            'description' => __('Используйте электронную подпись для входа в систему', 'cryptopro-auth'),
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
     * Шорткод для диагностики [cryptopro_debug].
     * 
     * @param mixed $atts Атрибуты шорткода.
     * @return string HTML код.
     * @author @ddnitecry
     */
    public function debug_shortcode(mixed $atts): string {
        if (!current_user_can('manage_options')) {
            return __('Только администраторы могут использовать этот шорткод', 'cryptopro-auth');
        }
        
        $atts = shortcode_atts(array(
            'title' => __('Диагностика CryptoPro Auth', 'cryptopro-auth'),
            'class' => 'cryptopro-debug-info'
        ), $atts);
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($atts['class']); ?>">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <div class="debug-info">
                <h4><?php esc_html_e('Информация о системе:', 'cryptopro-auth'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Версия PHP:', 'cryptopro-auth'); ?> <?php echo esc_html(phpversion()); ?></li>
                    <li><?php esc_html_e('Версия WordPress:', 'cryptopro-auth'); ?> <?php echo esc_html(get_bloginfo('version')); ?></li>
                    <li><?php esc_html_e('Режим отладки:', 'cryptopro-auth'); ?> <?php echo $this->is_debug_enabled() ? esc_html(__('Включен', 'cryptopro-auth')) : esc_html(__('Выключен', 'cryptopro-auth')); ?></li>
                    <li><?php esc_html_e('Версия плагина:', 'cryptopro-auth'); ?> <?php echo defined('CRYPTOPRO_AUTH_VERSION') ? esc_html(CRYPTOPRO_AUTH_VERSION) : 'Unknown'; ?></li>
                </ul>
                
                <h4><?php esc_html_e('Проверка плагина:', 'cryptopro-auth'); ?></h4>
                <button id="test-plugin-detection" class="button button-primary"><?php esc_html_e('Запустить тест обнаружения', 'cryptopro-auth'); ?></button>
                <div id="test-results" style="margin-top: 15px;"></div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-plugin-detection').on('click', function() {
                const results = $('#test-results');
                results.html('<p><?php esc_html_e('Проверка объектов...', 'cryptopro-auth'); ?></p>');
                
                const objectsToCheck = [
                    'CryptoPro', 'cryptoPro', 'cadesplugin', 'CAdESCOM',
                    'kontur', 'Kontur', 'crypto', 'Crypto'
                ];
                
                let foundObjects = [];
                
                objectsToCheck.forEach(objName => {
                    if (window[objName]) {
                        foundObjects.push('<li><strong>' + objName + ':</strong> <?php esc_html_e('найден', 'cryptopro-auth'); ?></li>');
                    } else {
                        foundObjects.push('<li><strong>' + objName + ':</strong> <?php esc_html_e('не найден', 'cryptopro-auth'); ?></li>');
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
     * Проверка режима отладки.
     * 
     * @return bool
     * @author @ddnitecry
     */
    private function is_debug_enabled(): bool {
        $settings = get_option('cryptopro_auth_settings', array());
        return !empty($settings['debug_mode']);
    }
}
