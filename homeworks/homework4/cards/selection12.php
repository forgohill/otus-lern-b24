<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\EmbarkedClassSurvivalReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 12");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$reportRows = [];
$reportError = null;

try {
  $reportRows = (new EmbarkedClassSurvivalReport())->getRows();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Порт посадки и класс: корреляция не равна причине»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection11.php">
        Предыдущая
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
      <span class="homework-btn homework-btn--secondary homework-btn--disabled" aria-disabled="true">
        Следующая
      </span>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        Embarked сам по себе слабее объясняет выживаемость. Но если смотреть порт посадки вместе с классом,
        становится видно, что часть эффекта порта связана с составом пассажиров по классам.
      </div>

      <?php if ($reportError !== null): ?>
        <div class="homework-note">
          Не удалось получить данные отчёта: <?= htmlspecialcharsbx($reportError) ?>
        </div>
      <?php elseif ($reportRows === []): ?>
        <div class="homework-note">
          Нет данных по порту посадки и классу. Проверьте, что таблица пассажиров и справочники заполнены.
        </div>
      <?php else: ?>
        <div class="homework-table-wrapper">
          <table class="homework-table">
            <thead>
              <tr>
                <th>Порт</th>
                <th>Название порта</th>
                <th>Класс</th>
                <th>Всего</th>
                <th>Выжили</th>
                <th>Выживаемость</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reportRows as $row): ?>
                <tr>
                  <td><?= htmlspecialcharsbx((string)$row['PORT']) ?></td>
                  <td><?= htmlspecialcharsbx((string)$row['PORT_NAME']) ?></td>
                  <td><?= (int)$row['PCLASS'] ?></td>
                  <td><?= (int)$row['TOTAL'] ?></td>
                  <td><?= (int)$row['SURVIVED'] ?></td>
                  <td><?= htmlspecialcharsbx(number_format((float)$row['SURVIVAL_RATE'], 1, '.', '')) ?>%</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="homework-report-summary">
          <h2>Главный вывод:</h2>
          <p>
            Порт посадки оказался не просто географией, а скрытым социальным признаком.
            Cherbourg показывает высокую выживаемость, потому что среди его пассажиров много 1 класса.
            Queenstown почти полностью состоит из 3 класса, а Southampton даёт самую большую и самую тяжёлую группу —
            много пассажиров 3 класса с выживаемостью всего 19.0%.
            Поэтому Embarked полезно анализировать не отдельно, а вместе с Pclass: порт помогает понять,
            какие социальные группы заходили на Titanic в разных точках маршрута.
          </p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
