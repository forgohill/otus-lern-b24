(function () {
 BX.ready(function () {
  var popupContent = document.getElementById('pantone-edit-popup-content');
  var editButtons = document.querySelectorAll('.pantone-edit-button');

  if (!popupContent || !editButtons.length || !BX.PopupWindowManager) {
   return;
  }

  popupContent.hidden = false;

  var fields = {
   id: popupContent.querySelector('[data-pantone-edit-field="id"]'),
   name: popupContent.querySelector('[data-pantone-edit-field="name"]'),
   hex: popupContent.querySelector('[data-pantone-edit-field="hex"]'),
   activeFrom: popupContent.querySelector('[data-pantone-edit-field="activeFrom"]'),
   tags: popupContent.querySelector('[data-pantone-edit-field="tags"]'),
   description: popupContent.querySelector('[data-pantone-edit-field="description"]'),
   fullDescription: popupContent.querySelector('[data-pantone-edit-field="fullDescription"]')
  };
  var form = popupContent.querySelector('.pantone-edit-form');

  var popup = BX.PopupWindowManager.create('pantone-color-edit-popup', null, {
   titleBar: 'Редактирование цвета',
   content: popupContent,
   width: 640,
   overlay: true,
   bindOptions: {
    position: 'fixed'
   },
   offsetLeft: 0,
   offsetTop: 0,
   closeIcon: true,
   closeByEsc: true,
   autoHide: false,
   draggable: {
    restrict: true
   },
   buttons: [
    new BX.PopupWindowButton({
     text: 'Сохранить',
     className: 'ui-btn ui-btn-success',
     events: {
      click: function () {
       if (form.requestSubmit) {
        form.requestSubmit();
       } else {
        form.submit();
       }
      }
     }
    }),
    new BX.PopupWindowButtonLink({
     text: 'Отмена',
     className: 'ui-btn ui-btn-link',
     events: {
      click: function () {
       this.popupWindow.close();
      }
     }
    })
   ]
  });

  function setFieldValue(field, value) {
   if (field) {
    field.value = value || '';
   }
  }

  editButtons.forEach(function (button) {
   button.addEventListener('click', function () {
    setFieldValue(fields.id, button.dataset.colorId);
    setFieldValue(fields.name, button.dataset.colorName);
    setFieldValue(fields.hex, button.dataset.colorHex);
    setFieldValue(fields.activeFrom, button.dataset.colorActiveFrom);
    setFieldValue(fields.tags, button.dataset.colorTags);
    setFieldValue(fields.description, button.dataset.colorDescription);
    setFieldValue(fields.fullDescription, button.dataset.colorFullDescription);

    popup.show();
    popup.adjustPosition({
     forceBindPosition: false
    });
   });
  });
 });
})();
