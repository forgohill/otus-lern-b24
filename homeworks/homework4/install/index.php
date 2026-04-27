<?

use Models\Titanic\Install\IblockInstaller;
use Models\Titanic\Install\TableInstaller;
use Models\Titanic\Orm\CabinsTable;
use Models\Titanic\Orm\PassengerCabinTable;
use Models\Titanic\Orm\PassengersTable;
use Models\Titanic\Orm\TicketsTable;
use Models\Titanic\Service\TitanicCabinsImporter;
use Models\Titanic\Service\TitanicPassengersImporter;
use Models\Titanic\Service\TitanicTicketsImporter;
use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle("Проект Titanic");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$installer = new IblockInstaller();
$tableInstaller = new TableInstaller();
$cabinsImporter = new TitanicCabinsImporter();
$passengersImporter = new TitanicPassengersImporter();
$ticketsImporter = new TitanicTicketsImporter();

$installResult = null;
$tableInstallResult = null;
$tableUninstallResult = null;
$ticketsImportResult = $_SESSION['homework4_tickets_import_result'] ?? null;
$cabinsImportResult = $_SESSION['homework4_cabins_import_result'] ?? null;
$passengersImportResult = $_SESSION['homework4_passengers_import_result'] ?? null;

unset($_SESSION['homework4_tickets_import_result']);
unset($_SESSION['homework4_cabins_import_result']);
unset($_SESSION['homework4_passengers_import_result']);

$dictionaryInstallers = $installer->getDictionaryInstallers();
$tableStates = $tableInstaller->getTableStates();
$tableFillStates = [];

$allIblocksInstalled = true;

foreach ($dictionaryInstallers as $dictionaryInstaller) {
  if (!$dictionaryInstaller->isInstalled()) {
    $allIblocksInstalled = false;
    break;
  }
}

$tablesInstalled = $tableInstaller->isInstalled();
$ticketsFilled = false;
$cabinsFilled = false;
$passengersFilled = false;

$fillTableTitles = [
  TicketsTable::class => 'Билеты Titanic',
  CabinsTable::class => 'Каюты Titanic',
  PassengersTable::class => 'Пассажиры Titanic',
  PassengerCabinTable::class => 'Связи пассажир-каюта',
];

foreach ($tableInstaller->getTableClasses() as $tableClass) {
  $installed = !empty($tableStates[$tableClass]['installed']);
  $count = 0;
  $errors = [];

  if ($installed) {
    try {
      $count = (int)$tableClass::getCount();
    } catch (\Throwable $exception) {
      $errors[] = $exception->getMessage();
    }
  }

  $tableFillStates[$tableClass] = [
    'title' => $fillTableTitles[$tableClass] ?? basename(str_replace('\\', '/', $tableClass)),
    'table' => $tableStates[$tableClass]['table'] ?? $tableClass::getTableName(),
    'installed' => $installed,
    'checked' => $errors === [],
    'filled' => $errors === [] && $count > 0,
    'count' => $count,
    'errors' => $errors,
  ];
}

if ($tablesInstalled) {
  $ticketsFilled = $tableFillStates[TicketsTable::class]['filled'];
  $cabinsFilled = $tableFillStates[CabinsTable::class]['filled'];
  $passengersFilled = $tableFillStates[PassengersTable::class]['filled'];
}

$allTablesFilled = $tableFillStates !== [];
foreach ($tableFillStates as $tableFillState) {
  if (!$tableFillState['filled']) {
    $allTablesFilled = false;
    break;
  }
}

$tablesCanBeInstalled = $allIblocksInstalled;
$ticketsCanBeImported = $allIblocksInstalled && $tablesInstalled && !$ticketsFilled;
$cabinsCanBeImported = $allIblocksInstalled && $tablesInstalled && $ticketsFilled && !$cabinsFilled;
$passengersCanBeImported = $allIblocksInstalled && $tablesInstalled && $ticketsFilled && $cabinsFilled && !$passengersFilled;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && isset($_POST['fill_titanic_tickets'])) {
  $ticketsImportResult = $ticketsImporter->import($_SERVER['DOCUMENT_ROOT'] . '/homeworks/homework4/titanic.csv');
  $_SESSION['homework4_tickets_import_result'] = $ticketsImportResult;
  LocalRedirect($APPLICATION->GetCurPage(false));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && isset($_POST['fill_titanic_cabins'])) {
  $cabinsImportResult = $cabinsImporter->import($_SERVER['DOCUMENT_ROOT'] . '/homeworks/homework4/titanic.csv');
  $_SESSION['homework4_cabins_import_result'] = $cabinsImportResult;
  LocalRedirect($APPLICATION->GetCurPage(false));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && isset($_POST['fill_titanic_passengers'])) {
  $passengersImportResult = $passengersImporter->import($_SERVER['DOCUMENT_ROOT'] . '/homeworks/homework4/titanic.csv');
  $_SESSION['homework4_passengers_import_result'] = $passengersImportResult;
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

      <a
        class="homework-btn homework-btn--success<?= !$allTablesFilled ? ' homework-btn--disabled' : '' ?>"
        href="<?= $allTablesFilled ? '/homeworks/homework4/index.php' : 'javascript:void(0)' ?>"
        <?= !$allTablesFilled ? 'aria-disabled="true"' : '' ?>>
        Все данные установлены, вернитесь на страницу с приложением Titanic
      </a>
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

      <?php if (!$ticketsCanBeImported): ?>
        <div class="homework-note">
          <?php if (!$allIblocksInstalled): ?>
            Сначала установите нужные инфоблоки и таблицы.
          <?php elseif (!$tablesInstalled): ?>
            Сначала установите пользовательские таблицы.
          <?php elseif ($ticketsFilled): ?>
            Билеты уже заполнены.
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (!$cabinsCanBeImported): ?>
        <div class="homework-note">
          <?php if (!$allIblocksInstalled): ?>
            Сначала установите нужные инфоблоки, таблицы и заполните билеты.
          <?php elseif (!$tablesInstalled): ?>
            Сначала установите пользовательские таблицы и заполните билеты.
          <?php elseif (!$ticketsFilled): ?>
            Сначала заполните билеты.
          <?php elseif ($cabinsFilled): ?>
            Каюты уже заполнены.
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (!$passengersCanBeImported): ?>
        <div class="homework-note">
          <?php if (!$allIblocksInstalled): ?>
            Сначала установите нужные инфоблоки, таблицы, билеты и каюты.
          <?php elseif (!$tablesInstalled): ?>
            Сначала установите пользовательские таблицы, заполните билеты и каюты.
          <?php elseif (!$ticketsFilled): ?>
            Сначала заполните билеты.
          <?php elseif (!$cabinsFilled): ?>
            Сначала заполните каюты.
          <?php elseif ($passengersFilled): ?>
            Пассажиры уже заполнены.
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (is_array($ticketsImportResult)): ?>
        <div class="homework-note">
          <?php if (!empty($ticketsImportResult['success'])): ?>
            Билеты заполнены.
          <?php else: ?>
            Заполнение билетов завершилось с ошибками.
          <?php endif; ?>
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
            class="homework-btn homework-btn--secondary homework-btn--danger-hover<?= $tableInstaller->isEmpty() ? ' homework-btn--disabled' : '' ?>"
            <?= $tableInstaller->isEmpty() ? 'disabled' : '' ?>>
            Удалить таблицы
          </button>
        </form>

        <form method="post" class="homework-toolbar-form">
          <?= bitrix_sessid_post() ?>
          <button
            type="submit"
            name="fill_titanic_tickets"
            value="Y"
            class="homework-btn homework-btn--primary<?= !$ticketsCanBeImported ? ' homework-btn--disabled' : '' ?>"
            <?= !$ticketsCanBeImported ? 'disabled' : '' ?>>
            Заполнить билеты
          </button>
        </form>

        <form method="post" class="homework-toolbar-form">
          <?= bitrix_sessid_post() ?>
          <button
            type="submit"
            name="fill_titanic_cabins"
            value="Y"
            class="homework-btn homework-btn--primary<?= !$cabinsCanBeImported ? ' homework-btn--disabled' : '' ?>"
            <?= !$cabinsCanBeImported ? 'disabled' : '' ?>>
            Заполнить каюты
          </button>
        </form>

        <form method="post" class="homework-toolbar-form">
          <?= bitrix_sessid_post() ?>
          <button
            type="submit"
            name="fill_titanic_passengers"
            value="Y"
            class="homework-btn homework-btn--primary<?= !$passengersCanBeImported ? ' homework-btn--disabled' : '' ?>"
            <?= !$passengersCanBeImported ? 'disabled' : '' ?>>
            Заполнить пассажиров
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-status-grid">
        <div class="homework-status-list">
          <div class="homework-note homework-note--spaced">
            Это список установленных инфоблоков Titanic.
          </div>
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

        <div class="homework-status-list">
          <div class="homework-note homework-note--spaced">
            Это список кастомных таблиц Titanic.
          </div>
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
      </div>

      <div class="homework-status-list homework-status-list--wide" style="margin-top: 18px;">
        <div class="homework-note homework-note--spaced">
          Это список заполнения кастомных таблиц Titanic.
        </div>
        <?php foreach ($tableFillStates as $tableFillState): ?>
          <div class="homework-status-row">
            <div>
              <p class="homework-status-title"><?= htmlspecialcharsbx($tableFillState['title']) ?></p>
              <p class="homework-status-text">
                Таблица: <?= htmlspecialcharsbx($tableFillState['table']) ?>,
                записей: <?= (int)$tableFillState['count'] ?>
              </p>
              <?php if (!empty($tableFillState['errors'])): ?>
                <?php foreach ($tableFillState['errors'] as $error): ?>
                  <p class="homework-status-text">
                    Ошибка проверки: <?= htmlspecialcharsbx((string)$error) ?>
                  </p>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <span class="homework-status-badge <?= $tableFillState['filled'] ? 'homework-status-badge--ok' : 'homework-status-badge--warn' ?>">
              <?php if (!$tableFillState['installed']): ?>
                Нет таблицы
              <?php elseif (!$tableFillState['checked']): ?>
                Ошибка проверки
              <?php elseif ($tableFillState['filled']): ?>
                Заполнена
              <?php else: ?>
                Пустая
              <?php endif; ?>
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

      <?php if (is_array($ticketsImportResult) && !empty($ticketsImportResult['errors'])): ?>
        <div class="homework-note homework-note--spaced">
          <?php foreach ($ticketsImportResult['errors'] as $error): ?>
            <div><?= htmlspecialcharsbx((string)$error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (is_array($cabinsImportResult)): ?>
        <div class="homework-note homework-note--spaced">
          <?php if (!empty($cabinsImportResult['success'])): ?>
            Каюты заполнены.
          <?php else: ?>
            Заполнение кают завершилось с ошибками.
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (is_array($cabinsImportResult) && !empty($cabinsImportResult['errors'])): ?>
        <div class="homework-note homework-note--spaced">
          <?php foreach ($cabinsImportResult['errors'] as $error): ?>
            <div><?= htmlspecialcharsbx((string)$error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (is_array($passengersImportResult)): ?>
        <div class="homework-note homework-note--spaced">
          <?php if (!empty($passengersImportResult['success'])): ?>
            Пассажиры заполнены.
          <?php else: ?>
            Заполнение пассажиров завершилось с ошибками.
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (is_array($passengersImportResult) && !empty($passengersImportResult['errors'])): ?>
        <div class="homework-note homework-note--spaced">
          <?php foreach ($passengersImportResult['errors'] as $error): ?>
            <div><?= htmlspecialcharsbx((string)$error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
