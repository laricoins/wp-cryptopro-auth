<?php
/**
 * Plugin Name:       CryptoPro Auth
 * Plugin URI:        https://github.com/yourusername/cryptopro-auth
 * Description:       Авторизация в WordPress с помощью ЭЦП КриптоПро
 * Version:           1.0.0
 * Author:            WebPavlo
 * Author URI:        https://github.com/yourusername
 * License:           GPL v2 or later
 * Text Domain:       cryptopro-auth
 * Domain Path:       /languages
 * Requires PHP:      8.2
 * Requires at least: 5.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('ABSPATH')) {
    exit;
}

define('CRYPTOPRO_AUTH_VERSION', '1.0.0');
define('CRYPTOPRO_AUTH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CRYPTOPRO_AUTH_PLUGIN_PATH', plugin_dir_path(__FILE__));

class CryptoProAuthPlugin {
    
    /**
     * @var CryptoProAuthPlugin|null $instance Singleton instance
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
        require_once CRYPTOPRO_AUTH_PLUGIN_PATH . 'includes/class-auth-handler.php';
        require_once CRYPTOPRO_AUTH_PLUGIN_PATH . 'includes/class-certificate-validator.php';
        require_once CRYPTOPRO_AUTH_PLUGIN_PATH . 'includes/class-api-integration.php';
        require_once CRYPTOPRO_AUTH_PLUGIN_PATH . 'includes/class-shortcodes.php';
        require_once CRYPTOPRO_AUTH_PLUGIN_PATH . 'includes/class-admin.php';
        require_once CRYPTOPRO_AUTH_PLUGIN_PATH . 'includes/class-ajax.php';
        
        // Инициализация компонентов
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Регистрация шорткодов
        new CryptoPro_Shortcodes();
        
        // Инициализация обработчика авторизации
        new CryptoPro_Auth_Handler();
        
        // Инициализация админки
        if (is_admin()) {
            new CryptoPro_Admin();
        }
        
        // Инициализация AJAX
        if (wp_doing_ajax()) {
            new CryptoPro_Ajax();
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
        $version = $this->is_debug_enabled() ? wp_rand(1000, 9999) . '.' . time() : CRYPTOPRO_AUTH_VERSION;
    
        // ПОДКЛЮЧАЕМ CADESPLUGIN_API.JS ПЕРВЫМ (важно для зависимостей)
        wp_enqueue_script(
            'cadesplugin-api',
            CRYPTOPRO_AUTH_PLUGIN_URL . 'assets/js/cadesplugin_api.js',
            array(), // без зависимостей, это базовый скрипт
            $version,
            true
        );
        
        // ПОДКЛЮЧАЕМ ОСНОВНОЙ СКРИПТ (зависит от cadesplugin-api)
        wp_enqueue_script(
            'cryptopro-auth',
            CRYPTOPRO_AUTH_PLUGIN_URL . 'assets/js/cryptopro-plugin.js',
            array('jquery', 'cadesplugin-api'), // jQuery и cadesplugin как зависимости
            $version,
            true
        );
        
        wp_enqueue_style(
            'cryptopro-auth-style',
            CRYPTOPRO_AUTH_PLUGIN_URL . 'assets/css/style.css',
            array(),
            $version
        );
        
        // Локализация скрипта
        wp_localize_script('cryptopro-auth', 'cryptopro_auth', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cryptopro_auth_nonce'),
            'auth_url' => wp_login_url(),
            'debug_mode' => $this->is_debug_enabled(),
            'test_mode' => $this->is_test_mode(),
            'debug_container' => $this->get_debug_container(),
            'plugin_version' => $version,
            'strings' => array(
                'plugin_not_found' => __('Плагин КриптоПро не найден', 'cryptopro-auth'),
                'no_certificates' => __('Сертификаты не найдены', 'cryptopro-auth'),
                'auth_success' => __('Авторизация успешна', 'cryptopro-auth'),
                'auth_error' => __('Ошибка авторизации', 'cryptopro-auth'),
                'select_certificate' => __('Выберите сертификат:', 'cryptopro-auth'),
                'certificate_selected' => __('Сертификат выбран', 'cryptopro-auth'),
                'auth_process' => __('Начало процесса авторизации...', 'cryptopro-auth'),
                'login_with_crypto' => __('Войти с помощью КриптоПро', 'cryptopro-auth'),
                'network_error' => __('Ошибка сети', 'cryptopro-auth')
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
        include CRYPTOPRO_AUTH_PLUGIN_PATH . 'templates/login-form.php';
    }
    
    /**
     * Проверка включенного режима отладки.
     * 
     * @return bool
     * @author @ddnitecry
     */
    public function is_debug_enabled(): bool {
        $settings = get_option('cryptopro_auth_settings', array());
        return !empty($settings['debug_mode']);
    }
    
    /**
     * Проверка включенного тестового режима.
     * 
     * @return bool
     * @author @ddnitecry
     */
    public function is_test_mode(): bool {
        $settings = get_option('cryptopro_auth_settings', array());
        return !empty($settings['test_mode']);
    }
    
    /**
     * Получение селектора контейнера для отладки.
     * 
     * @return string
     * @author @ddnitecry
     */
    public function get_debug_container(): string {
        $settings = get_option('cryptopro_auth_settings', array());
        return $settings['debug_container'] ?? '.cryptopro-debug-container';
    }
}

// Инициализация плагина
CryptoProAuthPlugin::getInstance();

// Код активации плагина
register_activation_hook(__FILE__, 'cryptopro_auth_activate');
function cryptopro_auth_activate() {
    add_option('cryptopro_auth_settings', array(
        'api_endpoint' => '',
        'allowed_organizations' => array(),
        'certificate_lifetime' => 365,
        'auto_create_users' => true,
        'default_user_role' => 'subscriber',
        'debug_mode' => false,
        'test_mode' => false,
        'redirect_url' => '',
        'debug_container' => '.cryptopro-debug-container'
    ));
    
    // Создание лог-файла
    $upload_dir = wp_upload_dir();
    $log_dir = $upload_dir['basedir'] . '/cryptopro-auth-logs';
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }
}

// Код деактивации плагина
register_deactivation_hook(__FILE__, 'cryptopro_auth_deactivate');
function cryptopro_auth_deactivate() {
    // Очистка временных данных
}