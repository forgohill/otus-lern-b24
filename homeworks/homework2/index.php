<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle("ДЗ #2: Отладка и логирование");

Loader::includeModule('ui');
Extension::load([
    'ui.buttons',
    'ui.fonts.opensans',
]);

$logFileName = 'custom_' . date('d.m.Y') . '.log';
$logFileRelativePath = '/local/logs/' . $logFileName;
$logFileFullPath = $_SERVER['DOCUMENT_ROOT'] . $logFileRelativePath;
$logFileExists = file_exists($logFileFullPath);

$logFileIsEmpty = false;
if ($logFileExists) {
    clearstatcache(true, $logFileFullPath);
    $logFileIsEmpty = filesize($logFileFullPath) === 0;
}

$logFileAdminUrl = '/bitrix/admin/fileman_file_edit.php?path=' . rawurlencode($logFileRelativePath) . '&full_src=Y';

$logFileExceptionName = 'exception_' . date('d.m.Y') . '.log';
$logFileExceptionRelativePath = '/local/logs/' . $logFileExceptionName;
$logFileExceptionFullPath = $_SERVER['DOCUMENT_ROOT'] . $logFileExceptionRelativePath;
$logFileExceptionExists = file_exists($logFileExceptionFullPath);

$logFileExceptionIsEmpty = false;
if ($logFileExceptionExists) {
    clearstatcache(true, $logFileExceptionFullPath);
    $logFileExceptionIsEmpty = filesize($logFileExceptionFullPath) === 0;
}

$logFileExceptionAdminUrl = '/bitrix/admin/fileman_file_edit.php?path=' . rawurlencode($logFileExceptionRelativePath) . '&full_src=Y';

?>

<style>
    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: "Open Sans", Arial, sans-serif;
    }

    .homework-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px 16px 48px;
    }

    .homework-hero {
        background: #f8fafc;
        border: 1px solid #dfe5ec;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .homework-title {
        margin: 0 0 16px;
        font-size: 28px;
        line-height: 36px;
        font-weight: 700;
        color: #1f2d3d;
    }

    .homework-subtitle {
        margin: 0 0 12px;
        font-size: 20px;
        line-height: 28px;
        font-weight: 600;
        color: #2f3b47;
    }

    .homework-text {
        margin: 0 0 20px;
        font-size: 15px;
        line-height: 24px;
        color: #525c69;
        max-width: 800px;
    }

    .homework-section {
        background: #ffffff;
        border: 1px solid #dfe5ec;
        border-radius: 16px;
        margin-bottom: 24px;
        overflow: hidden;
    }

    .homework-section-header {
        padding: 18px 24px;
        border-bottom: 1px solid #eef2f4;
        font-size: 20px;
        line-height: 28px;
        font-weight: 600;
        color: #1f2d3d;
        background: #fff;
    }

    .homework-section-body {
        padding: 24px;
    }

    .homework-actions {
        margin: 0;
        padding: 0;
        list-style: none;
        border: 1px solid #eef2f4;
        border-radius: 12px;
        overflow: hidden;
    }

    .homework-actions-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 16px 18px;
        border-bottom: 1px solid #eef2f4;
        background: #fff;
    }

    .homework-actions-item:last-child {
        border-bottom: none;
    }

    .homework-actions-label {
        font-size: 15px;
        line-height: 22px;
        color: #2f3b47;
    }

    .homework-floating-top {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 1000;
    }

    .homework-btn-danger-outline-disabled {
        background: #fff !important;
        color: #f23c53 !important;
        border: 1px solid #f23c53 !important;
        box-shadow: none !important;
        opacity: 0.55;
        cursor: not-allowed;
        pointer-events: none;
    }

    .homework-btn-danger-outline-disabled:hover,
    .homework-btn-danger-outline-disabled:focus,
    .homework-btn-danger-outline-disabled:active {
        background: #fff !important;
        color: #f23c53 !important;
        border: 1px solid #f23c53 !important;
        box-shadow: none !important;
    }

    @media (max-width: 768px) {
        .homework-title {
            font-size: 24px;
            line-height: 32px;
        }

        .homework-subtitle {
            font-size: 18px;
            line-height: 26px;
        }

        .homework-actions-item {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="homework-page" id="top">
    <div class="homework-hero">
        <h1 class="homework-title"><?php $APPLICATION->ShowTitle(); ?></h1>

        <div>
            <h2 class="homework-subtitle">Пояснительная записка</h2>

            <p class="homework-text">
                В данной работе реализованы две части домашнего задания: кастомный логгер и обработка исключений.
                Главная страница объединяет обе части и предоставляет доступ к просмотру логов, записи данных,
                очистке файлов и переходу к исходному коду.
            </p>

            <p class="homework-text">
                <strong>Часть 1 — Logger.</strong> В первой части реализован кастомный логгер, который записывает
                данные в лог-файл за текущую дату. При добавлении записи в лог сохраняются сообщение пользователя
                и дополнительная служебная информация: дата и время, адрес страницы, метод запроса и IP-адрес.
                Каждая запись отделяется от следующей, поэтому лог удобно читать и анализировать.
            </p>

            <p class="homework-text">
                Очистка лога выполняется отдельным действием. При этом файл не удаляется, а очищается его содержимое,
                после чего пользователь возвращается на главную страницу задания.
            </p>

            <p class="homework-text">
                <strong>Часть 2 — Exception.</strong> Во второй части реализована проверка логирования исключений.
                При нажатии на кнопку специально вызывается ошибка, чтобы проверить работу механизма обработки
                исключений. Информация об ошибке записывается в отдельный лог-файл исключений.
            </p>

            <p class="homework-text">
                В лог исключений сохраняются основные сведения об ошибке: её тип, сообщение, файл, строка,
                код и стек вызовов. Это позволяет использовать лог для отладки и анализа причин возникновения ошибки.
            </p>

            <p class="homework-text">
                Таким образом, в работе показаны два механизма: ручная запись данных в кастомный лог
                и автоматическая запись информации об исключениях. Реализованный интерфейс позволяет
                наглядно выполнить все основные действия и проверить результат.
            </p>
        </div>

        <a href="/homeworks/index.php" class="ui-btn ui-btn-light-border ui-btn-round">
            <span class="ui-btn-text">Возврат в общее меню</span>
        </a>
    </div>

    <div class="homework-section" id="logger">
        <div class="homework-section-header">Часть 1 — Logger</div>

        <div class="homework-section-body">
            <p class="homework-text">
                Работа с кастомным логгером: просмотр файла лога, запись новых данных,
                очистка файла и переход к исходному классу.
            </p>

            <ul class="homework-actions">
                <li class="homework-actions-item">
                    <span class="homework-actions-label">Файл лога из п.1 ДЗ</span>

                    <?php if ($logFileExists): ?>
                        <a
                            href="<?= htmlspecialcharsbx($logFileAdminUrl) ?>"
                            target="_blank"
                            class="ui-btn ui-btn-light-border ui-btn-round">
                            Открыть лог за сегодня
                        </a>
                    <?php else: ?>
                        <button
                            type="button"
                            class="ui-btn ui-btn-light-border ui-btn-disabled ui-btn-round">
                            Файл лога на сегодняшнюю дату отсутствует
                        </button>
                    <?php endif; ?>
                </li>

                <li class="homework-actions-item">
                    <span class="homework-actions-label">Добавление в лог из п.1 ДЗ</span>
                    <a href="writelog.php" class="ui-btn ui-btn-success ui-btn-round">
                        <span class="ui-btn-text">Выполнить</span>
                    </a>
                </li>

                <li class="homework-actions-item">
                    <span class="homework-actions-label">Очистить лог из п.1 ДЗ</span>

                    <?php if (!$logFileExists): ?>
                        <button
                            type="button"
                            class="ui-btn ui-btn-round homework-btn-danger-outline-disabled">
                            Файл лога на сегодняшнюю дату отсутствует
                        </button>
                    <?php elseif ($logFileIsEmpty): ?>
                        <button
                            type="button"
                            class="ui-btn ui-btn-round homework-btn-danger-outline-disabled">
                            Лог за сегодня уже пуст
                        </button>
                    <?php else: ?>
                        <a
                            href="clearlog.php"
                            class="ui-btn ui-btn-danger ui-btn-round">
                            Очистить лог за сегодня
                        </a>
                    <?php endif; ?>
                </li>

                <li class="homework-actions-item">
                    <span class="homework-actions-label">Файл с классом кастомного логгера</span>
                    <a
                        href="/bitrix/admin/fileman_file_edit.php?path=%2Flocal%2FApp%2FDebug%2FLog.php&full_src=Y"
                        target="_blank"
                        class="ui-btn ui-btn-primary ui-btn-round">
                        <span class="ui-btn-text">Открыть код</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="homework-section" id="exception">
        <div class="homework-section-header">Часть 2 — Exception</div>

        <div class="homework-section-body">
            <p class="homework-text">
                Работа с логированием исключений: просмотр exception-лога, запись исключения,
                очистка файла и переход к классу обработки.
            </p>

            <ul class="homework-actions">
                <li class="homework-actions-item">
                    <span class="homework-actions-label">Файл лога из п.2 ДЗ</span>

                    <?php if ($logFileExceptionExists): ?>
                        <a
                            href="<?= htmlspecialcharsbx($logFileExceptionAdminUrl) ?>"
                            target="_blank"
                            class="ui-btn ui-btn-light-border ui-btn-round">
                            Открыть лог за сегодня
                        </a>
                    <?php else: ?>
                        <button
                            type="button"
                            class="ui-btn ui-btn-light-border ui-btn-disabled ui-btn-round">
                            Файл лога на сегодняшнюю дату отсутствует
                        </button>
                    <?php endif; ?>
                </li>

                <li class="homework-actions-item">
                    <span class="homework-actions-label">Добавление в лог из п.2 ДЗ</span>
                    <a href="writeexception.php" class="ui-btn ui-btn-success ui-btn-round">
                        <span class="ui-btn-text">Выполнить</span>
                    </a>
                </li>

                <li class="homework-actions-item">
                    <span class="homework-actions-label">Очистить лог из п.2 ДЗ</span>

                    <?php if (!$logFileExceptionExists): ?>
                        <button
                            type="button"
                            class="ui-btn ui-btn-round homework-btn-danger-outline-disabled">
                            Файл лога на сегодняшнюю дату отсутствует
                        </button>
                    <?php elseif ($logFileExceptionIsEmpty): ?>
                        <button
                            type="button"
                            class="ui-btn ui-btn-round homework-btn-danger-outline-disabled">
                            Лог за сегодня уже пуст
                        </button>
                    <?php else: ?>
                        <a
                            href="clearexception.php"
                            class="ui-btn ui-btn-danger ui-btn-round">
                            Очистить лог за сегодня
                        </a>
                    <?php endif; ?>
                </li>

                <li class="homework-actions-item">
                    <span class="homework-actions-label">Файл с классом системного исключений</span>
                    <a
                        href="/bitrix/admin/fileman_file_edit.php?path=%2Flocal%2FApp%2FDebug%2FLog.php&full_src=Y"
                        target="_blank"
                        class="ui-btn ui-btn-primary ui-btn-round">
                        <span class="ui-btn-text">Открыть код</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="homework-floating-top">
    <a href="#top" class="ui-btn ui-btn-primary ui-btn-round">
        <span class="ui-btn-text">Наверх</span>
    </a>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>