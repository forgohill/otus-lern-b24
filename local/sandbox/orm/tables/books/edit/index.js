document.addEventListener('DOMContentLoaded', function () {
  if (!window.BX || !BX.PopupWindowManager) {
    return;
  }

  var popup = null;
  var popupContent = null;
  var popupEditorHost = null;
  var popupEditor = null;
  var popupTarget = null;
  var popupTitle = null;

  function ensurePopup() {
    if (popup) {
      return popup;
    }

    popupContent = document.createElement('div');
    popupContent.className = 'sandbox-book-field-popup';

    popupTitle = document.createElement('div');
    popupTitle.className = 'sandbox-book-field-popup-title';

    popupEditorHost = document.createElement('div');
    popupEditorHost.className = 'sandbox-book-field-popup-editor';

    var actions = document.createElement('div');
    actions.className = 'sandbox-book-field-popup-actions';

    var saveButton = document.createElement('button');
    saveButton.type = 'button';
    saveButton.className = 'ui-btn ui-btn-success ui-btn-round';
    saveButton.textContent = 'Применить';

    var cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.className = 'ui-btn ui-btn-light-border ui-btn-round';
    cancelButton.textContent = 'Отмена';

    actions.appendChild(cancelButton);
    actions.appendChild(saveButton);

    popupContent.appendChild(popupTitle);
    popupContent.appendChild(popupEditorHost);
    popupContent.appendChild(actions);

    popup = BX.PopupWindowManager.create('sandbox-book-field-popup', null, {
      content: popupContent,
      autoHide: false,
      closeIcon: {
        top: '10px',
        right: '10px',
      },
      overlay: {
        opacity: 50,
      },
      width: 560,
      height: 320,
      angle: false,
      events: {
        onPopupShow: function () {
          this.adjustPosition();
        },
      },
    });

    saveButton.addEventListener('click', function () {
      if (!popupTarget) {
        popup.close();
        return;
      }

      popupTarget.value = popupEditor.value;
      popupTarget.dispatchEvent(new Event('input', { bubbles: true }));
      popup.close();
    });

    cancelButton.addEventListener('click', function () {
      popup.close();
    });

    return popup;
  }

  document.querySelectorAll('[data-book-field-edit]').forEach(function (button) {
    button.addEventListener('click', function (event) {
      event.preventDefault();

      var targetSelector = this.getAttribute('data-book-field-target');
      var fieldLabel = this.getAttribute('data-book-field-label') || '';
      var fieldType = this.getAttribute('data-book-field-type') || 'text';
      var target = targetSelector ? document.querySelector(targetSelector) : null;

      if (!target) {
        return;
      }

      ensurePopup();

      popupTarget = target;
      popupTitle.textContent = 'Редактирование: ' + fieldLabel;
      popupEditorHost.innerHTML = '';

      if (fieldType === 'textarea') {
        popupEditor = document.createElement('textarea');
        popupEditor.className = 'sandbox-book-field-popup-input sandbox-book-field-popup-textarea';
        popupEditor.rows = 6;
      } else {
        popupEditor = document.createElement('input');
        popupEditor.className = 'sandbox-book-field-popup-input';
        popupEditor.type = fieldType;
      }

      popupEditor.value = target.value || '';
      popupEditorHost.appendChild(popupEditor);
      popup.show();
      popup.adjustPosition();
      popupEditor.focus();
      if (typeof popupEditor.select === 'function') {
        popupEditor.select();
      }
    });
  });
});
