<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php

use App\Debug\Log;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

$APPLICATION->SetTitle("Добавление в лог");

if (Loader::includeModule('ui')) {
    Extension::load([
        'ui.buttons',
        'ui.buttons.icons',
        'ui.forms',
        'ui.icons',
    ]);
}

$logFileName = 'custom_' . date('d.m.Y') . '.log';
$logFileRelativePath = '/local/logs/' . $logFileName;
$logFileFullPath = $_SERVER['DOCUMENT_ROOT'] . $logFileRelativePath;
$logFileExists = file_exists($logFileFullPath);

$logFileAdminUrl = '/bitrix/admin/fileman_file_edit.php?path=' . rawurlencode($logFileRelativePath) . '&full_src=Y';

$message = trim((string)($_GET['message'] ?? ''));

if ($message !== '') {
    Log::addLog(
        [
            'message' => $message,
            'created_at' => date('d.m.Y H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => 'GET',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ],
        false,
        'custom',
        true
    );

    LocalRedirect($APPLICATION->GetCurPage(false));
}
?>

<style>
    html {
        scroll-behavior: smooth;
    }

    .log-page {
        max-width: 900px;
        margin: 0 auto;
        padding: 24px 0 40px;
    }

    .log-top-actions {
        margin-bottom: 16px;
    }

    .log-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        padding: 24px 28px;
    }

    .log-card-header {
        margin-bottom: 20px;
    }

    .log-card-title {
        margin: 0 0 8px 0;
        font-size: 26px;
        line-height: 1.2;
        font-weight: 600;
        color: #1f2d3d;
    }

    .log-card-description {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.5;
    }

    .log-form-label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #344054;
    }

    .log-form-actions {
        margin-top: 22px;
    }
</style>

<div class="log-page">
    <div class="log-top-actions">
        <?php if ($logFileExists): ?>
            <a
                href="<?= htmlspecialcharsbx($logFileAdminUrl) ?>"
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
        <a href="/homeworks/homework2/index.php" class="ui-btn ui-btn-light-border ui-btn-round">
            <span class="ui-btn-text">Вернуться к ДЗ #2: Отладка и логирование</span>
        </a>
    </div>

    <div class="log-card">
        <div class="log-card-header">
            <h1 class="log-card-title">Запись в лог</h1>
            <p class="log-card-description">
                Введи сообщение и нажми кнопку. Запись будет добавлена в лог за сегодняшнюю дату.
            </p>
        </div>

        <form method="get">
            <label for="message" class="log-form-label">Сообщение</label>

            <div class="ui-ctl ui-ctl-textarea ui-ctl-resize-y" style="width: 100%;">
                <textarea
                    id="message"
                    name="message"
                    class="ui-ctl-element"
                    rows="8"
                    placeholder="Например: Тестовая отправка сообщения в Лог"></textarea>
            </div>

            <div class="log-form-actions">
                <button type="submit" class="ui-btn ui-btn-success ui-btn-round">
                    Записать в лог
                </button>
            </div>
        </form>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>