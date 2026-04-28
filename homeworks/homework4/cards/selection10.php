<?

use Bitrix\Main\Page\Asset;
use Models\Titanic\Repository\FareAnalyticsReport;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Выборка 10");

Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">');
Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');

$mostExpensiveTickets = [];
$mostExpensivePerPassenger = [];
$zeroFarePassengers = [];
$reportError = null;

try {
  $report = new FareAnalyticsReport();
  $mostExpensiveTickets = $report->getMostExpensiveTickets(limit: 10);
  $mostExpensivePerPassenger = $report->getMostExpensivePerPassenger(limit: 10);
  $zeroFarePassengers = $report->getZeroFarePassengers();
} catch (\Throwable $exception) {
  $reportError = $exception->getMessage();
}
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">«Цена спасения: Fare и реальная стоимость на человека»</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection9.php">
        Предыдущая
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/index.php">
        Назад
      </a>
      <a class="homework-btn homework-btn--secondary" href="/homeworks/homework4/cards/selection11.php">
        Следующая
      </a>
    </div>
  </div>

  <div class="homework-section">
    <div class="homework-section-body">
      <div class="homework-subtitle">
        Fare в CSV показывает цену билета, но один Ticket мог быть общим для нескольких пассажиров.
        Поэтому дополнительно считаем Fare per passenger: приблизительную цену на человека.
      </div>

      <?php if ($reportError !== null): ?>
        <div class="homework-note">
          Не удалось получить данные отчёта: <?= htmlspecialcharsbx($reportError) ?>
        </div>
      <?php else: ?>
        <div class="homework-report-summary">
          <h2>Самые дорогие билеты:</h2>
          <div class="homework-table-wrapper">
            <table class="homework-table">
              <thead>
                <tr>
                  <th>PassengerId</th>
                  <th>ФИО</th>
                  <th>Класс</th>
                  <th>Ticket</th>
                  <th>Fare</th>
                  <th>Размер группы</th>
                  <th>Fare на человека</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($mostExpensiveTickets as $row): ?>
                  <tr>
                    <td><?= (int)$row['PASSENGER_EXTERNAL_ID'] ?></td>
                    <td><?= htmlspecialcharsbx((string)$row['FULL_NAME']) ?></td>
                    <td><?= htmlspecialcharsbx((string)$row['PCLASS_NAME']) ?></td>
                    <td><?= htmlspecialcharsbx((string)$row['TICKET_RAW']) ?></td>
                    <td><?= htmlspecialcharsbx(number_format((float)$row['FARE'], 4, '.', '')) ?></td>
                    <td><?= (int)$row['TICKET_GROUP_SIZE'] ?></td>
                    <td><?= htmlspecialcharsbx(number_format((float)$row['FARE_PER_PASSENGER'], 4, '.', '')) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="homework-report-summary">
          <h2>Самые дорогие билеты на человека:</h2>
          <div class="homework-table-wrapper">
            <table class="homework-table">
              <thead>
                <tr>
                  <th>PassengerId</th>
                  <th>ФИО</th>
                  <th>Класс</th>
                  <th>Ticket</th>
                  <th>Fare</th>
                  <th>Размер группы</th>
                  <th>Fare на человека</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($mostExpensivePerPassenger as $row): ?>
                  <tr>
                    <td><?= (int)$row['PASSENGER_EXTERNAL_ID'] ?></td>
                    <td><?= htmlspecialcharsbx((string)$row['FULL_NAME']) ?></td>
                    <td><?= htmlspecialcharsbx((string)$row['PCLASS_NAME']) ?></td>
                    <td><?= htmlspecialcharsbx((string)$row['TICKET_RAW']) ?></td>
                    <td><?= htmlspecialcharsbx(number_format((float)$row['FARE'], 4, '.', '')) ?></td>
                    <td><?= (int)$row['TICKET_GROUP_SIZE'] ?></td>
                    <td><?= htmlspecialcharsbx(number_format((float)$row['FARE_PER_PASSENGER'], 4, '.', '')) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="homework-report-summary">
          <h2>Пассажиры с Fare = 0:</h2>
          <?php if ($zeroFarePassengers === []): ?>
            <div class="homework-note">
              Нет пассажиров с Fare = 0.
            </div>
          <?php else: ?>
            <div class="homework-table-wrapper">
              <table class="homework-table">
                <thead>
                  <tr>
                    <th>PassengerId</th>
                    <th>ФИО</th>
                    <th>Класс</th>
                    <th>Ticket</th>
                    <th>Fare</th>
                    <th>Размер группы</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($zeroFarePassengers as $row): ?>
                    <tr>
                      <td><?= (int)$row['PASSENGER_EXTERNAL_ID'] ?></td>
                      <td><?= htmlspecialcharsbx((string)$row['FULL_NAME']) ?></td>
                      <td><?= htmlspecialcharsbx((string)$row['PCLASS_NAME']) ?></td>
                      <td><?= htmlspecialcharsbx((string)$row['TICKET_RAW']) ?></td>
                      <td><?= htmlspecialcharsbx(number_format((float)$row['FARE'], 4, '.', '')) ?></td>
                      <td><?= (int)$row['TICKET_GROUP_SIZE'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <div class="homework-report-summary">
          <h2>Главный вывод:</h2>
          <p>
            Fare — это не просто цена билета. В Titanic dataset это поле часто показывает стоимость общего билета,
            который мог быть оформлен сразу на несколько пассажиров. Поэтому Fare per passenger даёт более честную картину:
            он отделяет дорогой семейный или групповой билет от реально дорогого места одного пассажира.
          </p>
          <p>
            Например, билет PC 17755 с ценой 512.3292 выглядит как самый дорогой билет в таблице.
            В датасетах Titanic этот ticket действительно связан с пассажирами первого класса вроде Cardeza, Ward и Lesurer.
            Но если делить цену на группу из 3 человек, получается около 170.78 на человека, а не 512 на каждого.
          </p>

          <h2>Что видно по самым дорогим билетам:</h2>
          <p>
            Самые дорогие билеты почти полностью принадлежат первому классу.
            Это ожидаемо: Titanic был не просто транспортом, а социальным пространством,
            где цена билета отражала уровень доступа к комфорту, палубам, каютам и сервису.
            По историческим описаниям первого класса, обычная каюта могла стоить около £30,
            стандартные suite — примерно £100–300, а самые роскошные parlour suite — £500–1000.
          </p>
          <p>
            В таблице хорошо видно, что дорогие билеты часто связаны не с одиночками, а с группами:
            PC 17755 — 512.3292, группа из 3 человек;
            19950 — 263.0000, группа Fortune из 4 человек;
            PC 17608 — 262.3750, группа Ryerson из 2 человек;
            PC 17558 — 247.5208, группа Baxter из 2 человек.
            То есть высокая сумма Fare часто говорит не только о богатстве, но и о том,
            что билет мог покрывать семью, сопровождающих или несколько мест.
          </p>

          <h2>Почему Fare per passenger интереснее, чем просто Fare:</h2>
          <p>
            Если смотреть только на Fare, то лидером будет общий дорогой билет PC 17755.
            Но если смотреть на цену на человека, картина меняется: на первое место выходит
            Farthing, Mr. John с билетом PC 17483 и ценой 221.7792 за одного пассажира.
          </p>
          <p>
            Это делает Fare per passenger сильным производным полем:
            Fare per passenger = Fare / размер группы по Ticket.
            Такой расчёт помогает честнее сравнивать пассажиров между собой.
            Один человек с билетом за 221.7792 фактически ехал дороже,
            чем пассажир из группы, где общий билет стоил 512.3292, но делился на троих.
          </p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
