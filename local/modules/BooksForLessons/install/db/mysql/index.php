<?php

class BooksForLessons extends CModule
{
 public function InstallDB(): array
 {
  global $DB;

  $sqlFile = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/BooksForLessons/install/db/mysql/books.sql';

  if (!is_file($sqlFile)) {
   return [
    'success' => false,
    'message' => 'SQL-файл не найден.',
    'errors' => [$sqlFile],
   ];
  }

  $errors = $DB->RunSqlBatch($sqlFile);

  if ($errors === false) {
   return [
    'success' => true,
    'message' => 'Таблица books успешно создана.',
   ];
  }

  return [
   'success' => false,
   'message' => 'Не удалось создать таблицы.',
   'errors' => is_array($errors) ? $errors : [$errors],
  ];
 }
}
