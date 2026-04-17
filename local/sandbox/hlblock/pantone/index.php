<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
require_once __DIR__ . '/PantoneColorsHelper.php';

$APPLICATION->SetTitle('PantoneColors: список');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/local/sandbox/style.css');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/local/sandbox/hlblock/pantone/index.css');
$pantoneColorsHelper  = new PantoneColorsHelper();
$colorItems = $pantoneColorsHelper->getPreparedItems();
?>

<div class="sandbox-page">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
  <a href="/local/sandbox/hlblock/pantone/add/index.php" class="ui-btn ui-btn-primary ui-btn-round">Добавить цвет</a>
  <a href="/local/sandbox/hlblock/pantone/edit/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Редактировать цвета</a>
 </div>

 <div class="sandbox-hero">
  <h1 class="sandbox-title">PantoneColors: список</h1>
  <p class="sandbox-text">Заготовка страницы чтения элементов HL-блока PantoneColors.</p>
 </div>

 <div class="sandbox-section">
  <div class="sandbox-section-header">Чтение</div>
  <div class="sandbox-section-body pantone-colors">
   <p class="sandbox-study-text">Здесь будет выборка цветов из HL-блока.</p>

   <div class="pantone-list">
    <?php foreach ($colorItems as $color): ?>

     <div class="pantone-item">
      <div class="pantone-swatch" style="--pantone-color: <?= htmlspecialcharsbx($color['background_color']) ?>;"></div>

      <div class="pantone-content">
       <div class=""><?= htmlspecialcharsbx($color['active_from']) ?></div>
       <div class="pantone-name">
        <?= htmlspecialcharsbx($color['name']) ?>
       </div>

       <div class="pantone-hex">
        <?= htmlspecialcharsbx($color['background_color']) ?>
       </div>
      </div>

      <ol class="pantone-tags">
       <?php foreach ($color['tags'] as $tag): ?>
        <li class="pantone-tag"><?= htmlspecialcharsbx($tag) ?></li>
       <?php endforeach; ?>
      </ol>
     </div>
    <?php endforeach; ?>
   </div>
  </div>
 </div>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
