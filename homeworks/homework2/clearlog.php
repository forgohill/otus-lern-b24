<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use App\Debug\Log;

$logFileName = 'custom_' . date('d.m.Y') . '.log';
$logFileRelativePath = '/local/logs/' . $logFileName;
$logFileFullPath = $_SERVER['DOCUMENT_ROOT'] . $logFileRelativePath;

if (file_exists($logFileFullPath)) {
 Log::cleanLog('custom', true);
}

LocalRedirect('/homeworks/homework2/index.php');
exit;
