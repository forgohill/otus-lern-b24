<?php

declare(strict_types=1);

namespace App\Clinic;

final class ClinicConfig
{
 public const IBLOCK_DOCTORS = 'doctors';
 public const PROCEDURES_IBLOCK_CODE = 'procedures';

 public const DOCTOR_LAST_NAME = 'LAST_NAME';
 public const DOCTOR_FIRST_NAME = 'FIRST_NAME';
 public const DOCTOR_MIDDLE_NAME = 'MIDDLE_NAME';
 public const DOCTOR_PROCEDURE_IDS = 'PROC_IDS_MULTI';

 public const PROCEDURE_DESCRIPTION = 'PROCEDURE_DESCRIPTION';
 public const PROCEDURE_DESCRIPTION_VALUE = 'PROCEDURE_DESCRIPTION.VALUE';


 private function __construct() {}
}
