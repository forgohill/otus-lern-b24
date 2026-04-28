<?

use Models\Titanic\Install\IblockInstaller;
use Models\Titanic\Install\TableInstaller;
use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("ДЗ #4: Проект Titanic");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$iblockInstaller = new IblockInstaller();
$tableInstaller = new TableInstaller();
$allIblocksInstalled = true;

foreach ($iblockInstaller->getDictionaryInstallers() as $dictionaryInstaller) {
  if (!$dictionaryInstaller->isInstalled()) {
    $allIblocksInstalled = false;
    break;
  }
}

$allTablesInstalled = $tableInstaller->isInstalled();
$allTablesFilled = $allTablesInstalled;
$tableStates = $tableInstaller->getTableStates();

foreach ($tableInstaller->getTableClasses() as $tableClass) {
  if (empty($tableStates[$tableClass]['installed'])) {
    $allTablesFilled = false;
    break;
  }

  try {
    if ((int)$tableClass::getCount() === 0) {
      $allTablesFilled = false;
      break;
    }
  } catch (\Throwable) {
    $allTablesFilled = false;
    break;
  }
}

$titanicReady = $allIblocksInstalled && $allTablesInstalled && $allTablesFilled;
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">Стартовая страница домашнего проекта Titanic.</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/index.php">
        Вернуть к списку ДЗ
      </a>
      <a class="homework-btn homework-btn--primary" href="/homeworks/homework4/install/index.php">
        Открыть инсталятор
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <?php if (!$titanicReady): ?>
        <div class="homework-setup-banner">
          <div>
            <p class="homework-status-title">Приложение Titanic ещё не готово к работе</p>
            <p class="homework-status-text">
              Для работы с приложением Titanic необходимо установить и заполнить его данные.
              Приложение недоступно, нажмите кнопку «Открыть инсталятор» выше.
            </p>
          </div>
        </div>
      <?php else: ?>
        <div class="homework-topic-grid">
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection1.php">
            «Шанс на спасение: пол и класс решали больше, чем возраст»
          </a>
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection2.php">
            «Размер семьи: одиночки выживали хуже»
          </a>
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection3.php">
            «Одиночка или не одиночка»
          </a>
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection4.php">
            «Титул в имени: скрытая социальная роль»
          </a>
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection5.php">
            «Граница детства: когда шанс на спасение резко падал»
          </a>
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection6.php">
            «Где ты жил на Titanic - там и начиналась статистика выживания»
          </a>
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection7.php">
            «Несколько кают у одного пассажира»
          </a>
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection8.php">
            «Группы по Ticket: одиночные и групповые билеты»
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
