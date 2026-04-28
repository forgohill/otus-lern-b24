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
      <?php if ($titanicReady): ?>
        <a class="homework-btn homework-btn--primary" href="/homeworks/homework4/passengers.php">
          Показать всю таблицу
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-description">
        <p>
          В проекте есть две основные части: код модуля в <code>local/modules/Titanic</code> и пользовательские страницы домашнего задания в <code>homeworks/homework4</code>. Модуль содержит бизнес-логику, ORM-классы, установщики, сервисы импорта и репозитории. Папка <code>homework4</code> даёт веб-интерфейс для установки, заполнения данных и просмотра аналитики.
        </p>
        <p>
          Данные берутся из файла <code>titanic.csv</code>. В нём 891 пассажир Titanic плюс строка заголовков. CSV парсится сервисом <code>TitanicCsvParser</code>, после чего данные раскладываются не в одну плоскую таблицу, а в несколько связанных сущностей.
        </p>
        <p>
          В проекте создаются три справочных инфоблока Bitrix в типе <code>lists</code>: классы пассажиров, порты посадки и палубы кают. Эти инфоблоки используются как справочники, а связь с ними выполняется через ID элементов инфоблоков. Для получения ORM-класса инфоблока используются сервисы <code>TitanicClassesIblock</code>, <code>TitanicPortsIblock</code> и <code>TitanicCabinDecksIblock</code>, которые вызывают <code>Iblock::wakeUp($id)->getEntityDataClass()</code>.
        </p>

        <details class="homework-description-more">
          <summary>Читать описание полностью</summary>
          <p>
            Основные пользовательские таблицы описаны через D7 ORM <code>DataManager</code>: <code>TicketsTable</code>, <code>CabinsTable</code>, <code>PassengersTable</code> и <code>PassengerCabinTable</code>. Главная таблица проекта — <code>otus_titanic_passengers</code>. Она хранит пассажиров и связывает их с билетом, классом, портом посадки, палубой и каютами.
          </p>
          <p>
            В проекте отрабатываются разные типы ORM-связей Bitrix. <code>Reference</code> используется для связей пассажира с билетом и элементами инфоблоков. <code>OneToMany</code> используется между билетом и пассажирами: один билет может относиться к нескольким пассажирам. <code>ManyToMany</code> используется между пассажирами и каютами через промежуточную таблицу <code>otus_titanic_passenger_cabin</code>.
          </p>
          <p>
            Установка данных сделана пошагово через страницу <code>homeworks/homework4/install/index.php</code>. Сначала устанавливаются справочные инфоблоки, затем создаются ORM-таблицы, потом импортируются билеты, каюты и пассажиры. Импорт разбит на отдельные сервисы: <code>TitanicTicketsImporter</code>, <code>TitanicCabinsImporter</code>, <code>TitanicPassengersImporter</code>. Такой порядок нужен, потому что пассажиры ссылаются на уже созданные билеты, каюты и элементы инфоблоков.
          </p>
          <p>
            После установки проект показывает стартовую страницу с 12 аналитическими карточками. Каждая карточка использует отдельный репозиторий отчёта: по полу и классу, размеру семьи, одиночным пассажирам, титулам в имени, возрастным группам, палубам, нескольким каютам, группам билетов, префиксам билетов, стоимости билетов и зависимости порта посадки от класса.
          </p>
          <p>
            Отдельно есть страница полной таблицы пассажиров. Она использует <code>PassengersRepository</code>, который получает ORM-коллекцию пассажиров вместе со связанными сущностями: билетом, классом, портом, палубой и каютами. Это показывает практическую работу <code>fetchCollection()</code> и объектной модели D7 ORM.
          </p>
        </details>
      </div>
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
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection9.php">
            «Префикс билета: скрытая подсказка о типе поездки»
          </a>
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection10.php">
            «Цена спасения: Fare и реальная стоимость на человека»
          </a>
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection11.php">
            «Бесплатные билеты: пассажиры с Fare = 0»
          </a>
          <a class="homework-topic-card" href="/homeworks/homework4/cards/selection12.php">
            «Порт посадки и класс: корреляция не равна причине»
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
