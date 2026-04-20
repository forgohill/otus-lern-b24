<?php

namespace Models\HospitalClients;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Crm\ContactTable;

/**
 * Класс ClientsTable
 * 
 * Поля:
 * <ul>
 * <li> id int обязательное
 * <li> first_name string(50) необязательное
 * <li> last_name string(50) необязательное
 * <li> age int необязательное
 * <li> doctor_id int необязательное
 * <li> procedure_id int необязательное
 * <li> contact_id int необязательное
 * </ul>
 *
 * @package Bitrix\Clients
 **/

class HospitalClientsTable extends DataManager
{
 /**
  * Returns DB table name for entity.
  *
  * @return string
  */
 public static function getTableName()
 {
  return 'hospital_clients';
 }

 /**
  * Returns entity map definition.
  *
  * @return array
  */
 public static function getMap()
 {
  return [
   'id' => (new IntegerField(
    'id',
    []
   ))->configureTitle(Loc::getMessage('CLIENTS_ENTITY_ID_FIELD'))
    ->configurePrimary(true)
    ->configureAutocomplete(true),
   'first_name' => (new StringField(
    'first_name',
    [
     'validation' => [__CLASS__, 'validateFirstName']
    ]
   ))->configureTitle(Loc::getMessage('CLIENTS_ENTITY_FIRST_NAME_FIELD')),
   'last_name' => (new StringField(
    'last_name',
    [
     'validation' => [__CLASS__, 'validateLastName']
    ]
   ))->configureTitle(Loc::getMessage('CLIENTS_ENTITY_LAST_NAME_FIELD')),
   'age' => (new IntegerField(
    'age',
    []
   ))->configureTitle(Loc::getMessage('CLIENTS_ENTITY_AGE_FIELD'))
    ->configureSize(1),
   'doctor_id' => (new IntegerField(
    'doctor_id',
    []
   ))->configureTitle(Loc::getMessage('CLIENTS_ENTITY_DOCTOR_ID_FIELD'))
    ->configureSize(1),
   'procedure_id' => (new IntegerField(
    'procedure_id',
    []
   ))->configureTitle(Loc::getMessage('CLIENTS_ENTITY_PROCEDURE_ID_FIELD'))
    ->configureSize(1),
   'contact_id' => (new IntegerField(
    'contact_id',
    []
   ))->configureTitle(Loc::getMessage('CLIENTS_ENTITY_CONTACT_ID_FIELD')),

   // виртуальное поле CONTACT которое получает запись из таблицы контактов
   // где ID равен значению contact_id таблицы hospital_clients
   (new Reference(
    name: 'CONTACT',
    referenceEntity: ContactTable::class,
    referenceFilter: Join::on('this.contact_id', 'ref.ID')
   ))->configureJoinType(type: 'inner'),

  ];
 }

 /**
  * Returns validators for first_name field.
  *
  * @return array
  */
 public static function validateFirstName(): array
 {
  return [
   new LengthValidator(null, 50),
  ];
 }

 /**
  * Returns validators for last_name field.
  *
  * @return array
  */
 public static function validateLastName(): array
 {
  return [
   new LengthValidator(null, 50),
  ];
 }
 /**
  * Returns validators for age field.
  *
  * @return array
  */
 public static function validateAge()
 {
  return [
   new LengthValidator(min: 2, max: null),
  ];
 }
}
