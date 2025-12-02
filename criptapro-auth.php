<?php
/**
 * Plugin Name:       CriptaPro Auth
 * Plugin URI:        https://github.com/laricoins/wp-cryptopro-auth
 * Description:       WordPress authentication using CryptoPro digital signature
 * Version:           1.0.5
 * Author:            vit_sh
 * Author URI:        https://github.com/laricoins
 * License:           GPL v2 or later
 * Text Domain:       criptapro-auth
 * Domain Path:       /languages
 * Requires PHP:      8.2
 * Requires at least: 5.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('ABSPATH')) {
    exit;
}

define('CRIPTAPRO_AUTH_VERSION', '1.0.5');
define('CRIPTAPRO_AUTH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CRIPTAPRO_AUTH_PLUGIN_PATH', plugin_dir_path(__FILE__));

class CriptaProAuthPlugin {
    
    /**
     * @var CriptaProAuthPlugin|null $instance Singleton instance
     */
    private static ?self $instance = null;
    
    /**
     * Получение экземпляра класса (Singleton).
     * 
     * @return self
     * @author @ddnitecry
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Конструктор класса.
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Инициализация плагина.
     * 
     * @return void
     * @author @ddnitecry
     */
    private function init(): void {
        // Подключение зависимостей
        require_once CRIPTAPRO_AUTH_PLUGIN_PATH . 'includes/class-auth-handler.php';
        require_once CRIPTAPRO_AUTH_PLUGIN_PATH . 'includes/class-certificate-validator.php';
        require_once CRIPTAPRO_AUTH_PLUGIN_PATH . 'includes/class-api-integration.php';
        require_once CRIPTAPRO_AUTH_PLUGIN_PATH . 'includes/class-shortcodes.php';
        require_once CRIPTAPRO_AUTH_PLUGIN_PATH . 'includes/class-admin.php';
        require_once CRIPTAPRO_AUTH_PLUGIN_PATH . 'includes/class-ajax.php';
        
        // Инициализация компонентов
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Регистрация шорткодов
        new CriptaPro_Shortcodes();
        
        // Инициализация обработчика авторизации
        new CriptaPro_Auth_Handler();
        
        // Инициализация админки
        if (is_admin()) {
            new CriptaPro_Admin();
        }
        
        // Инициализация AJAX
        if (wp_doing_ajax()) {
            new CriptaPro_Ajax();
        }
    }
    

    /**
     * Подключение скриптов и стилей.
     * 
     * @return void
     * @author @ddnitecry
     */
    public function enqueue_scripts(): void {
        // В режиме отладки используем случайную версию для сброса кеша
        // В продакшене используем версию плагина
        $version = $this->is_debug_enabled() ? wp_rand(1000, 9999) . '.' . time() : CRIPTAPRO_AUTH_VERSION;
    
        // ПОДКЛЮЧАЕМ CADESPLUGIN_API.JS ПЕРВЫМ (важно для зависимостей)
        wp_enqueue_script(
            'cadesplugin-api',
            CRIPTAPRO_AUTH_PLUGIN_URL . 'assets/js/cadesplugin_api.js',
            array(), // без зависимостей, это базовый скрипт
            $version,
            true
        );
        
        // ПОДКЛЮЧАЕМ ОСНОВНОЙ СКРИПТ (зависит от cadesplugin-api)
        wp_enqueue_script(
            'criptapro-auth',
            CRIPTAPRO_AUTH_PLUGIN_URL . 'assets/js/criptapro-plugin.js',
            array('jquery', 'cadesplugin-api'), // jQuery и cadesplugin как зависимости
            $version,
            true
        );
        
        wp_enqueue_style(
            'criptapro-auth-style',
            CRIPTAPRO_AUTH_PLUGIN_URL . 'assets/css/criptapro-style.css',
            array(),
            $version
        );
        
        // Локализация скрипта
        wp_localize_script('criptapro-auth', 'criptapro_auth', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('criptapro_auth_nonce'),
            'auth_url' => wp_login_url(),
            'debug_mode' => $this->is_debug_enabled(),
            'test_mode' => $this->is_test_mode(),
            'debug_container' => $this->get_debug_container(),
            'plugin_version' => $version,
            'strings' => array(
                'plugin_not_found' => __('Плагин КриптоПро не найден', 'criptapro-auth'),
                'no_certificates' => __('Сертификаты не найдены', 'criptapro-auth'),
                'auth_success' => __('Авторизация успешна', 'criptapro-auth'),
                'auth_error' => __('Ошибка авторизации', 'criptapro-auth'),
                'select_certificate' => __('Выберите сертификат:', 'criptapro-auth'),
                'certificate_selected' => __('Сертификат выбран', 'criptapro-auth'),
                'auth_process' => __('Начало процесса авторизации...', 'criptapro-auth'),
                'login_with_crypto' => __('Войти с помощью КриптоПро', 'criptapro-auth'),
                'network_error' => __('Ошибка сети', 'criptapro-auth')
            )
        ));
    }
    
    /**
     * Добавление кнопки входа (устарело, используется шорткод).
     * 
     * @return void
     * @author @ddnitecry
     */
    public function add_cryptopro_login_button(): void {
        include CRIPTAPRO_AUTH_PLUGIN_PATH . 'templates/login-form.php';
    }
    
    /**
     * Проверка включенного режима отладки.
     * 
     * @return bool
     * @author @ddnitecry
     */
    public function is_debug_enabled(): bool {
        $settings = get_option('criptapro_auth_settings', array());
        return !empty($settings['debug_mode']);
    }
    
    /**
     * Проверка включенного тестового режима.
     * 
     * @return bool
     * @author @ddnitecry
     */
    public function is_test_mode(): bool {
        $settings = get_option('criptapro_auth_settings', array());
        return !empty($settings['test_mode']);
    }
    
    /**
     * Получение селектора контейнера для отладки.
     * 
     * @return string
     * @author @ddnitecry
     */
    public function get_debug_container(): string {
        $settings = get_option('criptapro_auth_settings', array());
        return $settings['debug_container'] ?? '.criptapro-debug-container';
    }
}

// Инициализация плагина
CriptaProAuthPlugin::getInstance();

// Код активации плагина
register_activation_hook(__FILE__, 'criptapro_auth_activate');
function criptapro_auth_activate() {
    add_option('criptapro_auth_settings', array(
        'api_endpoint' => '',
        'allowed_organizations' => array(),
        'certificate_lifetime' => 365,
        'auto_create_users' => true,
        'default_user_role' => 'subscriber',
        'debug_mode' => false,
        'test_mode' => false,
        'redirect_url' => '',
        'debug_container' => '.criptapro-debug-container'
    ));
    
    // Создание лог-файла
    $upload_dir = wp_upload_dir();
    $log_dir = $upload_dir['basedir'] . '/criptapro-auth-logs';
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }
}

// Код деактивации плагина
register_deactivation_hook(__FILE__, 'criptapro_auth_deactivate');
function criptapro_auth_deactivate() {
    // Очистка временных данных
}