<?php

declare(strict_types=1);

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
require __DIR__ . '/demo_data.php';

$doctorId = isset($_GET['ID']) ? (int)$_GET['ID'] : 0;
$isEditMode = $doctorId > 0;

$APPLICATION->SetTitle($isEditMode ? 'Редактирование врача' : 'Добавление врача');

Loader::includeModule('ui');

Extension::load([
  'ui.buttons',
  'ui.forms',
  'ui.fonts.opensans',
]);

$formData = $isEditMode
  ? homework3GetDoctorForEdit($doctorId)
  : homework3GetDoctorDraftFormData();

$procedures = homework3GetProcedureNames();
$demoNotice = homework3GetDemoNotice();
$submitNotice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
  $formData['LAST_NAME'] = trim((string)($_POST['LAST_NAME'] ?? ''));
  $formData['FIRST_NAME'] = trim((string)($_POST['FIRST_NAME'] ?? ''));
  $formData['MIDDLE_NAME'] = trim((string)($_POST['MIDDLE_NAME'] ?? ''));
  $formData['BIRTH_DATE'] = trim((string)($_POST['BIRTH_DATE'] ?? ''));
  $formData['INN'] = trim((string)($_POST['INN'] ?? ''));

  $postedProcedures = $_POST['PROCEDURES'] ?? [];
  $postedProcedures = is_array($postedProcedures) ? array_map('intval', $postedProcedures) : [];

  $formData['PROCEDURES'] = array_values(array_filter(
    $postedProcedures,
    static fn(int $id): bool => isset($procedures[$id])
  ));

  $submitNotice = homework3GetDemoSubmitNotice();
}

$cancelUrl = $isEditMode
  ? 'doctor_view.php?ID=' . (int)$doctorId
  : 'index.php';

$selectedProcedureNames = [];
foreach ($formData['PROCEDURES'] as $procedureId) {
  if (isset($procedures[$procedureId])) {
    $selectedProcedureNames[] = $procedures[$procedureId];
  }
}
?>

<style>
  body {
    font-family: "Open Sans", Arial, sans-serif;
  }

  .doctor-form-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 24px 16px 48px;
  }

  .doctor-form-hero {
    background: #f8fafc;
    border: 1px solid #dfe5ec;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
  }

  .doctor-form-title {
    margin: 0 0 12px;
    font-size: 28px;
    line-height: 36px;
    font-weight: 700;
    color: #1f2d3d;
  }

  .doctor-form-text {
    margin: 0;
    font-size: 15px;
    line-height: 24px;
    color: #525c69;
    max-width: 720px;
  }

  .doctor-form-card {
    background: #ffffff;
    border: 1px solid #dfe5ec;
    border-radius: 16px;
    padding: 24px;
  }

  .doctor-form-notice {
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 20px;
  }

  .doctor-form-notice--info {
    background: #f0f7ff;
    border: 1px solid #b9d6f7;
    color: #1d5f98;
  }

  .doctor-form-notice--success {
    background: #f0fff4;
    border: 1px solid #b7ebc6;
    color: #1f6b38;
  }

  .doctor-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px 20px;
  }

  .doctor-form-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .doctor-form-field--full {
    grid-column: 1 / -1;
  }

  .doctor-form-field .ui-ctl {
    width: 100%;
    max-width: 100%;
  }

  .doctor-form-label {
    font-size: 14px;
    line-height: 20px;
    font-weight: 600;
    color: #2f3b47;
  }

  .doctor-form-hint {
    font-size: 13px;
    line-height: 18px;
    color: #7d8691;
  }

  .doctor-form-selected {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
  }

  .doctor-form-selected-item {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 999px;
    background: #eef2f4;
    color: #2f3b47;
    font-size: 13px;
    line-height: 18px;
    font-weight: 600;
  }

  .doctor-form-selected-empty {
    margin-top: 10px;
    font-size: 13px;
    line-height: 18px;
    color: #7d8691;
  }

  .doctor-form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 24px;
  }

  @media (max-width: 768px) {
    .doctor-form-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="doctor-form-page">
  <div class="doctor-form-hero">
    <h1 class="doctor-form-title">
      <?= $isEditMode ? 'Редактирование врача' : 'Добавление врача' ?>
    </h1>

    <p class="doctor-form-text">
      Форма переведена в безопасный демо-режим. Поля даты рождения и ИНН пока
      оставлены как текстовые заглушки, чтобы страница не падала во время переделки проекта.
    </p>
  </div>

  <div class="doctor-form-card">
    <div class="doctor-form-notice doctor-form-notice--info">
      <?= htmlspecialcharsbx($demoNotice) ?>
    </div>

    <?php if ($submitNotice !== ''): ?>
      <div class="doctor-form-notice doctor-form-notice--success">
        <?= htmlspecialcharsbx($submitNotice) ?>
      </div>
    <?php endif; ?>

    <form action="" method="post">
      <?= bitrix_sessid_post() ?>

      <?php if ($isEditMode): ?>
        <input type="hidden" name="ID" value="<?= $doctorId ?>">
      <?php endif; ?>

      <div class="doctor-form-grid">
        <div class="doctor-form-field">
          <label class="doctor-form-label" for="last_name">Фамилия</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input
              type="text"
              id="last_name"
              name="LAST_NAME"
              class="ui-ctl-element"
              value="<?= htmlspecialcharsbx($formData['LAST_NAME']) ?>"
              placeholder="Демо: фамилия врача">
          </div>
        </div>

        <div class="doctor-form-field">
          <label class="doctor-form-label" for="first_name">Имя</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input
              type="text"
              id="first_name"
              name="FIRST_NAME"
              class="ui-ctl-element"
              value="<?= htmlspecialcharsbx($formData['FIRST_NAME']) ?>"
              placeholder="Демо: имя врача">
          </div>
        </div>

        <div class="doctor-form-field">
          <label class="doctor-form-label" for="middle_name">Отчество</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input
              type="text"
              id="middle_name"
              name="MIDDLE_NAME"
              class="ui-ctl-element"
              value="<?= htmlspecialcharsbx($formData['MIDDLE_NAME']) ?>"
              placeholder="Демо: отчество врача">
          </div>
        </div>

        <div class="doctor-form-field">
          <label class="doctor-form-label" for="birth_date">Дата рождения</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input
              type="date"
              id="birth_date"
              name="BIRTH_DATE"
              class="ui-ctl-element"
              value="<?= htmlspecialcharsbx($formData['BIRTH_DATE']) ?>">
          </div>

          <div class="doctor-form-hint">
            Временная заглушка. Поле оставлено только для безопасного открытия страницы.
          </div>
        </div>

        <div class="doctor-form-field doctor-form-field--full">
          <label class="doctor-form-label" for="inn">ИНН</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input
              type="text"
              id="inn"
              name="INN"
              class="ui-ctl-element"
              value="<?= htmlspecialcharsbx($formData['INN']) ?>"
              placeholder="Временная заглушка, можно не заполнять">
          </div>

          <div class="doctor-form-hint">
            Поле временно отключено от реального сохранения и оставлено как демо-текст.
          </div>
        </div>

        <div class="doctor-form-field doctor-form-field--full">
          <label class="doctor-form-label" for="procedures">Процедуры</label>

          <div class="ui-ctl ui-ctl-multiple-select">
            <select
              id="procedures"
              name="PROCEDURES[]"
              class="ui-ctl-element"
              multiple
              size="8">
              <?php foreach ($procedures as $procedureId => $procedureName): ?>
                <option
                  value="<?= (int)$procedureId ?>"
                  <?= in_array((int)$procedureId, $formData['PROCEDURES'], true) ? 'selected' : '' ?>>
                  <?= htmlspecialcharsbx($procedureName) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="doctor-form-hint">
            Здесь показан демо-список процедур, чтобы верстка формы не была пустой.
          </div>

          <?php if (!empty($selectedProcedureNames)): ?>
            <div class="doctor-form-selected">
              <?php foreach ($selectedProcedureNames as $selectedProcedureName): ?>
                <span class="doctor-form-selected-item">
                  <?= htmlspecialcharsbx($selectedProcedureName) ?>
                </span>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="doctor-form-selected-empty">
              Пока ни одна процедура не выбрана.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="doctor-form-actions">
        <button type="submit" class="ui-btn ui-btn-success ui-btn-round">
          <span class="ui-btn-text">Проверить форму</span>
        </button>

        <a href="<?= htmlspecialcharsbx($cancelUrl) ?>" class="ui-btn ui-btn-light-border ui-btn-round">
          <span class="ui-btn-text">Назад</span>
        </a>
      </div>
    </form>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
