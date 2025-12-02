<?php
if (!defined('ABSPATH')) {
    exit;
}

class CriptaPro_Shortcodes {
    
    /**
     * Конструктор класса.
     * 
     * @author @ddnitecry
     */
    public function __construct() {
        add_shortcode('criptapro_login', array($this, 'login_shortcode'));
        add_shortcode('criptapro_auth', array($this, 'auth_shortcode'));
        add_shortcode('criptapro_debug', array($this, 'debug_shortcode'));
    }
    
    /**
     * Шорткод для кнопки входа [criptapro_login].
     * 
     * @param mixed $atts Атрибуты шорткода.
     * @return string HTML код.
     * @author @ddnitecry
     */
    public function login_shortcode(mixed $atts): string {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            return sprintf(
                '<div class="criptapro-logged-in-message">%s, %s</div>',
                esc_html($current_user->display_name),
                __('вы уже залогинены', 'criptapro-auth')
            );
        }

        $atts = shortcode_atts(array(
            'button_text' => __('Войти с помощью КриптоПро', 'criptapro-auth'),
            'show_guide' => 'true',
            'show_debug' => $this->is_debug_enabled() ? 'true' : 'false',
            'class' => 'criptapro-login-form'
        ), $atts);
        
        ob_start();
        ?>
        <div class="criptapro-auth-container <?php echo esc_attr($atts['class']); ?>">
            <button class="criptapro-auth-btn" type="button">
                <?php echo esc_html($atts['button_text']); ?>
            </button>
            <div class="criptapro-auth-status"></div>
            
            <?php if ($atts['show_debug'] === 'true'): ?>
            <div class="criptapro-debug-container" style="margin-top: 15px;"></div>
            <?php endif; ?>
            
            <?php if ($atts['show_guide'] === 'true'): ?>
            <div class="criptapro-auth-guide" style="display: none;">
                <h4><?php esc_html_e('Для работы необходимы:', 'criptapro-auth'); ?></h4>
                <ol>
                    <li><?php esc_html_e('Установить КриптоПро CSP', 'criptapro-auth'); ?></li>
                    <li><?php esc_html_e('Установить КриптоПро Browser Plugin', 'criptapro-auth'); ?></li>
                    <li><?php esc_html_e('Настроить сертификат ЭЦП', 'criptapro-auth'); ?></li>
                </ol>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Шорткод для блока авторизации [criptapro_auth].
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
            'title' => __('Авторизация через ЭЦП', 'criptapro-auth'),
            'description' => __('Используйте электронную подпись для входа в систему', 'criptapro-auth'),
            'show_debug' => $this->is_debug_enabled() ? 'true' : 'false',
            'redirect' => '',
            'class' => 'criptapro-auth-block'
        ), $atts);
        
        ob_start();
        ?>
        <div class="criptapro-auth-block <?php echo esc_attr($atts['class']); ?>">
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
     * Шорткод для диагностики [criptapro_debug].
     * 
     * @param mixed $atts Атрибуты шорткода.
     * @return string HTML код.
     * @author @ddnitecry
     */
    public function debug_shortcode(mixed $atts): string {
        if (!current_user_can('manage_options')) {
            return __('Только администраторы могут использовать этот шорткод', 'criptapro-auth');
        }
        
        $atts = shortcode_atts(array(
            'title' => __('Диагностика CriptaPro Auth', 'criptapro-auth'),
            'class' => 'criptapro-debug-info'
        ), $atts);
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($atts['class']); ?>">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <div class="debug-info">
                <h4><?php esc_html_e('Информация о системе:', 'criptapro-auth'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Версия PHP:', 'criptapro-auth'); ?> <?php echo esc_html(phpversion()); ?></li>
                    <li><?php esc_html_e('Версия WordPress:', 'criptapro-auth'); ?> <?php echo esc_html(get_bloginfo('version')); ?></li>
                    <li><?php esc_html_e('Режим отладки:', 'criptapro-auth'); ?> <?php echo $this->is_debug_enabled() ? esc_html(__('Включен', 'criptapro-auth')) : esc_html(__('Выключен', 'criptapro-auth')); ?></li>
                    <li><?php esc_html_e('Версия плагина:', 'criptapro-auth'); ?> <?php echo defined('CRIPTAPRO_AUTH_VERSION') ? esc_html(CRIPTAPRO_AUTH_VERSION) : 'Unknown'; ?></li>
                </ul>
                
                <h4><?php esc_html_e('Проверка плагина:', 'criptapro-auth'); ?></h4>
                <button id="test-plugin-detection" class="button button-primary"><?php esc_html_e('Запустить тест обнаружения', 'criptapro-auth'); ?></button>
                <div id="test-results" style="margin-top: 15px;"></div>
            </div>
        </div>
        
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
        $settings = get_option('criptapro_auth_settings', array());
        return !empty($settings['debug_mode']);
    }
}
