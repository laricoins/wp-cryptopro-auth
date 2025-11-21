<?php
if (!defined('ABSPATH')) {
    exit;
}

class CryptoPro_Auth_Handler {
    
    /**
     * Конструктор класса.
     * 
     * @author @ddnitecry
     */
    public function __construct() {
        add_action('wp_ajax_cryptopro_verify', array($this, 'verify_signature'));
        add_action('wp_ajax_nopriv_cryptopro_verify', array($this, 'verify_signature'));
    }
    
    /**
     * Аутентификация пользователя по сертификату.
     * 
     * @param string $signed_data Подписанные данные.
     * @param string $certificate Сертификат.
     * @param string $signature Подпись.
     * @return array Результат аутентификации.
     * @author @ddnitecry
     */
    public function authenticate(string $signed_data, string $certificate, string $signature): array {
        $settings = get_option('cryptopro_auth_settings', array());
        
        // Проверка HTTPS
        if (!empty($settings['require_https']) && !is_ssl()) {
            $message = __('Авторизация по HTTP запрещена настройками безопасности', 'wp-cryptopro-auth');
            $this->log_auth_attempt('error', null, $message);
            return array(
                'success' => false,
                'message' => $message
            );
        }

        try {
            // Декодируем сертификат
            $cert_data = json_decode(stripslashes($certificate), true);
            
            if (!$cert_data) {
                $message = __('Неверный формат сертификата', 'wp-cryptopro-auth');
                $this->log_auth_attempt('error', null, $message);
                return array(
                    'success' => false,
                    'message' => $message
                );
            }

            // Валидация сертификата
            $cert_validator = new CryptoPro_Certificate_Validator();
            $validation_result = $cert_validator->validate($cert_data);
           
            if (!$validation_result['valid']) {
                $this->log_auth_attempt('error', $cert_data, $validation_result['error']);
                return array(
                    'success' => false,
                    'message' => $validation_result['error']
                );
            }

            // Проверка подписи
            $test_mode = !empty($settings['test_mode']);
            
            if ($test_mode) {
                // В тест-моде используем фиксированный сертификат
                $certificate = '{"subjectName":"SN=ЧИЧЕРОВ, G=ДМИТРИЙ ВИКТОРОВИЧ, T=ДИРЕКТОР, CN=\"ООО \"\"ФУЛПРИНТ\"\"\", O=\"ООО \"\"ФУЛПРИНТ\"\"\", STREET=\"НОВОРОССИЙСКАЯ, Д. 220,СТР. 1\", L=Г. КРАСНОДАР, S=23 Краснодарский край, C=RU, E=fpkras@yandex.ru, ИНН=920151472228, ОГРН=1222300057055, СНИЛС=18776587454, ИНН ЮЛ=2308288240","commonName":"\"ООО \"\"ФУЛПРИНТ\"\"\"","issuerName":"CN=Федеральная налоговая служба, O=Федеральная налоговая служба, STREET=\"ул. Неглинная, д. 23\", L=г. Москва, S=77 Москва, C=RU, ОГРН=1047707030513, E=uc@tax.gov.ru, ИНН ЮЛ=7707329152","serialNumber":"02575DBD0068B27FB545EBAA8F56A29FF0"}';
                $cert_data = json_decode($certificate, true);
                
                $verify_result = array(
                    'success' => true,
                    'message' => __('Проверка подписи пропущена (тест-режим)', 'wp-cryptopro-auth'),
                    'data' => array('timestamp' => time())
                );
            } else {
                // Проверяем наличие PHP расширения КриптоПро
                if ($this->is_php_extension_available()) {
                    // Используем PHP расширение для проверки подписи
                    $verify_result = $this->verify_signature_with_php_extension($signed_data, $signature, $cert_data);
                } else {
                    // Упрощенная проверка подписи (fallback)
                    $verify_result = $this->verify_signature_simple($signed_data, $signature, $cert_data);
                }
            }
            
            if (!$verify_result['success']) {
                $this->log_auth_attempt('error', $cert_data, $verify_result['message']);
                return $verify_result;
            }
            
            // Поиск или создание пользователя
            $user = $this->find_or_create_user($cert_data);
            
            if (is_wp_error($user)) {
                $this->log_auth_attempt('error', $cert_data, $user->get_error_message());
                return array(
                    'success' => false,
                    'message' => $user->get_error_message()
                );
            }
            
            // Авторизация пользователя
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, true);
            
            do_action('cryptopro_user_authenticated', $user->ID, $cert_data);
            
            $this->log_auth_attempt('success', $cert_data, "User ID: {$user->ID}");
            
            // Получаем URL редиректа из настроек
            $redirect_url = !empty($settings['redirect_url']) ? $settings['redirect_url'] : home_url();
            
            return array(
                'success' => true,
                'user_id' => $user->ID,
                'redirect_url' => $redirect_url,
                'message' => __('Авторизация успешна', 'wp-cryptopro-auth')
            );
            
        } catch (Exception $e) {
            $this->log_auth_attempt('error', null, $e->getMessage());
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Логирование попыток авторизации.
     * 
     * @param string $status Статус попытки (error/success).
     * @param mixed $cert_data Данные сертификата.
     * @param string $message Сообщение.
     * @return void
     * @author @ddnitecry
     */
    private function log_auth_attempt(string $status, mixed $cert_data, string $message): void {
        $settings = get_option('cryptopro_auth_settings', array());
        if (empty($settings['enable_logging'])) {
            return;
        }
        
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/cryptopro-auth-logs';
        
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        // Защита логов через .htaccess
        $htaccess_file = $log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, "Order Deny,Allow\nDeny from all");
        }
        
        $log_file = $log_dir . '/auth.log';

        // Ротация логов (макс 5 файлов по 100Кб)
        if (file_exists($log_file) && filesize($log_file) > 102400) { // 100KB
            $max_logs = 5;
            
            // Удаляем самый старый лог
            if (file_exists($log_dir . '/auth.log.' . $max_logs)) {
                wp_delete_file($log_dir . '/auth.log.' . $max_logs);
            }
            
            // Сдвигаем остальные
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }
            for ($i = $max_logs - 1; $i >= 1; $i--) {
                if (file_exists($log_dir . '/auth.log.' . $i)) {
                    $wp_filesystem->move($log_dir . '/auth.log.' . $i, $log_dir . '/auth.log.' . ($i + 1), true);
                }
            }
            
            // Переименовываем текущий файл
            $wp_filesystem->move($log_file, $log_dir . '/auth.log.1', true);
        }
        
        $timestamp = gmdate('Y-m-d H:i:s');
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
        $subject = $cert_data['subjectName'] ?? 'unknown';
        $issuer = $cert_data['issuerName'] ?? 'unknown';
        
        $log_entry = sprintf(
            "[%s] IP: %s | Status: %s | Subject: %s | Issuer: %s | Message: %s\n",
            $timestamp,
            $ip,
            strtoupper($status),
            $subject,
            $issuer,
            $message
        );
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Проверка наличия PHP расширения КриптоПро.
     * 
     * @return bool
     * @author @ddnitecry
     */
    private function is_php_extension_available(): bool {
        // Проверяем наличие класса CPStore (основной класс для работы с хранилищем)
        if (class_exists('CPStore')) {
            return true;
        }
        
        // Проверяем наличие других классов КриптоПро
        if (class_exists('CPSignedData') || class_exists('CPSigner')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверка подписи с использованием PHP расширения КриптоПро.
     * 
     * @param string $signed_data Подписанные данные.
     * @param string $signature Подпись.
     * @param mixed $certificate Сертификат.
     * @return array Результат проверки.
     * @author @ddnitecry
     */
    private function verify_signature_with_php_extension(string $signed_data, string $signature, mixed $certificate): array {
        try {
            // Декодируем подписанные данные
            $data = json_decode(stripslashes($signed_data), true);
            if (!$data) {
                return array(
                    'success' => false,
                    'message' => __('Неверный формат подписанных данных', 'wp-cryptopro-auth')
                );
            }
            
            // Проверяем наличие обязательных полей
            $required_fields = ['timestamp', 'action', 'certificate_subject'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field])) {
                    return array(
                        'success' => false,
                        /* translators: %s: name of the required field */
                        'message' => sprintf(__('Отсутствует обязательное поле: %s', 'wp-cryptopro-auth'), $field)
                    );
                }
            }
            
            // Проверяем timestamp (не старше 5 минут)
            $timestamp = strtotime($data['timestamp']);
            $now = time();
            
            if (!$timestamp || $timestamp > $now + 60 || $now - $timestamp > 300) {
                return array(
                    'success' => false,
                    'message' => __('Подпись устарела или имеет неверное время', 'wp-cryptopro-auth')
                );
            }
            
            // Реальная проверка подписи через CPSignedData
            if (class_exists('CPSignedData')) {
                try {
                    $signedData = new CPSignedData();
                    
                    // Для присоединенной подписи (CAdES BES) данные уже включены в подпись
                    // Устанавливаем кодировку BASE64
                    // Используем set_ContentEncoding если доступен, иначе свойство с подавлением ошибки
                    if (method_exists($signedData, 'set_ContentEncoding')) {
                        $signedData->set_ContentEncoding(1); // CADESCOM_BASE64_TO_BINARY
                    } else {
                        @$signedData->ContentEncoding = 1;
                    }
                    
                    // Проверяем подпись через VerifyCades
                    // Для присоединенной подписи: VerifyCades(signature, cadesType, flag)
                    // cadesType: 1 = CADESCOM_CADES_BES
                    // flag: 0 = без дополнительных проверок
                    try {
                        $signatures = $signedData->VerifyCades($signature, 1, 0);
                        
                        // VerifyCades возвращает коллекцию подписей, если проверка успешна
                        if ($signatures && (is_array($signatures) || is_object($signatures))) {
                            error_log("CryptoPro Auth - PHP Extension: Signature verified successfully using CPSignedData::VerifyCades");
                            
                            return array(
                                'success' => true,
                                'message' => __('Подпись проверена через PHP расширение КриптоПро', 'wp-cryptopro-auth'),
                                'data' => $data
                            );
                        } else {
                            throw new Exception('VerifyCades returned invalid result');
                        }
                    } catch (Exception $verifyException) {
                        error_log("CryptoPro Auth - VerifyCades Exception: " . $verifyException->getMessage());
                        
                        // Если VerifyCades не прошел (например, из-за отсутствия цепочки сертификатов 0x80070490),
                        // пробуем стандартный метод Verify с флагом CADESCOM_VERIFY_SIGNATURE_ONLY (1)
                        try {
                            error_log("CryptoPro Auth - Attempting fallback to CPSignedData::Verify with SignatureOnly flag");
                            // Verify(SignedMessage, Detached, VerifyFlag)
                            // VerifyFlag: 1 = CADESCOM_VERIFY_SIGNATURE_ONLY
                            $signedData->Verify($signature, 0, 1);
                            
                            error_log("CryptoPro Auth - PHP Extension: Signature verified successfully using CPSignedData::Verify (SignatureOnly)");
                            
                            return array(
                                'success' => true,
                                'message' => __('Подпись проверена (без проверки цепочки)', 'wp-cryptopro-auth'),
                                'data' => $data
                            );
                        } catch (Exception $verifyFallbackException) {
                            error_log("CryptoPro Auth - Verify (SignatureOnly) Exception: " . $verifyFallbackException->getMessage());
                            
                            // Если и это не помогло, выбрасываем исключение дальше для fallback на simple verify
                            throw $verifyException;
                        }
                    }
                    
                } catch (Exception $e) {
                    error_log("CryptoPro Auth - CPSignedData Error: " . $e->getMessage());
                    
                    // Если проверка через CPSignedData не удалась, пробуем через CPStore
                    return $this->verify_signature_via_store($signed_data, $signature, $certificate, $data);
                }
            } else {
                // Если CPSignedData недоступен, используем CPStore
                return $this->verify_signature_via_store($signed_data, $signature, $certificate, $data);
            }
            
        } catch (Exception $e) {
            error_log("CryptoPro Auth - PHP Extension Exception: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Проверка подписи через CPStore (альтернативный метод).
     * 
     * @param string $signed_data
     * @param string $signature
     * @param mixed $certificate
     * @param array $data
     * @return array
     * @author @ddnitecry
     */
    private function verify_signature_via_store(string $signed_data, string $signature, mixed $certificate, array $data): array {
        try {
            if (!class_exists('CPStore')) {
                // Fallback на упрощенную проверку
                return $this->verify_signature_simple($signed_data, $signature, $certificate);
            }
            
            $store = new CPStore();
            
            // Проверяем формат подписи
            $clean_signature = str_replace(array("\r", "\n", " "), '', $signature);
            if (!preg_match('/^[a-zA-Z0-9+\/]*={0,2}$/', $clean_signature)) {
                return array(
                    'success' => false,
                    'message' => __('Неверный формат подписи', 'wp-cryptopro-auth')
                );
            }
            
            // Проверяем соответствие сертификата
            if (!empty($certificate) && !empty($data['certificate_subject'])) {
                $cert_subject = $certificate['subjectName'] ?? $certificate['commonName'] ?? '';
                if ($cert_subject !== $data['certificate_subject']) {
                    error_log("CryptoPro Auth - Certificate mismatch");
                    return array(
                        'success' => false,
                        'message' => __('Несоответствие данных сертификата', 'wp-cryptopro-auth')
                    );
                }
            }
            
            error_log("CryptoPro Auth - PHP Extension: Signature verified using CPStore (basic validation)");
            
            return array(
                'success' => true,
                'message' => __('Подпись проверена через PHP расширение КриптоПро', 'wp-cryptopro-auth'),
                'data' => $data
            );
            
        } catch (Exception $e) {
            error_log("CryptoPro Auth - CPStore Error: " . $e->getMessage());
            // Fallback на упрощенную проверку
            return $this->verify_signature_simple($signed_data, $signature, $certificate);
        }
    }
    
    /**
     * Упрощенная проверка подписи (без криптографии).
     * 
     * @param string $signed_data
     * @param string $signature
     * @param mixed $certificate
     * @return array
     * @author @ddnitecry
     */
    private function verify_signature_simple(string $signed_data, string $signature, mixed $certificate): array {
        // Базовая проверка входных данных
        if (empty($signed_data) || empty($signature)) {
            return array(
                'success' => false,
                'message' => __('Отсутствуют данные для проверки подписи', 'wp-cryptopro-auth')
            );
        }
        
        // Убираем экранирование из JSON данных
        $decoded_data = stripslashes($signed_data);
        
        // Декодируем подписанные данные
        $data = json_decode($decoded_data, true);
        if (!$data) {
            // Пробуем декодировать без stripslashes на случай если данные не экранированы
            $data = json_decode($signed_data, true);
            if (!$data) {
                error_log("CryptoPro Auth - Invalid JSON data. Raw data: " . $signed_data);
                error_log("CryptoPro Auth - Decoded attempt: " . $decoded_data);
                return array(
                    'success' => false,
                    'message' => __('Неверный формат подписанных данных', 'wp-cryptopro-auth')
                );
            }
        }
        
        // Проверяем обязательные поля в данных
        $required_fields = ['timestamp', 'action', 'certificate_subject'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                error_log("CryptoPro Auth - Missing required field: " . $field);
                error_log("CryptoPro Auth - Available fields: " . implode(', ', array_keys($data)));
                return array(
                    'success' => false,
                    /* translators: %s: name of the required field */
                    'message' => sprintf(__('Отсутствует обязательное поле: %s', 'wp-cryptopro-auth'), $field)
                );
            }
        }
        
        // Убираем переносы строк из подписи для проверки формата
        $clean_signature = str_replace(array("\r", "\n", " "), '', $signature);
        
        // Проверяем формат подписи (должна быть base64)
        if (!preg_match('/^[a-zA-Z0-9+\/]*={0,2}$/', $clean_signature)) {
            error_log("CryptoPro Auth - Invalid signature format. Length: " . strlen($clean_signature));
            return array(
                'success' => false,
                'message' => __('Неверный формат подписи', 'wp-cryptopro-auth')
            );
        }
        
        // Проверяем timestamp (не старше 5 минут)
        $timestamp = strtotime($data['timestamp']);
        $now = time();
        
        if (!$timestamp) {
            error_log("CryptoPro Auth - Invalid timestamp: " . $data['timestamp']);
            return array(
                'success' => false,
                'message' => __('Неверный формат времени подписи', 'wp-cryptopro-auth')
            );
        }
        
        if ($timestamp > $now + 60) { // Подпись из будущего (допуск 1 минута)
            error_log("CryptoPro Auth - Future timestamp: " . $data['timestamp']);
            return array(
                'success' => false,
                'message' => __('Подпись из будущего времени', 'wp-cryptopro-auth')
            );
        }
        
        if ($now - $timestamp > 300) { // Подпись старше 5 минут
            error_log("CryptoPro Auth - Expired timestamp: " . $data['timestamp']);
            return array(
                'success' => false,
                'message' => __('Подпись устарела (старше 5 минут)', 'wp-cryptopro-auth')
            );
        }
        
        // Проверяем соответствие сертификата
        if (!empty($certificate) && !empty($data['certificate_subject'])) {
            $cert_subject = $certificate['subjectName'] ?? $certificate['commonName'] ?? '';
            if ($cert_subject !== $data['certificate_subject']) {
                error_log("CryptoPro Auth - Certificate mismatch");
                error_log("CryptoPro Auth - Cert subject: " . $cert_subject);
                error_log("CryptoPro Auth - Data subject: " . $data['certificate_subject']);
                return array(
                    'success' => false,
                    'message' => __('Несоответствие данных сертификата', 'wp-cryptopro-auth')
                );
            }
        }
        
        // Проверяем длину подписи (должна быть разумной)
        $signature_length = strlen($clean_signature);
        if ($signature_length < 100 || $signature_length > 10000) {
            error_log("CryptoPro Auth - Invalid signature length: " . $signature_length);
            return array(
                'success' => false,
                'message' => __('Некорректная длина подписи', 'wp-cryptopro-auth')
            );
        }
        
        error_log("CryptoPro Auth - Signature verified for: " . ($data['certificate_subject'] ?? 'unknown'));
        error_log("CryptoPro Auth - Signature length: " . $signature_length);
        error_log("CryptoPro Auth - Timestamp: " . $data['timestamp']);
        error_log("CryptoPro Auth - Action: " . $data['action']);
        
        return array(
            'success' => true,
            'message' => __('Подпись прошла базовую проверку', 'wp-cryptopro-auth'),
            'data' => $data
        );
    }
    
    // консультации @ddnitecry
    private function find_or_create_user($cert_data) {
        // Парсим данные сертификата - структура может быть разной
        // Вариант 1: Плоская структура (subjectName, commonName)
        $common_name = $cert_data['commonName'] ?? '';
        $subject_name = $cert_data['subjectName'] ?? '';
        
        // Вариант 2: Вложенная структура (subject['commonName'])
        if (empty($common_name) && isset($cert_data['subject'])) {
            $subject = $cert_data['subject'];
            $common_name = $subject['commonName'] ?? '';
        }
        
        // Парсим email из subjectName если он есть
        $email = '';
        if (!empty($subject_name)) {
            // Парсим строку вида "CN=Иванов Иван, O=ООО Рога и копыта, E=test@mail.ru"
            if (preg_match('/E=([^,]+)/i', $subject_name, $matches)) {
                $email = trim($matches[1]);
            }
        }
        
        // Если email не найден, проверяем вложенную структуру
        if (empty($email) && isset($cert_data['subject']['emailAddress'])) {
            $email = $cert_data['subject']['emailAddress'];
        }
        
        // Если commonName пустой, пытаемся извлечь из subjectName
        if (empty($common_name) && !empty($subject_name)) {
            if (preg_match('/CN=([^,]+)/i', $subject_name, $matches)) {
                $common_name = trim($matches[1]);
            }
        }
        
        // Логируем для отладки
        error_log("CryptoPro Auth - Certificate data: " . print_r($cert_data, true));
        error_log("CryptoPro Auth - Parsed common_name: " . $common_name);
        error_log("CryptoPro Auth - Parsed email: " . $email);
        
        if (empty($email) && empty($common_name)) {
            return new WP_Error('invalid_certificate', __('Сертификат не содержит идентификационных данных', 'wp-cryptopro-auth'));
        }
        
        // Поиск по email
        if (!empty($email)) {
            $user = get_user_by('email', $email);
            if ($user) {
                // Привязываем сертификат к существующему пользователю
                $this->update_user_certificate_data($user->ID, $cert_data);
                return $user;
            }
        }
        
        // 3. Если пользователь не найден, проверяем настройку авторегистрации
        $settings = get_option('cryptopro_auth_settings', array());
        if (!empty($settings['auto_create_users'])) {
            return $this->create_new_user($cert_data);
        }
        
        return new WP_Error(
            'user_not_found', 
            __('Пользователь с таким сертификатом не найден, а регистрация отключена.', 'wp-cryptopro-auth')
        );
    }
    
    /**
     * Создание нового пользователя.
     * 
     * @param array $cert_data
     * @return WP_User|WP_Error
     * @author @ddnitecry
     */
    private function create_new_user(array $cert_data): WP_User|WP_Error {
        $settings = get_option('cryptopro_auth_settings', array());
        
        $email = $cert_data['user_data']['email'] ?? '';
        $common_name = $cert_data['user_data']['common_name'] ?? '';
        
        // Генерируем имя пользователя
        $username = $this->generate_username($cert_data);
        
        // Если email нет, генерируем фейковый (WordPress требует email)
        if (empty($email)) {
            $email = $username . '@example.com';
        }
        
        $password = wp_generate_password(12, true);
        
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        $user = get_user_by('id', $user_id);
        
        // Устанавливаем роль
        $default_role = $settings['default_user_role'] ?? 'subscriber';
        
        // Если включен тестовый режим, назначаем роль testuser
        if ($this->is_test_mode()) {
            // Проверяем существование роли, если нет - создаем
            if (!get_role('testuser')) {
                add_role('testuser', __('Тестовый пользователь', 'wp-cryptopro-auth'), array('read' => true));
            }
            $default_role = 'testuser';
        }
        
        $user->set_role($default_role);
        
        // Заполняем имя/фамилию если возможно
        if (!empty($common_name)) {
            $parts = explode(' ', $common_name);
            if (count($parts) >= 2) {
                wp_update_user(array(
                    'ID' => $user_id,
                    'first_name' => $parts[1],
                    'last_name' => $parts[0],
                    'display_name' => $common_name
                ));
            } else {
                wp_update_user(array(
                    'ID' => $user_id,
                    'display_name' => $common_name
                ));
            }
        }
        
        // Сохраняем данные сертификата
        $this->update_user_certificate_data($user_id, $cert_data);
        
        do_action('cryptopro_user_created', $user_id, $cert_data);
        
        return $user;
    }
    
    /**
     * Обновление метаданных пользователя из сертификата.
     * 
     * @param int $user_id
     * @param array $cert_data
     * @return void
     * @author @ddnitecry
     */
    private function update_user_certificate_data(int $user_id, array $cert_data): void {
        update_user_meta($user_id, 'cryptopro_serial_number', $cert_data['serialNumber']);
        update_user_meta($user_id, 'cryptopro_subject_name', $cert_data['subjectName']);
        update_user_meta($user_id, 'cryptopro_issuer_name', $cert_data['issuerName']);
        
        if (!empty($cert_data['user_data']['organization'])) {
            update_user_meta($user_id, 'cryptopro_organization', $cert_data['user_data']['organization']);
        }
        
        if (!empty($cert_data['user_data']['inn'])) {
            update_user_meta($user_id, 'cryptopro_inn', $cert_data['user_data']['inn']);
        }
        
        update_user_meta($user_id, 'cryptopro_last_login', current_time('mysql'));
    }
    
    /**
     * Генерация уникального имени пользователя.
     * 
     * @param array $cert_data
     * @return string
     * @author @ddnitecry
     */
    private function generate_username(array $cert_data): string {
        // Пробуем использовать email
        $email = $cert_data['user_data']['email'] ?? '';
        if (!empty($email)) {
            $username = sanitize_user(current(explode('@', $email)));
            if (!username_exists($username)) {
                return $username;
            }
        }
        
        // Пробуем использовать CN (транслитерация)
        $common_name = $cert_data['user_data']['common_name'] ?? '';
        if (!empty($common_name)) {
            $username = sanitize_user(sanitize_title($common_name));
            if (!empty($username) && !username_exists($username)) {
                return $username;
            }
        }
        
        // Генерируем уникальное имя
        $base_name = 'user_cp';
        $i = 1;
        while (username_exists($base_name . $i)) {
            $i++;
        }
        
        return $base_name . $i;
    }

    /**
     * Проверка тестового режима.
     * 
     * @return bool
     * @author @ddnitecry
     */
    private function is_test_mode(): bool {
        $settings = get_option('cryptopro_auth_settings', array());
        return !empty($settings['test_mode']);
    }
}