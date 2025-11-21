<?php
/**
 * Debug information template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Р вЂќР С‘Р В°Р С–Р Р…Р С•РЎРѓРЎвЂљР С‘Р С”Р В° CryptoPro Auth', 'wp-cryptopro-auth'); ?></h1>
    
    <div class="card">
        <h2><?php esc_html_e('Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ Р С• РЎРѓР С‘РЎРѓРЎвЂљР ВµР СР Вµ', 'wp-cryptopro-auth'); ?></h2>
        <ul>
            <li><?php esc_html_e('Р вЂ™Р ВµРЎР‚РЎРѓР С‘РЎРЏ PHP:', 'wp-cryptopro-auth'); ?> <?php echo esc_html(phpversion()); ?></li>
            <li><?php esc_html_e('Р вЂ™Р ВµРЎР‚РЎРѓР С‘РЎРЏ WordPress:', 'wp-cryptopro-auth'); ?> <?php echo esc_html(get_bloginfo('version')); ?></li>
            <li><?php esc_html_e('Р вЂРЎР‚Р В°РЎС“Р В·Р ВµРЎР‚:', 'wp-cryptopro-auth'); ?> <?php echo isset($_SERVER['HTTP_USER_AGENT']) ? esc_html(sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']))) : 'Unknown'; ?></li>
        </ul>
    </div>

    <div class="card">
        <h2><?php esc_html_e('Р СћР ВµРЎРѓРЎвЂљ Р С•Р В±Р Р…Р В°РЎР‚РЎС“Р В¶Р ВµР Р…Р С‘РЎРЏ Р С—Р В»Р В°Р С–Р С‘Р Р…Р В°', 'wp-cryptopro-auth'); ?></h2>
        <button id="test-plugin-detection" class="button button-primary"><?php esc_html_e('Р вЂ”Р В°Р С—РЎС“РЎРѓРЎвЂљР С‘РЎвЂљРЎРЉ РЎвЂљР ВµРЎРѓРЎвЂљ', 'wp-cryptopro-auth'); ?></button>
        <div id="test-results" style="margin-top: 15px;"></div>
    </div>

    <div class="card">
        <h2><?php esc_html_e('Р В РЎС“РЎвЂЎР Р…Р В°РЎРЏ Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В°', 'wp-cryptopro-auth'); ?></h2>
        <p><?php esc_html_e('Р С›РЎвЂљР С”РЎР‚Р С•Р в„–РЎвЂљР Вµ Р С”Р С•Р Р…РЎРѓР С•Р В»РЎРЉ Р В±РЎР‚Р В°РЎС“Р В·Р ВµРЎР‚Р В° (F12) Р С‘ Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚РЎРЉРЎвЂљР Вµ Р Р…Р В°Р В»Р С‘РЎвЂЎР С‘Р Вµ Р С•Р В±РЎР‰Р ВµР С”РЎвЂљР С•Р Р†:', 'wp-cryptopro-auth'); ?></p>
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
        results.html('<p><?php esc_html_e('Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° Р С•Р В±РЎР‰Р ВµР С”РЎвЂљР С•Р Р†...', 'wp-cryptopro-auth'); ?></p>');
        
        // Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚РЎРЏР ВµР С РЎР‚Р В°Р В·Р В»Р С‘РЎвЂЎР Р…РЎвЂ№Р Вµ Р С•Р В±РЎР‰Р ВµР С”РЎвЂљРЎвЂ№
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
                foundObjects.push(`<li><strong>${objName}:</strong> <?php esc_html_e('Р Р…Р В°Р в„–Р Т‘Р ВµР Р…', 'wp-cryptopro-auth'); ?></li>`);
            } else {
                foundObjects.push(`<li><strong>${objName}:</strong> <?php esc_html_e('Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…', 'wp-cryptopro-auth'); ?></li>`);
            }
        });
        
        results.html('<ul>' + foundObjects.join('') + '</ul>');
    });
});
</script>