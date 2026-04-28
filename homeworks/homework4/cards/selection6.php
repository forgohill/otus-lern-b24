<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\CabinDeckSurvivalReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 6");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$reportRows = [];
$presenceRows = [];
$reportError = null;

try {
  $report = new CabinDeckSurvivalReport();
  $reportRows = $report->getRows();
  $presenceRows = $report->getCabinPresenceRows();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}

$rowsByDeck = [];
$rowsByPresence = [];

foreach ($reportRows as $row) {
  $rowsByDeck[(string)$row['deck']] = $row;
}

foreach ($presenceRows as $row) {
  $rowsByPresence[(string)$row['bucket']] = $row;
}

$deckOrder = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'empty'];
$deckLabels = [
  'A' => 'A',
  'B' => 'B',
  'C' => 'C',
  'D' => 'D',
  'E' => 'E',
  'F' => 'F',
  'G' => 'G',
  'empty' => 'Cabin пустой',
];
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Где ты жил на Titanic - там и начиналась статистика выживания»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection5.php">
        Предыдущая
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection7.php">
        Следующая
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        Поле Cabin выглядит как обычная информация о каюте, но на самом деле оно становится сильным косвенным признаком.
        Если каюта указана, пассажир чаще относился к более обеспеченным группам, особенно к 1 классу.
        Поэтому палуба показывает не только физическое место на корабле, но и социальное положение пассажира.
      </div>

      <?php if ($reportError !== null): ?>
        <div class="homework-note">
          Не удалось получить данные отчёта: <?= htmlspecialcharsbx($reportError) ?>
        </div>
      <?php elseif ($reportRows === []): ?>
        <div class="homework-note">
          Нет данных для отображения. Проверьте, что таблица пассажиров заполнена.
        </div>
      <?php else: ?>
        <div class="homework-table-wrapper">
          <table class="homework-table">
            <thead>
              <tr>
                <th>Палуба</th>
                <th>Всего</th>
                <th>Выжили</th>
                <th>Выживаемость</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($deckOrder as $deck): ?>
                <?php if (!isset($rowsByDeck[$deck])) { continue; } ?>
                <?php $row = $rowsByDeck[$deck]; ?>
                <tr>
                  <td><?= htmlspecialcharsbx((string)$deckLabels[$deck]) ?></td>
                  <td><?= (int)$row['total'] ?></td>
                  <td><?= (int)$row['survived'] ?></td>
                  <td><?= htmlspecialcharsbx(number_format((float)$row['survival_rate'], 1, '.', '')) ?>%</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="homework-report-summary">
          <h2>Главный вывод:</h2>
          <?php if (isset($rowsByDeck['empty'], $rowsByDeck['B'], $rowsByDeck['D'])): ?>
            <p>
              Самая большая и важная группа — это пассажиры без указанной каюты.
              Их <?= (int)$rowsByDeck['empty']['total'] ?> человек, и выживаемость у них всего
              <?= htmlspecialcharsbx(number_format((float)$rowsByDeck['empty']['survival_rate'], 1, '.', '')) ?>%.
              Это резко ниже, чем у большинства палуб с известной каютой.
            </p>
            <p>
              Например:
              B — <?= htmlspecialcharsbx(number_format((float)($rowsByDeck['B']['survival_rate'] ?? 0.0), 1, '.', '')) ?>%,
              D — <?= htmlspecialcharsbx(number_format((float)($rowsByDeck['D']['survival_rate'] ?? 0.0), 1, '.', '')) ?>%,
              E — <?= htmlspecialcharsbx(number_format((float)($rowsByDeck['E']['survival_rate'] ?? 0.0), 1, '.', '')) ?>%.
              То есть пассажиры с известной каютой на этих палубах выживали примерно в 2.5 раза чаще, чем пассажиры с пустым Cabin.
            </p>
            <p>
              Интересное наблюдение:
              пустое значение Cabin — это не просто «нет данных». В контексте Titanic оно само становится важным признаком.
              Скорее всего, отсутствие каюты чаще встречалось у пассажиров 2-го и 3-го класса,
              поэтому группа Cabin пустой показывает гораздо более низкую выживаемость.
            </p>
            <p>
              Аккуратный статистический момент:
              по палубам A, F и особенно G нужно делать выводы осторожно.
              Там мало пассажиров в выборке.
              Например, на палубе G всего <?= (int)($rowsByDeck['G']['total'] ?? 0) ?> человек.
            </p>
          <?php else: ?>
            <p>
              По этим данным палуба и наличие каюты связаны с шансом на спасение и косвенно отражают класс пассажира.
            </p>
          <?php endif; ?>
        </div>

        <div class="homework-report-summary">
          <h2>Подборки:</h2>
          <div class="homework-status-list">
            <div class="homework-status-row">
              <div>
                <p class="homework-status-title">Пассажиры с известной каютой</p>
                <p class="homework-status-text">
                  <?= (int)($rowsByPresence['Пассажиры с известной каютой']['total'] ?? 0) ?> человек,
                  выживаемость <?= htmlspecialcharsbx(number_format((float)($rowsByPresence['Пассажиры с известной каютой']['survival_rate'] ?? 0.0), 1, '.', '')) ?>%.
                </p>
              </div>
            </div>
            <div class="homework-status-row">
              <div>
                <p class="homework-status-title">Пассажиры без указанной каюты</p>
                <p class="homework-status-text">
                  <?= (int)($rowsByPresence['Пассажиры без указанной каюты']['total'] ?? 0) ?> человек,
                  выживаемость <?= htmlspecialcharsbx(number_format((float)($rowsByPresence['Пассажиры без указанной каюты']['survival_rate'] ?? 0.0), 1, '.', '')) ?>%.
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="homework-report-summary">
          <h2>Палубы и смысл:</h2>
          <table class="homework-table">
            <thead>
              <tr>
                <th>Палуба</th>
                <th>Что примерно означает</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>A</td>
                <td>Верхняя пассажирская палуба, связана с прогулочной зоной 1 класса</td>
              </tr>
              <tr>
                <td>B</td>
                <td>Одна из престижных верхних палуб, много кают 1 класса</td>
              </tr>
              <tr>
                <td>C</td>
                <td>Тоже в основном дорогие пассажирские каюты, часто 1 класс</td>
              </tr>
              <tr>
                <td>D</td>
                <td>Важная общественная палуба: салоны, столовые, часть кают</td>
              </tr>
              <tr>
                <td>E</td>
                <td>Более смешанная палуба: каюты разных классов и часть служебных помещений</td>
              </tr>
              <tr>
                <td>F</td>
                <td>Ниже, ближе к средним/нижним пассажирским зонам</td>
              </tr>
              <tr>
                <td>G</td>
                <td>Одна из нижних пассажирских палуб, меньше данных в выборке</td>
              </tr>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
