<?php

declare(strict_types=1);

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
require __DIR__ . '/demo_data.php';

$APPLICATION->SetTitle('Карточка врача');

Loader::includeModule('ui');

Extension::load([
  'ui.buttons',
  'ui.fonts.opensans',
  'ui.icon-set.main',
]);

$doctorId = isset($_GET['ID']) ? (int)$_GET['ID'] : 0;
$doctor = homework3GetDoctorCardData($doctorId);
$procedures = homework3GetProceduresByIds($doctor['procedure_ids'] ?? []);
$demoNotice = homework3GetDemoNotice();

$backUrl = 'index.php';
$editUrl = 'doctor_form.php?ID=' . (int)($doctor['id'] ?? 0);

function formatDoctorBirthDate(?string $birthDate): string
{
  $birthDate = trim((string)$birthDate);

  return $birthDate !== '' ? $birthDate : 'Демо-заглушка';
}
?>

<style>
  body {
    font-family: "Open Sans", Arial, sans-serif;
  }

  .doctor-view-page {
    max-width: 980px;
    margin: 0 auto;
    padding: 24px 16px 48px;
  }

  .doctor-view-hero {
    background: #f8fafc;
    border: 1px solid #dfe5ec;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
  }

  .doctor-view-title {
    margin: 0 0 12px;
    font-size: 28px;
    line-height: 36px;
    font-weight: 700;
    color: #1f2d3d;
  }

  .doctor-view-text {
    margin: 0;
    font-size: 15px;
    line-height: 24px;
    color: #525c69;
    max-width: 760px;
  }

  .doctor-view-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
  }

  .doctor-view-card {
    background: #ffffff;
    border: 1px solid #dfe5ec;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
  }

  .doctor-view-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid #eef2f4;
    font-size: 20px;
    line-height: 28px;
    font-weight: 600;
    color: #1f2d3d;
    background: #fff;
  }

  .doctor-view-card-body {
    padding: 24px;
  }

  .doctor-view-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
  }

  .doctor-view-field {
    border: 1px solid #eef2f4;
    border-radius: 14px;
    background: #fbfcfd;
    padding: 18px 20px;
  }

  .doctor-view-field-label {
    display: inline-block;
    margin-bottom: 8px;
    font-size: 12px;
    line-height: 18px;
    font-weight: 600;
    color: #7d8691;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .doctor-view-field-value {
    margin: 0;
    font-size: 17px;
    line-height: 26px;
    font-weight: 600;
    color: #1f2d3d;
    word-break: break-word;
  }

  .doctor-view-procedures {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .doctor-view-procedure-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border: 1px solid #eef2f4;
    border-radius: 12px;
    background: #fbfcfd;
    padding: 6px 14px 6px 16px;
  }

  .doctor-view-procedure-main {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    flex: 1 1 auto;
  }

  .doctor-view-procedure-bullet {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #3bc8f5;
    flex: 0 0 8px;
  }

  .doctor-view-procedure-name {
    font-size: 15px;
    line-height: 24px;
    color: #2f3b47;
  }

  .doctor-view-procedure-remove {
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
    opacity: 0.45;
    cursor: not-allowed;
    flex: 0 0 32px;
  }

  .doctor-view-procedure-remove .ui-icon-set {
    --ui-icon-set__icon-size: 30px;
    --ui-icon-set__icon-color: currentColor;
  }

  .doctor-view-danger-btn[disabled] {
    opacity: 0.45;
    cursor: not-allowed;
  }

  .doctor-view-notice {
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 20px;
    background: #f0f7ff;
    border: 1px solid #b9d6f7;
    color: #1d5f98;
  }

  @media (max-width: 768px) {
    .doctor-view-grid {
      grid-template-columns: 1fr;
    }

    .doctor-view-title {
      font-size: 24px;
      line-height: 32px;
    }
  }
</style>

<div class="doctor-view-page">
  <div class="doctor-view-hero">
    <h1 class="doctor-view-title">Карточка врача</h1>

    <p class="doctor-view-text">
      Эта страница временно показывает демо-карточку. Она нужна как безопасная заглушка,
      пока данные врача, дата рождения и ИНН будут переделываться заново.
    </p>

    <div class="doctor-view-toolbar">
      <a href="<?= htmlspecialcharsbx($backUrl) ?>" class="ui-btn ui-btn-light-border ui-btn-round">
        <span class="ui-btn-text">Назад к списку</span>
      </a>

      <a href="<?= htmlspecialcharsbx($editUrl) ?>" class="ui-btn ui-btn-light-border ui-btn-round">
        <span class="ui-btn-text">Редактировать</span>
      </a>

      <button type="button" class="ui-btn ui-btn-light-border ui-btn-round doctor-view-danger-btn" disabled>
        <span class="ui-btn-text">Удаление позже</span>
      </button>
    </div>
  </div>

  <div class="doctor-view-notice">
    <?= htmlspecialcharsbx($demoNotice) ?>
  </div>

  <div class="doctor-view-card">
    <div class="doctor-view-card-header">Данные врача</div>

    <div class="doctor-view-card-body">
      <div class="doctor-view-grid">
        <div class="doctor-view-field">
          <span class="doctor-view-field-label">ФИО</span>
          <p class="doctor-view-field-value">
            <?= htmlspecialcharsbx((string)($doctor['full_name'] ?? 'Демо-врач')) ?>
          </p>
        </div>

        <div class="doctor-view-field">
          <span class="doctor-view-field-label">Дата рождения</span>
          <p class="doctor-view-field-value">
            <?= htmlspecialcharsbx(formatDoctorBirthDate($doctor['birth_date'] ?? '')) ?>
          </p>
        </div>

        <div class="doctor-view-field">
          <span class="doctor-view-field-label">ИНН</span>
          <p class="doctor-view-field-value">Временная заглушка</p>
        </div>

        <div class="doctor-view-field">
          <span class="doctor-view-field-label">ID врача</span>
          <p class="doctor-view-field-value">
            <?= (int)($doctor['id'] ?? 0) ?>
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="doctor-view-card">
    <div class="doctor-view-card-header">Процедуры врача</div>

    <div class="doctor-view-card-body">
      <div class="doctor-view-procedures">
        <?php foreach ($procedures as $procedure): ?>
          <div class="doctor-view-procedure-item">
            <div class="doctor-view-procedure-main">
              <span class="doctor-view-procedure-bullet"></span>

              <span class="doctor-view-procedure-name">
                <?= htmlspecialcharsbx((string)($procedure['name'] ?? '')) ?>
              </span>
            </div>

            <button
              type="button"
              class="doctor-view-procedure-remove"
              title="Демо-режим"
              aria-label="Демо-режим"
              disabled>
              <span class="ui-icon-set --trash-bin"></span>
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
