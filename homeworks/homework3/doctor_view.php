<?php

declare(strict_types=1);

use App\Clinic\Repository\DoctorRepository;
use App\Clinic\Repository\ProcedureRepository;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('Карточка врача');

Loader::includeModule('ui');
Loader::includeModule('iblock');

Extension::load([
  'ui.buttons',
  'ui.fonts.opensans',
  'ui.icon-set.main',
]);

$doctorRepository = new DoctorRepository();
$procedureRepository = new ProcedureRepository();

$doctorId = isset($_GET['ID']) ? (int)$_GET['ID'] : 0;

$doctor = [];
$procedures = [];
$errors = [];

if ($doctorId <= 0) {
  $errors[] = 'Не передан ID врача.';
} else {
  try {
    /**
     * 1 запрос:
     * загружаем врача и связанные ID процедур.
     */
    $doctor = $doctorRepository->getDoctorCardData($doctorId);

    if ($doctor === []) {
      $errors[] = 'Врач не найден.';
    } else {
      /**
       * 2 запрос:
       * подтягиваем сами процедуры по связанным ID.
       */
      $procedureIds = $doctor['procedure_ids'] ?? [];
      $procedureIds = is_array($procedureIds) ? array_map('intval', $procedureIds) : [];

      if (!empty($procedureIds)) {
        $procedures = $procedureRepository->getByIds($procedureIds);
      }
    }
  } catch (\Throwable $e) {
    $errors[] = 'Не удалось загрузить карточку врача: ' . $e->getMessage();
  }
}

/**
 * Собирает ФИО.
 */
function buildDoctorFullName(array $doctor): string
{
  $fullName = trim((string)($doctor['full_name'] ?? ''));

  if ($fullName !== '') {
    return $fullName;
  }

  return trim(
    ($doctor['last_name'] ?? '') . ' ' .
      ($doctor['first_name'] ?? '') . ' ' .
      ($doctor['middle_name'] ?? '')
  );
}

/**
 * Форматирует дату рождения.
 */
function formatDoctorBirthDate(?string $birthDate): string
{
  $birthDate = trim((string)$birthDate);

  if ($birthDate === '') {
    return '—';
  }

  if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $birthDate) === 1) {
    return $birthDate;
  }

  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate) === 1) {
    $date = DateTime::createFromFormat('Y-m-d', $birthDate);

    return $date instanceof DateTime ? $date->format('d.m.Y') : $birthDate;
  }

  return $birthDate;
}

$doctorFullName = $doctor !== [] ? buildDoctorFullName($doctor) : '';

$backUrl = 'index.php';
$editUrl = $doctorId > 0 ? 'doctor_form.php?ID=' . $doctorId : 'index.php';

/**
 * Пока оставляем заглушки под удаление,
 * но сами кнопки корзин НЕ убираем.
 */
$deleteDoctorAction = '';
$deleteProcedureAction = '';
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
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease;
    flex: 0 0 32px;
  }

  .doctor-view-procedure-remove:hover {
    background: #e6eaee;
    color: #525c69;
  }

  .doctor-view-procedure-remove .ui-icon-set {
    --ui-icon-set__icon-size: 30px;
    --ui-icon-set__icon-color: currentColor;
  }

  .doctor-view-empty {
    border: 1px dashed #dfe5ec;
    border-radius: 12px;
    background: #fbfcfd;
    padding: 18px 20px;
    font-size: 15px;
    line-height: 24px;
    color: #7d8691;
  }

  .doctor-view-edit-btn {
    border-color: #2fc6f6 !important;
    color: #2fc6f6 !important;
    background: #ffffff !important;
  }

  .doctor-view-edit-btn:hover {
    border-color: #2fc6f6 !important;
    background: #2fc6f6 !important;
    color: #ffffff !important;
  }

  .doctor-view-danger-btn {
    border-color: #ff5752 !important;
    color: #ff5752 !important;
    background: #ffffff !important;
  }

  .doctor-view-danger-btn:hover {
    border-color: #ff5752 !important;
    background: #ff5752 !important;
    color: #ffffff !important;
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
    border: 1px solid #f1c0c0;
    color: #a82424;
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
      Здесь выводятся основные данные врача и список процедур,
      которые он выполняет.
    </p>

    <div class="doctor-view-toolbar">
      <a href="<?= htmlspecialcharsbx($backUrl) ?>" class="ui-btn ui-btn-light-border ui-btn-round">
        <span class="ui-btn-text">Назад к списку</span>
      </a>

      <?php if ($doctor !== []): ?>
        <a href="<?= htmlspecialcharsbx($editUrl) ?>" class="ui-btn ui-btn-light-border ui-btn-round doctor-view-edit-btn">
          <span class="ui-btn-text">
            Редактировать
          </span>
        </a>

        <form action="<?= htmlspecialcharsbx($deleteDoctorAction) ?>" method="post" style="margin: 0;">
          <?= bitrix_sessid_post() ?>
          <input type="hidden" name="DOCTOR_ID" value="<?= (int)($doctor['id'] ?? 0) ?>">

          <button type="submit" class="ui-btn ui-btn-light-border ui-btn-round doctor-view-danger-btn">
            <span class="ui-btn-text">
              Удалить врача
            </span>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="doctor-view-notice doctor-view-notice--error">
      <?php foreach ($errors as $error): ?>
        <div><?= htmlspecialcharsbx($error) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($doctor !== []): ?>
    <div class="doctor-view-card">
      <div class="doctor-view-card-header">Данные врача</div>

      <div class="doctor-view-card-body">
        <div class="doctor-view-grid">
          <div class="doctor-view-field">
            <span class="doctor-view-field-label">ФИО</span>
            <p class="doctor-view-field-value">
              <?= htmlspecialcharsbx($doctorFullName !== '' ? $doctorFullName : '—') ?>
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
            <p class="doctor-view-field-value">
              <?= htmlspecialcharsbx((string)($doctor['individual_tax_number'] ?? '—')) ?>
            </p>
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
        <?php if (!empty($procedures)): ?>
          <div class="doctor-view-procedures">
            <?php foreach ($procedures as $procedure): ?>
              <div class="doctor-view-procedure-item">
                <div class="doctor-view-procedure-main">
                  <span class="doctor-view-procedure-bullet"></span>

                  <span class="doctor-view-procedure-name">
                    <?= htmlspecialcharsbx((string)($procedure['name'] ?? '')) ?>
                  </span>
                </div>

                <form action="<?= htmlspecialcharsbx($deleteProcedureAction) ?>" method="post" style="margin: 0;">
                  <?= bitrix_sessid_post() ?>
                  <input type="hidden" name="DOCTOR_ID" value="<?= (int)($doctor['id'] ?? 0) ?>">
                  <input type="hidden" name="PROCEDURE_ID" value="<?= (int)($procedure['id'] ?? 0) ?>">

                  <button
                    type="submit"
                    class="doctor-view-procedure-remove"
                    title="Удалить"
                    aria-label="Удалить">
                    <span class="ui-icon-set --trash-bin"></span>
                  </button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="doctor-view-empty">
            У этого врача пока не назначены процедуры.
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>