<?php

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Page\Asset;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
/** @var CMain $APPLICATION */
$APPLICATION->SetTitle('Tagged cache');

Asset::getInstance()->addCss('/local/sandbox/style.css');

$cacheTime = 15;
$variant = (string)($_REQUEST['variant'] ?? 'a');
$variants = [
 'a' => [
  'title' => 'A',
  'cacheId' => 'mycache_list_tagged_a',
  'cacheDir' => 'myTaggedCacheA',
  'cacheTag' => 'sandbox_tagged_cache_a',
  'message' => 'Это tagged cache A',
  'players' => ['Alpha', 'Atlas', 'Aria'],
  'class' => 'sandbox-blue-rectangle',
 ],
 'b' => [
  'title' => 'B',
  'cacheId' => 'mycache_list_tagged_b',
  'cacheDir' => 'myTaggedCacheB',
  'cacheTag' => 'sandbox_tagged_cache_b',
  'message' => 'Это tagged cache B',
  'players' => ['Beta', 'Bella', 'Boris'],
  'class' => 'sandbox-lilac-rectangle',
 ],
];

if (!isset($variants[$variant])) {
 $variant = 'a';
}

$currentVariant = $variants[$variant];

$cache = Cache::createInstance();
$taggedCache = Application::getInstance()->getTaggedCache();

if (
 $_SERVER['REQUEST_METHOD'] === 'POST'
 && isset($_POST['clear_cache'])
 && $_POST['clear_cache'] === 'Y'
 && check_bitrix_sessid()
) {
 $taggedCache->clearByTag($currentVariant['cacheTag']);
 LocalRedirect($APPLICATION->GetCurPage() . '?variant=' . urlencode($variant));
 exit;
}

$result = [
 'message' => '',
 'players' => [],
 'time' => null,
 'tag' => $currentVariant['cacheTag'],
];
$cacheState = 'не определён';

if ($cache->initCache($cacheTime, $currentVariant['cacheId'], $currentVariant['cacheDir'])) {
 $cachedResult = $cache->getVars();
 if (is_array($cachedResult)) {
  $result = array_merge($result, $cachedResult);
 }
 $cacheState = 'данные из кеша ' . $currentVariant['title'];
} elseif ($cache->startDataCache()) {
 $taggedCache->startTagCache($currentVariant['cacheDir']);
 $taggedCache->registerTag($currentVariant['cacheTag']);

 $result = [
  'message' => $currentVariant['message'],
  'players' => $currentVariant['players'],
  'time' => time(),
  'tag' => $currentVariant['cacheTag'],
 ];

 echo '<div class="' . htmlspecialcharsbx($currentVariant['class']) . '"></div>';
 $taggedCache->endTagCache();
 $cache->endDataCache($result);
 $cacheState = 'создана новая запись';
}
?>

<div class="sandbox-page">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
  <form method="get" class="sandbox-action-form">
   <input type="hidden" name="variant" value="a">
   <button type="submit" class="ui-btn ui-btn-primary ui-btn-round">A</button>
  </form>
  <form method="get" class="sandbox-action-form">
   <input type="hidden" name="variant" value="b">
   <button type="submit" class="ui-btn ui-btn-primary ui-btn-round">B</button>
  </form>
  <form method="post" class="sandbox-action-form">
   <?= bitrix_sessid_post() ?>
   <input type="hidden" name="clear_cache" value="Y">
   <input type="hidden" name="variant" value="<?= htmlspecialcharsbx($variant) ?>">
   <button type="submit" class="ui-btn ui-btn-danger ui-btn-round">Очистить текущий</button>
  </form>
 </div>

 <div class="sandbox-hero">
  <h1 class="sandbox-title"><?= htmlspecialcharsbx($APPLICATION->GetTitle()) ?>: <?= htmlspecialcharsbx($currentVariant['title']) ?></h1>
  <p class="sandbox-text">A показывает голубой прямоугольник, B - сиреневый. У каждого варианта свой tagged cache и свой тег.</p>
 </div>
 <?php $cache->output() ?>

 <div><?= htmlspecialcharsbx($cacheState) ?></div>
 <div><?= htmlspecialcharsbx($result['message']) ?></div>
 <div>Тег: <?= htmlspecialcharsbx($result['tag']) ?></div>
 <div>Время: <?= htmlspecialcharsbx((string)$result['time']) ?></div>
 <ul>
  <?php foreach ($result['players'] as $player): ?>
   <li><?= htmlspecialcharsbx($player) ?></li>
  <?php endforeach; ?>
 </ul>
</div>
<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
