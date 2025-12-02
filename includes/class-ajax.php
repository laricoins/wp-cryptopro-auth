<?php
if (!defined('ABSPATH')) {
    exit;
}

class CriptaPro_Ajax {

    /**
     * Конструктор класса.
     * 
     * @author @ddnitecry
     */
    public function __construct() {
        add_action('wp_ajax_criptapro_auth', array($this, 'handle_auth_request'));
        add_action('wp_ajax_nopriv_criptapro_auth', array($this, 'handle_auth_request'));
        add_action('wp_ajax_criptapro_check_cpstore', array($this, 'check_cpstore'));
    }

    /**
     * Обработка AJAX запроса авторизации.
     * 
     * @return void
     * @author @ddnitecry
     */
    public function handle_auth_request(): void {
        // CORS headers
        $settings = get_option('criptapro_auth_settings', array());
        if (!empty($settings['enable_cors'])) {
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_ORIGIN'])) : '';
            $allowed_origins_text = $settings['allowed_origins'] ?? home_url();
            $allowed_origins = array_filter(array_map('trim', explode("\n", $allowed_origins_text)));
            
            if (in_array($origin, $allowed_origins)) {
                header("Access-Control-Allow-Origin: $origin");
                header("Access-Control-Allow-Methods: POST, OPTIONS");
                header("Access-Control-Allow-Credentials: true");
                header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
            }
            
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                status_header(200);
                exit;
            }
        }

        check_ajax_referer('criptapro_auth_nonce', 'nonce');
        
        $signed_data = isset($_POST['signed_data']) ? sanitize_text_field(wp_unslash($_POST['signed_data'])) : '';
        $certificate = isset($_POST['certificate']) ? sanitize_text_field(wp_unslash($_POST['certificate'])) : '';
        $signature = isset($_POST['signature']) ? sanitize_text_field(wp_unslash($_POST['signature'])) : '';
        
        if (empty($signed_data) || empty($certificate)) {
            wp_send_json_error(__('Неверные данные для авторизации', 'criptapro-auth'));
        }
        
        $auth_handler = new CriptaPro_Auth_Handler();
        $result = $auth_handler->authenticate($signed_data, $certificate, $signature);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Проверка доступности CPStore (диагностика).
     * 
     * @return void
     * @author @ddnitecry
     */
    public function check_cpstore(): void {
        check_ajax_referer('criptapro_auth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Недостаточно прав', 'criptapro-auth'));
        }
        
        $result = array(
            'available' => false,
            'message' => '',
            'details' => array(),
            'php_extension' => false
        );
        
        try {
            // Проверка доступности PHP расширения КриптоПро
            $php_extension_available = false;
            $available_classes = array();
            
            if (class_exists('CPStore')) {
                $php_extension_available = true;
                $available_classes[] = 'CPStore';
            }
            
            if (class_exists('CPSignedData')) {
                $php_extension_available = true;
                $available_classes[] = 'CPSignedData';
            }
            
            if (class_exists('CPSigner')) {
                $php_extension_available = true;
                $available_classes[] = 'CPSigner';
            }
            
            $result['php_extension'] = $php_extension_available;
            
            if ($php_extension_available) {
                $result['available'] = true;
                $result['message'] = __('PHP расширение КриптоПро доступно', 'criptapro-auth');
                $result['details']['classes'] = implode(', ', $available_classes);
                
                // Попытка создать экземпляр CPStore
                if (class_exists('CPStore')) {
                    try {
                        $store = new CPStore();
                        $result['details']['instance'] = __('Экземпляр CPStore успешно создан', 'criptapro-auth');
                        $result['details']['verification'] = __('Проверка подписи будет выполняться через PHP расширение', 'criptapro-auth');
                    } catch (Exception $e) {
                        $result['details']['instance_error'] = $e->getMessage();
                    }
                }
            } else {
                $result['message'] = __('PHP расширение КриптоПро не найдено. Будет использована упрощенная проверка подписи.', 'criptapro-auth');
                $result['details']['note'] = __('Для полной проверки подписи установите PHP расширение КриптоПро. Подробнее: https://cryptopro.ru', 'criptapro-auth');
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }
        
        wp_send_json_success($result);
    }
}
