<?php
/**
 * Template for login form
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="cryptopro-login-container" class="cryptopro-auth-container">
    <h3><?php esc_html_e('Войти через ЭЦП', 'cryptopro-auth'); ?></h3>
    <button class="cryptopro-auth-btn" type="button">
        <?php esc_html_e('Войти с помощью КриптоПро', 'cryptopro-auth'); ?>
    </button>
    <div class="cryptopro-auth-status"></div>
    <div class="cryptopro-auth-guide" style="display: none;">
        <h4><?php esc_html_e('Для работы необходимы:', 'cryptopro-auth'); ?></h4>
        <ol>
            <li><?php esc_html_e('Установить КриптоПро CSP', 'cryptopro-auth'); ?></li>
            <li><?php esc_html_e('Установить КриптоПро Browser Plugin', 'cryptopro-auth'); ?></li>
            <li><?php esc_html_e('Настроить сертификат ЭЦП', 'cryptopro-auth'); ?></li>
        </ol>
        <p><a href="https://www.cryptopro.ru/products/csp" target="_blank"><?php esc_html_e('Скачать КриптоПро CSP', 'cryptopro-auth'); ?></a></p>
    </div>
</div>