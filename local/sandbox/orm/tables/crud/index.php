<?php

use Bitrix\Main\Page\Asset;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('CRUD-запросы');
Asset::getInstance()->addCss('/local/sandbox/style.css');

?>

<div class="sandbox-study">
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
