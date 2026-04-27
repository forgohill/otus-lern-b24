<?

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Models\Titanic\Service\TitanicCsvParser;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle("Проект Titanic: парсер CSV");

Loader::includeModule('ui');
Extension::load([
  'ui.fonts.opensans',
]);

Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$csvPath = $_SERVER['DOCUMENT_ROOT'] . '/homeworks/homework4/titanic.csv';
$parserRows = [];
$parserError = null;
$showDump = isset($_GET['preview']) && $_GET['preview'] === 'Y';

if ($showDump) {
  try {
    $parser = new TitanicCsvParser();
    $parserRows = $parser->parse($csvPath);

    if (function_exists('dump')) {
      dump(array_slice($parserRows, 0, 5));
    }
  } catch (\Throwable $exception) {
    $parserError = $exception->getMessage();
  }
}
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">Страница для проверки работы CSV-парсера Titanic.</div>

    <div class="homework-toolbar" style="margin-top: 18px;">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>

      <a class="homework-btn homework-btn--primary" href="/homeworks/homework4/admin.php?preview=Y">
        Показать dump
      </a>
    </div>

    <?php if ($parserError !== null): ?>
      <div class="homework-note homework-note--spaced">
        <?= htmlspecialcharsbx($parserError) ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">Пустая таблица для просмотра структуры данных.</div>

      <div class="homework-table-wrapper" style="overflow-x: auto;">
        <table class="homework-table">
          <thead>
            <tr>
              <th>#</th>
              <th>PassengerId</th>
              <th>Survived</th>
              <th>Pclass</th>
              <th>Name</th>
              <th>Sex</th>
              <th>Age</th>
              <th>SibSp</th>
              <th>Parch</th>
              <th>Ticket</th>
              <th>Fare</th>
              <th>Cabin</th>
              <th>Embarked</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($showDump && !empty($parserRows)): ?>
              <?php foreach (array_slice($parserRows, 0, 5) as $index => $row): ?>
                <tr>
                  <td><?= (int)($index + 1) ?></td>
                  <td><?= htmlspecialcharsbx((string)$row['passenger_id']) ?></td>
                  <td><?= htmlspecialcharsbx((string)$row['survived']) ?></td>
                  <td><?= htmlspecialcharsbx((string)$row['pclass']) ?></td>
                  <td><?= htmlspecialcharsbx((string)$row['name']) ?></td>
                  <td><?= htmlspecialcharsbx((string)$row['sex']) ?></td>
                  <td><?= htmlspecialcharsbx((string)($row['age'] ?? '')) ?></td>
                  <td><?= htmlspecialcharsbx((string)$row['sibsp']) ?></td>
                  <td><?= htmlspecialcharsbx((string)$row['parch']) ?></td>
                  <td><?= htmlspecialcharsbx((string)$row['ticket']) ?></td>
                  <td><?= htmlspecialcharsbx((string)$row['fare']) ?></td>
                  <td><?= htmlspecialcharsbx((string)($row['cabin'] ?? '')) ?></td>
                  <td><?= htmlspecialcharsbx((string)($row['embarked'] ?? '')) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="12" style="text-align: center; padding: 24px 16px;">
                  Нажмите «Показать dump», чтобы увидеть результат парсера.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
