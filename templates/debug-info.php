<?php
/**
 * Debug information template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Диагностика CryptoPro Auth', 'cryptopro-auth'); ?></h1>
    
    <div class="card">
        <h2><?php esc_html_e('Информация о системе', 'cryptopro-auth'); ?></h2>
        <ul>
            <li><?php esc_html_e('Версия PHP:', 'cryptopro-auth'); ?> <?php echo esc_html(phpversion()); ?></li>
            <li><?php esc_html_e('Версия WordPress:', 'cryptopro-auth'); ?> <?php echo esc_html(get_bloginfo('version')); ?></li>
            <li><?php esc_html_e('Браузер:', 'cryptopro-auth'); ?> <?php echo isset($_SERVER['HTTP_USER_AGENT']) ? esc_html(sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']))) : 'Unknown'; ?></li>
        </ul>
    </div>

    <div class="card">
        <h2><?php esc_html_e('Тест обнаружения плагина', 'cryptopro-auth'); ?></h2>
        <button id="test-plugin-detection" class="button button-primary"><?php esc_html_e('Запустить тест', 'cryptopro-auth'); ?></button>
        <div id="test-results" style="margin-top: 15px;"></div>
    </div>

    <div class="card">
        <h2><?php esc_html_e('Ручная проверка', 'cryptopro-auth'); ?></h2>
        <p><?php esc_html_e('Откройте консоль браузера (F12) и проверьте наличие объектов:', 'cryptopro-auth'); ?></p>
        <code>
            console.log('CryptoPro:', window.CryptoPro);<br>
            console.log('cadesplugin:', window.cadesplugin);<br>
            console.log('CAdESCOM:', window.CAdESCOM);
        </code>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#test-plugin-detection').on('click', function() {
        const results = $('#test-results');
        results.html('<p><?php esc_html_e('Проверка объектов...', 'cryptopro-auth'); ?></p>');
        
        // Проверяем различные объекты
        const objectsToCheck = [
            'CryptoPro',
            'cryptoPro', 
            'CAdESCOM',
            'cadesplugin',
            'CSP',
            'csp'
        ];
        
        let foundObjects = [];
        
        objectsToCheck.forEach(objName => {
            if (window[objName]) {
                foundObjects.push(`<li><strong>${objName}:</strong> <?php esc_html_e('найден', 'cryptopro-auth'); ?></li>`);
            } else {
                foundObjects.push(`<li><strong>${objName}:</strong> <?php esc_html_e('не найден', 'cryptopro-auth'); ?></li>`);
            }
        });
        
        results.html('<ul>' + foundObjects.join('') + '</ul>');
    });
});
</script>