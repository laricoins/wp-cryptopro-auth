<?php
/**
 * Debug information template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Диагностика CriptaPro Auth', 'criptapro-auth'); ?></h1>
    
    <div class="card">
        <h2><?php esc_html_e('Информация о системе', 'criptapro-auth'); ?></h2>
        <ul>
            <li><?php esc_html_e('Версия PHP:', 'criptapro-auth'); ?> <?php echo esc_html(phpversion()); ?></li>
            <li><?php esc_html_e('Версия WordPress:', 'criptapro-auth'); ?> <?php echo esc_html(get_bloginfo('version')); ?></li>
            <li><?php esc_html_e('Браузер:', 'criptapro-auth'); ?> <?php echo isset($_SERVER['HTTP_USER_AGENT']) ? esc_html(sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']))) : 'Unknown'; ?></li>
        </ul>
    </div>

    <div class="card">
        <h2><?php esc_html_e('Тест обнаружения плагина', 'criptapro-auth'); ?></h2>
        <button id="test-plugin-detection" class="button button-primary"><?php esc_html_e('Запустить тест', 'criptapro-auth'); ?></button>
        <div id="test-results" style="margin-top: 15px;"></div>
    </div>

    <div class="card">
        <h2><?php esc_html_e('Ручная проверка', 'criptapro-auth'); ?></h2>
        <p><?php esc_html_e('Откройте консоль браузера (F12) и проверьте наличие объектов:', 'criptapro-auth'); ?></p>
        <code>
            console.log('CryptoPro:', window.CryptoPro);<br>
            console.log('cadesplugin:', window.cadesplugin);<br>
            console.log('CAdESCOM:', window.CAdESCOM);
        </code>
    </div>
</div>

<?php
// Inline script removed and moved to assets/js/cryptopro-plugin.js
?>