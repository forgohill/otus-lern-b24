<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

$APPLICATION->SetTitle("Генерация исключения");

if (Loader::includeModule('ui')) {
    Extension::load([
        'ui.buttons',
        'ui.buttons.icons',
        'ui.forms',
        'ui.icons',
    ]);
}


$logFileName = 'exception_' . date('d.m.Y') . '.log';
$logFileRelativePath = '/local/logs/' . $logFileName;
$logFileFullPath = $_SERVER['DOCUMENT_ROOT'] . $logFileRelativePath;
$logFileExists = file_exists($logFileFullPath);

$logFileAdminUrl = '/bitrix/admin/fileman_file_edit.php?path=' . rawurlencode($logFileRelativePath) . '&full_src=Y';

$throwException = (string)($_GET['throw_exception'] ?? '') === 'Y';

if ($throwException) {
    $h = 1 / 0;
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

    .log-form-actions {
        margin-top: 22px;
    }
</style>

<div class="log-page">
    <div class="log-top-actions">
        <?php if ($logFileExists): ?>
            <a
                href="<?= htmlspecialcharsbx($logFileAdminUrl) ?>"
                class="ui-btn ui-btn-light-border ui-btn-round"
                target="_blank">
                Открыть лог исключений
            </a>
        <?php else: ?>
            <button
                type="button"
                class="ui-btn ui-btn-light-border ui-btn-disabled ui-btn-round">
                Файл лога исключений отсутствует
            </button>
        <?php endif; ?>

        <a href="/homeworks/homework2/index.php" class="ui-btn ui-btn-light-border ui-btn-round">
            <span class="ui-btn-text">Вернуться к ДЗ #2: Отладка и логирование</span>
        </a>
    </div>

    <div class="log-card">
        <div class="log-card-header">
            <h1 class="log-card-title">Генерация исключения</h1>
            <p class="log-card-description">
                Нажми кнопку ниже, чтобы специально сгенерировать исключение и проверить запись в лог исключений.
            </p>
        </div>

        <form method="get"
            target="_blank"
            id="exception-form">
            <input type="hidden" name="throw_exception" value="Y">

            <div class="log-form-actions">
                <button
                    type="submit"
                    class="ui-btn ui-btn-danger ui-btn-round"
                    id="exception-submit-btn">
                    Сгенерировать исключение
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('exception-form');
        const button = document.getElementById('exception-submit-btn');

        if (!form) {
            return;
        }

        form.addEventListener('submit', function() {
            if (button) {
                button.disabled = true;
                button.textContent = 'Генерация...';
            }

            setTimeout(function() {
                window.location.reload();
            }, 2000);
        });
    });
</script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>