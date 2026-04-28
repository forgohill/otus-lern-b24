<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\AgeSexSurvivalReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 5");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$reportRows = [];
$reportError = null;

try {
  $reportRows = (new AgeSexSurvivalReport())->getRows();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}

$rowsByGroup = [];

foreach ($reportRows as $row) {
  $rowsByGroup[(string)$row['age_group'] . '|' . (string)$row['sex']] = $row;
}

$ageLabels = [
  '0-5' => '0–5',
  '6-12' => '6–12',
  '13-18' => '13–18',
  '19-30' => '19–30',
  '31-45' => '31–45',
  '46-60' => '46–60',
  '60+' => '60+',
  'unknown' => 'unknown',
];

$sexLabels = [
  'female' => 'Женщина',
  'male' => 'Мужчина',
  'unknown' => 'Неизвестно',
];

$keyRows = [
  '0-5|female' => $rowsByGroup['0-5|female'] ?? null,
  '0-5|male' => $rowsByGroup['0-5|male'] ?? null,
  '6-12|female' => $rowsByGroup['6-12|female'] ?? null,
  '6-12|male' => $rowsByGroup['6-12|male'] ?? null,
  '13-18|female' => $rowsByGroup['13-18|female'] ?? null,
  '13-18|male' => $rowsByGroup['13-18|male'] ?? null,
  '19-30|female' => $rowsByGroup['19-30|female'] ?? null,
  '19-30|male' => $rowsByGroup['19-30|male'] ?? null,
  '31-45|female' => $rowsByGroup['31-45|female'] ?? null,
  '31-45|male' => $rowsByGroup['31-45|male'] ?? null,
  '46-60|female' => $rowsByGroup['46-60|female'] ?? null,
  '46-60|male' => $rowsByGroup['46-60|male'] ?? null,
  '60+|female' => $rowsByGroup['60+|female'] ?? null,
  '60+|male' => $rowsByGroup['60+|male'] ?? null,
];
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Граница детства: когда шанс на спасение резко падал»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection4.php">
        Предыдущая
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection6.php">
        Следующая
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        Эта выборка показывает, что на Titanic возраст сам по себе не был главным фактором.
        Самая сильная картина появляется, если смотреть возраст вместе с полом: маленькие мальчики ещё имели высокий шанс на спасение,
        но уже подростки мужского пола попадали в группу риска почти как взрослые мужчины.
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
                <th>Группа</th>
                <th>Пол</th>
                <th>Всего</th>
                <th>Выжили</th>
                <th>Выживаемость</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reportRows as $row): ?>
                <tr>
                  <td><?= htmlspecialcharsbx($ageLabels[(string)$row['age_group']] ?? (string)$row['age_group']) ?></td>
                  <td><?= htmlspecialcharsbx($sexLabels[(string)$row['sex']] ?? (string)$row['sex']) ?></td>
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
            $keyRows['0-5|female'] !== null &&
            $keyRows['0-5|male'] !== null &&
            $keyRows['6-12|female'] !== null &&
            $keyRows['6-12|male'] !== null &&
            $keyRows['13-18|female'] !== null &&
            $keyRows['13-18|male'] !== null &&
            $keyRows['19-30|female'] !== null &&
            $keyRows['19-30|male'] !== null &&
            $keyRows['31-45|female'] !== null &&
            $keyRows['31-45|male'] !== null &&
            $keyRows['46-60|female'] !== null &&
            $keyRows['46-60|male'] !== null &&
            $keyRows['60+|female'] !== null &&
            $keyRows['60+|male'] !== null
          ): ?>
            <p>
              До определённого возраста мальчики ещё воспринимались как дети.
              В группе 0–5 лет выживаемость высокая у обоих полов:
              женщины/девочки — <?= htmlspecialcharsbx(number_format((float)$keyRows['0-5|female']['survival_rate'], 1, '.', '')) ?>%,
              мужчины/мальчики — <?= htmlspecialcharsbx(number_format((float)$keyRows['0-5|male']['survival_rate'], 1, '.', '')) ?>%.
            </p>
            <p>
              Но дальше появляется резкий перелом. В группе 13–18 лет девушки выживали в
              <?= htmlspecialcharsbx(number_format((float)$keyRows['13-18|female']['survival_rate'], 1, '.', '')) ?>% случаев,
              а юноши — только в <?= htmlspecialcharsbx(number_format((float)$keyRows['13-18|male']['survival_rate'], 1, '.', '')) ?>%.
              Это один из самых сильных контрастов во всей таблице.
            </p>
            <p>
              Что видно у женщин:
              почти во всех возрастных группах после 13 лет женская выживаемость остаётся высокой.
              13–18 — <?= htmlspecialcharsbx(number_format((float)$keyRows['13-18|female']['survival_rate'], 1, '.', '')) ?>%,
              19–30 — <?= htmlspecialcharsbx(number_format((float)$keyRows['19-30|female']['survival_rate'], 1, '.', '')) ?>%,
              31–45 — <?= htmlspecialcharsbx(number_format((float)$keyRows['31-45|female']['survival_rate'], 1, '.', '')) ?>%,
              46–60 — <?= htmlspecialcharsbx(number_format((float)$keyRows['46-60|female']['survival_rate'], 1, '.', '')) ?>%.
              Это хорошо показывает, что для женщин возраст был менее критичным фактором.
            </p>
            <p>
              Что видно у мужчин:
              у мужчин после детского возраста выживаемость резко падает.
              13–18 — <?= htmlspecialcharsbx(number_format((float)$keyRows['13-18|male']['survival_rate'], 1, '.', '')) ?>%,
              19–30 — <?= htmlspecialcharsbx(number_format((float)$keyRows['19-30|male']['survival_rate'], 1, '.', '')) ?>%,
              31–45 — <?= htmlspecialcharsbx(number_format((float)$keyRows['31-45|male']['survival_rate'], 1, '.', '')) ?>%,
              46–60 — <?= htmlspecialcharsbx(number_format((float)$keyRows['46-60|male']['survival_rate'], 1, '.', '')) ?>%,
              60+ — <?= htmlspecialcharsbx(number_format((float)$keyRows['60+|male']['survival_rate'], 1, '.', '')) ?>%.
              То есть начиная с подросткового возраста мужчина почти всегда оказывался в зоне высокого риска.
            </p>
            <p>
              Самое интригующее наблюдение:
              группа 6–12 лет выглядит необычно: девочки имеют выживаемость
              <?= htmlspecialcharsbx(number_format((float)$keyRows['6-12|female']['survival_rate'], 1, '.', '')) ?>%,
              а мальчики — <?= htmlspecialcharsbx(number_format((float)$keyRows['6-12|male']['survival_rate'], 1, '.', '')) ?>%.
              Это выбивается из общей логики таблицы.
              Здесь стоит отдельно проверить класс билета, размер семьи и количество пассажиров в группе,
              потому что выборка маленькая: всего <?= (int)$keyRows['6-12|female']['total'] ?> девочек и
              <?= (int)$keyRows['6-12|male']['total'] ?> мальчиков.
            </p>
            <h2>Что видно по данным в таблице:</h2>
            <ul>
              <li>0–5 - высокий шанс спасения у обоих полов</li>
              <li>13–18 - резкий перелом между девушками и юношами</li>
              <li>60+ - у женщин картина лучше, но выборка маленькая</li>
            </ul>
          <?php else: ?>
            <p>
              По этим данным возраст лучше читать вместе с полом: тогда видно, как сильно меняется шанс на спасение.
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
