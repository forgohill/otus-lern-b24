<?php

declare(strict_types=1);

use App\Clinic\ProcedureRepository;
use App\Clinic\ProcedureService;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('Форма процедуры');

Loader::includeModule('ui');

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

$errors = [];
$successMessage = '';
$cancelUrl = 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
  $action = (string)($_POST['action'] ?? '');

  try {
    $service = new ProcedureService();

    if ($action === 'save') {
      $formData['name'] = trim((string)($_POST['name'] ?? ''));
      $formData['description'] = trim((string)($_POST['description'] ?? ''));

      $result = $service->create($formData);

      if ($result['success']) {
        $successMessage = 'Процедура создана. ID: ' . $result['id'];
        $formData = [
          'name' => '',
          'description' => '',
        ];
      } else {
        $errors = $result['errors'];
      }
    } elseif ($action === 'delete') {
      $procedureId = (int)($_POST['procedure_id'] ?? 0);
      $result = $service->delete($procedureId);

      if ($result['success']) {
        $successMessage = 'Процедура удалена. ID: ' . $procedureId;
      } else {
        $errors = $result['errors'];
      }
    }
  } catch (\Throwable $exception) {
    $errors[] = $exception->getMessage();
  }
}

try {
  $procedureRepository = new ProcedureRepository();
  $existingProcedures = $procedureRepository->getList();
} catch (\Throwable $exception) {
  $existingProcedures = [];
  $errors[] = $exception->getMessage();
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

  .procedure-form-notice--error {
    background: #fff5f5;
    border: 1px solid #f3c2c2;
    color: #b42318;
  }

  .procedure-form-notice--success {
    background: #f0fff4;
    border: 1px solid #b7ebc6;
    color: #1f6b38;
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
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 20px 12px 12px;
    border-bottom: 1px solid #eef2f4;
  }

  .procedure-existing-item:last-child {
    border-bottom: none;
  }

  .procedure-existing-content {
    min-width: 0;
    flex: 1 1 auto;
  }

  .procedure-existing-name {
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
    line-height: 22px;
    font-weight: 600;
    color: #2f3b47;
  }

  .procedure-existing-description {
    font-size: 13px;
    line-height: 20px;
    color: #6b7682;
    word-break: break-word;
  }

  .procedure-delete-form {
    margin: 0;
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
    color: #7d8691;
    cursor: pointer;
    flex: 0 0 32px;
    transition: background-color 0.2s ease, color 0.2s ease;
  }

  .procedure-delete-btn:hover {
    background: #fff1f1;
    color: #d64545;
  }

  .procedure-delete-btn .ui-icon-set {
    --ui-icon-set__icon-size: 32px;
    --ui-icon-set__icon-color: currentColor;
  }
</style>

<div class="procedure-form-page">
  <div class="procedure-form-hero">
    <h1 class="procedure-form-title">Добавление процедуры</h1>

    <p class="procedure-form-text">
      На этой странице можно добавить новую процедуру и сразу увидеть текущий список процедур из инфоблока.
    </p>
  </div>

  <div class="procedure-form-card">
    <?php if ($successMessage !== ''): ?>
      <div class="procedure-form-notice procedure-form-notice--success">
        <?= htmlspecialcharsbx($successMessage) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="procedure-form-notice procedure-form-notice--error">
        <?php foreach ($errors as $error): ?>
          <div><?= htmlspecialcharsbx((string)$error) ?></div>
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
                placeholder="Например: Первичный осмотр">
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
                placeholder="Краткое описание процедуры"><?= htmlspecialcharsbx($formData['description']) ?></textarea>
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

        <a
          href="<?= htmlspecialcharsbx($cancelUrl) ?>"
          class="ui-btn ui-btn-light-border ui-btn-round">
          <span class="ui-btn-text">Назад</span>
        </a>
      </div>
    </form>

    <div class="procedure-existing-block">
      <h2 class="procedure-existing-title">Список процедур</h2>

      <p class="procedure-existing-text">
        Ниже показаны процедуры, которые уже есть в инфоблоке.
      </p>

      <div class="procedure-existing-scroll">
        <ul class="procedure-existing-list">
          <?php foreach ($existingProcedures as $procedure): ?>
            <li class="procedure-existing-item">
              <div class="procedure-existing-content">
                <span class="procedure-existing-name">
                  <?= htmlspecialcharsbx((string)($procedure['NAME'] ?? '')) ?>
                </span>

                <div class="procedure-existing-description">
                  <?= htmlspecialcharsbx((string)($procedure['DESCRIPTION'] ?? '')) ?>
                </div>
              </div>

              <form
                action=""
                method="post"
                class="procedure-delete-form"
                onsubmit="return confirm('Удалить процедуру?');">
                <?= bitrix_sessid_post() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="procedure_id" value="<?= (int)($procedure['ID'] ?? 0) ?>">

                <button
                  type="submit"
                  class="procedure-delete-btn"
                  title="Удалить процедуру"
                  aria-label="Удалить процедуру">
                  <div class="ui-icon-set --trash-bin"></div>
                </button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
