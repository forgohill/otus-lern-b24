<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\TicketPrefixSurvivalReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 9");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$reportRows = [];
$reportError = null;

try {
  $reportRows = (new TicketPrefixSurvivalReport())->getRows();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}

$prefixInfoRows = [
  [
    'PREFIX' => 'NUMERIC',
    'MEANING' => 'Билет без буквенного префикса. Самая массовая группа.',
    'CAUTION' => 'Это обычные числовые билеты. Внутри смешаны разные классы и маршруты, поэтому сам по себе NUMERIC слабее объясняет выживаемость.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'PREFIX' => 'PC',
    'MEANING' => 'Очень сильный признак дорогих билетов, часто 1 класс.',
    'CAUTION' => 'Точную расшифровку лучше не утверждать жёстко. По данным видно, что много PC связано с 1 классом, высокими тарифами и часто Cherbourg. Например, PC 17599, PC 17608, PC 17757 встречаются у пассажиров 1 класса с дорогими каютами и высокими Fare.',
    'SOURCE' => 'GitHub',
    'URL' => 'https://raw.githubusercontent.com/plenoi/BDM/master/titanic.txt',
  ],
  [
    'PREFIX' => 'CA / C.A. / CASOTON',
    'MEANING' => 'Часто групповые или семейные билеты, особенно 2–3 класс.',
    'CAUTION' => 'Хороший пример: большие семьи вроде Goodwin или Sage идут по CA 2144 / CA. 2343. Это может быть связано с контрактными/агентскими билетами, но точную расшифровку лучше писать как гипотезу.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'PREFIX' => 'A5 / A4 / AS / SCA4',
    'MEANING' => 'Низкостатусные билеты, часто 3 класс, часто Southampton.',
    'CAUTION' => 'В статистике A5 и A4 имеют очень низкую выживаемость. Это не потому, что буква A убивала людей, а потому что такие билеты часто попадали в социально уязвимые группы.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'PREFIX' => 'SOTONOQ / SOTONO2 / STONO2',
    'MEANING' => 'Похоже на билеты, связанные с Southampton.',
    'CAUTION' => 'SOTON почти явно читается как Southampton. Например, у Manuel Gonçalves Estanislau указан билет SOTON/O.Q. 3101306, порт посадки Southampton, 3 класс, Fare £7 1s.',
    'SOURCE' => 'Titanic Universe',
    'URL' => 'https://titanicuniverse.com/passengers/manuel-goncalves-estanislau/',
  ],
  [
    'PREFIX' => 'SCPARIS / SC/PARIS',
    'MEANING' => 'Вероятно, билеты, оформленные через Paris/континентальную Европу, часто посадка Cherbourg.',
    'CAUTION' => 'В данных много пассажиров 2 класса с SC/PARIS, которые садились в Cherbourg. Например семья Laroche ехала по SC/Paris 2123, порт Cherbourg.',
    'SOURCE' => 'GitHub',
    'URL' => 'https://raw.githubusercontent.com/plenoi/BDM/master/titanic.txt',
  ],
  [
    'PREFIX' => 'SCAH / SCAHBASLE',
    'MEANING' => 'Подтип SC, где Basle почти наверняка указывает на Basel/Базель как место оформления или агентскую привязку.',
    'CAUTION' => 'Пример: SC/AH Basle 541 у пассажирки 2 класса, посадка Cherbourg.',
    'SOURCE' => 'GitHub',
    'URL' => 'https://raw.githubusercontent.com/plenoi/BDM/master/titanic.txt',
  ],
  [
    'PREFIX' => 'SOC / SOP / SOPP / SP / SCOW',
    'MEANING' => 'Похоже на служебно-агентские коды билетов, часто Southampton и 2 класс.',
    'CAUTION' => 'Например S.O.C. 14879 — большая группа мужчин 2 класса, посадка Southampton, Fare 73.5. В статистике выживаемость низкая, потому что это мужская группа 2 класса.',
    'SOURCE' => 'GitHub',
    'URL' => 'https://raw.githubusercontent.com/plenoi/BDM/master/titanic.txt',
  ],
  [
    'PREFIX' => 'FCC / FC / FA',
    'MEANING' => 'Небольшая группа, часто 2 класс и Southampton.',
    'CAUTION' => 'Например F.C.C. 13529 встречается у семьи Hart во 2 классе, посадка Southampton. Если FCC даёт высокий процент, группу всё равно нужно читать осторожно: пассажиров мало.',
    'SOURCE' => 'GitHub',
    'URL' => 'https://raw.githubusercontent.com/plenoi/BDM/master/titanic.txt',
  ],
  [
    'PREFIX' => 'WC',
    'MEANING' => 'Небольшая группа билетов, часто 2–3 класс.',
    'CAUTION' => 'Точную расшифровку лучше не писать. Важно не название, а аналитический смысл: низкая выживаемость указывает, что эта группа была связана не с дорогими каютами, а с более уязвимыми пассажирами.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'PREFIX' => 'PP / PPP / SWPP',
    'MEANING' => 'Малые группы, возможно агентские/групповые коды.',
    'CAUTION' => 'По ним нельзя делать сильный вывод: 2–3 пассажира — слишком маленькая выборка.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'PREFIX' => 'WEP',
    'MEANING' => 'Маленькая группа, встречается у отдельных билетов 1 класса.',
    'CAUTION' => 'Если в таблице всего несколько человек, это скорее редкий код, чем надёжный фактор.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'PREFIX' => 'C / SC',
    'MEANING' => 'Обобщённые буквенные коды, часто связаны с агентскими сериями.',
    'CAUTION' => 'По одному коду C или SC нельзя уверенно сказать маршрут. Надо смотреть вместе с Pclass, Fare, Embarked.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'PREFIX' => 'LINE',
    'MEANING' => 'Особый случай, не обычный билет.',
    'CAUTION' => 'В Kaggle-разборах LINE описывают как исключение: такой ticket был связан не с обычным пассажирским номером, а с группой сотрудников/line employees и нулевым Fare.',
    'SOURCE' => 'Kaggle',
    'URL' => 'https://www.kaggle.com/code/pliptor/titanic-ticket-only-study',
  ],
];
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Префикс билета: скрытая подсказка о типе поездки»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection8.php">
        Предыдущая
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection10.php">
        Следующая
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        Из Ticket можно выделить префикс: PC, CA, A/5, SOTON/OQ и другие.
        Это не просто часть строки, а возможный скрытый признак маршрута, типа билета или группы пассажиров.
      </div>

      <?php if ($reportError !== null): ?>
        <div class="homework-note">
          Не удалось получить данные отчёта: <?= htmlspecialcharsbx($reportError) ?>
        </div>
      <?php elseif ($reportRows === []): ?>
        <div class="homework-note">
          Нет данных по префиксам билетов. Проверьте, что таблица пассажиров и связь TICKET заполнены.
        </div>
      <?php else: ?>
        <div class="homework-table-wrapper">
          <table class="homework-table">
            <thead>
              <tr>
                <th>Префикс</th>
                <th>Всего</th>
                <th>Выжили</th>
                <th>Выживаемость</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reportRows as $row): ?>
                <tr>
                  <td><?= htmlspecialcharsbx((string)$row['PREFIX']) ?></td>
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
            Префикс билета оказался скрытым признаком маршрута и статуса.
            На первый взгляд PC, CA, A/5, SOTON/OQ и SC/PARIS выглядят как технический мусор в поле Ticket.
            Но после группировки видно, что разные префиксы дают разную выживаемость.
            PC показывает высокую выживаемость и часто связан с дорогими билетами 1 класса.
            А префиксы вроде A5, SOTONOQ, WC и SOC чаще попадают в группы с низким шансом спасения.
            Поэтому Ticket Prefix можно использовать как производное поле: оно помогает увидеть не только номер билета,
            но и скрытую связь с классом, портом посадки, агентством продажи и типом пассажирской группы.
          </p>
        </div>

        <div class="homework-report-summary">
          <h2>Как читать префиксы:</h2>
          <div class="homework-table-wrapper">
            <table class="homework-table homework-table--passenger-info">
              <thead>
                <tr>
                  <th>Префикс</th>
                  <th>Что можно сказать в ДЗ</th>
                  <th>Как читать аккуратно</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($prefixInfoRows as $prefixInfoRow): ?>
                  <tr>
                    <td><?= htmlspecialcharsbx((string)$prefixInfoRow['PREFIX']) ?></td>
                    <td><?= htmlspecialcharsbx((string)$prefixInfoRow['MEANING']) ?></td>
                    <td class="homework-table__description">
                      <?= htmlspecialcharsbx((string)$prefixInfoRow['CAUTION']) ?>
                      <?php if (!empty($prefixInfoRow['URL']) && !empty($prefixInfoRow['SOURCE'])): ?>
                        <a href="<?= htmlspecialcharsbx((string)$prefixInfoRow['URL']) ?>" target="_blank" rel="noopener noreferrer">
                          <?= htmlspecialcharsbx((string)$prefixInfoRow['SOURCE']) ?>
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
