<?php

declare(strict_types=1);

use App\Clinic\DoctorRepository;
use App\Clinic\DoctorService;
use App\Clinic\ProcedureRepository;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

function homework3DoctorFormCreateDefaults(): array
{
  return [
    'last_name' => '',
    'first_name' => '',
    'middle_name' => '',
    'procedure_ids' => [],
  ];
}

function homework3DoctorFormNormalizeProcedureIds(mixed $procedureIds): array
{
  if (!is_array($procedureIds)) {
    return [];
  }

  return array_values(array_unique(array_filter(
    array_map('intval', $procedureIds),
    static fn(int $procedureId): bool => $procedureId > 0
  )));
}

function homework3DoctorFormCreateRequestData(array $request): array
{
  return [
    'last_name' => trim((string)($request['last_name'] ?? '')),
    'first_name' => trim((string)($request['first_name'] ?? '')),
    'middle_name' => trim((string)($request['middle_name'] ?? '')),
    'procedure_ids' => homework3DoctorFormNormalizeProcedureIds($request['procedure_ids'] ?? []),
  ];
}

function homework3DoctorFormCreateDoctorData(array $doctor): array
{
  return [
    'last_name' => (string)($doctor['last_name'] ?? ''),
    'first_name' => (string)($doctor['first_name'] ?? ''),
    'middle_name' => (string)($doctor['middle_name'] ?? ''),
    'procedure_ids' => is_array($doctor['procedure_ids'] ?? null)
      ? homework3DoctorFormNormalizeProcedureIds($doctor['procedure_ids'])
      : [],
  ];
}

function homework3DoctorFormLoadProcedures(): array
{
  $procedureRepository = new ProcedureRepository();

  return $procedureRepository->getList();
}

function homework3DoctorFormLoadDoctor(int $doctorId): ?array
{
  $doctorRepository = new DoctorRepository();

  return $doctorRepository->getById($doctorId);
}

function homework3DoctorFormSubmit(int $doctorId, array $formData): array
{
  $service = new DoctorService();
  $result = $doctorId > 0
    ? $service->update($doctorId, $formData)
    : $service->create($formData);

  if (!($result['success'] ?? false)) {
    return [
      'errors' => is_array($result['errors'] ?? null)
        ? $result['errors']
        : [(string)Loc::getMessage('CLINIC_DOCTOR_FORM_SAVE_ERROR')],
      'successMessage' => '',
      'formData' => $formData,
    ];
  }

  return [
    'errors' => [],
    'successMessage' => $doctorId > 0
      ? (string)Loc::getMessage('CLINIC_DOCTOR_FORM_SUCCESS_UPDATED', ['#ID#' => (string)$result['id']])
      : (string)Loc::getMessage('CLINIC_DOCTOR_FORM_SUCCESS_CREATED', ['#ID#' => (string)$result['id']]),
    'formData' => $doctorId > 0 ? $formData : homework3DoctorFormCreateDefaults(),
  ];
}

function homework3DoctorFormBuildProcedureSelectorData(array $procedures, array $selectedProcedureIds): array
{
  $dialogItems = [];
  $selectedItems = [];

  foreach ($procedures as $procedure) {
    $procedureId = (int)($procedure['ID'] ?? 0);
    $procedureName = trim((string)($procedure['NAME'] ?? ''));

    if ($procedureId <= 0 || $procedureName === '') {
      continue;
    }

    $item = [
      'id' => $procedureId,
      'entityId' => 'procedure',
      'title' => $procedureName,
      'tabs' => 'procedures',
    ];

    $dialogItems[] = $item;

    if (in_array($procedureId, $selectedProcedureIds, true)) {
      $selectedItems[] = $item;
    }
  }

  return [
    'dialogItems' => $dialogItems,
    'selectedItems' => $selectedItems,
  ];
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

Loc::loadMessages(__FILE__);

$doctorId = isset($_GET['ID']) ? (int)$_GET['ID'] : (int)($_POST['ID'] ?? 0);
$isEditMode = $doctorId > 0;

Loader::includeModule('ui');

Extension::load([
  'ui.forms',
  'ui.buttons',
  'ui.layout-form',
  'ui.fonts.opensans',
  'ui.entity-selector',
]);

$errors = [];
$successMessage = '';
$formData = homework3DoctorFormCreateDefaults();
$procedures = [];

try {
  $procedures = homework3DoctorFormLoadProcedures();
} catch (\Throwable $exception) {
  $errors[] = $exception->getMessage();
}

if ($isEditMode && $_SERVER['REQUEST_METHOD'] !== 'POST') {
  try {
    $doctor = homework3DoctorFormLoadDoctor($doctorId);

    if ($doctor === null) {
      $errors[] = (string)Loc::getMessage('CLINIC_DOCTOR_FORM_DOCTOR_NOT_FOUND');
      $doctorId = 0;
      $isEditMode = false;
    } else {
      $formData = homework3DoctorFormCreateDoctorData($doctor);
    }
  } catch (\Throwable $exception) {
    $errors[] = $exception->getMessage();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
  $formData = homework3DoctorFormCreateRequestData($_POST);

  try {
    $submitResult = homework3DoctorFormSubmit($doctorId, $formData);
    $errors = $submitResult['errors'];
    $successMessage = $submitResult['successMessage'];
    $formData = $submitResult['formData'];
  } catch (\Throwable $exception) {
    $errors[] = $exception->getMessage();
  }
}

$cancelUrl = $isEditMode
  ? 'doctor_view.php?ID=' . $doctorId
  : 'index.php';
$formActionUrl = 'doctor_form.php' . ($isEditMode ? '?ID=' . $doctorId : '');
$pageTitle = $isEditMode
  ? (string)Loc::getMessage('CLINIC_DOCTOR_FORM_TITLE_EDIT')
  : (string)Loc::getMessage('CLINIC_DOCTOR_FORM_TITLE_CREATE');
$pageHeading = $isEditMode
  ? (string)Loc::getMessage('CLINIC_DOCTOR_FORM_HEADING_EDIT')
  : (string)Loc::getMessage('CLINIC_DOCTOR_FORM_HEADING_CREATE');
$pageDescription = $isEditMode
  ? (string)Loc::getMessage('CLINIC_DOCTOR_FORM_DESCRIPTION_EDIT')
  : (string)Loc::getMessage('CLINIC_DOCTOR_FORM_DESCRIPTION_CREATE');
$APPLICATION->SetTitle($pageTitle);

$procedureSelectorData = homework3DoctorFormBuildProcedureSelectorData(
  $procedures,
  $formData['procedure_ids']
);
$procedureDialogItems = $procedureSelectorData['dialogItems'];
$selectedProcedureItems = $procedureSelectorData['selectedItems'];

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
    border: 1px solid #f3c2c2;
    color: #b42318;
  }

  .doctor-form-notice--success {
    background: #f0fff4;
    border: 1px solid #b7ebc6;
    color: #1f6b38;
  }

  .doctor-form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 24px;
  }

  .doctor-form-actions .ui-btn {
    margin: 0;
  }

  .doctor-procedure-selector {
    padding-top: 6px;
  }

  .doctor-procedure-help {
    margin-top: 8px;
    font-size: 13px;
    line-height: 18px;
    color: #6b7682;
  }

  .doctor-procedure-empty {
    padding: 12px 14px;
    border: 1px dashed #cfd8e0;
    border-radius: 10px;
    color: #6b7682;
    background: #fbfcfd;
  }
</style>

<div class="doctor-form-page">
  <div class="doctor-form-hero">
    <h1 class="doctor-form-title">
      <?= htmlspecialcharsbx($pageHeading) ?>
    </h1>

    <p class="doctor-form-text">
      <?= htmlspecialcharsbx($pageDescription) ?>
    </p>
  </div>

  <div class="doctor-form-card">
    <?php if ($successMessage !== ''): ?>
      <div class="doctor-form-notice doctor-form-notice--success">
        <?= htmlspecialcharsbx($successMessage) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="doctor-form-notice doctor-form-notice--error">
        <?php foreach ($errors as $error): ?>
          <div><?= htmlspecialcharsbx((string)$error) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form action="<?= htmlspecialcharsbx($formActionUrl) ?>" method="post" id="doctor-form">
      <?= bitrix_sessid_post() ?>

      <?php if ($doctorId > 0): ?>
        <input type="hidden" name="ID" value="<?= $doctorId ?>">
      <?php endif; ?>

      <div class="ui-form">
        <div class="ui-form-row">
          <div class="ui-form-label">
            <div class="ui-ctl-label-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_DOCTOR_FORM_FIELD_LAST_NAME')) ?></div>
          </div>
          <div class="ui-form-content">
            <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
              <input
                type="text"
                name="last_name"
                class="ui-ctl-element"
                value="<?= htmlspecialcharsbx($formData['last_name']) ?>"
                placeholder="<?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_DOCTOR_FORM_PLACEHOLDER_LAST_NAME')) ?>">
            </div>
          </div>
        </div>

        <div class="ui-form-row">
          <div class="ui-form-label">
            <div class="ui-ctl-label-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_DOCTOR_FORM_FIELD_FIRST_NAME')) ?></div>
          </div>
          <div class="ui-form-content">
            <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
              <input
                type="text"
                name="first_name"
                class="ui-ctl-element"
                value="<?= htmlspecialcharsbx($formData['first_name']) ?>"
                placeholder="<?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_DOCTOR_FORM_PLACEHOLDER_FIRST_NAME')) ?>">
            </div>
          </div>
        </div>

        <div class="ui-form-row">
          <div class="ui-form-label">
            <div class="ui-ctl-label-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_DOCTOR_FORM_FIELD_MIDDLE_NAME')) ?></div>
          </div>
          <div class="ui-form-content">
            <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
              <input
                type="text"
                name="middle_name"
                class="ui-ctl-element"
                value="<?= htmlspecialcharsbx($formData['middle_name']) ?>"
                placeholder="<?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_DOCTOR_FORM_PLACEHOLDER_MIDDLE_NAME')) ?>">
            </div>
          </div>
        </div>

        <div class="ui-form-row">
          <div class="ui-form-label">
            <div class="ui-ctl-label-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_DOCTOR_FORM_FIELD_PROCEDURES')) ?></div>
          </div>
          <div class="ui-form-content">
            <?php if ($procedureDialogItems === []): ?>
              <div class="doctor-procedure-empty"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_DOCTOR_FORM_PROCEDURES_EMPTY')) ?></div>
            <?php else: ?>
              <div class="doctor-procedure-selector" id="doctor-procedure-selector"></div>
              <div class="doctor-procedure-help">
                <?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_DOCTOR_FORM_PROCEDURES_HELP')) ?>
              </div>
              <div id="doctor-procedure-inputs"></div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="doctor-form-actions">
        <button
          type="submit"
          class="ui-btn ui-btn-success ui-btn-round">
          <span class="ui-btn-text">
            <?= htmlspecialcharsbx($isEditMode
              ? (string)Loc::getMessage('CLINIC_DOCTOR_FORM_BUTTON_SAVE_CHANGES')
              : (string)Loc::getMessage('CLINIC_DOCTOR_FORM_BUTTON_SAVE')) ?>
          </span>
        </button>

        <a
          href="<?= htmlspecialcharsbx($cancelUrl) ?>"
          class="ui-btn ui-btn-light-border ui-btn-round">
          <span class="ui-btn-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_DOCTOR_FORM_BUTTON_BACK')) ?></span>
        </a>
      </div>
    </form>
  </div>
</div>

<?php if ($procedureDialogItems !== []): ?>
  <script>
    BX.ready(function() {

      const dialogItems = <?= Json::encode($procedureDialogItems) ?>;
      const selectedItems = <?= Json::encode($selectedProcedureItems) ?>;

      const hiddenInputsContainer = document.getElementById('doctor-procedure-inputs');
      const selectorContainer = document.getElementById('doctor-procedure-selector');

      const syncProcedureInputs = function(selector) {
        hiddenInputsContainer.innerHTML = '';

        selector.getTags().forEach(function(tag) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'procedure_ids[]';
          input.value = tag.getId();
          hiddenInputsContainer.appendChild(input);
        });
      };

      const procedureSelector = new BX.UI.EntitySelector.TagSelector({
        id: 'doctor-procedure-tag-selector',
        multiple: true,
        textBoxAutoHide: true,
        textBoxWidth: 320,
        maxHeight: 140,
        placeholder: <?= Json::encode((string)Loc::getMessage('CLINIC_DOCTOR_FORM_SELECTOR_PLACEHOLDER')) ?>,
        addButtonCaption: <?= Json::encode((string)Loc::getMessage('CLINIC_DOCTOR_FORM_SELECTOR_ADD')) ?>,
        addButtonCaptionMore: <?= Json::encode((string)Loc::getMessage('CLINIC_DOCTOR_FORM_SELECTOR_ADD_MORE')) ?>,
        items: selectedItems,
        dialogOptions: {
          id: 'doctor-procedure-dialog',
          context: 'homework3-doctor-procedure-selector',
          multiple: true,
          dropdownMode: true,
          enableSearch: true,
          width: 520,
          showAvatars: false,
          tabs: [{
            id: 'procedures',
            title: <?= Json::encode((string)Loc::getMessage('CLINIC_DOCTOR_FORM_SELECTOR_TAB_PROCEDURES')) ?>
          }],
          items: dialogItems,
          selectedItems: selectedItems
        },
        events: {
          onAfterTagAdd: function(event) {
            syncProcedureInputs(event.getTarget());
          },
          onAfterTagRemove: function(event) {
            syncProcedureInputs(event.getTarget());
          }
        }
      });

      procedureSelector.renderTo(selectorContainer);
      syncProcedureInputs(procedureSelector);
    });
  </script>
<?php endif; ?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
