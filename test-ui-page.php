<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\Facade\Toolbar;

Extension::load([
 'ui.buttons',
 'ui.buttons.icons',
 'ui.forms',
 'ui.icons',
 'ui.dialogs.messagebox',
]);

Toolbar::setTitle('Мой раздел');

?>
<div style="padding: 16px;">

 <div style="display:flex; gap:12px; margin-bottom:16px;">
  <button class="ui-btn ui-btn-primary">Сохранить</button>
  <button class="ui-btn ui-btn-light-border ui-btn-icon-back">Назад</button>
  <button class="ui-btn ui-btn-danger">Удалить</button>
 </div>

 <div style="display:grid; gap:12px; max-width:500px;">
  <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
   <input type="text" class="ui-ctl-element" placeholder="Название">
  </div>

  <div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-danger">
   <input type="text" class="ui-ctl-element" value="Ошибка валидации">
  </div>

  <div>
   <span class="ui-icon ui-icon-common-user"><i></i></span>
  </div>
 </div>

</div>

<script>
 BX.ready(function() {
  const btn = document.querySelector('.ui-btn-danger');

  if (btn) {
   btn.addEventListener('click', function() {
    BX.UI.Dialogs.MessageBox.confirm(
     'Точно удалить?',
     'Подтверждение',
     function(messageBox) {
      messageBox.close();
     },
     'Да',
     function(messageBox) {
      messageBox.close();
     }
    );
   });
  }
 });
</script>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
