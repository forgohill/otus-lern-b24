<?

use Models\Titanic\Install\IblockInstaller;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle("Проект Titanic");

Loader::includeModule('ui');
Extension::load([
  'ui.fonts.opensans',
]);

Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$installer = new IblockInstaller();
$installResult = null;
$dictionaryInstallers = $installer->getDictionaryInstallers();
$allInstalled = true;

foreach ($dictionaryInstallers as $dictionaryInstaller) {
  if (!$dictionaryInstaller->isInstalled()) {
    $allInstalled = false;
    break;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && isset($_POST['install_titanic_iblocks'])) {
  $installResult = $installer->install();
}
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">Установка справочных инфоблоков Titanic в типе <code>lists</code>.</div>

    <?php if (is_array($installResult)): ?>
      <div class="homework-note">
        <?php if (!empty($installResult['success'])): ?>
          Инфоблоки установлены.
        <?php else: ?>
          Установка завершилась с ошибками.
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="homework-toolbar" style="margin-top: 18px;">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>

      <form method="post" class="homework-toolbar-form">
        <?= bitrix_sessid_post() ?>
        <button
          type="submit"
          name="install_titanic_iblocks"
          value="Y"
          class="homework-btn homework-btn--primary<?= $allInstalled ? ' homework-btn--disabled' : '' ?>"
          <?= $allInstalled ? 'disabled' : '' ?>>
          Установить инфоблоки
        </button>
      </form>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-status-list">
        <?php foreach ($dictionaryInstallers as $dictionaryInstaller): ?>
          <div class="homework-status-row">
            <div>
              <p class="homework-status-title"><?= htmlspecialcharsbx($dictionaryInstaller->getTitle()) ?></p>
              <p class="homework-status-text">
                Код: <?= htmlspecialcharsbx($dictionaryInstaller->getCode()) ?>
              </p>
            </div>
            <span class="homework-status-badge <?= $dictionaryInstaller->isInstalled() ? 'homework-status-badge--ok' : 'homework-status-badge--warn' ?>">
              <?= htmlspecialcharsbx($dictionaryInstaller->getInstallStatus()) ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (is_array($installResult) && !empty($installResult['errors'])): ?>
        <div class="homework-note homework-note--spaced">
          <?php foreach ($installResult['errors'] as $error): ?>
            <div><?= htmlspecialcharsbx((string)$error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>