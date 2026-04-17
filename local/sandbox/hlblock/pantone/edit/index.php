<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
require_once __DIR__ . '/../PantoneColorsService.php';

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

$APPLICATION->SetTitle('PantoneColors: редактирование');
Asset::getInstance()->addCss('/local/sandbox/style.css');
Asset::getInstance()->addCss('/local/sandbox/hlblock/pantone/index.css');
Asset::getInstance()->addCss('/local/sandbox/hlblock/pantone/edit/index.css');
Asset::getInstance()->addJs('/local/sandbox/hlblock/pantone/edit/index.js');

if (Loader::includeModule('ui')) {
 Extension::load([
  'main.popup',
  'ui.buttons',
 ]);
}

$pantoneColorsHelper = new PantoneColorsHelper();
$pantoneColorsService = new PantoneColorsService($pantoneColorsHelper);
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 if (!check_bitrix_sessid()) {
  $errors[] = 'Сессия истекла. Обновите страницу и отправьте форму еще раз.';
 } else {
  $result = $pantoneColorsService->updateMulti($_POST, false);

  if ($result->isSuccess()) {
   $successMessage = 'Цвет обновлен.';
  } else {
   foreach ($result->getErrorMessages() as $errorMessage) {
    $errors[] = $errorMessage;
   }
  }
 }
}

$colorItems = $pantoneColorsHelper->getPreparedItems();
?>

<div class="sandbox-page">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/hlblock/pantone/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
  <a href="/local/sandbox/hlblock/pantone/add/index.php" class="ui-btn ui-btn-primary ui-btn-round">Добавить цвет</a>
 </div>

 <div class="sandbox-hero">
  <h1 class="sandbox-title">PantoneColors: редактирование</h1>
  <p class="sandbox-text">Выберите цвет из HL-блока PantoneColors и откройте форму редактирования в попапе.</p>
 </div>

 <div class="sandbox-section">
  <div class="sandbox-section-header">Редактирование</div>
  <div class="sandbox-section-body pantone-colors pantone-edit">
   <p class="sandbox-study-text">Выберите цвет, откройте форму редактирования и сохраните изменения.</p>

   <?php if ($successMessage !== ''): ?>
    <div class="sandbox-status sandbox-status--success sandbox-status--compact pantone-edit-status">
     <?= htmlspecialcharsbx($successMessage) ?>
    </div>
   <?php endif; ?>

   <?php if ($errors): ?>
    <div class="sandbox-status sandbox-status--error sandbox-status--compact pantone-edit-status">
     <?php foreach ($errors as $error): ?>
      <div><?= htmlspecialcharsbx($error) ?></div>
     <?php endforeach; ?>
    </div>
   <?php endif; ?>

   <?php if ($colorItems): ?>
    <div class="pantone-list">
     <?php foreach ($colorItems as $color): ?>
      <?php
      $tagLine = implode(', ', $color['tags']);
      ?>
      <div class="pantone-item pantone-edit-item">
       <div class="pantone-swatch" style="--pantone-color: <?= htmlspecialcharsbx($color['background_color']) ?>;"></div>

       <div class="pantone-content">
        <div class="pantone-date"><?= htmlspecialcharsbx($color['active_from']) ?></div>
        <div class="pantone-name"><?= htmlspecialcharsbx($color['name']) ?></div>
        <div class="pantone-hex"><?= htmlspecialcharsbx($color['background_color']) ?></div>
       </div>

       <ol class="pantone-tags">
        <?php foreach ($color['tags'] as $tag): ?>
         <li class="pantone-tag"><?= htmlspecialcharsbx($tag) ?></li>
        <?php endforeach; ?>
       </ol>

       <div class="pantone-edit-actions">
        <button
         class="ui-btn ui-btn-primary ui-btn-round pantone-edit-button"
         type="button"
         data-color-id="<?= (int)$color['id'] ?>"
         data-color-name="<?= htmlspecialcharsbx($color['name']) ?>"
         data-color-hex="<?= htmlspecialcharsbx($color['background_color']) ?>"
         data-color-active-from="<?= htmlspecialcharsbx($color['active_from_input']) ?>"
         data-color-tags="<?= htmlspecialcharsbx($tagLine) ?>"
         data-color-description="<?= htmlspecialcharsbx($color['description']) ?>"
         data-color-full-description="<?= htmlspecialcharsbx($color['full_description']) ?>"
        >
         Редактировать
        </button>
       </div>
      </div>
     <?php endforeach; ?>
    </div>
   <?php else: ?>
    <div class="sandbox-status sandbox-status--compact">
     Цвета пока не найдены.
    </div>
   <?php endif; ?>
  </div>
 </div>
</div>

<div id="pantone-edit-popup-content" class="pantone-edit-popup" hidden>
 <form class="pantone-edit-form" action="" method="post">
  <?= bitrix_sessid_post() ?>
  <input type="hidden" name="<?= PantoneColorsHelper::FIELD_ID ?>" data-pantone-edit-field="id">

  <div class="pantone-edit-form-grid">
   <label class="pantone-edit-field">
    <span class="pantone-edit-label">Название цвета</span>
    <input class="pantone-edit-input" type="text" name="<?= PantoneColorsHelper::FIELD_NAME ?>" data-pantone-edit-field="name" required>
   </label>

   <label class="pantone-edit-field">
    <span class="pantone-edit-label">HEX-код</span>
    <input class="pantone-edit-input" type="text" name="<?= PantoneColorsHelper::FIELD_HEX_CODE ?>" data-pantone-edit-field="hex" maxlength="7" required>
   </label>

   <label class="pantone-edit-field">
    <span class="pantone-edit-label">Дата активности</span>
    <input class="pantone-edit-input" type="date" name="<?= PantoneColorsHelper::FIELD_ACTIVE_FROM ?>" data-pantone-edit-field="activeFrom">
   </label>

   <label class="pantone-edit-field">
    <span class="pantone-edit-label">Теги</span>
    <input class="pantone-edit-input" type="text" name="<?= PantoneColorsHelper::FIELD_TAGS ?>" data-pantone-edit-field="tags">
   </label>
  </div>

  <label class="pantone-edit-field">
   <span class="pantone-edit-label">Описание</span>
   <textarea class="pantone-edit-textarea" name="<?= PantoneColorsHelper::FIELD_DESCRIPTION ?>" rows="4" data-pantone-edit-field="description" placeholder="Короткое описание цвета"></textarea>
  </label>

  <label class="pantone-edit-field">
   <span class="pantone-edit-label">Полное описание</span>
   <textarea class="pantone-edit-textarea" name="<?= PantoneColorsHelper::FIELD_FULL_DESCRIPTION ?>" rows="5" data-pantone-edit-field="fullDescription" placeholder="Подробное описание цвета"></textarea>
  </label>
 </form>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
