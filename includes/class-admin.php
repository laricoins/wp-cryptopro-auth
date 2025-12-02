<?php
if (!defined('ABSPATH')) {
    exit;
}

class CriptaPro_Admin {

    /**
     * Конструктор класса.
     * 
     * @author @ddnitecry
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Добавляем ссылку "Настройки" в список плагинов
        $plugin_basename = plugin_basename(CRIPTAPRO_AUTH_PLUGIN_PATH . 'cryptopro-auth.php');
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'add_action_links'));
    }

    /**
     * Добавляет ссылку "Настройки" на странице плагинов.
     * 
     * @param array $links Массив ссылок действий плагина.
     * @return array Обновленный массив ссылок.
     * @author @ddnitecry
     */
    public function add_action_links(array $links): array {
        $settings_link = '<a href="options-general.php?page=criptapro-auth-settings">' . __('Настройки', 'criptapro-auth') . '</a>';
        // Добавляем ссылку в конец массива (после Deactivate)
        $links['settings'] = $settings_link;
        return $links;
    }

    /**
     * Регистрирует страницу настроек в меню админки.
     * 
     * @return void
     * @author @ddnitecry
     */
    public function add_admin_menu(): void {
        add_options_page(
            __('CriptaPro Auth Settings', 'criptapro-auth'),
            __('CriptaPro Auth', 'criptapro-auth'),
            'manage_options',
            'criptapro-auth-settings',
            array($this, 'admin_settings_page')
        );
    }

    /**
     * Отображает страницу настроек.
     * 
     * @return void
     * @author @ddnitecry
     */
    public function admin_settings_page(): void {
        include CRIPTAPRO_AUTH_PLUGIN_PATH . 'templates/admin-settings.php';
    }

    /**
     * Регистрирует настройки плагина.
     * 
     * @return void
     * @author @ddnitecry
     */
    public function register_settings(): void {
        register_setting('criptapro_auth_settings', 'criptapro_auth_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));
    }

    /**
     * Очищает и валидирует настройки перед сохранением.
     * 
     * @param array $input Входные данные настроек.
     * @return array Очищенные данные.
     * @author @ddnitecry
     */
    public function sanitize_settings(array $input): array {
        $sanitized = array();
        
        if (isset($input['api_endpoint'])) {
            $sanitized['api_endpoint'] = esc_url_raw($input['api_endpoint']);
        }
        
        if (isset($input['redirect_url'])) {
            $sanitized['redirect_url'] = esc_url_raw($input['redirect_url']);
        }
        
        $sanitized['auto_create_users'] = isset($input['auto_create_users']) ? 1 : 0;
        $sanitized['debug_mode'] = isset($input['debug_mode']) ? 1 : 0;
        $sanitized['test_mode'] = isset($input['test_mode']) ? 1 : 0;
        
        if (isset($input['default_user_role'])) {
            $sanitized['default_user_role'] = sanitize_text_field($input['default_user_role']);
        }
        
        // Настройки безопасности
        if (isset($input['trusted_issuers'])) {
            $sanitized['trusted_issuers'] = sanitize_textarea_field($input['trusted_issuers']);
        }
        
        $sanitized['enable_logging'] = isset($input['enable_logging']) ? 1 : 0;
        $sanitized['require_https'] = isset($input['require_https']) ? 1 : 0;
        $sanitized['enable_cors'] = isset($input['enable_cors']) ? 1 : 0;
        
        if (isset($input['allowed_origins'])) {
            // Разделяем по строкам, очищаем и собираем обратно
            $origins = explode("\n", $input['allowed_origins']);
            $clean_origins = array();
            foreach ($origins as $origin) {
                $clean_origin = esc_url_raw(trim($origin));
                if (!empty($clean_origin)) {
                    $clean_origins[] = $clean_origin;
                }
            }
            $sanitized['allowed_origins'] = implode("\n", $clean_origins);
        }
        
        return $sanitized;
    }

    /**
     * Подключает скрипты и стили для админки.
     * 
     * @param string $hook Текущая страница админки.
     * @return void
     * @author @ddnitecry
     */
    public function enqueue_admin_scripts(string $hook): void {
        if ($hook === 'settings_page_criptapro-auth-settings') {
            $random_version = wp_rand(1000, 9999) . '.' . time();
            
            wp_enqueue_script(
                'criptapro-auth-admin',
                CRIPTAPRO_AUTH_PLUGIN_URL . 'assets/js/criptapro-admin.js',
                array('jquery'),
                $random_version,
                true
            );
            
            wp_enqueue_style(
                'criptapro-auth-admin-style',
                CRIPTAPRO_AUTH_PLUGIN_URL . 'assets/css/criptapro-admin.css',
                array(),
                $random_version
            );
            
            wp_localize_script('criptapro-auth-admin', 'criptapro_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('criptapro_auth_nonce'),
                'strings' => array(
                    'checking' => __('Проверка...', 'criptapro-auth'),
                    'check_success' => __('Проверка успешна', 'criptapro-auth'),
                    'check_error' => __('Ошибка проверки', 'criptapro-auth')
                )
            ));
        }
    }
}
