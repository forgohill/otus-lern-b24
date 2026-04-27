<?

use Models\Titanic\Install\IblockInstaller;
use Models\Titanic\Install\TableInstaller;
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
$tableInstaller = new TableInstaller();

$installResult = null;
$tableInstallResult = null;
$tableUninstallResult = null;

$dictionaryInstallers = $installer->getDictionaryInstallers();
$tableStates = $tableInstaller->getTableStates();

$allIblocksInstalled = true;

foreach ($dictionaryInstallers as $dictionaryInstaller) {
  if (!$dictionaryInstaller->isInstalled()) {
    $allIblocksInstalled = false;
    break;
  }
}

$tablesCanBeInstalled = $allIblocksInstalled;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && isset($_POST['install_titanic_iblocks'])) {
  $installResult = $installer->install();
  LocalRedirect($APPLICATION->GetCurPage(false));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && isset($_POST['install_titanic_tables'])) {
  $tableInstallResult = $tableInstaller->install();
  $tableStates = $tableInstaller->getTableStates();
  LocalRedirect($APPLICATION->GetCurPage(false));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && isset($_POST['uninstall_titanic_tables'])) {
  $tableUninstallResult = $tableInstaller->uninstall();
  $tableStates = $tableInstaller->getTableStates();
  LocalRedirect($APPLICATION->GetCurPage(false));
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
          class="homework-btn homework-btn--primary<?= $allIblocksInstalled ? ' homework-btn--disabled' : '' ?>"
          <?= $allIblocksInstalled ? 'disabled' : '' ?>>
          Установить инфоблоки
        </button>
      </form>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">Установка пользовательских таблиц Titanic через ORM.</div>

      <?php if (!$tablesCanBeInstalled): ?>
        <div class="homework-note">
          Сначала установите нужные инфоблоки.
        </div>
      <?php endif; ?>

      <?php if (is_array($tableInstallResult) || is_array($tableUninstallResult)): ?>
        <div class="homework-note">
          <?php if (is_array($tableInstallResult)): ?>
            <?php if (!empty($tableInstallResult['success'])): ?>
              Таблицы установлены.
            <?php else: ?>
              Установка таблиц завершилась с ошибками.
            <?php endif; ?>
          <?php endif; ?>

          <?php if (is_array($tableUninstallResult)): ?>
            <?php if (!empty($tableUninstallResult['success'])): ?>
              Таблицы удалены.
            <?php else: ?>
              Удаление таблиц завершилось с ошибками.
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="homework-toolbar" style="margin-top: 18px;">
        <form method="post" class="homework-toolbar-form">
          <?= bitrix_sessid_post() ?>
          <button
            type="submit"
            name="install_titanic_tables"
            value="Y"
            class="homework-btn homework-btn--primary<?= (!$tablesCanBeInstalled || $tableInstaller->isInstalled()) ? ' homework-btn--disabled' : '' ?>"
            <?= (!$tablesCanBeInstalled || $tableInstaller->isInstalled()) ? 'disabled' : '' ?>>
            Установить таблицы
          </button>
        </form>

        <form method="post" class="homework-toolbar-form">
          <?= bitrix_sessid_post() ?>
          <button
            type="submit"
            name="uninstall_titanic_tables"
            value="Y"
            class="homework-btn homework-btn--secondary<?= $tableInstaller->isEmpty() ? ' homework-btn--disabled' : '' ?>"
            <?= $tableInstaller->isEmpty() ? 'disabled' : '' ?>>
            Удалить таблицы
          </button>
        </form>
      </div>
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

      <div class="homework-status-list" style="margin-top: 18px;">
        <?php foreach ($tableStates as $tableClass => $tableState): ?>
          <div class="homework-status-row">
            <div>
              <p class="homework-status-title"><?= htmlspecialcharsbx(basename(str_replace('\\', '/', $tableClass))) ?></p>
              <p class="homework-status-text">
                Таблица: <?= htmlspecialcharsbx($tableState['table']) ?>
              </p>
            </div>
            <span class="homework-status-badge <?= $tableState['installed'] ? 'homework-status-badge--ok' : 'homework-status-badge--warn' ?>">
              <?= $tableState['installed'] ? 'Установлена' : 'Не установлена' ?>
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

      <?php if (is_array($tableInstallResult) && !empty($tableInstallResult['errors'])): ?>
        <div class="homework-note homework-note--spaced">
          <?php foreach ($tableInstallResult['errors'] as $error): ?>
            <div><?= htmlspecialcharsbx((string)$error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (is_array($tableUninstallResult) && !empty($tableUninstallResult['errors'])): ?>
        <div class="homework-note homework-note--spaced">
          <?php foreach ($tableUninstallResult['errors'] as $error): ?>
            <div><?= htmlspecialcharsbx((string)$error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
