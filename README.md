# CriptaPro Auth

**WordPress authentication using CryptoPro digital signature**  
**Авторизация в WordPress с помощью ЭЦП КриптоПро**

---

## 🇬🇧 English

### Description

The plugin allows users to log in to WordPress using CryptoPro digital signature certificates. Automatic registration of new users based on certificate data is supported.

**Key features:**
*   Authorization by GOST digital signature
*   Automatic user registration
*   Server-side signature verification (via php-cades or simplified mode)
*   Compatibility mode when PHP extension is not available
*   **Security:** Trusted CA whitelist, login attempt logging, HTTPS support, CORS

### Requirements

**Server-side:**
*   WordPress 5.0 or higher
*   PHP 8.2 or higher
*   Recommended: PHP extension for CryptoPro (e.g., `php-cades`), but the plugin has a compatibility mode without it
*   **SSL certificate (HTTPS)** - strongly recommended for security

**Client-side (user):**
*   CryptoPro CSP
*   CryptoPro Browser Plugin
*   Valid personal digital signature certificate

### Installation

1.  Upload the `criptapro-auth` folder to the `/wp-content/plugins/` directory
2.  Activate the plugin through the "Plugins" menu in WordPress
3.  Go to settings: **Settings → CriptaPro Auth**

### Settings

Available settings in the admin panel:

#### Basic Settings
*   **API Endpoint**: URL for external signature verification (if not using local PHP extension)
*   **Auto-create users**: If enabled, new users will be automatically registered on first login
*   **Default role**: Role assigned to new users (e.g., `subscriber`)
*   **Debug mode**: Enables technical information output to browser console and diagnostics page
    *   *Caching*: In debug mode, scripts and styles load with random version to bypass browser cache. In normal mode, plugin version is used
*   **Test mode**: Allows testing login process without real cryptographic signature verification (WARNING: Do not use on production!)

#### Security
*   **Trusted CA (Whitelist)**: List of certificate issuers allowed to log in. Restricts access to employees of specific organizations only (e.g., FTS)
    
    **How Whitelist works:**
    You enter key phrases in settings, one per line. The plugin checks the Issuer field of the certificate. If at least one of your phrases is found in the issuer string, the certificate is considered trusted.

    *Example:* In settings you specified:
    > Federal Tax Service
    > Tensor

    *   Certificate A: Issuer `CN=Federal Tax Service, O=Federal Tax Service...` → **ACCESS GRANTED** (match found "Federal Tax Service")
    *   Certificate B: Issuer `CN=Tensor LLC, O=Tensor LLC...` → **ACCESS GRANTED** (match found "Tensor")
    *   Certificate C: Issuer `CN=GlobalSign, O=GlobalSign nv-sa...` → **ACCESS DENIED** (no matches)

*   **Logging**: Records all login attempts (successful and failed) to a log file
    *   *Security*: Logs are protected from direct web access (`.htaccess` is automatically created)
    *   *Rotation*: Automatic log rotation enabled (stores up to 5 files of 100 KB each) to prevent disk overflow
*   **Require HTTPS**: Prohibits authorization over insecure HTTP protocol
*   **CORS Settings**: Manage Cross-Origin Resource Sharing headers for authentication API

### Shortcodes

The plugin provides shortcodes for placing authentication elements on site pages:

#### `[criptapro_login]`
Displays "Login with CryptoPro" button.
**Note:** If user is already logged in, a message will be displayed instead of the button: "Username, you are already logged in".

**Parameters:**
*   `button_text` — Button text (default: "Login with CryptoPro")
*   `show_guide` — Show brief instructions under button (`true`/`false`, default: `true`)
*   `class` — Container CSS class

**Example:**
```
[criptapro_login button_text="EDS Login" show_guide="false"]
```

#### `[criptapro_auth]`
Displays full authentication block with title and description.
**Note:** If user is already logged in, title and description are hidden, showing only the logged-in message.

**Parameters:**
*   `title` — Block title
*   `description` — Description
*   `redirect` — URL to redirect after login

**Example:**
```
[criptapro_auth title="Personal Account" description="Please authenticate"]
```

#### `[criptapro_debug]`
Displays diagnostic information about system and plugin. Visible only to administrators.

### Hooks for Developers

*   `criptapro_user_authenticated` (`$user_id`, `$cert_data`) — Fires after successful authentication
*   `criptapro_user_created` (`$user_id`, `$cert_data`) — Fires after new user registration
*   `criptapro_auth_settings_page_end` (`$criptapro_settings`) — Hook for adding additional settings sections on plugin page

### Plugin Structure

*   `criptapro-auth.php` — Main file
*   `includes/` — Business logic (authentication, validation, shortcodes, admin, AJAX)
*   `templates/` — HTML templates
*   `assets/` — JS scripts and styles

---

## 🇷🇺 Русский

### Описание

Плагин позволяет пользователям входить на сайт WordPress, используя сертификаты ЭЦП КриптоПро. Поддерживается автоматическая регистрация новых пользователей по данным из сертификата.

**Основные возможности:**
*   Авторизация по ЭЦП (ГОСТ)
*   Автоматическая регистрация пользователей
*   Проверка подписи на сервере (через php-cades или упрощенная)
*   Режим совместимости при отсутствии PHP-расширения
*   **Безопасность:** Whitelist доверенных УЦ, логирование попыток входа, поддержка HTTPS, CORS

### Требования

**На сервере:**
*   WordPress 5.0 или выше
*   PHP 8.2 или выше
*   Рекомендуется: PHP-расширение для работы с КриптоПро (например, `php-cades`), но плагин имеет режим совместимости и без него
*   **SSL сертификат (HTTPS)** - настоятельно рекомендуется для безопасности

**На клиенте (у пользователя):**
*   КриптоПро CSP
*   КриптоПро Browser Plugin
*   Действующий личный сертификат ЭЦП

### Установка

1.  Загрузите папку `criptapro-auth` в директорию `/wp-content/plugins/`
2.  Активируйте плагин через меню "Плагины" в WordPress
3.  Перейдите в настройки: **Настройки → CriptaPro Auth**

### Настройки

В админ-панели доступны следующие настройки:

#### Основные
*   **API Endpoint**: URL для внешней проверки подписи (если не используется локальное PHP-расширение)
*   **Автосоздание пользователей**: Если включено, новые пользователи будут автоматически регистрироваться при первом входе
*   **Роль по умолчанию**: Роль, которая будет назначена новым пользователям (например, `subscriber`)
*   **Режим отладки**: Включает вывод технической информации в консоль браузера и на странице диагностики
    *   *Кеширование*: В режиме отладки скрипты и стили загружаются со случайной версией для сброса браузерного кеша. В обычном режиме используется версия плагина
*   **Тест-режим**: Позволяет тестировать процесс входа без реальной проверки криптографической подписи (ВНИМАНИЕ: Не использовать на боевом сайте!)

#### Безопасность
*   **Доверенные УЦ (Whitelist)**: Список издателей сертификатов, которым разрешен вход. Позволяет ограничить доступ только сотрудникам определенных организаций (например, ФНС)
    
    **Как работает Whitelist:**
    Вы вводите в настройках ключевые фразы, по одной на строку. Плагин проверяет поле Issuer (Издатель) сертификата. Если хотя бы одна из ваших фраз содержится в строке издателя, сертификат считается доверенным.

    *Пример:* В настройках вы указали:
    > Федеральная налоговая служба
    > Тензор

    *   Сертификат А: Издатель `CN=Федеральная налоговая служба, O=Федеральная налоговая служба...` → **ДОСТУП РАЗРЕШЕН** (найдено совпадение "Федеральная налоговая служба")
    *   Сертификат Б: Издатель `CN=ООО "Компания Тензор", O=ООО "Компания Тензор"...` → **ДОСТУП РАЗРЕШЕН** (найдено совпадение "Тензор")
    *   Сертификат В: Издатель `CN=GlobalSign, O=GlobalSign nv-sa...` → **ДОСТУП ЗАПРЕЩЕН** (совпадений нет)

*   **Логирование**: Запись всех попыток входа (успешных и неудачных) в лог-файл
    *   *Безопасность*: Логи защищены от прямого доступа через веб (автоматически создается `.htaccess`)
    *   *Ротация*: Включена автоматическая ротация логов (хранится до 5 файлов по 100 КБ), чтобы предотвратить переполнение диска
*   **Требовать HTTPS**: Запрещает авторизацию по незащищенному протоколу HTTP
*   **Настройки CORS**: Управление заголовками Cross-Origin Resource Sharing для API авторизации

### Шорткоды

Плагин предоставляет шорткоды для размещения элементов авторизации на страницах сайта:

#### `[criptapro_login]`
Выводит кнопку "Войти с помощью КриптоПро".
**Примечание:** Если пользователь уже авторизован, вместо кнопки будет выведено сообщение: "Имя пользователя, вы уже залогинены".

**Параметры:**
*   `button_text` — Текст на кнопке (по умолчанию: "Войти с помощью КриптоПро")
*   `show_guide` — Показать краткую инструкцию под кнопкой (`true`/`false`, по умолчанию: `true`)
*   `class` — CSS класс контейнера

**Пример:**
```
[criptapro_login button_text="Вход по ЭЦП" show_guide="false"]
```

#### `[criptapro_auth]`
Выводит полноценный блок авторизации с заголовком и описанием.
**Примечание:** Если пользователь уже авторизован, заголовок и описание скрываются, и выводится только сообщение о том, что пользователь уже вошел в систему.

**Параметры:**
*   `title` — Заголовок блока
*   `description` — Описание
*   `redirect` — URL для перенаправления после входа

**Пример:**
```
[criptapro_auth title="Личный кабинет" description="Пожалуйста, авторизуйтесь"]
```

#### `[criptapro_debug]`
Выводит диагностическую информацию о системе и плагине. Виден только администраторам.

### Хуки для разработчиков

*   `criptapro_user_authenticated` (`$user_id`, `$cert_data`) — Срабатывает после успешной авторизации
*   `criptapro_user_created` (`$user_id`, `$cert_data`) — Срабатывает после регистрации нового пользователя
*   `criptapro_auth_settings_page_end` (`$criptapro_settings`) — Хук для добавления дополнительных секций настроек на странице плагина

### Структура плагина

*   `criptapro-auth.php` — Основной файл
*   `includes/` — Логика работы (аутентификация, валидация, шорткоды, админка, AJAX)
*   `templates/` — HTML-шаблоны
*   `assets/` — JS скрипты и стили

---

## Changelog / История изменений

### Version 1.0.5 (Dec 2, 2025)
**English:**
- Updated author name to match contributor username (vit_sh)
- Ensured consistency across all plugin files

**Русский:**
- Обновлено имя автора для соответствия contributor username (vit_sh)
- Обеспечена согласованность во всех файлах плагина

### Version 1.0.4 (Dec 2, 2025)
**English:**
- Minor improvements and bug fixes

**Русский:**
- Незначительные улучшения и исправления ошибок

### Version 1.0.3 (Dec 2, 2025)
**English:**
- Fixed contributor username in readme.txt
- Translated admin page title to English
- Removed commented script tags
- Removed screenshot files from plugin folder

**Русский:**
- Исправлен contributor username в readme.txt
- Переведен заголовок админ-страницы на английский
- Удалены закомментированные script теги
- Удалены файлы скриншотов из папки плагина

### Version 1.0.2 (Dec 2, 2025)
**English:**
- Renamed plugin to CriptaPro Auth
- Fixed inline scripts and direct file access issues
- Added bilingual English/Russian descriptions

**Русский:**
- Переименован плагин в CriptaPro Auth
- Исправлены inline скрипты и проблемы прямого доступа к файлам
- Добавлены двуязычные описания (английский/русский)

### Version 1.0.1
**English:**
- Tested compatibility with WordPress 6.9
- Added `criptapro_auth_settings_page_end` action hook

**Русский:**
- Протестирована совместимость с WordPress 6.9
- Добавлен action hook `criptapro_auth_settings_page_end`

### Version 1.0.0
**English:**
- First release

**Русский:**
- Первый релиз

---

## Acknowledgments / Благодарности

Special thanks to [CryptoPro](https://cryptopro.ru) and its developers for consultations and assistance in implementing the integration.

Выражаем благодарность компании [КриптоПро](https://cryptopro.ru) и её разработчикам за консультации и помощь в реализации интеграции.

## Contact / Контакты

For questions, contact Telegram: **@ddnitecry**  
По вопросам обращайтесь в Telegram: **@ddnitecry**

## License / Лицензия

GPLv2 or later
