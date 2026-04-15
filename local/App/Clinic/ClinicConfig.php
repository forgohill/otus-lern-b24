<?php

declare(strict_types=1);

namespace App\Clinic;

/**
 * Централизованные коды инфоблоков и их свойств для модуля клиники.
 */
final class ClinicConfig
{
 /**
  * Символьный код инфоблока врачей.
  */
 public const IBLOCK_DOCTORS = 'doctors';

 /**
  * Символьный код инфоблока процедур.
  */
 public const PROCEDURES_IBLOCK_CODE = 'procedures';

 /**
  * Код свойства фамилии врача.
  */
 public const DOCTOR_LAST_NAME = 'LAST_NAME';

 /**
  * Код свойства имени врача.
  */
 public const DOCTOR_FIRST_NAME = 'FIRST_NAME';

 /**
  * Код свойства отчества врача.
  */
 public const DOCTOR_MIDDLE_NAME = 'MIDDLE_NAME';

 /**
  * Код множественного свойства процедур врача.
  */
 public const DOCTOR_PROCEDURE_IDS = 'PROC_IDS_MULTI';

 /**
  * Код свойства описания процедуры.
  */
 public const PROCEDURE_DESCRIPTION = 'PROCEDURE_DESCRIPTION';

 /**
  * ORM-путь к значению свойства описания процедуры.
  */
 public const PROCEDURE_DESCRIPTION_VALUE = 'PROCEDURE_DESCRIPTION.VALUE';


 /**
  * Запрещает создание экземпляров класса-конфига.
  */
  private function __construct() {}
}
