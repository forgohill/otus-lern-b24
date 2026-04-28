<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\FamilySizeSurvivalReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 2");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$reportRows = [];
$reportError = null;
$reportRowsBySize = [];

try {
  $reportRows = (new FamilySizeSurvivalReport())->getRows();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}

$singleRow = null;
$bestFamilyRow = null;

foreach ($reportRows as $row) {
  $reportRowsBySize[(int)$row['family_size']] = $row;

  if ((int)$row['family_size'] === 1) {
    $singleRow = $row;
    continue;
  }

  if ($bestFamilyRow === null || (float)$row['survival_rate'] > (float)$bestFamilyRow['survival_rate']) {
    $bestFamilyRow = $row;
  }
}
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Размер семьи: одиночки выживали хуже»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection1.php">
        Предыдущая
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection3.php">
        Следующая
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        Агрегированная выборка через Bitrix D7 ORM: сравнение выживаемости по размеру семьи пассажира.
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
                <th>Размер семьи</th>
                <th>Всего</th>
                <th>Выжили</th>
                <th>Выживаемость</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reportRows as $row): ?>
                <tr>
                  <td><?= htmlspecialcharsbx((string)$row['family_label']) ?></td>
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
          <?php if ($singleRow !== null && $bestFamilyRow !== null): ?>
            <?php
            $familyTwoRow = $reportRowsBySize[2] ?? null;
            $familyThreeRow = $reportRowsBySize[3] ?? null;
            $familyFourRow = $reportRowsBySize[4] ?? null;
            $familyFivePlusRow = $reportRowsBySize[5] ?? null;
            ?>
            <p>
              На Titanic одиночество не помогало выжить. Пассажиры без семьи имели один из самых низких показателей выживаемости —
              всего <?= htmlspecialcharsbx(number_format((float)$singleRow['survival_rate'], 1, '.', '')) ?>%.
              Но и слишком большая семья тоже становилась фактором риска: в группе 5+ человек выжило только
              <?= htmlspecialcharsbx(number_format((float)($familyFivePlusRow['survival_rate'] ?? 0.0), 1, '.', '')) ?>%.
            </p>
            <p>
              Что видно по данным:
              лучше всего выживали пассажиры с небольшой семьёй рядом.
              У групп из 2–4 человек выживаемость заметно выше:
            </p>
            <ul>
              <?php if ($familyTwoRow !== null): ?>
                <li>семья из 2 человек — <?= htmlspecialcharsbx(number_format((float)$familyTwoRow['survival_rate'], 1, '.', '')) ?>%</li>
              <?php endif; ?>
              <?php if ($familyThreeRow !== null): ?>
                <li>семья из 3 человек — <?= htmlspecialcharsbx(number_format((float)$familyThreeRow['survival_rate'], 1, '.', '')) ?>%</li>
              <?php endif; ?>
              <?php if ($familyFourRow !== null): ?>
                <li>семья из 4 человек — <?= htmlspecialcharsbx(number_format((float)$familyFourRow['survival_rate'], 1, '.', '')) ?>%</li>
              <?php endif; ?>
            </ul>
            <p>
              Это может говорить о том, что небольшая семья давала преимущество: люди могли помогать друг другу,
              держаться вместе и быстрее ориентироваться во время эвакуации.
            </p>
            <p>
              Самое интересное наблюдение: максимальная выживаемость у пассажиров с размером семьи
              <?= htmlspecialcharsbx((string)$bestFamilyRow['family_label']) ?> — <?= htmlspecialcharsbx(number_format((float)$bestFamilyRow['survival_rate'], 1, '.', '')) ?>%.
              Но после этого показатель резко падает: у семей 5+ человек выживаемость всего
              <?= htmlspecialcharsbx(number_format((float)($familyFivePlusRow['survival_rate'] ?? 0.0), 1, '.', '')) ?>%.
              То есть большая семья могла уже не помогать, а усложнять спасение: труднее собраться, перемещаться и попасть в шлюпку всем вместе.
            </p>
          <?php else: ?>
            <p>
              По этим данным у одиночек и пассажиров с семьёй заметно различается шанс выживания,
              и семейный контекст влияет на исход не меньше, чем класс билета.
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
