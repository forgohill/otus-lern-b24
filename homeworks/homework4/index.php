<?

use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("ДЗ #_: ___");

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
?>
<h1 class="mb-3"><? $APPLICATION->ShowTitle() ?></h1>

<a class="btn btn-primary" href="/homeworks/homework4/install-museum-stories.php">
  Открыть установку инфоблоков Museum Stories
</a>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
