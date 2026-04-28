<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\PassengersRepository;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Titanic: полная таблица пассажиров");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$passengers = [];
$tableError = null;

try {
  $passengers = (new PassengersRepository())->getItems();
} catch (\Throwable $exception) {
  $tableError = $exception->getMessage();
}

$columns = [
  'ID' => 'ID',
  'PASSENGER_EXTERNAL_ID' => 'PassengerId',
  'FULL_NAME' => 'ФИО',
  'SEX' => 'Sex',
  'AGE' => 'Age',
  'SIBSP' => 'SibSp',
  'PARCH' => 'Parch',
  'FARE' => 'Fare',
  'SURVIVED' => 'Survived',
  'TICKET_ID' => 'Ticket ID',
  'TICKET_RAW' => 'Ticket',
  'TICKET_PREFIX' => 'Ticket prefix',
  'TICKET_NUMBER' => 'Ticket number',
  'TICKET_PASSENGER_COUNT' => 'Ticket passenger count',
  'TICKET_FARE_TOTAL' => 'Ticket fare total',
  'PCLASS_ELEMENT_ID' => 'Pclass element ID',
  'PCLASS_NAME' => 'Pclass name',
  'PCLASS_CODE' => 'Pclass code',
  'EMBARKED_ELEMENT_ID' => 'Embarked element ID',
  'EMBARKED_NAME' => 'Embarked name',
  'EMBARKED_CODE' => 'Embarked code',
  'CABIN_DECK_ELEMENT_ID' => 'Cabin deck element ID',
  'CABIN_DECK_NAME' => 'Cabin deck name',
  'CABIN_DECK_CODE' => 'Cabin deck code',
  'CABIN_RAW' => 'Cabin raw',
  'CABINS' => 'Cabins',
];

$formatValue = static function (mixed $value): string {
  if (is_array($value)) {
    $cabinCodes = [];

    foreach ($value as $cabin) {
      if (!is_array($cabin)) {
        continue;
      }

      $cabinCode = trim((string)($cabin['CABIN_CODE'] ?? ''));

      if ($cabinCode !== '') {
        $cabinCodes[] = $cabinCode;
      }
    }

    return implode(', ', $cabinCodes);
  }

  if ($value === null) {
    return '';
  }

  if (is_float($value)) {
    return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.');
  }

  return (string)$value;
};
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">
      Полный вывод пассажиров из `PassengersRepository::getItems()` вместе со связанными полями.
    </div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <?php if ($tableError !== null): ?>
        <div class="homework-note">
          Не удалось получить таблицу пассажиров: <?= htmlspecialcharsbx($tableError) ?>
        </div>
      <?php else: ?>
        <div class="homework-subtitle">
          Всего строк: <?= count($passengers) ?>
        </div>

        <div class="homework-table-wrapper" style="overflow-x: auto;">
          <table class="homework-table">
            <thead>
              <tr>
                <?php foreach ($columns as $columnTitle): ?>
                  <th><?= htmlspecialcharsbx($columnTitle) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($passengers as $passenger): ?>
                <tr>
                  <?php foreach ($columns as $columnKey => $columnTitle): ?>
                    <td><?= htmlspecialcharsbx($formatValue($passenger[$columnKey] ?? null)) ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
