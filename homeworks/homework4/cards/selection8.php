<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\TicketGroupSurvivalReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 8");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$reportRows = [];
$singleTicketPassengers = [];
$groupTicketPassengers = [];
$largestTicketGroups = [];
$reportError = null;

try {
  $report = new TicketGroupSurvivalReport();
  $reportRows = $report->getRows();
  $singleTicketPassengers = $report->getSingleTicketPassengers();
  $groupTicketPassengers = $report->getGroupTicketPassengers();
  $largestTicketGroups = $report->getLargestTicketGroups(limit: 8);
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}

$singleTicketCount = count($singleTicketPassengers);
$groupTicketCount = count($groupTicketPassengers);

$ticketInfoRows = [
  [
    'TICKET' => '347082',
    'GROUP' => 'Семья Andersson',
    'DESCRIPTION' => 'Шведская семья 3 класса: отец Anders, мать Alfrida и дети. Они ехали из Швеции в Канаду, в район Winnipeg. В источниках указывается, что вся семья Andersson погибла, тела большинства не были найдены.',
    'SOURCE' => 'The RMS Titanic and it\'s passengers',
    'URL' => 'https://titanicstory.wordpress.com/2012/04/04/the-entire-andersson-family-was-lost-on-the-titanic/',
  ],
  [
    'TICKET' => '1601',
    'GROUP' => 'Группа китайских моряков',
    'DESCRIPTION' => 'Это не семья, а рабочая группа. В открытых источниках указано, что по билету 1601 ехали китайские пассажиры 3 класса; часть источников описывает их как моряков, направлявшихся дальше на работу, в том числе в сторону Кубы. В отличие от большинства больших групп 3 класса, здесь выживаемость высокая: исторически из восьми китайских пассажиров выжили шестеро.',
    'SOURCE' => 'Fotmpdc',
    'URL' => 'https://www.fotmpdc.org/chinese_passengers',
  ],
  [
    'TICKET' => 'CA. 2343',
    'GROUP' => 'Семья Sage',
    'DESCRIPTION' => 'Большая английская семья 3 класса. Они собирались переехать в Jacksonville, Florida, где John Sage хотел развивать ферму с пеканом. Исторически семья Sage насчитывала 11 человек на этом билете; все погибли. В train.csv видна только часть этой группы.',
    'SOURCE' => 'The RMS Titanic and it\'s passengers',
    'URL' => 'https://titanicstory.wordpress.com/2012/04/06/the-sage-family-were-on-their-way-to-their-pecan-farm-in-florida/',
  ],
  [
    'TICKET' => '3101295',
    'GROUP' => 'Семья Panula',
    'DESCRIPTION' => 'Финская семья 3 класса: мать Maria/Maija Panula и дети. Они ехали в Pennsylvania, чтобы воссоединиться с отцом семейства. Все члены семьи, попавшие на Titanic, погибли; в источниках также упоминается сопровождавшая их Sanni Riihivuori.',
    'SOURCE' => 'Titanic Wiki',
    'URL' => 'https://titanic.fandom.com/wiki/Eino_Viljami_Panula',
  ],
  [
    'TICKET' => 'CA 2144',
    'GROUP' => 'Семья Goodwin',
    'DESCRIPTION' => 'Английская семья 3 класса: родители и шестеро детей. Они должны были переехать в США, потому что отец получил перспективу работы в районе Niagara. Из-за угольной забастовки их пересадили на Titanic. Вся семья погибла; младший Sidney Goodwin долго был известен как Unknown Child, пока его личность не подтвердили позднее.',
    'SOURCE' => 'Titanic Wiki',
    'URL' => 'https://titanic.fandom.com/wiki/Goodwin_family',
  ],
  [
    'TICKET' => '347088',
    'GROUP' => 'Семья Skoog',
    'DESCRIPTION' => 'Шведская семья, ранее жившая в США, решила вернуться обратно в Америку. Они ехали 3 классом из Southampton по билету 347088; вместе с ними путешествовали родственницы Elin Pettersson и Jenny Henriksson. Семья Skoog погибла.',
    'SOURCE' => 'Titanic Wiki',
    'URL' => 'https://titanic.fandom.com/wiki/Margit_Elizabeth_Skoog',
  ],
  [
    'TICKET' => '382652',
    'GROUP' => 'Семья Rice',
    'DESCRIPTION' => 'Ирландская вдова Margaret Rice и её сыновья. Они возвращались в Washington после смерти мужа Margaret. Их билет 3 класса стоил £29; вся семья погибла, тело Margaret было найдено и опознано.',
    'SOURCE' => 'Westmeath Independent',
    'URL' => 'https://www.westmeathindependent.ie/2011/11/23/the-athlone-titanic-six-who-never-came-home/',
  ],
  [
    'TICKET' => 'S.O.C. 14879',
    'GROUP' => 'Группа молодых мужчин из Англии',
    'DESCRIPTION' => 'Это не семейный билет в чистом виде. В группе были братья Hickman и их знакомые/попутчики: Ambrose Hood, Charles Davies, Percy Deacon, William Dibden и другие. Они ехали во 2 классе в Канаду, в район Manitoba, за новыми возможностями. Все погибли. В выборке видно 5 человек, но исторически группа по этому билету была больше.',
    'SOURCE' => 'Titanic Universe',
    'URL' => 'https://titanicuniverse.com/passengers/lewis-hickman/',
  ],
];
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Группы по Ticket: кто ехал один, а кто был в общей брони»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection7.php">
        Предыдущая
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection9.php">
        Следующая
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        TICKET_GROUP_SIZE показывает, сколько пассажиров имеют одинаковый Ticket.
        Это скрытый признак: один билет может означать одиночную поездку, семейную бронь или группу пассажиров.
      </div>

      <?php if ($reportError !== null): ?>
        <div class="homework-note">
          Не удалось получить данные отчёта: <?= htmlspecialcharsbx($reportError) ?>
        </div>
      <?php elseif ($reportRows === []): ?>
        <div class="homework-note">
          Нет данных по билетам. Проверьте, что таблица пассажиров и связь TICKET заполнены.
        </div>
      <?php else: ?>
        <div class="homework-table-wrapper">
          <table class="homework-table">
            <thead>
              <tr>
                <th>Размер группы по билету</th>
                <th>Всего пассажиров</th>
                <th>Выжили</th>
                <th>Выживаемость</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reportRows as $row): ?>
                <tr>
                  <td><?= (int)$row['TICKET_GROUP_SIZE'] ?></td>
                  <td><?= (int)$row['TOTAL'] ?></td>
                  <td><?= (int)$row['SURVIVED'] ?></td>
                  <td><?= htmlspecialcharsbx(number_format((float)$row['SURVIVAL_RATE'], 1, '.', '')) ?>%</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="homework-report-summary">
          <h2>Подборки:</h2>
          <div class="homework-status-list">
            <div class="homework-status-row">
              <div>
                <p class="homework-status-title">Пассажиры с одиночным билетом</p>
                <p class="homework-status-text"><?= (int)$singleTicketCount ?> человек</p>
              </div>
            </div>
            <div class="homework-status-row">
              <div>
                <p class="homework-status-title">Пассажиры с групповым билетом</p>
                <p class="homework-status-text"><?= (int)$groupTicketCount ?> человек</p>
              </div>
            </div>
          </div>
        </div>

        <div class="homework-report-summary">
          <h2>Самые большие группы по одному билету:</h2>
          <div class="homework-table-wrapper">
            <table class="homework-table">
              <thead>
                <tr>
                  <th>Ticket</th>
                  <th>Размер группы</th>
                  <th>Выжили</th>
                  <th>Выживаемость</th>
                  <th>Пассажиры</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($largestTicketGroups as $ticketGroup): ?>
                  <tr>
                    <td><?= htmlspecialcharsbx((string)$ticketGroup['TICKET_RAW']) ?></td>
                    <td><?= (int)$ticketGroup['TICKET_GROUP_SIZE'] ?></td>
                    <td><?= (int)$ticketGroup['SURVIVED'] ?></td>
                    <td><?= htmlspecialcharsbx(number_format((float)$ticketGroup['SURVIVAL_RATE'], 1, '.', '')) ?>%</td>
                    <td>
                      <?php foreach ((array)$ticketGroup['PASSENGERS'] as $passenger): ?>
                        <div>
                          <?= (int)$passenger['PASSENGER_EXTERNAL_ID'] ?> -
                          <?= htmlspecialcharsbx((string)$passenger['FULL_NAME']) ?>
                          <?php if (!empty($passenger['PCLASS_NAME'])): ?>
                            (<?= htmlspecialcharsbx((string)$passenger['PCLASS_NAME']) ?>)
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="homework-report-summary">
          <h2>Главный вывод:</h2>
          <p>
            Один билет на Titanic часто скрывает целую группу людей.
            Повторяющийся Ticket показывает, кто путешествовал вместе: семьи, родственники, друзья или рабочие группы.
            Большие семьи 3 класса часто погибали целиком — Andersson, Sage, Panula, Goodwin, Skoog и Rice.
            Но билет 1601 показывает исключение: группа китайских моряков 3 класса имела высокую выживаемость.
            Поэтому поле Ticket оказывается не технической строкой, а скрытым признаком социальной связи между пассажирами.
          </p>
        </div>

        <div class="homework-report-summary">
          <h2>Исторический смысл групп:</h2>
          <div class="homework-table-wrapper">
            <table class="homework-table homework-table--passenger-info">
              <thead>
                <tr>
                  <th>Ticket</th>
                  <th>Кто это был</th>
                  <th>Исторический смысл группы</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($ticketInfoRows as $ticketInfoRow): ?>
                  <tr>
                    <td><?= htmlspecialcharsbx((string)$ticketInfoRow['TICKET']) ?></td>
                    <td><?= htmlspecialcharsbx((string)$ticketInfoRow['GROUP']) ?></td>
                    <td class="homework-table__description">
                      <?= htmlspecialcharsbx((string)$ticketInfoRow['DESCRIPTION']) ?>
                      <?php if (!empty($ticketInfoRow['URL']) && !empty($ticketInfoRow['SOURCE'])): ?>
                        <a href="<?= htmlspecialcharsbx((string)$ticketInfoRow['URL']) ?>" target="_blank" rel="noopener noreferrer">
                          <?= htmlspecialcharsbx((string)$ticketInfoRow['SOURCE']) ?>
                        </a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
