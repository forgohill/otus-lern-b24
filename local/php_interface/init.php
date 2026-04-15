<?php
$autoload = $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}
