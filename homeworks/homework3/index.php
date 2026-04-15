<?php

declare(strict_types=1);

use App\Clinic\DoctorService;
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

$errors = [];
$doctors = [];
$doctorRepository = new \App\Clinic\DoctorRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
  $action = (string)($_POST['action'] ?? '');
  $doctorId = (int)($_POST['doctor_id'] ?? 0);

  if ($action === 'delete') {
    try {
      $doctorService = new DoctorService();
      $deleteResult = $doctorService->delete($doctorId);

      if (!($deleteResult['success'] ?? false)) {
        $errors = is_array($deleteResult['errors'] ?? null)
          ? $deleteResult['errors']
          : [(string)Loc::getMessage('CLINIC_INDEX_DOCTOR_DELETE_ERROR')];
      } else {
        LocalRedirect('index.php');
        exit;
      }
    } catch (\Throwable $exception) {
      $errors[] = $exception->getMessage();
    }
  }
}

try {
  $doctors = $doctorRepository->getList();
} catch (\Throwable $exception) {
  $errors[] = $exception->getMessage();
}
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

  .homework-description {
    position: relative;
    margin: 0;
    font-size: 15px;
    line-height: 24px;
    color: #525c69;
    max-width: 800px;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 5;
    overflow: hidden;
  }

  .homework-description::after {
    content: "";
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    height: 36px;
    background: linear-gradient(to bottom, rgba(248, 250, 252, 0), #f8fafc 90%);
  }

  .homework-description.is-expanded {
    display: block;
    overflow: visible;
  }

  .homework-description.is-expanded::after {
    display: none;
  }

  .homework-description code {
    padding: 1px 6px;
    border-radius: 6px;
    background: #eef3f8;
    color: #1f2d3d;
    font-size: 13px;
  }

  .homework-description-toggle {
    margin-top: 12px;
    padding: 0;
    border: none;
    background: transparent;
    color: #2f7cf6;
    font-size: 14px;
    line-height: 20px;
    font-weight: 600;
    cursor: pointer;
  }

  .homework-description-toggle:hover {
    color: #1b63d0;
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
    margin-top: auto;
    justify-content: space-between;
  }

  .doctor-card-delete-form {
    margin: 0;
  }

  .doctor-card-redact-btn {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .doctor-card-icon-btn {
    min-width: 40px;
    padding-left: 0;
    padding-right: 0;
    justify-content: center;
    color: #7d8691;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
  }

  .doctor-card-icon-btn .ui-btn-text {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .doctor-card-icon {
    --ui-icon-set__icon-size: 16px;
  }

  .doctor-card-icon-btn--delete:hover,
  .doctor-card-icon-btn--delete:focus {
    background-color: #ff5752 !important;
    border-color: #ff5752 !important;
    color: #ffffff !important;
    box-shadow: 0 6px 18px rgba(255, 87, 82, 0.22);
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

  .homework-notice {
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 20px;
  }

  .homework-notice--error {
    background: #fff5f5;
    border: 1px solid #f3c2c2;
    color: #b42318;
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

      <div class="homework-description" id="project-description">
        Проект реализован как небольшой модуль на Bitrix D7 и разделён на два уровня:
        страницы интерфейса в <code>homeworks/homework3</code> и прикладная логика
        в <code>local/App/Clinic</code>. Файл <code>index.php</code> отвечает за стартовую
        страницу: он подключает Bitrix UI, получает список врачей через
        <code>DoctorRepository</code> и выводит карточки со ссылками на просмотр,
        редактирование и создание врача.<br><br>

        Файл <code>doctor_form.php</code> реализует форму создания и редактирования врача:
        внутри него отдельно выделены шаги загрузки данных, обработки POST и подготовки
        переменных для шаблона, а выбор процедур сделан через <code>TagSelector</code>.
        Файл <code>procedure_form.php</code> отвечает за создание и удаление процедур,
        а также за вывод списка уже существующих процедур. Файл
        <code>doctor_view.php</code> показывает карточку врача и связанные с ним процедуры,
        а удаление врача делегирует сервису, а не выполняет напрямую на странице.<br><br>

        Вся бизнес-логика вынесена в <code>local/App/Clinic</code>. Файл
        <code>ClinicConfig.php</code> хранит константы с кодами инфоблоков и свойств,
        чтобы не дублировать их по проекту. <code>DoctorRepository.php</code> отвечает
        за чтение врачей: он получает список врачей, одного врача по ID и данные для
        детального просмотра. Для карточки врача он собирает связанные процедуры через
        ORM Bitrix и <code>registerRuntimeField</code>, то есть связь между врачом и
        процедурами строится на уровне запроса, а не в шаблоне страницы.<br><br>

        <code>ProcedureRepository.php</code> читает список процедур и процедуры по массиву
        идентификаторов. <code>DoctorService.php</code> отвечает за создание, обновление
        и удаление врачей, проверяет обязательные поля, назначает <code>IBLOCK_ID</code>,
        сохраняет ФИО и отдельно синхронизирует множественное свойство с процедурами.
        <code>ProcedureService.php</code> создаёт и удаляет процедуры и также проверяет
        входные данные перед сохранением. Дополнительно проект поддерживает локализацию:
        для страниц используются файлы в <code>homeworks/homework3/lang/ru</code>, а для
        прикладных классов в <code>local/App/Clinic/lang/ru</code>.
      </div>

      <button
        type="button"
        class="homework-description-toggle"
        id="project-description-toggle"
        aria-expanded="false"
        aria-controls="project-description">
        Показать все
      </button>
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
      <?php if (!empty($errors)): ?>
        <div class="homework-notice homework-notice--error">
          <?php foreach ($errors as $error): ?>
            <div><?= htmlspecialcharsbx((string)$error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="homework-cards">
        <?php foreach ($doctors as $doctor): ?>
          <?php
          $doctorId = (int)($doctor['id'] ?? 0);
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
              <div class="doctor-card-redact-btn">
                <a
                  href="<?= htmlspecialcharsbx($doctorEditUrl) ?>"
                  class="ui-btn ui-btn-light-border ui-btn-round doctor-card-icon-btn"
                  title="<?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_DOCTOR_EDIT_TITLE')) ?>"
                  aria-label="<?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_DOCTOR_EDIT_TITLE')) ?>">
                  <span class="ui-btn-text">
                    <div class="ui-icon-set --edit-pencil doctor-card-icon"></div>
                  </span>
                </a>

                <form
                  action=""
                  method="post"
                  class="doctor-card-delete-form"
                  onsubmit="return confirm('<?= \CUtil::JSEscape((string)Loc::getMessage('CLINIC_INDEX_DOCTOR_DELETE_CONFIRM')) ?>');">
                  <?= bitrix_sessid_post() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="doctor_id" value="<?= $doctorId ?>">

                  <button
                    type="submit"
                    class="ui-btn ui-btn-light-border ui-btn-round doctor-card-icon-btn doctor-card-icon-btn--delete"
                    title="<?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_DOCTOR_DELETE_TITLE')) ?>"
                    aria-label="<?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_DOCTOR_DELETE_TITLE')) ?>">
                    <span class="ui-btn-text">
                      <div class="ui-icon-set --trash-bin doctor-card-icon"></div>
                    </span>
                  </button>
                </form>

              </div>
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

<script>
  BX.ready(function() {
    const description = document.getElementById('project-description');
    const toggle = document.getElementById('project-description-toggle');

    if (!description || !toggle) {
      return;
    }

    toggle.addEventListener('click', function() {
      const expanded = description.classList.toggle('is-expanded');

      toggle.textContent = expanded ? 'Свернуть' : 'Показать все';
      toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    });
  });
</script>

<div class="homework-floating-top">
  <a href="#top" class="ui-btn ui-btn-primary ui-btn-round">
    <span class="ui-btn-text"><?= htmlspecialcharsbx((string)Loc::getMessage('CLINIC_INDEX_SCROLL_TOP')) ?></span>
  </a>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>