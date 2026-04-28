<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\FareAnalyticsReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 11");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$zeroFarePassengers = [];
$reportError = null;
$zeroFareInfoRows = [
  [
    'PASSENGER' => 'Lionel Leonard',
    'DESCRIPTION' => 'Настоящее имя — Andrew Shannon. Родился в Queenstown/Cobh, служил в Royal Navy, позже стал гражданином США и работал quartermaster на SS Philadelphia компании American Line. Из-за угольной забастовки рейс Philadelphia отменили, и его отправили в Нью-Йорк на Titanic как пассажира 3 класса. Погиб, тело не было идентифицировано.',
    'REASON' => 'Это был не туристический билет, а фактически служебная пересадка сотрудника другой линии.',
    'SOURCE' => 'Cobh Edition',
    'URL' => 'https://www.cobhedition.com/cobh-born-titanic-victim-to-be-remembered/',
  ],
  [
    'PASSENGER' => 'William Harrison',
    'DESCRIPTION' => 'Личный секретарь J. Bruce Ismay, главы White Star Line. Плыл 1 классом, ticket 112059, cabin B94. Погиб, тело было найдено Mackay-Bennett и похоронено в Halifax.',
    'REASON' => 'Вероятно, ехал как часть окружения руководителя White Star Line, поэтому Fare в датасете равен 0.',
    'SOURCE' => 'Titanic Museum',
    'URL' => 'https://www.titanicmuseum.org/artefacts/harrison-titanic-letter/',
  ],
  [
    'PASSENGER' => 'William Henry Tornquist',
    'DESCRIPTION' => 'Моряк/сотрудник, который тоже оказался на Titanic из-за отмены рейса Philadelphia. В источниках указано, что из этой группы моряков почти все погибли, а Tornquist спасся в lifeboat 15.',
    'REASON' => 'Служебная пересадка из-за отменённого рейса, как у группы LINE.',
    'SOURCE' => 'Sig Theatre',
    'URL' => 'https://www.sigtheatre.org/titanicticket/third-class',
  ],
  [
    'PASSENGER' => 'Francis “Frank” Parkes',
    'DESCRIPTION' => 'Молодой сотрудник Harland & Wolff, plumber. Был включён в элитную Guarantee Group — группу специалистов верфи, которые сопровождали Titanic в первом рейсе и должны были следить за работой судна. Погиб, тело не было идентифицировано.',
    'REASON' => 'Это был рабочий рейс от Harland & Wolff, а не обычная покупка билета.',
    'SOURCE' => 'Титаник Вселенная',
    'URL' => 'https://titanicuniverse.com/passengers/francis-parkes/',
  ],
  [
    'PASSENGER' => 'William Cahoone Johnson Jr.',
    'DESCRIPTION' => '19-летний американский моряк. Работал на American Line, служил на SS Philadelphia. Из-за британской угольной забастовки рейс Philadelphia отменили, и он вместе с другими моряками был вынужден ехать на Titanic. Погиб.',
    'REASON' => 'Служебная пересадка сотрудника American Line.',
    'SOURCE' => 'Титаник Вселенная',
    'URL' => 'https://titanicuniverse.com/passengers/william-cahoone-johnson-jr/',
  ],
  [
    'PASSENGER' => 'Alfred Fleming Cunningham',
    'DESCRIPTION' => 'Apprentice fitter из Belfast, сотрудник Harland & Wolff. Был выбран в Guarantee Group. Titanic Belfast пишет, что он должен был сопровождать судно, следить за незавершёнными работами и устранять проблемы в рейсе. Погиб, тело не было найдено.',
    'REASON' => 'Служебный билет от верфи Harland & Wolff.',
    'SOURCE' => 'Titanic Belfast',
    'URL' => 'https://www.titanicbelfast.com/history-of-titanic/titanic-stories/a-history-of-the-shipyard-the-people-of-belfast/',
  ],
  [
    'PASSENGER' => 'William Campbell',
    'DESCRIPTION' => 'Ирландский carpenter/joiner, сотрудник Harland & Wolff. Входил в Guarantee Group, ticket 239853, ехал 2 классом из Belfast. Погиб, тело не было идентифицировано.',
    'REASON' => 'Рабочая поездка в составе гарантийной группы строителей Titanic.',
    'SOURCE' => 'Титаник Вселенная',
    'URL' => 'https://titanicuniverse.com/passengers/william-campbell/',
  ],
  [
    'PASSENGER' => 'Anthony Wood “Archie/Artie” Frost',
    'DESCRIPTION' => 'Foreman Engine Fitter в Harland & Wolff. Плыл 2 классом как член Guarantee Group. Его задача — следить за работой судна и помогать устранять проблемы. Погиб, тело не было найдено.',
    'REASON' => 'Служебный билет специалиста верфи.',
    'SOURCE' => 'Titanic Pages',
    'URL' => 'https://www.titanicpages.com/anthonyfrost',
  ],
  [
    'PASSENGER' => 'Alfred Johnson',
    'DESCRIPTION' => 'В датасете указан как пассажир 3 класса с ticket LINE. В списках пассажиров он проходит как Alfred Johnson, 49 лет, направление — New York City.',
    'REASON' => 'По LINE и Fare = 0 его логично относить к нестандартным/служебным билетам, но по нему меньше биографических деталей, чем по Leonard, Tornquist и Johnson Jr.',
    'SOURCE' => 'GG Archives',
    'URL' => 'https://www.ggarchives.com/OceanTravel/Titanic/05-Manifests.html',
  ],
  [
    'PASSENGER' => 'William Henry Marsh Parr',
    'DESCRIPTION' => 'Специалист по электрике Harland & Wolff. Работал над электрическими системами Olympic и Titanic, был assistant manager в electrical department. Плыл 1 классом, ticket 112052, как член Guarantee Group. Погиб, тело не было идентифицировано.',
    'REASON' => 'Служебная поездка инженера Harland & Wolff.',
    'SOURCE' => 'Титаник Вселенная',
    'URL' => 'https://titanicuniverse.com/passengers/william-henry-marsh-parr/',
  ],
  [
    'PASSENGER' => 'Ennis Hastings Watson',
    'DESCRIPTION' => 'Молодой apprentice electrician из Harland & Wolff. Входил в Guarantee Group, ticket 239856, ехал 2 классом. Погиб в 18 лет.',
    'REASON' => 'Служебная поездка специалиста, который должен был следить за электрическими системами.',
    'SOURCE' => 'Титаник Вселенная',
    'URL' => 'https://titanicuniverse.com/passengers/ennis-hastings-watson/',
  ],
  [
    'PASSENGER' => 'Robert J. Knight',
    'DESCRIPTION' => 'Leading hand engineer / fitter Harland & Wolff. Был членом Guarantee Group, ticket 239855, ехал 2 классом из Belfast. Погиб, тело не было идентифицировано.',
    'REASON' => 'Служебный билет технического специалиста.',
    'SOURCE' => 'Титаник Вселенная',
    'URL' => 'https://titanicuniverse.com/passengers/robert-knight/',
  ],
  [
    'PASSENGER' => 'Thomas Andrews Jr.',
    'DESCRIPTION' => 'Один из главных людей в истории Titanic: shipbuilder, управляющий и руководитель чертёжного отдела Harland & Wolff, фактически главный представитель строителей судна. Плыл 1 классом как лидер Guarantee Group, ticket 112050 был complimentary. Погиб.',
    'REASON' => 'Бесплатный/служебный билет руководителя Guarantee Group.',
    'SOURCE' => 'Titanic Wiki',
    'URL' => 'https://titanic.fandom.com/wiki/Thomas_Andrews',
  ],
  [
    'PASSENGER' => 'Richard Fry',
    'DESCRIPTION' => 'Личный valet J. Bruce Ismay. Плыл вместе с Ismay и его секретарём William Harrison, занимал cabin B-102. Погиб, тело не было идентифицировано.',
    'REASON' => 'Сопровождающий руководителя White Star Line, поэтому Fare мог быть нулевым.',
    'SOURCE' => 'Википедия',
    'URL' => 'https://en.wikipedia.org/wiki/Richard_Thomas_Fry',
  ],
  [
    'PASSENGER' => 'Jonkheer Johan George Reuchlin',
    'DESCRIPTION' => 'Голландский дворянин и представитель/директор Holland America Line. Его поездка была не обычным туризмом: он оценивал Olympic-class liners, потому что Holland America Line планировала похожее большое судно. TitanicUniverse прямо указывает, что его ticket 19972 был complimentary из-за его позиции в Holland America Line.',
    'REASON' => 'Приглашённый профессиональный гость судоходной индустрии.',
    'SOURCE' => 'Титаник Вселенная',
    'URL' => 'https://titanicuniverse.com/passengers/jonkh-johan-george-reuchlin/',
  ],
];

try {
  $zeroFarePassengers = (new FareAnalyticsReport())->getZeroFarePassengers();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Бесплатные билеты: пассажиры с Fare = 0»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection10.php">
        Предыдущая
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection12.php">
        Следующая
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        Эта таблица показывает только пассажиров, у которых в исходных данных цена билета равна нулю.
      </div>

      <?php if ($reportError !== null): ?>
        <div class="homework-note">
          Не удалось получить данные отчёта: <?= htmlspecialcharsbx($reportError) ?>
        </div>
      <?php elseif ($zeroFarePassengers === []): ?>
        <div class="homework-note">
          Нет пассажиров с Fare = 0.
        </div>
      <?php else: ?>
        <div class="homework-report-summary">
          <h2>Пассажиры с бесплатными билетами:</h2>
          <div class="homework-table-wrapper">
            <table class="homework-table">
              <thead>
                <tr>
                  <th>PassengerId</th>
                  <th>ФИО</th>
                  <th>Класс</th>
                  <th>Ticket</th>
                  <th>Размер группы</th>
                  <th>Fare</th>
                  <th>Fare на человека</th>
                  <th>Выжил</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($zeroFarePassengers as $row): ?>
                  <tr>
                    <td><?= (int)$row['PASSENGER_EXTERNAL_ID'] ?></td>
                    <td><?= htmlspecialcharsbx((string)$row['FULL_NAME']) ?></td>
                    <td><?= htmlspecialcharsbx((string)$row['PCLASS_NAME']) ?></td>
                    <td><?= htmlspecialcharsbx((string)$row['TICKET_RAW']) ?></td>
                    <td><?= (int)$row['TICKET_GROUP_SIZE'] ?></td>
                    <td><?= htmlspecialcharsbx(number_format((float)$row['FARE'], 4, '.', '')) ?></td>
                    <td><?= htmlspecialcharsbx(number_format((float)$row['FARE_PER_PASSENGER'], 4, '.', '')) ?></td>
                    <td><?= ((int)$row['SURVIVED'] === 1) ? 'Да' : 'Нет' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

      <div class="homework-report-summary">
        <h2>Кто были эти пассажиры:</h2>
        <div class="homework-table-wrapper">
          <table class="homework-table homework-table--passenger-info">
            <thead>
              <tr>
                <th>Пассажир</th>
                <th>Кто это был</th>
                <th>Почему мог быть Fare = 0</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($zeroFareInfoRows as $infoRow): ?>
                <tr>
                  <td><?= htmlspecialcharsbx((string)$infoRow['PASSENGER']) ?></td>
                  <td class="homework-table__description">
                    <?= htmlspecialcharsbx((string)$infoRow['DESCRIPTION']) ?>
                    <?php if (!empty($infoRow['URL']) && !empty($infoRow['SOURCE'])): ?>
                      <a href="<?= htmlspecialcharsbx((string)$infoRow['URL']) ?>" target="_blank" rel="noopener noreferrer">
                        <?= htmlspecialcharsbx((string)$infoRow['SOURCE']) ?>
                      </a>
                    <?php endif; ?>
                  </td>
                  <td class="homework-table__description">
                    <?= htmlspecialcharsbx((string)$infoRow['REASON']) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
