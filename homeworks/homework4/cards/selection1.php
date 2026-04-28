<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\SexClassSurvivalReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 1");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$reportRows = [];
$reportError = null;
$sexLabels = [
  'female' => 'Женщина',
  'male' => 'Мужчина',
];

try {
  $reportRows = (new SexClassSurvivalReport())->getRows();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Шанс на спасение: пол и класс решали больше, чем возраст»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        Агрегированная выборка через Bitrix D7 ORM: группировка по полу и классу пассажира.
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
                <th>Пол</th>
                <th>Класс</th>
                <th>Всего</th>
                <th>Выжили</th>
                <th>Выживаемость</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reportRows as $row): ?>
                <tr>
                  <td><?= htmlspecialcharsbx($sexLabels[$row['sex']] ?? $row['sex']) ?></td>
                  <td><?= (int)$row['pclass'] ?></td>
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
          <p>
            На Titanic решал не один фактор, а их сочетание: пол + класс билета.
            Самая защищённая группа — женщины 1-го класса: выжили 91 из 94,
            то есть почти 97%. Самая уязвимая группа — мужчины 3-го класса:
            выжили только 13.5%.
          </p>

          <h2>Что видно по данным:</h2>
          <p>
            У женщин выживаемость резко падает от 1-го к 3-му классу:
            96.8% → 92.1% → 50.0%.
            То есть даже для женщин социальный класс сильно влиял на шанс спасения.
          </p>
          <p>
            У мужчин ситуация намного хуже во всех классах. Даже в 1-м классе
            выжило только 36.9%, а во 2-м и 3-м классе — около 15% и 13%.
            Это показывает, насколько сильным было правило эвакуации
            «женщины и дети первыми».
          </p>

          <h2>Интересное наблюдение:</h2>
          <p>
            Женщина 3-го класса имела больше шансов выжить, чем мужчина 1-го класса:
            50.0% против 36.9%. Значит, пол в этой выборке оказался сильнее класса,
            но класс всё равно заметно усиливал или ослаблял шанс на спасение.
          </p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
