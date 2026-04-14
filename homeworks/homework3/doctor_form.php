<?php

declare(strict_types=1);

use App\Clinic\Repository\DoctorRepository;
use App\Clinic\Repository\ProcedureRepository;
use App\Clinic\Service\DoctorFormService;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$doctorId = isset($_GET['ID']) ? (int)$_GET['ID'] : 0;
$isEditMode = $doctorId > 0;

$APPLICATION->SetTitle($isEditMode ? 'Редактирование врача' : 'Добавление врача');

Loader::includeModule('ui');
Loader::includeModule('iblock');

Extension::load([
  'ui.buttons',
  'ui.forms',
  'ui.fonts.opensans',
]);

$doctorRepository = new DoctorRepository();
$procedureRepository = new ProcedureRepository();
$doctorFormService = new DoctorFormService();

$formData = [
  'LAST_NAME' => '',
  'FIRST_NAME' => '',
  'MIDDLE_NAME' => '',
  'BIRTH_DATE' => '',
  'INN' => '',
  'PROCEDURES' => [],
];

$errors = [];
$procedures = [];

/**
 * Приводит дату к формату value для input[type="date"].
 * Поддерживает:
 * - d.m.Y
 * - Y-m-d
 */
function normalizeDateForInput(?string $value): string
{
  $value = trim((string)$value);

  if ($value === '') {
    return '';
  }

  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
    return $value;
  }

  if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value) === 1) {
    $date = DateTime::createFromFormat('d.m.Y', $value);

    return $date instanceof DateTime ? $date->format('Y-m-d') : '';
  }

  return '';
}

try {
  /**
   * Ожидаем формат:
   * [
   *   5 => 'Название процедуры',
   *   7 => 'Название процедуры 2',
   * ]
   */
  $procedures = $procedureRepository->getAllNames();
} catch (\Throwable $e) {
  $errors[] = 'Не удалось загрузить список процедур: ' . $e->getMessage();
}

if ($isEditMode) {
  try {
    $doctor = $doctorRepository->getDoctorForEdit($doctorId);

    if ($doctor === []) {
      $errors[] = 'Врач не найден.';
    } else {
      $formData = [
        'LAST_NAME' => (string)($doctor['last_name'] ?? ''),
        'FIRST_NAME' => (string)($doctor['first_name'] ?? ''),
        'MIDDLE_NAME' => (string)($doctor['middle_name'] ?? ''),
        'BIRTH_DATE' => normalizeDateForInput((string)($doctor['birth_date'] ?? '')),
        'INN' => (string)($doctor['individual_tax_number'] ?? ''),
        'PROCEDURES' => array_map('intval', $doctor['procedure_ids'] ?? []),
      ];
    }
  } catch (\Throwable $e) {
    $errors[] = 'Не удалось загрузить данные врача: ' . $e->getMessage();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
  $formData['LAST_NAME'] = trim((string)($_POST['LAST_NAME'] ?? ''));
  $formData['FIRST_NAME'] = trim((string)($_POST['FIRST_NAME'] ?? ''));
  $formData['MIDDLE_NAME'] = trim((string)($_POST['MIDDLE_NAME'] ?? ''));
  $formData['BIRTH_DATE'] = trim((string)($_POST['BIRTH_DATE'] ?? ''));
  $formData['INN'] = trim((string)($_POST['INN'] ?? ''));

  $postedProcedures = $_POST['PROCEDURES'] ?? [];
  $postedProcedures = is_array($postedProcedures) ? array_map('intval', $postedProcedures) : [];

  /**
   * Оставляем только реально существующие ID процедур.
   */
  $formData['PROCEDURES'] = array_values(array_filter(
    $postedProcedures,
    static fn(int $id): bool => isset($procedures[$id])
  ));

  try {
    $saveResult = $doctorFormService->save($formData, $isEditMode ? $doctorId : null);

    if (!empty($saveResult['success'])) {
      LocalRedirect('doctor_view.php?ID=' . (int)$saveResult['id']);
    }

    $errors = $saveResult['errors'] ?? ['Не удалось сохранить врача.'];
  } catch (\Throwable $e) {
    $errors[] = 'Ошибка при сохранении врача: ' . $e->getMessage();
  }
}

$cancelUrl = $isEditMode
  ? 'doctor_view.php?ID=' . $doctorId
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

  .doctor-form-notice--error {
    background: #fff5f5;
    border: 1px solid #f1c0c0;
    color: #a82424;
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
      Форма работает с бизнес-данными врача: фамилия, имя, отчество, дата рождения, ИНН и процедуры.
    </p>
  </div>

  <div class="doctor-form-card">
    <?php if (!empty($errors)): ?>
      <div class="doctor-form-notice doctor-form-notice--error">
        <?php foreach ($errors as $error): ?>
          <div><?= htmlspecialcharsbx($error) ?></div>
        <?php endforeach; ?>
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
              placeholder="Введите фамилию">
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
              placeholder="Введите имя">
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
              placeholder="Введите отчество">
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
              placeholder="Введите ИНН">
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
            Для выбора нескольких процедур удерживай Ctrl или Cmd.
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
          <span class="ui-btn-text">Сохранить</span>
        </button>

        <a href="<?= htmlspecialcharsbx($cancelUrl) ?>" class="ui-btn ui-btn-light-border ui-btn-round">
          <span class="ui-btn-text">Отмена</span>
        </a>
      </div>
    </form>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>