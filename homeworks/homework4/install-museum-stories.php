<?

use App\MuseumStories\BuildingsIblockInstaller;
use App\MuseumStories\HallsIblockInstaller;
use App\MuseumStories\TypesIblockInstaller;
use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Установка Museum Stories");

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$installResults = [
  'buildings' => null,
  'halls' => null,
  'types' => null,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
  if (isset($_POST['install_museum_buildings'])) {
    $installer = new BuildingsIblockInstaller();
    $installResults['buildings'] = $installer->install();
  }

  if (isset($_POST['install_museum_halls'])) {
    $installer = new HallsIblockInstaller();
    $installResults['halls'] = $installer->install();
  }

  if (isset($_POST['install_museum_types'])) {
    $installer = new TypesIblockInstaller();
    $installResults['types'] = $installer->install();
  }
}
?>
<h1 class="mb-3"><? $APPLICATION->ShowTitle() ?></h1>

<form method="post" class="mb-4 d-flex flex-wrap gap-2">
  <?= bitrix_sessid_post() ?>
  <button type="submit" name="install_museum_buildings" value="Y" class="btn btn-primary">
    Создать инфоблок зданий
  </button>
  <button type="submit" name="install_museum_halls" value="Y" class="btn btn-primary">
    Создать инфоблок залов
  </button>
  <button type="submit" name="install_museum_types" value="Y" class="btn btn-primary">
    Создать инфоблок типов
  </button>
</form>

<?php foreach ($installResults as $key => $installResult): ?>
  <?php if (!is_array($installResult)): ?>
    <?php continue; ?>
  <?php endif; ?>

  <?php
  $titles = [
    'buildings' => 'Инфоблок зданий',
    'halls' => 'Инфоблок залов',
    'types' => 'Инфоблок типов',
  ];
  $title = $titles[$key] ?? 'Инфоблок';
  ?>

  <?php if ($installResult['success']): ?>
    <div class="alert alert-success">
      <?= htmlspecialcharsbx($title) ?> создан или уже существует. ID: <?= (int)($installResult['id'] ?? 0) ?>
    </div>
  <?php else: ?>
    <div class="alert alert-danger">
      <div>Не удалось создать <?= htmlspecialcharsbx($title) ?>.</div>
      <?php if (!empty($installResult['errors'])): ?>
        <ul class="mb-0 mt-2">
          <?php foreach ($installResult['errors'] as $error): ?>
            <li><?= htmlspecialcharsbx((string)$error) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

<a class="btn btn-outline-secondary" href="/homeworks/homework4/index.php">Назад</a>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
