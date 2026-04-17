<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
require_once __DIR__ . '/../PantoneColorsService.php';

$APPLICATION->SetTitle('PantoneColors: добавление');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/local/sandbox/style.css');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/local/sandbox/hlblock/pantone/add/index.css');

$pantoneColorsService = new PantoneColorsService();
$formData = [];
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $formData = $_POST;

 if (!check_bitrix_sessid()) {
  $errors[] = 'Сессия истекла. Обновите страницу и отправьте форму еще раз.';
 } else {
  $result = $pantoneColorsService->addMulti($_POST, false);

  if ($result->isSuccess()) {
   $successMessage = 'Цвет добавлен.';
   $formData = [];
  } else {
   foreach ($result->getErrorMessages() as $errorMessage) {
    $errors[] = $errorMessage;
   }
  }
 }
}

$fieldValue = static function (string $field) use (&$formData): string {
 return htmlspecialcharsbx((string)($formData[$field] ?? ''));
};
?>

<div class="sandbox-page">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/hlblock/pantone/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
 </div>

 <div class="sandbox-hero">
  <h1 class="sandbox-title">PantoneColors: добавление</h1>
  <p class="sandbox-text">Создание нового цвета в HL-блоке PantoneColors.</p>
 </div>

 <div class="sandbox-section">
  <div class="sandbox-section-header">Добавление</div>
  <div class="sandbox-section-body pantone-add">
   <?php if ($successMessage !== ''): ?>
    <div class="sandbox-status sandbox-status--success pantone-form-status">
     <?= htmlspecialcharsbx($successMessage) ?>
    </div>
   <?php endif; ?>

   <?php if ($errors): ?>
    <div class="sandbox-status sandbox-status--error pantone-form-status">
     <?php foreach ($errors as $error): ?>
      <div><?= htmlspecialcharsbx($error) ?></div>
     <?php endforeach; ?>
    </div>
   <?php endif; ?>

   <form class="pantone-form" method="post" action="">
    <?= bitrix_sessid_post() ?>

    <div class="pantone-form-grid">
     <label class="pantone-field">
      <span class="pantone-label">Название цвета</span>
      <input
       class="pantone-input"
       type="text"
       name="<?= PantoneColorsHelper::FIELD_NAME ?>"
       placeholder="Например, Черный"
       value="<?= $fieldValue(PantoneColorsHelper::FIELD_NAME) ?>"
       required
      >
     </label>

     <label class="pantone-field">
      <span class="pantone-label">HEX-код</span>
      <input
       class="pantone-input"
       type="text"
       name="<?= PantoneColorsHelper::FIELD_HEX_CODE ?>"
       placeholder="Например, f0f0f0"
       maxlength="7"
       value="<?= $fieldValue(PantoneColorsHelper::FIELD_HEX_CODE) ?>"
       required
      >
     </label>

     <label class="pantone-field">
      <span class="pantone-label">Дата активности</span>
      <input
       class="pantone-input"
       type="date"
       name="<?= PantoneColorsHelper::FIELD_ACTIVE_FROM ?>"
       value="<?= $fieldValue(PantoneColorsHelper::FIELD_ACTIVE_FROM) ?>"
      >
     </label>
    </div>

    <label class="pantone-field">
     <span class="pantone-label">Теги</span>
     <input
      class="pantone-input"
      type="text"
      name="<?= PantoneColorsHelper::FIELD_TAGS ?>"
      placeholder="Например, базовый, темный, neutral"
      value="<?= $fieldValue(PantoneColorsHelper::FIELD_TAGS) ?>"
     >
    </label>

    <label class="pantone-field">
     <span class="pantone-label">Описание</span>
     <textarea
      class="pantone-textarea"
      name="<?= PantoneColorsHelper::FIELD_DESCRIPTION ?>"
      rows="4"
      placeholder="Короткое описание цвета"
     ><?= $fieldValue(PantoneColorsHelper::FIELD_DESCRIPTION) ?></textarea>
    </label>

    <label class="pantone-field">
     <span class="pantone-label">Полное описание</span>
     <textarea
      class="pantone-textarea"
      name="<?= PantoneColorsHelper::FIELD_FULL_DESCRIPTION ?>"
      rows="6"
      placeholder="Подробное описание, заметки или примеры использования"
     ><?= $fieldValue(PantoneColorsHelper::FIELD_FULL_DESCRIPTION) ?></textarea>
    </label>

    <div class="pantone-form-actions">
     <button class="ui-btn ui-btn-primary ui-btn-round" type="submit">Добавить цвет</button>
     <a href="/local/sandbox/hlblock/pantone/index.php" class="ui-btn ui-btn-light-border ui-btn-round">К списку</a>
    </div>
   </form>
  </div>
 </div>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
