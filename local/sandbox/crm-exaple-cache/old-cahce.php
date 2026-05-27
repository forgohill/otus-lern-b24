<?php

use Bitrix\Main\Page\Asset;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

/** @var CMain $APPLICATION */
global $APPLICATION;

$APPLICATION->SetTitle('Old cache');
Asset::getInstance()->addCss('/local/sandbox/style.css');
$cacheTime = 15; // время кеширования, указывается в секундах
// $cacheId = (string)($_REQUEST['CACHE_ID'] ?? 'old-cache-demo'); // уникальный строковый идентификатор кеша
$cacheId  = 'mycache_list';
$cacheDir = 'myCache'; // директория кеша

// создаем объект
$obCache = new CPHPCache; // если кеш есть и он ещё не истек, то
if ($obCache->InitCache($cacheTime, $cacheId, $cacheDir)) {
    // получаем закешированные переменные
    $arResult = $obCache->GetVars();
} elseif ($obCache->StartDataCache()) {
    // иначе обращаемся к базе
    $arResult = [
        'message' => 'тут кеш',
        'players' => ['Toni Kroos', 'Luka Modric', 'Federico Valverde']
    ];
    if ($obCache->StartDataCache()) {

        echo 'Запись -OLD CACHE- данных в кеш ' . time();
        echo '<div class="sandbox-red-square"></div>';

        $obCache->EndDataCache($arResult);
    }
    // записываем данные в файл кеша
}
$result = $arResult;
?>

<div class="sandbox-page">
    <div class="sandbox-top-actions">
        <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
    </div>

    <div class="sandbox-hero">
        <h1 class="sandbox-title"><?= htmlspecialcharsbx($APPLICATION->GetTitle()) ?></h1>
        <p class="sandbox-text">Пустая учебная страница для экспериментов со старым Cache API.</p>
    </div>
    <div><?= htmlspecialcharsbx($result['message']) ?></div>
    <ul>
        <?php foreach ($result['players'] as $player): ?>
            <li><?= htmlspecialcharsbx($player) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php $obCache->Output(); ?>
<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
