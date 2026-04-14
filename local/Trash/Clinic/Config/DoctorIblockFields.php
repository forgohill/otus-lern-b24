<?php

declare(strict_types=1);

namespace App\Clinic\Config;

final class DoctorIblockFields
{
 public const LAST_NAME = 'LAST_NAME';
 public const FIRST_NAME = 'FIRST_NAME';
 public const MIDDLE_NAME = 'MIDDLE_NAME';
 public const BIRTH_DATE = 'BIRTH_DATE';
 public const PROCEDURE_IDS = 'PROC_IDS_MULTI';
 public const INDIVIDUAL_TAX_NUMBER = 'INDIVIDUAL_TAX_NUMBER';

 private function __construct() {}
}
