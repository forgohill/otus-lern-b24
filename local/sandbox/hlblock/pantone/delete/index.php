<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
require_once __DIR__ . '/../PantoneColorsService.php';

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

$APPLICATION->SetTitle('PantoneColors: удаление');
Asset::getInstance()->addCss('/local/sandbox/style.css');
Asset::getInstance()->addCss('/local/sandbox/hlblock/pantone/index.css');
Asset::getInstance()->addCss('/local/sandbox/hlblock/pantone/delete/index.css');

if (Loader::includeModule('ui')) {
 Extension::load([
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
  $result = $pantoneColorsService->deleteMulti($_POST['color_ids'] ?? []);

  if ($result->isSuccess()) {
   $successMessage = 'Выбранные цвета удалены.';
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
 </div>

 <div class="sandbox-hero">
  <h1 class="sandbox-title">PantoneColors: удаление</h1>
  <p class="sandbox-text">Выберите один или несколько цветов из HL-блока PantoneColors для удаления.</p>
 </div>

 <div class="sandbox-section">
  <div class="sandbox-section-header">Удаление</div>
  <div class="sandbox-section-body pantone-colors pantone-delete">
   <?php if ($successMessage !== ''): ?>
    <div class="sandbox-status sandbox-status--success sandbox-status--compact pantone-delete-status">
     <?= htmlspecialcharsbx($successMessage) ?>
    </div>
   <?php endif; ?>

   <?php if ($errors): ?>
    <div class="sandbox-status sandbox-status--error sandbox-status--compact pantone-delete-status">
     <?php foreach ($errors as $error): ?>
      <div><?= htmlspecialcharsbx($error) ?></div>
     <?php endforeach; ?>
    </div>
   <?php endif; ?>

   <form class="pantone-delete-form" method="post" action="">
    <?= bitrix_sessid_post() ?>

    <div class="pantone-delete-toolbar">
     <button class="ui-btn ui-btn-light-border ui-btn-round pantone-delete-submit" type="submit">
      Удалить выбранные
     </button>
    </div>

    <?php if ($colorItems): ?>
     <div class="pantone-list">
      <?php foreach ($colorItems as $color): ?>
       <label class="pantone-item pantone-delete-item">
        <span class="pantone-delete-check-wrap">
         <input
          class="pantone-delete-check"
          type="checkbox"
          name="color_ids[]"
          value="<?= (int)$color['id'] ?>"
         >
        </span>

        <span class="pantone-swatch" style="--pantone-color: <?= htmlspecialcharsbx($color['background_color']) ?>;"></span>

        <span class="pantone-content">
         <span class="pantone-delete-date"><?= htmlspecialcharsbx($color['active_from']) ?></span>
         <span class="pantone-name"><?= htmlspecialcharsbx($color['name']) ?></span>
         <span class="pantone-hex"><?= htmlspecialcharsbx($color['background_color']) ?></span>
        </span>

        <span class="pantone-tags">
         <?php foreach ($color['tags'] as $tag): ?>
          <span class="pantone-tag"><?= htmlspecialcharsbx($tag) ?></span>
         <?php endforeach; ?>
        </span>
       </label>
      <?php endforeach; ?>
     </div>
    <?php else: ?>
     <div class="sandbox-status sandbox-status--compact">
      Цвета пока не найдены.
     </div>
    <?php endif; ?>
   </form>
  </div>
 </div>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
