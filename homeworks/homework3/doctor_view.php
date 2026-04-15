<?php

declare(strict_types=1);

use App\Clinic\DoctorRepository;
use Bitrix\Iblock\Elements\ElementDoctorsTable;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

Loader::includeModule('ui');

Extension::load([
  'ui.buttons',
  'ui.fonts.opensans',
  'ui.icon-set.main',
]);

$doctorId = isset($_REQUEST['ID']) ? (int)$_REQUEST['ID'] : 0;
$backUrl = 'index.php';
$editUrl = 'doctor_form.php?ID=' . $doctorId;

$errors = [];
$doctor = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
  $action = (string)($_POST['action'] ?? '');

  if ($action === 'delete') {
    try {
      if ($doctorId <= 0) {
        throw new \RuntimeException('Не передан ID врача');
      }

      if (!Loader::includeModule('iblock')) {
        throw new \RuntimeException('Модуль iblock не подключен');
      }

      $deleteResult = ElementDoctorsTable::delete($doctorId);

      if (!$deleteResult->isSuccess()) {
        $errors = $deleteResult->getErrorMessages();
      } else {
        LocalRedirect($backUrl);
        exit;
      }
    } catch (\Throwable $exception) {
      $errors[] = $exception->getMessage();
    }
  }
}

try {
  if ($doctorId <= 0) {
    $errors[] = 'Не передан ID врача';
  } else {
    $doctorRepository = new DoctorRepository();
    $doctor = $doctorRepository->getViewData($doctorId);

    if ($doctor === null) {
      $errors[] = 'Врач не найден';
    }
  }
} catch (\Throwable $exception) {
  $errors[] = $exception->getMessage();
}

$pageTitle = $doctor !== null
  ? 'Карточка врача: ' . (string)($doctor['full_name'] ?? '')
  : 'Карточка врача';

$APPLICATION->SetTitle($pageTitle);

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

  .doctor-view-delete-form {
    margin: 0;
  }

  .doctor-view-toolbar .doctor-view-action-btn {
    margin: 0;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
  }

  .doctor-view-toolbar .doctor-view-action-btn--edit:hover,
  .doctor-view-toolbar .doctor-view-action-btn--edit:focus {
    background-color: #2fc6f6 !important;
    border-color: #2fc6f6 !important;
    color: #ffffff !important;
    box-shadow: 0 6px 18px rgba(47, 198, 246, 0.25);
  }

  .doctor-view-toolbar .doctor-view-action-btn--delete:hover,
  .doctor-view-toolbar .doctor-view-action-btn--delete:focus {
    background-color: #ff5752 !important;
    border-color: #ff5752 !important;
    color: #ffffff !important;
    box-shadow: 0 6px 18px rgba(255, 87, 82, 0.22);
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
    gap: 10px;
  }

  .doctor-view-procedure-item {
    border: 1px solid #eef2f4;
    border-radius: 12px;
    background: #fbfcfd;
    padding: 14px 16px;
  }

  .doctor-view-procedure-name {
    display: block;
    font-size: 15px;
    line-height: 22px;
    font-weight: 600;
    color: #2f3b47;
    margin-bottom: 4px;
  }

  .doctor-view-procedure-description {
    display: block;
    font-size: 13px;
    line-height: 20px;
    color: #6b7682;
  }

  .doctor-view-empty {
    border: 1px dashed #dfe5ec;
    border-radius: 14px;
    padding: 24px;
    background: #fbfcfd;
    color: #525c69;
  }

  .doctor-view-notice {
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 20px;
  }

  .doctor-view-notice--error {
    background: #fff5f5;
    border: 1px solid #f3c2c2;
    color: #b42318;
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
    <h1 class="doctor-view-title"><?= htmlspecialcharsbx($pageTitle) ?></h1>

    <p class="doctor-view-text">
      Здесь показаны данные врача и связанные с ним процедуры.
    </p>

    <div class="doctor-view-toolbar">
      <a href="<?= htmlspecialcharsbx($backUrl) ?>" class="ui-btn ui-btn-light-border ui-btn-round">
        <span class="ui-btn-text">Назад к списку</span>
      </a>

      <?php if ($doctor !== null): ?>
        <a
          href="<?= htmlspecialcharsbx($editUrl) ?>"
          class="ui-btn ui-btn-light-border ui-btn-round doctor-view-action-btn doctor-view-action-btn--edit">
          <span class="ui-btn-text">Редактировать</span>
        </a>

        <form
          action=""
          method="post"
          class="doctor-view-delete-form"
          onsubmit="return confirm('Удалить врача?');">
          <?= bitrix_sessid_post() ?>
          <input type="hidden" name="ID" value="<?= (int)$doctorId ?>">
          <input type="hidden" name="action" value="delete">

          <button
            type="submit"
            class="ui-btn ui-btn-light-border ui-btn-round doctor-view-action-btn doctor-view-action-btn--delete">
            <span class="ui-btn-text">Удалить врача</span>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="doctor-view-notice doctor-view-notice--error">
      <?php foreach ($errors as $error): ?>
        <div><?= htmlspecialcharsbx((string)$error) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($doctor !== null): ?>
    <div class="doctor-view-card">
      <div class="doctor-view-card-header">Данные врача</div>

      <div class="doctor-view-card-body">
        <div class="doctor-view-grid">
          <div class="doctor-view-field">
            <span class="doctor-view-field-label">ФИО</span>
            <p class="doctor-view-field-value">
              <?= htmlspecialcharsbx((string)($doctor['full_name'] ?? '')) ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="doctor-view-card">
      <div class="doctor-view-card-header">Процедуры врача</div>

      <div class="doctor-view-card-body">
        <?php if (!empty($doctor['procedures'])): ?>
          <div class="doctor-view-procedures">
            <?php foreach ($doctor['procedures'] as $procedure): ?>
              <div class="doctor-view-procedure-item">
                <span class="doctor-view-procedure-name">
                  <?= htmlspecialcharsbx((string)($procedure['NAME'] ?? '')) ?>
                </span>

                <span class="doctor-view-procedure-description">
                  <?= htmlspecialcharsbx((string)($procedure['DESCRIPTION'] ?? '')) ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="doctor-view-empty">
            У этого врача пока не выбраны процедуры.
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>