<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\MultiCabinPassengersReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 7");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$reportRows = [];
$reportError = null;

try {
  $reportRows = (new MultiCabinPassengersReport())->getFamilyCabinGroups();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}

$passengerInfoRows = [
  [
    'CABIN_RAW' => 'C23 C25 C27',
    'PASSENGER' => 'Charles Alexander Fortune',
    'DESCRIPTION' => '19-летний сын канадского миллионера Марка Фортуна. Путешествовал с семьёй после тура по Европе. Погиб вместе с отцом.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'CABIN_RAW' => 'C23 C25 C27',
    'PASSENGER' => 'Mabel Helen Fortune',
    'DESCRIPTION' => 'Дочь Марка и Мэри Fortune. Выжила, как и мать с сёстрами.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'CABIN_RAW' => 'C23 C25 C27',
    'PASSENGER' => 'Alice Elizabeth Fortune',
    'DESCRIPTION' => 'Дочь семьи Fortune, пассажирка 1 класса. Выжила.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'CABIN_RAW' => 'C23 C25 C27',
    'PASSENGER' => 'Mark Fortune',
    'DESCRIPTION' => 'Богатый предприниматель из Виннипега, заработавший состояние на недвижимости. Путешествовал с женой, сыном и дочерьми; погиб. Семья занимала каюты C23, C25 и C27.',
    'SOURCE' => 'Titanic Pages',
    'URL' => 'https://www.titanicpages.com/markfortune',
  ],
  [
    'CABIN_RAW' => 'B96 B98',
    'PASSENGER' => 'William Ernest Carter',
    'DESCRIPTION' => 'Американский миллионер, наследник состояния угольного магната, игрок в поло. Выжил.',
    'SOURCE' => 'Wikipedia',
    'URL' => 'https://en.wikipedia.org/wiki/William_E._Carter',
  ],
  [
    'CABIN_RAW' => 'B96 B98',
    'PASSENGER' => 'Lucile Polk Carter',
    'DESCRIPTION' => 'Дочь William Ernest Carter и Lucile Carter. Была ребёнком/подростком в богатой семье Carter, выжила.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'CABIN_RAW' => 'B96 B98',
    'PASSENGER' => 'Lucile Carter, Mrs. William Ernest',
    'DESCRIPTION' => 'Американская светская дама, жена William Ernest Carter. Семья Carter путешествовала с детьми, прислугой, шофёром и даже автомобилем Renault; занимала каюты B96/B98.',
    'SOURCE' => 'Wikipedia',
    'URL' => 'https://en.wikipedia.org/wiki/Lucile_Carter',
  ],
  [
    'CABIN_RAW' => 'B96 B98',
    'PASSENGER' => 'William Thornton Carter II',
    'DESCRIPTION' => 'Сын семьи Carter, ребёнок 1 класса. Выжил вместе с матерью и сестрой.',
    'SOURCE' => 'Wikipedia',
    'URL' => 'https://en.wikipedia.org/wiki/Lucile_Carter',
  ],
  [
    'CABIN_RAW' => 'C22 C26',
    'PASSENGER' => 'Helen Loraine Allison',
    'DESCRIPTION' => 'Маленькая дочь семьи Allison. Погибла; это особенно трагичный случай, потому что она была единственным ребёнком 1-го и 2-го класса, погибшим при катастрофе.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'CABIN_RAW' => 'C22 C26',
    'PASSENGER' => 'Hudson Trevor Allison',
    'DESCRIPTION' => 'Маленький сын семьи Allison. Единственный из основной семьи Allison, кто выжил: его спасла няня Alice Cleaver.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'CABIN_RAW' => 'C22 C26',
    'PASSENGER' => 'Bessie Waldo Daniels Allison',
    'DESCRIPTION' => 'Мать семьи Allison, жена Hudson Allison. Погибла вместе с мужем и дочерью. Семья Allison ехала 1 классом с несколькими слугами; исторически их размещение связывают с каютами C22, C24 и C26.',
    'SOURCE' => 'Wikipedia',
    'URL' => 'https://en.wikipedia.org/wiki/Allison_family',
  ],
  [
    'CABIN_RAW' => 'B57 B59 B63 B66',
    'PASSENGER' => 'Emily Borie Ryerson',
    'DESCRIPTION' => 'Дочь богатой американской семьи Ryerson. Выжила.',
    'SOURCE' => null,
    'URL' => null,
  ],
  [
    'CABIN_RAW' => 'B57 B59 B63 B66',
    'PASSENGER' => 'Susan Parker “Suzette” Ryerson',
    'DESCRIPTION' => 'Старшая дочь Arthur и Emily Ryerson. Выжила. Семья возвращалась в США после смерти старшего сына Arthur Jr.; отец Arthur Ryerson был юристом, бизнесменом и президентом сталелитейной компании Joseph T. Ryerson & Sons.',
    'SOURCE' => 'Wikipedia',
    'URL' => 'https://en.wikipedia.org/wiki/Emily_Ryerson',
  ],
  [
    'CABIN_RAW' => 'B51 B53 B55',
    'PASSENGER' => 'Thomas Drake Martinez Cardeza',
    'DESCRIPTION' => 'Очень богатый пассажир 1 класса из семьи Cardeza. Путешествовал с матерью Charlotte Cardeza. Их suite B51/B53/B55 был одним из самых роскошных на Titanic: две спальни, ванная, гостиная с камином и частная прогулочная палуба.',
    'SOURCE' => 'Jefferson Library',
    'URL' => 'https://library.jefferson.edu/archives/collections/highlights/Titanic/',
  ],
  [
    'CABIN_RAW' => 'B51 B53 B55',
    'PASSENGER' => 'Frans Olof Carlsson',
    'DESCRIPTION' => 'Шведский пассажир 1 класса, живший в Нью-Йорке. Погиб; тело не было идентифицировано. В учебных датасетах он иногда оказывается с тем же Cabin raw, что и Cardeza, но исторически suite B51/B53/B55 сильнее всего связан именно с семьёй Cardeza — это пример спорного поля Cabin.',
    'SOURCE' => 'Titanic Wiki',
    'URL' => 'https://titanic.fandom.com/wiki/Frans_Olof_Carlsson',
  ],
  [
    'CABIN_RAW' => 'B58 B60',
    'PASSENGER' => 'Quigg Edmond Baxter',
    'DESCRIPTION' => 'Молодой канадец из богатой семьи, известный хоккеист и спортсмен из Монреаля. Погиб. Его история ещё интересна тем, что он тайно вёз на Titanic свою возлюбленную, бельгийскую певицу Berthe Mayné.',
    'SOURCE' => 'RMS Titanic, Inc.',
    'URL' => 'https://rmstitanicinc.com/blog/titanic-scandalous-love-affairs/',
  ],
  [
    'CABIN_RAW' => 'B58 B60',
    'PASSENGER' => 'Helene DeLaudeniere Chaput Baxter',
    'DESCRIPTION' => 'Мать Quigg Baxter. Путешествовала с сыном и дочерью. Каюты B58/B60 относились к дорогим помещениям 1 класса рядом с luxury-зоной B Deck.',
    'SOURCE' => 'Titanic Wiki',
    'URL' => 'https://titanic.fandom.com/wiki/Cabin_B-58',
  ],
  [
    'CABIN_RAW' => 'D10 D12',
    'PASSENGER' => 'William Bertram Greenfield',
    'DESCRIPTION' => '23-летний американец из семьи меховщиков/furriers из Нью-Йорка. Путешествовал с матерью Blanche Greenfield, занимал каюты D10/D12, выжил в спасательной шлюпке №7.',
    'SOURCE' => 'Titanic Wiki',
    'URL' => 'https://titanic.fandom.com/wiki/William_Bertram_Greenfield',
  ],
  [
    'CABIN_RAW' => 'C62 C64',
    'PASSENGER' => 'Madeleine Talmage Force Astor',
    'DESCRIPTION' => 'Молодая американская светская дама, жена одного из самых богатых пассажиров Titanic — John Jacob Astor IV. На момент катастрофы была беременна, спаслась в шлюпке №4; Astor погиб.',
    'SOURCE' => 'Wikipedia',
    'URL' => 'https://en.wikipedia.org/wiki/Madeleine_Astor',
  ],
  [
    'CABIN_RAW' => 'B82 B84',
    'PASSENGER' => 'Benjamin Guggenheim',
    'DESCRIPTION' => 'Американский промышленник из знаменитой семьи Guggenheim. Во время эвакуации помогал женщинам и детям садиться в шлюпки, сам погиб. Britannica описывает знаменитый эпизод, когда он переоделся в вечерний костюм и остался на корабле.',
    'SOURCE' => 'Encyclopedia Britannica',
    'URL' => 'https://www.britannica.com/biography/Benjamin-Guggenheim',
  ],
];
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Несколько кают у одной семьи или группы»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection6.php">
        Предыдущая
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection8.php">
        Следующая
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        Некоторые записи имеют несколько кают. Здесь пассажиры объединены по одинаковому набору кают,
        поэтому можно увидеть не только отдельного пассажира, но и семью или группу, которая делила один набор Cabin.
      </div>

      <?php if ($reportError !== null): ?>
        <div class="homework-note">
          Не удалось получить данные отчёта: <?= htmlspecialcharsbx($reportError) ?>
        </div>
      <?php elseif ($reportRows === []): ?>
        <div class="homework-note">
          Нет групп с несколькими каютами. Проверьте связи в таблице otus_titanic_passenger_cabin.
        </div>
      <?php else: ?>
        <div class="homework-table-wrapper">
          <table class="homework-table">
            <thead>
              <tr>
                <th>Cabin raw</th>
                <th>Кают</th>
                <th>Каюты</th>
                <th>Пассажиров</th>
                <th>Пассажиры</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reportRows as $row): ?>
                <tr>
                  <td><?= htmlspecialcharsbx((string)($row['CABIN_RAW'] ?? '')) ?></td>
                  <td><?= (int)$row['CABIN_COUNT'] ?></td>
                  <td><?= htmlspecialcharsbx(implode(', ', (array)$row['CABIN_CODES'])) ?></td>
                  <td><?= (int)$row['PASSENGER_COUNT'] ?></td>
                  <td>
                    <?php foreach ((array)$row['PASSENGERS'] as $passenger): ?>
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

        <div class="homework-report-summary">
          <h2>Главный вывод:</h2>
          <p>
            Несколько кают часто читаются не как признак одного человека, а как след семьи или группы,
            которые ехали вместе и были записаны с одним набором Cabin.
          </p>
          <p>
            В этой таблице одинаковые наборы кают объединены вместе: например C23 C25 C27 показывает всех пассажиров,
            связанных с этим набором, а не повторяет одну и ту же каютную группу отдельными строками.
          </p>
          <p>
            Есть исторически сложные записи вроде F G73: они могут выглядеть как несколько значений в строке,
            но требуют отдельной интерпретации. Поэтому здесь решающим считается количество связанных кают в ORM.
          </p>
        </div>

        <div class="homework-report-summary">
          <h2>Кто были эти пассажиры:</h2>
          <div class="homework-table-wrapper">
            <table class="homework-table homework-table--passenger-info">
              <thead>
                <tr>
                  <th>Cabin raw</th>
                  <th>Пассажир</th>
                  <th>Кто это был</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($passengerInfoRows as $infoRow): ?>
                  <tr>
                    <td><?= htmlspecialcharsbx((string)$infoRow['CABIN_RAW']) ?></td>
                    <td><?= htmlspecialcharsbx((string)$infoRow['PASSENGER']) ?></td>
                    <td class="homework-table__description">
                      <?= htmlspecialcharsbx((string)$infoRow['DESCRIPTION']) ?>
                      <?php if (!empty($infoRow['URL']) && !empty($infoRow['SOURCE'])): ?>
                        <a href="<?= htmlspecialcharsbx((string)$infoRow['URL']) ?>" target="_blank" rel="noopener noreferrer">
                          <?= htmlspecialcharsbx((string)$infoRow['SOURCE']) ?>
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
