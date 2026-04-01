<?php

namespace App\Debug;

use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Diag\FileExceptionHandlerLog;

class Log extends FileExceptionHandlerLog
{
 /**
  * Запись в лог
  *
  * @param           $message
  * @param   false   $clear
  * @param   string  $fileName
  *
  * @return void
  */

 public static function addLog($message, $clear = false, $fileName = 'custom', $timeVersion = true)
 {
  $logFile = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/' . $fileName;
  if ($timeVersion) {
   $logFile .= '_' . date("d.m.Y");
  }
  $logFile .= '.log';

  $_message = 'OTUS' . ' — ' . date("d.m.Y H:i:s");
  $_message .= "\n";
  $_message .= print_r($message, true);
  $_message .= "\n";
  $_message .= "---";
  $_message .= "\n";

  if ($clear) {
   file_put_contents($logFile, $_message);
  } else {
   file_put_contents($logFile, $_message, FILE_APPEND);
  }
 }

 /**
  * Очистка лога
  *
  * @param string $fileName
  * @param bool $timeVersion
  *
  * @return void
  */

 public static function cleanLog(string $fileName = 'custom', bool $timeVersion = true): void
 {
  $logFile = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/' . $fileName;

  if ($timeVersion) {
   $logFile .= '_' . date('d.m.Y');
  }

  $logFile .= '.log';

  file_put_contents($logFile, '');
 }


 /**
  * Запись в лог
  *
  * @param $exception
  * @param $logType
  *
  * @return void
  */

 public function write($exception, $logType): void
 {
  self::addLog(
   [
    'type'      => $logType,
    'message'   => $exception->getMessage(),
    'file'      => $exception->getFile(),
    'line'      => $exception->getLine(),
    'code'      => $exception->getCode(),
    'trace'     => $exception->getTraceAsString(),
    'formatted' => ExceptionHandlerFormatter::format($exception, false),
   ],
   false,
   'exception',
   true
  );
 }
}
