<?php

declare(strict_types=1);

use App\Clinic\Repository\ProcedureRepository;
use App\Clinic\Service\ProcedureFormService;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('Форма процедуры');

Loader::includeModule('ui');
Loader::includeModule('iblock');

Extension::load([
  'ui.forms',
  'ui.buttons',
  'ui.layout-form',
  'ui.fonts.opensans',
  'ui.icon-set.main',
]);

$formData = [
  'name' => '',
  'description' => '',
];

$existingProcedures = [];
$errors = [];
$successMessage = '';

$cancelUrl = 'index.php';

$procedureRepository = new ProcedureRepository();
$procedureFormService = new ProcedureFormService();

/**
 * Загружает существующие процедуры из инфоблока.
 *
 * Ожидаем, что ProcedureRepository::getAllForSelect()
 * вернёт массив такого вида:
 * [
 *   ['id' => 1, 'name' => 'Название процедуры'],
 *   ...
 * ]
 */
try {
  $existingProcedures = $procedureRepository->getAllForSelect();
} catch (\Throwable $e) {
  $errors[] = 'Не удалось загрузить список процедур: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
  $action = (string)($_POST['action'] ?? 'save');

  // Сохраняем введённые пользователем значения,
  // чтобы не потерять их при ошибке валидации.
  $formData['name'] = trim((string)($_POST['name'] ?? ''));
  $formData['description'] = trim((string)($_POST['description'] ?? ''));

  try {
    /**
     * Важно:
     * ProcedureFormService->save() ожидает ключи:
     * - name
     * - description
     *
     * Поэтому inputs в форме ниже тоже переведены на lower-case.
     */
    $saveResult = $procedureFormService->save($_POST);

    if (!empty($saveResult['success'])) {
      if ($action === 'save_add_more') {
        $successMessage = 'Процедура сохранена. Можно добавить следующую.';

        // Очищаем форму для следующего ввода.
        $formData = [
          'name' => '',
          'description' => '',
        ];

        // Перезагружаем список после успешного сохранения,
        // чтобы новая процедура сразу появилась ниже.
        $existingProcedures = $procedureRepository->getAllForSelect();
      } else {
        LocalRedirect($cancelUrl);
      }
    } else {
      $errors = $saveResult['errors'] ?? ['Не удалось сохранить процедуру.'];
    }
  } catch (\Throwable $e) {
    $errors[] = 'Ошибка при сохранении процедуры: ' . $e->getMessage();
  }
}
?>

<style>
  body {
    font-family: "Open Sans", Arial, sans-serif;
  }

  .procedure-form-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 24px 16px 48px;
  }

  .procedure-form-hero {
    background: #f8fafc;
    border: 1px solid #dfe5ec;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
  }

  .procedure-form-title {
    margin: 0 0 12px;
    font-size: 28px;
    line-height: 36px;
    font-weight: 700;
    color: #1f2d3d;
  }

  .procedure-form-text {
    margin: 0;
    font-size: 15px;
    line-height: 24px;
    color: #525c69;
    max-width: 720px;
  }

  .procedure-form-card {
    background: #ffffff;
    border: 1px solid #dfe5ec;
    border-radius: 16px;
    padding: 24px;
  }

  .procedure-form-notice {
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 20px;
  }

  .procedure-form-notice--success {
    background: #f0fff4;
    border: 1px solid #b7ebc6;
    color: #1f6b38;
  }

  .procedure-form-notice--error {
    background: #fff5f5;
    border: 1px solid #f1c0c0;
    color: #a82424;
  }

  .procedure-form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 24px;
    margin-bottom: 24px;
  }

  .procedure-form-actions .ui-btn {
    margin: 0;
  }

  .ui-form {
    margin-bottom: 0;
  }

  .ui-form-row:last-child {
    margin-bottom: 0;
  }

  .procedure-existing-block {
    margin-top: 28px;
    padding-top: 24px;
    border-top: 1px solid #eef2f4;
  }

  .procedure-existing-title {
    margin: 0 0 12px;
    font-size: 20px;
    line-height: 28px;
    font-weight: 700;
    color: #1f2d3d;
  }

  .procedure-existing-text {
    margin: 0 0 16px;
    font-size: 14px;
    line-height: 22px;
    color: #525c69;
  }

  .procedure-existing-scroll {
    border: 1px solid #dfe5ec;
    border-radius: 12px;
    background: #fbfcfd;
    height: 660px;
    overflow-y: auto;
  }

  .procedure-existing-list {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .procedure-existing-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 6px 20px 6px 12px;
    min-height: 32px;
    border-bottom: 1px solid #eef2f4;
  }

  .procedure-existing-item:last-child {
    border-bottom: none;
  }

  .procedure-existing-name {
    min-width: 0;
    font-size: 14px;
    line-height: 22px;
    color: #2f3b47;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .procedure-delete-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    border: none;
    border-radius: 8px;
    background: transparent;
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease;
    color: #7d8691;
    flex: 0 0 32px;
  }

  .procedure-delete-btn:hover {
    background: #e6eaee;
    color: #525c69;
  }

  .procedure-delete-btn .ui-icon-set {
    --ui-icon-set__icon-size: 32px;
    --ui-icon-set__icon-color: currentColor;
  }

  .procedure-existing-empty {
    border: 1px dashed #dfe5ec;
    border-radius: 12px;
    padding: 16px;
    background: #fbfcfd;
    font-size: 14px;
    line-height: 22px;
    color: #7d8691;
  }
</style>

<div class="procedure-form-page">
  <div class="procedure-form-hero">
    <h1 class="procedure-form-title">Добавление процедуры</h1>

    <p class="procedure-form-text">
      Здесь создаётся запись процедуры. Ниже формы выводится список уже существующих процедур.
    </p>
  </div>

  <div class="procedure-form-card">
    <?php if (!empty($successMessage)): ?>
      <div class="procedure-form-notice procedure-form-notice--success">
        <?= htmlspecialcharsbx($successMessage) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="procedure-form-notice procedure-form-notice--error">
        <?php foreach ($errors as $error): ?>
          <div><?= htmlspecialcharsbx($error) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form action="" method="post">
      <?= bitrix_sessid_post() ?>

      <div class="ui-form">
        <div class="ui-form-row">
          <div class="ui-form-label">
            <div class="ui-ctl-label-text">Название процедуры</div>
          </div>

          <div class="ui-form-content">
            <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
              <input
                type="text"
                name="name"
                class="ui-ctl-element"
                value="<?= htmlspecialcharsbx($formData['name']) ?>"
                placeholder="Введите название процедуры">
            </div>
          </div>
        </div>

        <div class="ui-form-row">
          <div class="ui-form-label">
            <div class="ui-ctl-label-text">Описание процедуры</div>
          </div>

          <div class="ui-form-content">
            <div class="ui-ctl ui-ctl-textarea ui-ctl-w100">
              <textarea
                name="description"
                class="ui-ctl-element"
                rows="6"
                placeholder="Введите описание процедуры"><?= htmlspecialcharsbx($formData['description']) ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="procedure-form-actions">
        <button
          type="submit"
          name="action"
          value="save"
          class="ui-btn ui-btn-success ui-btn-round">
          <span class="ui-btn-text">Сохранить</span>
        </button>

        <button
          type="submit"
          name="action"
          value="save_add_more"
          class="ui-btn ui-btn-primary ui-btn-round">
          <span class="ui-btn-text">Сохранить и добавить еще</span>
        </button>

        <a
          href="<?= htmlspecialcharsbx($cancelUrl) ?>"
          class="ui-btn ui-btn-light-border ui-btn-round">
          <span class="ui-btn-text">Отмена</span>
        </a>
      </div>
    </form>

    <div class="procedure-existing-block">
      <h2 class="procedure-existing-title">Существующие процедуры</h2>

      <p class="procedure-existing-text">
        В списке показываются только названия процедур.
      </p>

      <?php if (!empty($existingProcedures)): ?>
        <div class="procedure-existing-scroll">
          <ul class="procedure-existing-list">
            <?php foreach ($existingProcedures as $procedure): ?>
              <li class="procedure-existing-item">
                <span class="procedure-existing-name">
                  <?= htmlspecialcharsbx((string)($procedure['name'] ?? '')) ?>
                </span>

                <button
                  type="button"
                  class="procedure-delete-btn"
                  title="Удалить"
                  aria-label="Удалить">
                  <div class="ui-icon-set --trash-bin"></div>
                </button>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php else: ?>
        <div class="procedure-existing-empty">
          Процедуры пока не добавлены.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>