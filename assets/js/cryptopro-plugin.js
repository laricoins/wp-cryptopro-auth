(function ($) {
    'use strict';

    if (typeof cryptopro_auth === 'undefined') {
        console.error('CryptoPro Auth: Configuration not loaded!');
        return;
    }

    console.log('CryptoPro Auth Plugin loaded', cryptopro_auth);

    class CryptoProAuth {
        constructor(container) {
            this.container = container;
            this.certificates = [];
            this.selectedCertificate = null;
            this.debugMode = cryptopro_auth.debug_mode === true || cryptopro_auth.debug_mode === '1';
            this.testMode = cryptopro_auth.test_mode === true || cryptopro_auth.test_mode === '1';
            this.debugContainer = cryptopro_auth.debug_container || '.cryptopro-debug-container';
            this.isProcessing = false;
            this.signatureType = 'attached'; // Всегда используем присоединенную подпись

            this.init();
        }

        async init() {
            this.debugLog('=== CryptoPro Auth Initialized ===');
            this.debugLog('Домен: ' + window.location.hostname);
            this.createDebugContainer();
            this.bindEvents();

            this.updateStatus(cryptopro_auth.strings.login_with_crypto, 'info');
        }

        async testCryptoProObjects() {
            this.debugLog('Тестирование объектов КриптоПро...');

            try {
                const testObjects = [
                    "CAdESCOM.CPSigner",
                    "CAdESCOM.CadesSignedData",
                    "CAdESCOM.Store"
                ];

                for (const objName of testObjects) {
                    try {
                        const obj = await new Promise((resolve, reject) => {
                            cadesplugin.async_spawn(function* (args) {
                                try {
                                    var oObj = yield cadesplugin.CreateObjectAsync(objName);
                                    args[0](oObj);
                                } catch (e) {
                                    args[1](e);
                                }
                            }, resolve, reject);
                        });
                        this.debugLog(`✓ ${objName}: доступен`, 'success');
                    } catch (error) {
                        this.debugLog(`✗ ${objName}: ${error.message}`, 'error');
                        throw new Error(`Объект ${objName} не доступен: ${error.message}`);
                    }
                }

                return true;
            } catch (error) {
                this.debugLog('Ошибка тестирования объектов: ' + error.message, 'error');
                throw error;
            }
        }

        createDebugContainer() {
            if (this.debugMode) {
                let $debugContainer = $(this.debugContainer);
                if (!$debugContainer.length) {
                    $debugContainer = $('<div class="cryptopro-debug-container"></div>').css({
                        'max-height': '200px',
                        'overflow-y': 'auto',
                        'background': '#f8f9fa',
                        'border': '1px solid #e9ecef',
                        'border-radius': '4px',
                        'padding': '10px',
                        'font-family': 'monospace',
                        'font-size': '12px',
                        'margin-top': '10px'
                    });
                    $(this.container).append($debugContainer);
                }
                $debugContainer.html(`
                    <div class="debug-info">=== ДЕБАГ РЕЖИМ ВКЛЮЧЕН ===</div>
                    <div class="debug-warning">Домен: ${window.location.hostname}</div>
                    <div class="debug-info">CAdES Plugin API подключен</div>
                `);
            }
        }

        debugLog(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logMessage = `[${timestamp}] ${message}`;

            console.log(`[CryptoPro] ${logMessage}`);

            if (this.debugMode) {
                let $debugContainer = $(this.debugContainer);
                if (!$debugContainer.length) {
                    $debugContainer = $(this.container).find(this.debugContainer);
                }
                if ($debugContainer.length) {
                    const messageClass = 'debug-' + type;
                    const colors = {
                        'info': { bg: '#d1ecf1', color: '#0c5460', border: '#0073aa' },
                        'success': { bg: '#d4edda', color: '#155724', border: '#28a745' },
                        'warning': { bg: '#fff3cd', color: '#856404', border: '#ffc107' },
                        'error': { bg: '#f8d7da', color: '#721c24', border: '#dc3545' }
                    };
                    const color = colors[type] || colors.info;

                    $debugContainer.append(`
                        <div class="${messageClass}" style="
                            padding: 4px 8px; margin: 2px 0; border-radius: 3px;
                            border-left: 3px solid ${color.border};
                            background: ${color.bg}; color: ${color.color};
                        ">${logMessage}</div>
                    `);
                    $debugContainer.scrollTop($debugContainer[0].scrollHeight);
                }
            }
        }

        bindEvents() {
            const $container = $(this.container);
            const $button = $container.find('.cryptopro-auth-btn');

            this.debugLog('Кнопка найдена: ' + $button.length);

            $button.on('click', (e) => {
                e.preventDefault();

                if (this.isProcessing) {
                    this.debugLog('Процесс уже выполняется', 'warning');
                    return;
                }

                this.debugLog('=== КЛИК ПО КНОПКЕ АВТОРИЗАЦИИ ===', 'success');
                this.startAuthProcess();
            });
        }

        updateStatus(message, type = 'info') {
            const $status = $(this.container).find('.cryptopro-auth-status');
            if ($status.length) {
                const statusClass = `status-${type}`;
                $status.removeClass('status-success status-error status-warning status-info')
                    .addClass(statusClass)
                    .html(message)
                    .show();
                this.debugLog('Статус: ' + message);
            }
        }

        async startAuthProcess() {
            if (this.isProcessing) return;

            this.isProcessing = true;
            this.debugLog('Начало процесса авторизации...');
            this.updateStatus(cryptopro_auth.strings.auth_process, 'info');

            try {
                // Загружаем сертификаты по официальному методу
                await this.loadCertificatesOfficial();

                if (this.certificates.length > 0) {
                    this.showCertificateSelector();
                } else {
                    this.updateStatus(cryptopro_auth.strings.no_certificates, 'error');
                }

            } catch (error) {
                this.debugLog('Ошибка инициализации: ' + error.message, 'error');
                this.updateStatus('Ошибка: ' + error.message, 'error');
            } finally {
                this.isProcessing = false;
            }
        }

        async loadCertificatesOfficial() {
            this.debugLog('Загрузка сертификатов (официальный метод)...');
            this.updateStatus('Поиск сертификатов...', 'info');

            this.certificates = [];

            try {
                // Используем async_spawn как в официальном примере
                const certificates = await this.loadCertificatesWithAsyncSpawn();
                this.certificates = certificates;
                this.debugLog(`Успешно загружено сертификатов: ${this.certificates.length}`, 'success');

            } catch (error) {
                this.debugLog('Ошибка загрузки сертификатов: ' + error.message, 'error');
                throw error;
            }
        }

        async loadCertificatesWithAsyncSpawn() {
            return new Promise((resolve, reject) => {
                cadesplugin.async_spawn(function* (args) {
                    try {
                        var oStore = yield cadesplugin.CreateObjectAsync("CAdESCOM.Store");
                        yield oStore.Open(
                            cadesplugin.CAPICOM_CURRENT_USER_STORE,
                            cadesplugin.CAPICOM_MY_STORE,
                            cadesplugin.CAPICOM_STORE_OPEN_MAXIMUM_ALLOWED
                        );

                        var oCertificates = yield oStore.Certificates;
                        var count = yield oCertificates.Count;

                        var certificates = [];

                        for (var i = 1; i <= count; i++) {
                            try {
                                var oCert = yield oCertificates.Item(i);
                                var subjectName = yield oCert.SubjectName;
                                var issuerName = yield oCert.IssuerName;
                                var serialNumber = yield oCert.SerialNumber;
                                var validFrom = yield oCert.ValidFromDate;
                                var validTo = yield oCert.ValidToDate;
                                var hasPrivateKey = yield oCert.HasPrivateKey();

                                // Проверяем срок действия
                                var now = new Date();
                                var isValid = new Date(validFrom) <= now && new Date(validTo) >= now;

                                if (hasPrivateKey && isValid) {
                                    certificates.push({
                                        index: i,
                                        commonName: this.parseCommonName(subjectName),
                                        subjectName: subjectName,
                                        issuerName: issuerName,
                                        serialNumber: serialNumber,
                                        validFrom: validFrom,
                                        validTo: validTo,
                                        isValid: isValid,
                                        hasPrivateKey: hasPrivateKey,
                                        displayName: this.parseCommonName(subjectName),
                                        certificate: oCert
                                    });
                                }
                            } catch (certError) {
                                // Пропускаем проблемные сертификаты
                                continue;
                            }
                        }

                        yield oStore.Close();
                        return args[0](certificates);

                    } catch (error) {
                        return args[1](error);
                    }
                }.bind(this), resolve, reject);
            });
        }

        parseCommonName(subjectName) {
            if (!subjectName) return 'Неизвестный сертификат';

            var cnMatch = subjectName.match(/CN=([^,]+)/i);
            if (cnMatch && cnMatch[1]) {
                return cnMatch[1].trim();
            }

            return subjectName;
        }

        showCertificateSelector() {
            this.debugLog('Показ выбора сертификатов...');

            const $container = $(this.container);

            $container.find('.certificate-selector').remove();

            // Создаем модальное окно в стиле КриптоПро
            const siteUrl = window.location.origin;

            let html = `
                <div class="cryptopro-confirm-dialog" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                ">
                    <div class="cryptopro-dialog-content" style="
                        background: white;
                        border: 2px solid #333;
                        border-radius: 4px;
                        padding: 20px;
                        max-width: 500px;
                        width: 90%;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                    ">
                        <div style="
                            border-bottom: 1px solid #ccc;
                            padding-bottom: 10px;
                            margin-bottom: 15px;
                            font-weight: bold;
                            font-size: 14px;
                            text-align: center;
                        ">
                            Подтверждение доступа
                        </div>
                        
                        <div style="margin-bottom: 15px; font-size: 13px; line-height: 1.5;">
                            <p style="margin-bottom: 10px;">
                                Этот веб-сайт пытается выполнить операцию с ключами или сертификатами от имени пользователя.
                            </p>
                            <p style="
                                background: #f0f0f0;
                                padding: 8px;
                                border: 1px solid #ddd;
                                font-family: monospace;
                                font-size: 11px;
                                word-break: break-all;
                                margin: 10px 0;
                            ">${siteUrl}</p>
                            <p style="margin-top: 10px; color: #666; font-size: 12px;">
                                Выполнение таких операций следует разрешать только для веб-сайтов, которым вы доверяете.
                            </p>
                            <p style="margin-top: 10px; color: #666; font-size: 11px; line-height: 1.4;">
                                Чтобы отключить данное подтверждение для конкретного веб-сайта, его можно добавить в список доверенных веб-сайтов, доступный на странице настроек через меню <strong>Пуск → КРИПТО-ПРО → Настройки ЭЦП Browser plug-in</strong>.
                            </p>
                        </div>
                        
                        <div style="
                            border-top: 1px solid #ccc;
                            padding-top: 15px;
                            margin-top: 15px;
                            text-align: center;
                            font-weight: bold;
                        ">
                            Разрешить эту операцию?
                        </div>
            `;

            if (this.certificates.length === 0) {
                html += `
                        <div style="padding: 20px; text-align: center; color: #666; margin: 15px 0;">
                            ${cryptopro_auth.strings.no_certificates}
                        </div>
                        <div style="text-align: center; margin-top: 20px;">
                            <button class="cryptopro-btn-no" style="
                                background: #dc3545;
                                color: white;
                                padding: 10px 30px;
                                border: none;
                                border-radius: 4px;
                                cursor: pointer;
                                font-size: 14px;
                                margin-right: 10px;
                            ">Нет</button>
                        </div>
                `;
            } else {
                html += `
                        <div class="certificate-list" style="
                            max-height: 200px;
                            overflow-y: auto;
                            border: 1px solid #ddd;
                            margin: 15px 0;
                            background: #fafafa;
                        ">
                `;

                this.certificates.forEach((cert, index) => {
                    // Автоматически выбираем первый сертификат, если он один
                    if (this.certificates.length === 1 && index === 0) {
                        this.selectedCertificate = cert;
                    }

                    const isSelected = (this.selectedCertificate && this.selectedCertificate === cert) ?
                        'background: #d4edda; border-left: 3px solid #28a745;' : '';

                    html += `
                        <div class="certificate-item" style="
                            padding: 10px;
                            border-bottom: 1px solid #eee;
                            cursor: pointer;
                            transition: background-color 0.2s;
                            ${isSelected}
                        " data-index="${index}">
                            <div style="font-weight: bold; font-size: 13px; color: #333;">
                                ${cert.commonName}
                            </div>
                            <div style="font-size: 11px; color: #666; margin-top: 3px;">
                                ${cert.issuerName}
                            </div>
                            <div style="font-size: 10px; color: #999; margin-top: 3px;">
                                Действует до: ${new Date(cert.validTo).toLocaleDateString('ru-RU')}
                            </div>
                        </div>
                    `;
                });

                html += `
                        </div>
                        <div style="text-align: center; margin-top: 20px;">
                            <button class="cryptopro-btn-yes" style="
                                background: #28a745;
                                color: white;
                                padding: 10px 30px;
                                border: none;
                                border-radius: 4px;
                                cursor: pointer;
                                font-size: 14px;
                                margin-right: 10px;
                            ">Да</button>
                            <button class="cryptopro-btn-no" style="
                                background: #dc3545;
                                color: white;
                                padding: 10px 30px;
                                border: none;
                                border-radius: 4px;
                                cursor: pointer;
                                font-size: 14px;
                            ">Нет</button>
                        </div>
                `;
            }

            html += `
                    </div>
                </div>
            `;

            $container.append(html);
            this.debugLog('Интерфейс выбора создан');

            // Обработчики событий
            const $dialog = $container.find('.cryptopro-confirm-dialog');
            const $certItems = $dialog.find('.certificate-item');

            $certItems.hover(
                function () { $(this).css('background-color', '#e8f4f8'); },
                function () { $(this).css('background-color', ''); }
            );

            $certItems.on('click', (e) => {
                const index = $(e.currentTarget).data('index');
                this.selectedCertificate = this.certificates[index];

                $certItems.css('background', '').css('border-left', '');
                $(e.currentTarget).css('background', '#d4edda').css('border-left', '3px solid #28a745');

                this.debugLog(`Выбран сертификат: ${this.selectedCertificate.commonName}`);
            });

            // Кнопка "Да"
            $dialog.find('.cryptopro-btn-yes').on('click', () => {
                // Если сертификат не выбран, но есть только один - выбираем его
                if (!this.selectedCertificate && this.certificates.length === 1) {
                    this.selectedCertificate = this.certificates[0];
                }

                if (this.selectedCertificate) {
                    this.debugLog('Начало входа с выбранным сертификатом...');
                    $dialog.remove();
                    this.performLogin();
                } else {
                    alert('Выберите сертификат');
                }
            });

            // Кнопка "Нет"
            $dialog.find('.cryptopro-btn-no').on('click', () => {
                this.debugLog('Операция отменена пользователем');
                $dialog.remove();
                this.updateStatus('Операция отменена', 'warning');
                this.isProcessing = false;
            });

            // Закрытие по клику вне диалога
            $dialog.on('click', (e) => {
                if ($(e.target).hasClass('cryptopro-confirm-dialog')) {
                    $dialog.find('.cryptopro-btn-no').click();
                }
            });
        }

        async performLogin() {
            if (!this.selectedCertificate) {
                this.updateStatus('Выберите сертификат', 'error');
                return;
            }

            if (this.isProcessing) return;
            this.isProcessing = true;

            try {
                // Подготавливаем данные для подписи
                const authData = {
                    timestamp: new Date().toISOString(),
                    nonce: Math.random().toString(36).substring(2, 15),
                    action: 'cryptopro_auth',
                    site_url: window.location.href,
                    certificate_subject: this.selectedCertificate.subjectName,
                    signature_type: this.signatureType
                };

                const dataToSign = JSON.stringify(authData);
                this.debugLog('Данные для подписи подготовлены');

                let signature = '';

                // В тест-моде пропускаем подписание
                if (this.testMode) {
                    this.debugLog('Тест-режим: пропуск подписания', 'warning');
                    this.updateStatus('Тест-режим: отправка без подписи...', 'info');
                } else {
                    // Проверяем доступность объектов
                    this.debugLog('Проверка доступности объектов КриптоПро...');
                    await this.testCryptoProObjects();

                    // Создаем подпись (всегда присоединенная)
                    this.updateStatus('Подписание данных...', 'info');
                    this.debugLog('Начинаем процесс подписания...');
                    signature = await this.createAttachedSignature(this.selectedCertificate.subjectName, dataToSign);

                    this.debugLog('✅ Данные успешно подписаны', 'success');
                }


                this.updateStatus('Отправка данных на сервер...', 'info');

                // Отправляем на сервер
                const response = await this.sendAuthRequest(dataToSign, signature, this.signatureType);

                if (response.success) {
                    this.debugLog('✅ Авторизация успешна', 'success');
                    this.updateStatus(cryptopro_auth.strings.auth_success, 'success');

                    // Используем редирект из ответа сервера или перезагружаем страницу
                    const redirectUrl = response.data && response.data.redirect_url ? response.data.redirect_url : window.location.href;

                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1000);

                } else {
                    throw new Error(response.data || 'Ошибка сервера');
                }

            } catch (error) {
                let errorMessage = this.getFriendlyErrorMessage(error);

                this.debugLog('❌ Ошибка: ' + (error.message || 'неизвестная ошибка'), 'error');
                this.updateStatus('Ошибка: ' + errorMessage, 'error');
            } finally {
                this.isProcessing = false;
            }
        }

        // Метод для присоединенной подписи (данные включаются в подпись)
        async createAttachedSignature(certSubjectName, dataToSign) {
            this.debugLog('Создание присоединенной подписи...');

            return new Promise((resolve, reject) => {
                cadesplugin.async_spawn(function* (args) {
                    try {
                        // Используем уже выбранный сертификат напрямую
                        var oSigner = yield cadesplugin.CreateObjectAsync("CAdESCOM.CPSigner");
                        yield oSigner.propset_Certificate(this.selectedCertificate.certificate);
                        // Отключаем проверку отзыва сертификата, чтобы избежать ошибок при недоступности сервера OCSP/CRL
                        yield oSigner.propset_CheckCertificate(false);

                        var oSignedData = yield cadesplugin.CreateObjectAsync("CAdESCOM.CadesSignedData");
                        yield oSignedData.propset_Content(dataToSign);

                        this.debugLog('Вызываем SignCades...');
                        var sSignedMessage = yield oSignedData.SignCades(
                            oSigner,
                            cadesplugin.CADESCOM_CADES_BES
                        );

                        this.debugLog('Подпись создана успешно');
                        return args[0](sSignedMessage);

                    } catch (e) {
                        this.debugLog('Ошибка в createAttachedSignature: ' + e.message, 'error');

                        var errorMessage = this.getFriendlyErrorMessage(e);
                        var err = cadesplugin.getLastError ? cadesplugin.getLastError(e) : errorMessage;
                        return args[1](err);
                    }
                }.bind(this), resolve, reject);
            });
        }

        async sendAuthRequest(data, signature, signatureType) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: cryptopro_auth.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'cryptopro_auth',
                        nonce: cryptopro_auth.nonce,
                        signed_data: data,
                        signature: signature,
                        signature_type: signatureType,
                        certificate: JSON.stringify({
                            subjectName: this.selectedCertificate.subjectName,
                            commonName: this.selectedCertificate.commonName,
                            issuerName: this.selectedCertificate.issuerName,
                            serialNumber: this.selectedCertificate.serialNumber
                        })
                    },
                    success: (response) => {
                        if (response && typeof response === 'object') {
                            resolve(response);
                        } else {
                            reject(new Error('Некорректный ответ сервера'));
                        }
                    },
                    error: (xhr, status, error) => {
                        this.debugLog('Ошибка AJAX: ' + error, 'error');
                        reject(new Error(cryptopro_auth.strings.network_error + ': ' + error));
                    }
                });
            });
        }

    }

    // Инициализация при загрузке документа
    $(document).ready(() => {
        // Ждем инициализации cadesplugin как в официальном примере
        if (window.cadesplugin) {
            cadesplugin.then(function () {
                $('.cryptopro-auth-container').each(function () {
                    new CryptoProAuth(this);
                });
            }).catch(function (err) {
                console.error('CryptoPro Auth: Failed to initialize cadesplugin', err);
            });
        } else {
            console.error('CryptoPro Auth: cadesplugin not found');
        }
    });

})(jQuery);