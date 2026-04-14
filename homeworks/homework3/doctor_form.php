<?php

declare(strict_types=1);

use App\Clinic\DoctorRepository;
use App\Clinic\DoctorService;
use App\Clinic\ProcedureRepository;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$doctorId = isset($_GET['ID']) ? (int)$_GET['ID'] : (int)($_POST['ID'] ?? 0);
$isEditMode = $doctorId > 0;

$APPLICATION->SetTitle($isEditMode ? 'Редактирование врача' : 'Форма врача');

Loader::includeModule('ui');

Extension::load([
  'ui.forms',
  'ui.buttons',
  'ui.layout-form',
  'ui.fonts.opensans',
  'ui.entity-selector',
]);

$formData = [
  'last_name' => '',
  'first_name' => '',
  'middle_name' => '',
  'procedure_ids' => [],
];

$errors = [];
$successMessage = '';
$cancelUrl = 'index.php';
$formActionUrl = 'doctor_form.php' . ($isEditMode ? '?ID=' . $doctorId : '');

try {
  $procedureRepository = new ProcedureRepository();
  $procedures = $procedureRepository->getList();
} catch (\Throwable $exception) {
  $procedures = [];
  $errors[] = $exception->getMessage();
}

try {
  $doctorRepository = new DoctorRepository();

  if ($isEditMode && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $doctor = $doctorRepository->getById($doctorId);

    if ($doctor === null) {
      $errors[] = 'Врач не найден';
      $isEditMode = false;
      $doctorId = 0;
      $formActionUrl = 'doctor_form.php';
      $APPLICATION->SetTitle('Форма врача');
    } else {
      $formData = [
        'last_name' => (string)($doctor['LAST_NAME'] ?? ''),
        'first_name' => (string)($doctor['FIRST_NAME'] ?? ''),
        'middle_name' => (string)($doctor['MIDDLE_NAME'] ?? ''),
        'procedure_ids' => is_array($doctor['PROCEDURE_IDS'] ?? null)
          ? $doctor['PROCEDURE_IDS']
          : [],
      ];
    }
  }
} catch (\Throwable $exception) {
  $errors[] = $exception->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
  $formData['last_name'] = trim((string)($_POST['last_name'] ?? ''));
  $formData['first_name'] = trim((string)($_POST['first_name'] ?? ''));
  $formData['middle_name'] = trim((string)($_POST['middle_name'] ?? ''));

  $procedureIds = $_POST['procedure_ids'] ?? [];
  $formData['procedure_ids'] = is_array($procedureIds)
    ? array_values(array_unique(array_filter(array_map('intval', $procedureIds))))
    : [];

  try {
    $service = new DoctorService();

    if ($doctorId > 0) {
      $result = $service->update($doctorId, $formData);
    } else {
      $result = $service->create($formData);
    }

    if ($result['success']) {
      if ($doctorId > 0) {
        $successMessage = 'Изменения врача сохранены. ID: ' . $result['id'];
      } else {
        $successMessage = 'Врач создан. ID: ' . $result['id'];
        $formData = [
          'last_name' => '',
          'first_name' => '',
          'middle_name' => '',
          'procedure_ids' => [],
        ];
      }
    } else {
      $errors = $result['errors'];
    }
  } catch (\Throwable $exception) {
    $errors[] = $exception->getMessage();
  }
}

$procedureDialogItems = [];
$selectedProcedureItems = [];

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

  $procedureDialogItems[] = $item;

  if (in_array($procedureId, $formData['procedure_ids'], true)) {
    $selectedProcedureItems[] = $item;
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
      <?= $isEditMode ? 'Редактирование врача' : 'Добавление врача' ?>
    </h1>

    <p class="doctor-form-text">
      <?= $isEditMode
        ? 'На этой странице можно изменить врача и обновить связанные процедуры.'
        : 'На этой странице можно создать врача и выбрать связанные процедуры через Bitrix TagSelector.' ?>
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
            <div class="ui-ctl-label-text">Фамилия</div>
          </div>
          <div class="ui-form-content">
            <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
              <input
                type="text"
                name="last_name"
                class="ui-ctl-element"
                value="<?= htmlspecialcharsbx($formData['last_name']) ?>"
                placeholder="Например: Иванов">
            </div>
          </div>
        </div>

        <div class="ui-form-row">
          <div class="ui-form-label">
            <div class="ui-ctl-label-text">Имя</div>
          </div>
          <div class="ui-form-content">
            <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
              <input
                type="text"
                name="first_name"
                class="ui-ctl-element"
                value="<?= htmlspecialcharsbx($formData['first_name']) ?>"
                placeholder="Например: Иван">
            </div>
          </div>
        </div>

        <div class="ui-form-row">
          <div class="ui-form-label">
            <div class="ui-ctl-label-text">Отчество</div>
          </div>
          <div class="ui-form-content">
            <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
              <input
                type="text"
                name="middle_name"
                class="ui-ctl-element"
                value="<?= htmlspecialcharsbx($formData['middle_name']) ?>"
                placeholder="Например: Иванович">
            </div>
          </div>
        </div>

        <div class="ui-form-row">
          <div class="ui-form-label">
            <div class="ui-ctl-label-text">Процедуры</div>
          </div>
          <div class="ui-form-content">
            <?php if ($procedureDialogItems === []): ?>
              <div class="doctor-procedure-empty">Процедуры не найдены.</div>
            <?php else: ?>
              <div class="doctor-procedure-selector" id="doctor-procedure-selector"></div>
              <div class="doctor-procedure-help">
                Нажми «Добавить» и выбери одну или несколько процедур.
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
            <?= $isEditMode ? 'Сохранить изменения' : 'Сохранить' ?>
          </span>
        </button>

        <a
          href="<?= htmlspecialcharsbx($cancelUrl) ?>"
          class="ui-btn ui-btn-light-border ui-btn-round">
          <span class="ui-btn-text">Назад</span>
        </a>
      </div>
    </form>
  </div>
</div>

<?php if ($procedureDialogItems !== []): ?>
  <script>
    BX.ready(function() {
      const dialogItems = <?= \CUtil::PhpToJSObject($procedureDialogItems) ?>;
      const selectedItems = <?= \CUtil::PhpToJSObject($selectedProcedureItems) ?>;
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
        placeholder: 'Выберите процедуры',
        addButtonCaption: 'Добавить',
        addButtonCaptionMore: 'Добавить еще',
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
            title: 'Процедуры'
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