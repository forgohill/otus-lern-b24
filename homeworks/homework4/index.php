<?

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("ДЗ #4: Проект Titanic");

Loader::includeModule('ui');
Extension::load([
  'ui.fonts.opensans',
]);

Asset::getInstance()->addCss('/homeworks/homework4/src/styles/homework4.css');
?>
<div class="homework-page">
  <div class="homework-hero">
    <h1 class="homework-title"><? $APPLICATION->ShowTitle() ?></h1>
    <div class="homework-subtitle">Стартовая страница домашнего проекта Titanic.</div>
    <div class="homework-toolbar">
      <a class="homework-btn homework-btn--secondary" href="/homeworks/index.php">
        Вернуть к списку ДЗ
      </a>
      <a class="homework-btn homework-btn--primary" href="/homeworks/homework4/install/index.php">
        Открыть инсталятор
      </a>
    </div>
  </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
