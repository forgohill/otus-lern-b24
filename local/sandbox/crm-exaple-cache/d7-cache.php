<?php

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Data\Cache;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
/** @var CMain $APPLICATION */
$APPLICATION->SetTitle('D7 cache');

Asset::getInstance()->addCss('/local/sandbox/style.css');

$cacheTime = 15; // 3600 // 1 час
$cacheId = 'mycache_list';
$cacheDir = 'myCache';

$cache = Cache::createInstance();

$hit = $cache->initCache($cacheTime, $cacheId, $cacheDir);

$cacheState = 'не определён';
if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
 $result = $cache->getVars();
 $cacheState = 'данные из кеша';
} elseif ($cache->startDataCache()) {
 $result = [
  'message' => 'Это кеш: 👇',
  'players' => ['Kurt', 'Krist', 'Dave'],
  'time' => time(),
 ];
 if ($cache->startDataCache()) {
  echo 'Запись данных в кеш ' . time();
  echo '<div class="sandbox-green-square"></div>';
  $cache->endDataCache($result);
  $cacheState = 'создана новая запись';
 }
}
// вывод html
// $cache->output();

if (
 $_SERVER['REQUEST_METHOD'] === 'POST'
 && isset($_POST['clear_cache'])
 && $_POST['clear_cache'] === 'Y'
 && check_bitrix_sessid()
) {
 // очистка кеша
 $cache->clean($cacheId, $cacheDir);
 LocalRedirect($APPLICATION->GetCurPage());
 exit;
}
?>

<div class="sandbox-page">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
  <form method="post" class="sandbox-action-form">
   <?= bitrix_sessid_post() ?>
   <input type="hidden" name="clear_cache" value="Y">
   <button type="submit" class="ui-btn ui-btn-danger ui-btn-round">Очистить кеш</button>
  </form>
 </div>

 <div class="sandbox-hero">
  <h1 class="sandbox-title"><?= htmlspecialcharsbx($APPLICATION->GetTitle()) ?></h1>
  <p class="sandbox-text">Пустая учебная страница для экспериментов с D7 cache.</p>
 </div>
 <div><?= htmlspecialcharsbx($cacheState) ?></div>
 <div> <?= htmlspecialcharsbx($result['message']) ?> </div>
 <?php
 $cache->output();
 ?>
 <ul>
  <?php foreach ($result['players'] as $player): ?>
   <li><?= htmlspecialcharsbx($player) ?></li>
  <?php endforeach; ?>
 </ul>

</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
