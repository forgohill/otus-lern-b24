<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\AloneSurvivalReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 3");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$reportRows = [];
$reportError = null;

try {
  $reportRows = (new AloneSurvivalReport())->getRows();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}

$aloneRow = null;
$notAloneRow = null;

foreach ($reportRows as $row) {
  if ((int)$row['is_alone'] === 1) {
    $aloneRow = $row;
    continue;
  }

  $notAloneRow = $row;
}
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Одиночка или не одиночка»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection2.php">
        Предыдущая
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection4.php">
        Следующая
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        Агрегированная выборка через Bitrix D7 ORM: сравнение выживаемости по признаку одиночка или не одиночка.
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
                <th>Один / не один</th>
                <th>Всего</th>
                <th>Выжили</th>
                <th>Выживаемость</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reportRows as $row): ?>
                <tr>
                  <td><?= htmlspecialcharsbx((string)$row['label']) ?></td>
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
          <?php if ($aloneRow !== null && $notAloneRow !== null): ?>
            <p>
              Наличие родственников на борту заметно повышало шанс выжить.
              Среди пассажиров, которые были не одни, выживаемость составила
              <?= htmlspecialcharsbx(number_format((float)$notAloneRow['survival_rate'], 1, '.', '')) ?>%,
              а среди одиночек — только <?= htmlspecialcharsbx(number_format((float)$aloneRow['survival_rate'], 1, '.', '')) ?>%.
            </p>
            <p>
              Что видно по данным:
              разница почти в 20 процентных пунктов.
              Это сильный сигнал: пассажиры с семьёй чаще оказывались в более организованной ситуации —
              могли держаться вместе, помогать друг другу, искать шлюпки и не теряться в хаосе эвакуации.
            </p>
            <p>
              Самое интересное наблюдение:
              одиночек на борту было больше всего — <?= (int)$aloneRow['total'] ?> человек, но выживали они хуже.
              Возможно, в момент катастрофы человек без группы поддержки легче «растворялся» в толпе и позже получал доступ к спасению.
            </p>
          <?php else: ?>
            <p>
              По этим данным видно, что семейный контекст влияет на шанс спасения и связан с тем,
              как пассажиры могли держаться вместе во время эвакуации.
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
