<?php

use Local\Helper;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
global $APPLICATION;
$APPLICATION->SetTitle('Var Dumper Demo');

dump((object)
['DoDo ' => '100500',
 'DooDaa' => '200600']);
$iBlockCode = 'clients_s1';
dump(
 ['iBlockId' => Helper::getIblockIdByCode($iBlockCode),
  'iBlockCode' => $iBlockCode]
 );

 
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
?>