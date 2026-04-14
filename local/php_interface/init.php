<?php
$autoload = $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

use App\Iblock\Event\ElementEventHandler;

AddEventHandler(
    'iblock',
    'OnBeforeIBlockElementAdd',
    [ElementEventHandler::class, 'onBeforeElementAdd']
);

AddEventHandler(
    'iblock',
    'OnBeforeIBlockElementUpdate',
    [ElementEventHandler::class, 'onBeforeElementUpdate']
);
