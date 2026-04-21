<?php

use Bitrix\Main\Page\Asset;

use Bitrix\Main\Loader;
use Models\HospitalClients\HospitalClientsTable as Clients;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

Loader::includeModule('crm'); // нужен из-за Bitrix\Crm\ContactTable
Loader::includeModule('clients');


$APPLICATION->SetTitle('Описание таблицы');
Asset::getInstance()->addCss('/local/sandbox/style.css');


$collection = Clients::getList([
 // 'filter' => ['id' => 1],
 'select' => [
  '*',
  'CONTACT.*'
 ]
])->fetchCollection();

dump($collection);

$clientsInfo = [];
if ($collection != null) {
 foreach ($collection as $key => $client) {
  $clientsInfo[] = [
   'ID' => $client->getId(),
   'FIRST_NAME' => $client->getFirstName(),
   'LAST_NAME' => $client->getLastName(),
   'AGE' => $client->getAge(),
   'DOCTOR_ID' => $client->getDoctorId(),
   'PROCEDURE_ID' => $client->getProcedureId(),
   'CONTACT_ID' => $client->getContactId(),
   'CONTACT_POST' => $client->getContact()->getPost(),

  ];
  // dump($client);
 };
 dump($clientsInfo);
}


?>

<div class="sandbox-study">
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
