<?php

declare(strict_types=1);

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle((string)Loc::getMessage('CLINIC_INDEX_TITLE'));

Loader::includeModule('ui');

Extension::load([
  'ui.buttons',
  'ui.fonts.opensans',
  'ui.icon-set.main',
]);


$addDoctorUrl = 'doctor_form.php';
$addProcedureUrl = 'procedure_form.php';

$doctorRepository = new \App\Clinic\DoctorRepository();

$doctors = $doctorRepository->getList();
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

  .homework-floating-top {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 1000;
  }

  .homework-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
  }

  .homework-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
  }

  .doctor-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 175px;
    border: 1px solid #eef2f4;
    border-radius: 14px;
    background: #fff;
    padding: 20px;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
  }

  .doctor-card:hover {
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    transform: translateY(-2px);
  }

  .doctor-card-top {
    margin-bottom: 18px;
  }

  .doctor-card-label {
    display: inline-block;
    margin-bottom: 10px;
    font-size: 12px;
    line-height: 18px;
    font-weight: 600;
    color: #7d8691;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .doctor-card-name {
    margin: 0 0 12px;
    font-size: 20px;
    line-height: 28px;
    font-weight: 600;
    color: #1f2d3d;
  }

  .doctor-card-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: auto;
  }

  .doctor-card-icon-btn {
    min-width: 40px;
    padding-left: 0;
    padding-right: 0;
    justify-content: center;
  }

  .doctor-card-icon-btn .ui-btn-text {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .doctor-card-icon {
    --ui-icon-set__icon-size: 16px;
  }

  .doctor-card--add {
    align-items: center;
    justify-content: center;
    text-align: center;
    text-decoration: none;
    border: 1px dashed #fff;
    background: #fff;
  }

  .doctor-card--add:hover {
    border-color: #2f7cf6;
    box-shadow: 0 10px 24px hsla(217, 92%, 57%, 0.16);
  }

  .doctor-card-add-icon {
    margin-bottom: 12px;
    font-size: 64px;
    line-height: 1;
    font-weight: 300;
    color: #2f7cf6;
  }

  .doctor-card-add-title {
    margin-bottom: 8px;
    font-size: 22px;
    line-height: 30px;
    font-weight: 700;
    color: #1f2d3d;
  }

  .doctor-card-add-text {
    max-width: 220px;
    font-size: 14px;
    line-height: 22px;
    color: #5c6b7a;
  }

  .homework-empty {
    border: 1px dashed #dfe5ec;
    border-radius: 14px;
    padding: 24px;
    background: #fbfcfd;
    margin-bottom: 16px;
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

    .homework-toolbar {
      flex-direction: column;
      align-items: flex-start;
    }

    .doctor-card-add-icon {
      font-size: 56px;
    }
  }
</style>

<div class="homework-page" id="top">
  <div class="homework-hero">
    <h1 class="homework-title"><?php $APPLICATION->ShowTitle(); ?></h1>

    <div>
      <h2 class="homework-subtitle"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_SUBTITLE')) ?></h2>

      <p class="homework-text">
        <?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_DESCRIPTION')) ?>
      </p>
    </div>

    <div class="homework-toolbar">
      <a href="/homeworks/index.php" class="ui-btn ui-btn-light-border ui-btn-round">
        <span class="ui-btn-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_BACK_TO_HOMEWORKS')) ?></span>
      </a>

      <a href="<?= htmlspecialcharsbx($addDoctorUrl) ?>" class="ui-btn ui-btn-success ui-btn-round">
        <span class="ui-btn-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_ADD_DOCTOR')) ?></span>
      </a>

      <a href="<?= htmlspecialcharsbx($addProcedureUrl) ?>" class="ui-btn ui-btn-primary ui-btn-round">
        <span class="ui-btn-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_ADD_PROCEDURE')) ?></span>
      </a>
    </div>
  </div>

  <div class="homework-section" id="doctors">


    <div class="homework-section-body">


      <div class="homework-cards">
        <?php foreach ($doctors as $doctor): ?>
          <?php
          $doctorViewUrl = 'doctor_view.php?ID=' . (int)($doctor['id'] ?? 0);
          $doctorEditUrl = 'doctor_form.php?ID=' . (int)($doctor['id'] ?? 0);
          $doctorFullName = trim((string)($doctor['full_name'] ?? ''));
          ?>

          <div class="doctor-card">
            <div class="doctor-card-top">
              <span class="doctor-card-label"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_DOCTOR_LABEL')) ?></span>

              <h3 class="doctor-card-name">
                <?= htmlspecialcharsbx($doctorFullName !== '' ? $doctorFullName : (string)Loc::getMessage('CLINIC_INDEX_DOCTOR_NAME_EMPTY')) ?>
              </h3>
            </div>

            <div class="doctor-card-actions">
              <a href="<?= htmlspecialcharsbx($doctorViewUrl) ?>" class="ui-btn ui-btn-light-border ui-btn-round">
                <span class="ui-btn-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_DOCTOR_VIEW')) ?></span>
              </a>

              <a
                href="<?= htmlspecialcharsbx($doctorEditUrl) ?>"
                class="ui-btn ui-btn-light-border ui-btn-round doctor-card-icon-btn"
                title="<?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_DOCTOR_EDIT_TITLE')) ?>"
                aria-label="<?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_DOCTOR_EDIT_TITLE')) ?>">
                <span class="ui-btn-text">
                  <div class="ui-icon-set --edit-pencil doctor-card-icon"></div>
                </span>
              </a>
            </div>
          </div>
        <?php endforeach; ?>

        <a href="<?= htmlspecialcharsbx($addDoctorUrl) ?>" class="doctor-card doctor-card--add">
          <div class="doctor-card-add-icon">+</div>
          <div class="doctor-card-add-title"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_ADD_DOCTOR_CARD_TITLE')) ?></div>
          <div class="doctor-card-add-text">
            <?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_ADD_DOCTOR_CARD_TEXT')) ?>
          </div>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="homework-floating-top">
  <a href="#top" class="ui-btn ui-btn-primary ui-btn-round">
    <span class="ui-btn-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_SCROLL_TOP')) ?></span>
  </a>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>