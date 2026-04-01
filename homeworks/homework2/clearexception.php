<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use App\Debug\Log;

$logFileExceptionName = 'exception_' . date('d.m.Y') . '.log';
$logFileExceptionRelativePath = '/local/logs/' . $logFileExceptionName;
$logFileExceptionFullPath = $_SERVER['DOCUMENT_ROOT'] . $logFileExceptionRelativePath;

if (file_exists($logFileExceptionFullPath)) {
 Log::cleanLog('exception', true);
}

LocalRedirect('/homeworks/homework2/index.php');
exit;
