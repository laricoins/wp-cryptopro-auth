<?php
/**
 * Template for login form
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="criptapro-login-container" class="criptapro-auth-container">
    <h3><?php esc_html_e('Войти через ЭЦП', 'criptapro-auth'); ?></h3>
    <button class="criptapro-auth-btn" type="button">
        <?php esc_html_e('Войти с помощью КриптоПро', 'criptapro-auth'); ?>
    </button>
    <div class="criptapro-auth-status"></div>
    <div class="criptapro-auth-guide" style="display: none;">
        <h4><?php esc_html_e('Для работы необходимы:', 'criptapro-auth'); ?></h4>
        <ol>
            <li><?php esc_html_e('Установить КриптоПро CSP', 'criptapro-auth'); ?></li>
            <li><?php esc_html_e('Установить КриптоПро Browser Plugin', 'criptapro-auth'); ?></li>
            <li><?php esc_html_e('Настроить сертификат ЭЦП', 'criptapro-auth'); ?></li>
        </ol>
        <p><a href="https://www.cryptopro.ru/products/csp" target="_blank"><?php esc_html_e('Скачать КриптоПро CSP', 'criptapro-auth'); ?></a></p>
    </div>
</div>