=== CriptaPro Auth ===
Contributors: vit_sh
Tags: cryptopro, authentication, gost, signature, cades
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.0.5
Requires PHP: 8.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin for authorization on WordPress site using CryptoPro digital signature.

For questions, contact Telegram: **@ddnitecry**

== Description ==

=== English ===

The plugin allows users to log in to the site using CryptoPro digital signature certificates. Automatic registration of new users based on certificate data is supported.

**Key features:**
*   Authorization by GOST digital signature.
*   Automatic user registration.
*   Signature verification on the server (via php-cades or simplified).
*   Shortcodes for embedding login forms.
*   Security: Trusted CA whitelist, login attempt logging, HTTPS support, CORS.

**Requirements:**
*   WordPress 5.0 or higher
*   PHP 8.2 or higher
*   CryptoPro CSP (client-side)
*   CryptoPro Browser Plugin (client-side)

=== Русский ===

Плагин позволяет пользователям входить на сайт, используя сертификаты ЭЦП КриптоПро. Поддерживается автоматическая регистрация новых пользователей по данным из сертификата.

**Основные возможности:**
*   Авторизация по ЭЦП (ГОСТ).
*   Автоматическая регистрация пользователей.
*   Проверка подписи на сервере (через php-cades или упрощенная).
*   Шорткоды для встраивания форм входа.
*   Безопасность: Whitelist доверенных УЦ, логирование попыток входа, поддержка HTTPS, CORS.

**Требования:**
*   WordPress 5.0 или выше
*   PHP 8.2 или выше
*   КриптоПро CSP (на клиенте)
*   КриптоПро Browser Plugin (на клиенте)

== Installation ==

=== English ===

1.  Upload the `criptapro-auth` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the "Plugins" menu in WordPress.
3.  Go to settings: **Settings -> CriptaPro Auth**.
4.  Configure trusted certificate authorities and other security settings.
5.  Use shortcodes `[criptapro_login]` or `[criptapro_auth]` on your pages.

=== Русский ===

1.  Загрузите папку `criptapro-auth` в директорию `/wp-content/plugins/`.
2.  Активируйте плагин через меню "Плагины" в WordPress.
3.  Перейдите в настройки: **Настройки -> CriptaPro Auth**.
4.  Настройте доверенные удостоверяющие центры и другие параметры безопасности.
5.  Используйте шорткоды `[criptapro_login]` или `[criptapro_auth]` на ваших страницах.

== Frequently Asked Questions ==

=== English ===

= Does this plugin work without CryptoPro CSP? =
No, users must have CryptoPro CSP and CryptoPro Browser Plugin installed on their computers.

= Can I limit access to specific organizations? =
Yes, use the "Trusted CA Whitelist" feature in settings to allow only certificates from specific certificate authorities.

= Is HTTPS required? =
HTTPS is strongly recommended for security. You can enforce HTTPS-only authentication in plugin settings.

=== Русский ===

= Работает ли плагин без КриптоПро CSP? =
Нет, пользователи должны иметь установленные КриптоПро CSP и КриптоПро Browser Plugin на своих компьютерах.

= Могу ли я ограничить доступ определенным организациям? =
Да, используйте функцию "Whitelist доверенных УЦ" в настройках, чтобы разрешить только сертификаты от определенных удостоверяющих центров.

= Требуется ли HTTPS? =
HTTPS настоятельно рекомендуется для безопасности. Вы можете включить принудительную авторизацию только через HTTPS в настройках плагина.

== Screenshots ==

1. Login form with authentication button. / Форма входа с кнопкой авторизации.
2. Certificate selection window (browser interface). / Окно выбора сертификата (интерфейс браузера).
3. Plugin settings page. / Страница настроек плагина.
4. Diagnostics and component verification page. / Страница диагностики и проверки компонентов.

== Changelog ==

= 1.0.5 =
*   Updated author name to match contributor username (vit_sh).
*   Обновлено имя автора для соответствия contributor username (vit_sh).

= 1.0.4 =
*   Minor improvements and bug fixes.
*   Незначительные улучшения и исправления ошибок.

= 1.0.3 =
*   Fixed contributor username in readme.txt.
*   Translated admin page title to English.
*   Removed commented script tags.
*   Removed screenshot files from plugin folder.
*   Исправлен contributor username в readme.txt.
*   Переведен заголовок админ-страницы на английский.
*   Удалены закомментированные script теги.
*   Удалены файлы скриншотов из папки плагина.

= 1.0.2 =
*   Renamed plugin to CriptaPro Auth.
*   Fixed inline scripts and direct file access issues.
*   Added bilingual English/Russian descriptions.
*   Переименован плагин в CriptaPro Auth.
*   Исправлены inline скрипты и проблемы прямого доступа к файлам.
*   Добавлены двуязычные описания (английский/русский).

= 1.0.1 =
*   Tested compatibility with WordPress 6.9.
*   Added `criptapro_auth_settings_page_end` action hook.
*   Протестирована совместимость с WordPress 6.9.
*   Добавлен action hook `criptapro_auth_settings_page_end`.

= 1.0.0 =
*   First release. / Первый релиз.

== Upgrade Notice ==

= 1.0.2 =
Plugin renamed to comply with WordPress.org guidelines. Update recommended.
Плагин переименован для соответствия требованиям WordPress.org. Рекомендуется обновление.
