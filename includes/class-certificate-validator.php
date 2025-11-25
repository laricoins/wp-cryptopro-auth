<?php
if (!defined('ABSPATH')) {
    exit;
}

class CryptoPro_Certificate_Validator {
    
    /**
     * Валидация сертификата.
     * 
     * @param mixed $certificate Данные сертификата.
     * @return array Результат валидации.
     * @author @ddnitecry
     */
    public function validate(mixed $certificate): array {
        // Проверка базовой структуры
        if (empty($certificate) || !is_array($certificate)) {
            return array(
                'valid' => false,
                'error' => __('Неверный формат сертификата', 'cryptopro-auth')
            );
        }
        
        // Логируем для отладки
        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (class_exists('CryptoPro_Auth_Handler')) {
                CryptoPro_Auth_Handler::log_auth_attempt('debug', $certificate, 'Certificate Data Validation');
            }
        }
        
        // Проверяем обязательные поля
        $required_fields = array('subjectName', 'commonName', 'serialNumber');
        foreach ($required_fields as $field) {
            if (empty($certificate[$field])) {
                return array(
                    'valid' => false,
                    /* translators: %s: name of the required certificate field */
                    'error' => sprintf(__('Отсутствует обязательное поле: %s', 'cryptopro-auth'), $field)
                );
            }
        }
        
        // Проверка срока действия
        $validity_check = $this->check_validity($certificate);
        if (!$validity_check['valid']) {
            return $validity_check;
        }
        
        // Парсим subjectName для получения данных
        $parsed_subject = $this->parse_subject_name($certificate['subjectName']);
        
        // Проверка организации
        $org_check = $this->check_organization($parsed_subject);
        if (!$org_check['valid']) {
            return $org_check;
        }
        
        // Проверка издателя (Whitelist)
        $issuer_check = $this->check_issuer($certificate['issuerName']);
        if (!$issuer_check['valid']) {
            return $issuer_check;
        }
        
        return array(
            'valid' => true,
            'user_data' => array(
                'common_name' => $certificate['commonName'],
                'organization' => $parsed_subject['organization'],
                'email' => $parsed_subject['email'],
                'subject_name' => $certificate['subjectName'],
                'serial_number' => $certificate['serialNumber']
            )
        );
    }
    
    /**
     * Проверка срока действия сертификата.
     * 
     * @param array $certificate
     * @return array
     * @author @ddnitecry
     */
    private function check_validity(array $certificate): array {
        $now = time();
        
        // Проверяем дату начала действия
        if (!empty($certificate['validFrom'])) {
            $valid_from = strtotime($certificate['validFrom']);
            if ($valid_from && $valid_from > $now) {
                return array(
                    'valid' => false,
                    'error' => __('Сертификат еще не действителен', 'cryptopro-auth')
                );
            }
        }
        
        // Проверяем дату окончания действия
        if (!empty($certificate['validTo'])) {
            $valid_to = strtotime($certificate['validTo']);
            if ($valid_to && $valid_to < $now) {
                return array(
                    'valid' => false,
                    'error' => __('Срок действия сертификата истек', 'cryptopro-auth')
                );
            }
        }
        
        return array('valid' => true);
    }
    
    /**
     * Парсинг строки Subject Name.
     * 
     * @param string $subject_name
     * @return array
     * @author @ddnitecry
     */
    private function parse_subject_name(string $subject_name): array {
        $result = array(
            'organization' => '',
            'email' => '',
            'inn' => ''
        );
        
        if (empty($subject_name)) {
            return $result;
        }
        
        // Парсим строку вида "CN=Иванов Иван, O=ООО Рога и копыта, E=test@mail.ru"
        $parts = explode(',', $subject_name);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, '=') === false) continue;
            
            list($key, $value) = explode('=', $part, 2);
            $key = trim($key);
            $value = trim($value);
            
            switch (strtoupper($key)) {
                case 'O':
                    $result['organization'] = $value;
                    break;
                case 'E':
                case 'EMAIL':
                    $result['email'] = $value;
                    break;
                case 'INN':
                    $result['inn'] = $value;
                    break;
            }
        }
        
        return $result;
    }
    
    /**
     * Проверка разрешенной организации.
     * 
     * @param array $parsed_subject
     * @return array
     * @author @ddnitecry
     */
    private function check_organization(array $parsed_subject): array {
        $settings = get_option('cryptopro_auth_settings', array());
        $allowed_organizations = $settings['allowed_organizations'] ?? array();
        
        // Если ограничений по организациям нет - пропускаем проверку
        if (empty($allowed_organizations)) {
            return array('valid' => true);
        }
        
        $org = $parsed_subject['organization'];
        if (empty($org)) {
            return array(
                'valid' => false,
                'error' => __('Сертификат не содержит информации об организации', 'cryptopro-auth')
            );
        }
        
        // Проверяем вхождение организации в разрешенные
        if (!in_array($org, $allowed_organizations)) {
            return array(
                'valid' => false,
                /* translators: %s: organization name from the certificate */
                'error' => sprintf(__("Организация '%s' не имеет доступа", 'cryptopro-auth'), $org)
            );
        }
        
        return array('valid' => true);
    }
    
    /**
     * Проверка доверенного издателя (Whitelist).
     * 
     * @param string|null $issuer_name
     * @return array
     * @author @ddnitecry
     */
    private function check_issuer(?string $issuer_name): array {
        $settings = get_option('cryptopro_auth_settings', array());
        $trusted_issuers_text = $settings['trusted_issuers'] ?? '';
        
        if (empty($trusted_issuers_text)) {
            return array('valid' => true);
        }
        
        $trusted_issuers = array_filter(array_map('trim', explode("\n", $trusted_issuers_text)));
        
        if (empty($trusted_issuers)) {
            return array('valid' => true);
        }
        
        if (empty($issuer_name)) {
            return array(
                'valid' => false,
                'error' => __('Сертификат не содержит информации об издателе', 'cryptopro-auth')
            );
        }
        
        foreach ($trusted_issuers as $trusted) {
            // Проверяем, содержит ли строка издателя название доверенного УЦ (регистронезависимо)
            if (mb_stripos($issuer_name, $trusted) !== false) {
                return array('valid' => true);
            }
        }
        
        return array(
            'valid' => false,
            'error' => __('Сертификат выдан недоверенным Удостоверяющим Центром', 'cryptopro-auth')
        );
    }
}