<?php
$autoload = $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

AddEventHandler(
    'iblock',
    'OnBeforeIBlockElementAdd',
    ['\App\Clinic\Event\ClinicCodeHandler', 'onBeforeElementAdd']
);

AddEventHandler(
    'iblock',
    'OnBeforeIBlockElementUpdate',
    ['\App\Clinic\Event\ClinicCodeHandler', 'onBeforeElementUpdate']
);
