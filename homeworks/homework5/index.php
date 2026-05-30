<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

/** @var CMain $APPLICATION */
$APPLICATION->SetTitle("ДЗ 5: Кастомный компонент - Валюты");

?>
<p>
 <a class="ui-btn ui-btn-light-border ui-btn-round" href="/homeworks/">
  Назад к списку ДЗ
 </a>
</p>

<?php

$APPLICATION->IncludeComponent(
	"otus:currencies", 
	".default", 
	[
		"COMPONENT_TEMPLATE" => ".default",
		"CURRENCY" => "643",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600"
	],
	false
);
?>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
