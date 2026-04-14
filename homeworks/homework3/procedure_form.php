<?php

declare(strict_types=1);

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use App\Clinic\ProcedureRepository;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
require __DIR__ . '/demo_data.php';

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

$existingProcedures = homework3GetProcedureRows();
$demoNotice = homework3GetDemoNotice();
$submitNotice = '';

$cancelUrl = 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
  $formData['name'] = trim((string)($_POST['name'] ?? ''));
  $formData['description'] = trim((string)($_POST['description'] ?? ''));
  $submitNotice = homework3GetDemoSubmitNotice();
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

  .procedure-form-notice--info {
    background: #f0f7ff;
    border: 1px solid #b9d6f7;
    color: #1d5f98;
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
    opacity: 0.45;
    cursor: not-allowed;
    flex: 0 0 32px;
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
      Форма процедуры тоже временно переведена в демо-режим. Она нужна как текстовая
      заглушка, чтобы страница открывалась без связки с инфоблоком и сервисами сохранения.
    </p>
  </div>

  <div class="procedure-form-card">
    <div class="procedure-form-notice procedure-form-notice--info">
      <?= htmlspecialcharsbx($demoNotice) ?>
    </div>

    <?php if ($submitNotice !== ''): ?>
      <div class="procedure-form-notice procedure-form-notice--success">
        <?= htmlspecialcharsbx($submitNotice) ?>
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
                placeholder="Демо: название процедуры">
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
                placeholder="Демо: краткое описание процедуры"><?= htmlspecialcharsbx($formData['description']) ?></textarea>
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
          <span class="ui-btn-text">Проверить форму</span>
        </button>

        <a
          href="<?= htmlspecialcharsbx($cancelUrl) ?>"
          class="ui-btn ui-btn-light-border ui-btn-round">
          <span class="ui-btn-text">Назад</span>
        </a>
      </div>
    </form>

    <div class="procedure-existing-block">
      <h2 class="procedure-existing-title">Демо-список процедур</h2>

      <p class="procedure-existing-text">
        Ниже показаны временные записи-заглушки, чтобы правая часть страницы не была пустой.
      </p>

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
                title="Демо-режим"
                aria-label="Демо-режим"
                disabled>
                <div class="ui-icon-set --trash-bin"></div>
              </button>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>