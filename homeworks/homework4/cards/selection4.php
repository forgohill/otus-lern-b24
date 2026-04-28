<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\TitleSurvivalReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 4");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$reportRows = [];
$reportError = null;

try {
  $reportRows = (new TitleSurvivalReport())->getRows();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}

$rowsByTitle = [];

foreach ($reportRows as $row) {
  $rowsByTitle[(string)$row['title']] = $row;
}

$titleLabels = [
  'Mr' => 'Mr',
  'Mrs' => 'Mrs',
  'Miss' => 'Miss',
  'Master' => 'Master',
  'Rev' => 'Rev',
  'Dr' => 'Dr',
];

$titleMeanings = [
  'Mr' => 'взрослый мужчина',
  'Mrs' => 'замужняя женщина',
  'Miss' => 'незамужняя женщина / девушка',
  'Master' => 'мальчик',
  'Rev' => 'священник',
  'Dr' => 'доктор',
];
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Титул из имени: Mr, Mrs, Miss, Master»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection3.php">
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
        Агрегированная выборка через Bitrix D7 ORM: сравнение выживаемости по титулу пассажира.
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
                <th>Титул</th>
                <th>Всего</th>
                <th>Выжили</th>
                <th>Выживаемость</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($titleLabels as $title => $label): ?>
                <?php if (!isset($rowsByTitle[$title])) { continue; } ?>
                <?php $row = $rowsByTitle[$title]; ?>
                <tr>
                  <td><?= htmlspecialcharsbx((string)$label) ?></td>
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
          <?php if (
            isset($rowsByTitle['Mr'], $rowsByTitle['Mrs'], $rowsByTitle['Miss'], $rowsByTitle['Master'], $rowsByTitle['Rev'], $rowsByTitle['Dr'])
          ): ?>
            <p>
              Титул в имени оказался не просто частью текста, а сильным скрытым признаком.
              Он помогает увидеть не только пол пассажира, но и его социальную роль:
              взрослый мужчина, женщина, девушка, ребёнок, доктор или священник.
            </p>
            <p>
              Что видно по данным:
              самая низкая выживаемость у группы Mr — всего <?= htmlspecialcharsbx(number_format((float)$rowsByTitle['Mr']['survival_rate'], 1, '.', '')) ?>%.
              Это взрослая мужская группа, и она оказалась в зоне максимального риска.
              Из <?= (int)$rowsByTitle['Mr']['total'] ?> мужчин с титулом Mr выжили только <?= (int)$rowsByTitle['Mr']['survived'] ?>.
            </p>
            <p>
              У женщин показатели намного выше:
              Mrs — <?= htmlspecialcharsbx(number_format((float)$rowsByTitle['Mrs']['survival_rate'], 1, '.', '')) ?>%,
              Miss — <?= htmlspecialcharsbx(number_format((float)$rowsByTitle['Miss']['survival_rate'], 1, '.', '')) ?>%.
              Это хорошо подтверждает общую логику эвакуации: женщины чаще получали приоритет при посадке в шлюпки.
            </p>
            <p>
              Интересное наблюдение:
              титул Master показывает мальчиков, и их выживаемость — <?= htmlspecialcharsbx(number_format((float)$rowsByTitle['Master']['survival_rate'], 1, '.', '')) ?>%.
              Это заметно выше, чем у взрослых мужчин с титулом Mr, но ниже, чем у женщин.
              Значит, возрастная и социальная роль тоже влияла на шанс спасения.
            </p>
            <p>
              Необычная деталь:
              у священников с титулом Rev выживаемость <?= htmlspecialcharsbx(number_format((float)$rowsByTitle['Rev']['survival_rate'], 1, '.', '')) ?>%.
              Из <?= (int)$rowsByTitle['Rev']['total'] ?> человек не выжил никто.
              Для группы Dr показатель составил <?= htmlspecialcharsbx(number_format((float)$rowsByTitle['Dr']['survival_rate'], 1, '.', '')) ?>%,
              что тоже хорошо показывает влияние социальной роли.
            </p>
            <h2>Что означают титулы:</h2>
            <ul>
              <?php foreach ($titleMeanings as $title => $meaning): ?>
                <li><?= htmlspecialcharsbx($title) ?> — <?= htmlspecialcharsbx($meaning) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>
              По этим данным титул в имени влияет на шанс выживания и помогает увидеть социальную роль пассажира.
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
