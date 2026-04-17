<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('CRM - Смарт процессы');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/local/sandbox/style.css');

// Элемента смарт-процесса с фильтром по родительской сущности
use Bitrix\Crm\Service\Container;
use Bitrix\main\Loader;
// use Bitrix\Crm\Model\Dynamic\TypeTable; // тут храняться Смарт-процессы получить по ID кода или названия 

Loader::includeModule('crm');
$description_1 = 'Этот блок получает элементы смарт-процесса 1038, связанные со сделкой ID 5, и выбирает у них только ID и название.';
$domainProlongationFactory = Container::getInstance()->getFactory(1038);
$itemsFactory = $domainProlongationFactory->getItems([
 'filter' => ['PARENT_ID_' . \CCrmOwnerType::Deal => 5],
 'select' => ['ID', 'TITLE'],
]);


$description_2 = 'Получаем фабрику сделок CRM и через нее выбираем сделку с ID 5. Метод getItems() возвращает массив объектов Item, с которыми дальше можно работать через getId(), get("TITLE"), getData() и другие методы.';

$dealFactory = Container::getInstance()->getFactory(\CCrmOwnerType::Deal);
// $contactFactory = Container::getInstance()->getFactory(\CCrmOwnerType::Contact); // пример
// $companyFactory = Container::getInstance()->getFactory(\CCrmOwnerType::Company); // пример
// $dealFactory->getItemsFilteredByPermissions(['ID' => 5]); -- тоже что и ниже только с учетом прав текущего юзера
// $dealFactory->getItemsCountFilteredByPermissions(['ID' => 5]); -- количество элементов
$itemsDealFactory = $dealFactory->getItems([
 'filter' => ['ID' => 5],
 // 'select' => ['ID', 'TITLE'], // если включить поля придут пустые
]);

$description_3 = 'Создание нового контакст (СРМ сущности)';

$contactFactory = Container::getInstance()->getFactory(\CCrmOwnerType::Contact);
$itemsContactFactory = $contactFactory->getItems([
 'filter' => ['ID' => 5],
 // 'select' => ['ID', 'TITLE'], // если включить поля придут пустые
]);
$newContactItem = $contactFactory->createItem(); // Можно сразу предеать [] массивом где ключ поле => а значение и есть значение для поля
$newContactItem->setUfCrmInn('500100732259');
$newContactItem->setName('Александр');
$newContactItem->setLastName('БЮ.');
// $newContactItem->save(); // save() записывает новый или измененный Item в базу и возвращает Result; для полной CRM-логики лучше использовать операции фабрики.
$addOpetationNewContactItem = $contactFactory->getAddOperation($newContactItem);
// $addOpetationNewContactItem->disableBizProc(); // можно отключить то или иное в зависимости от необходимости
// $addOpetationNewContactItem->disableAllChecks(); //отключить сразу все
// $addOpetationNewContactItem ->enableBizProc(); // следом разрешаем только БП
// $addOpetationNewContactItem->launch(); // само создание контакта


$description_4 = 'Обновление  контакта (СРМ сущности)';
$conactItem = $contactFactory->getItem(8);
if ($contactItem) {
 $contactItem->setUfCrmInn('500100732259');
 $contactItem->setName('БЮ');
 $contactItem->setLastName('АЛЕКСАНДРОВ.');

 $updateOperation = $contactFactory->getUpdateOperation($contactItem);
 $updateResult = $updateOperation->launch(); // само изменение контакта

} else {
 dump('Контакт с ID 8 не найден');
}
$description_5 = 'Удаление контакта (CRM сущности)';
$contactItemForDelete = $contactFactory->getItem(8);
$deleteResultData = [
 'description' => $description_5,
 'success' => false,
 'errors' => ['Контакт с ID 8 не найден'],
];

if ($contactItemForDelete) {
 // $contactItemForDelete->delete(); // // delete() напрямую удаляет текущий Item из базы и возвращает Result; для CRM лучше использовать getDeleteOperation(), чтобы сработали проверки, права и связанные события.

 $deleteOperation = $contactFactory->getDeleteOperation($contactItemForDelete);
 $deleteResult = $deleteOperation->launch(); // само удаление контакта
 $deleteResultData = [
  'description' => $description_5,
  'success' => $deleteResult->isSuccess(),
  'errors' => $deleteResult->getErrorMessages(),
 ];
}

$p = \Bitrix\Main\PhoneNumber\Parser::getInstance();

$r = $p->parse('8 (995) 113-80-18');



?>

<div class="sandbox-page">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
 </div>

 <div class="sandbox-hero">
  <h1>CRM - Смарт процессы</h1>
  <p>Минимальная страница песочницы для экспериментов со смарт-процессами CRM.</p>
 </div>

 <div class="sandbox-section">
  <div class="sandbox-section-header">Раздел в разработке</div>
  <div class="sandbox-section-body">
   <p class="sandbox-text">
    Здесь будет интерфейс для проверки получения смарт-процессов и фабрик CRM.
   </p>
  </div>
 </div>
</div>
<?php
dump($description_1);
foreach ($itemsFactory as $key => $item) {
 dump([
  $key => $item->getData(),
  'ID' => $item->getId(),
  'TITLE' => $item->get('TITLE'),
 ]);
}

dump($domainProlongationFactory->getItem(1)->getTitle());

dump($description_2);
foreach ($itemsDealFactory as $key => $deal) {
 dump([

  'key' => $key,
  'ID' => $deal->getId(),
  'TITLE' => $deal->get('TITLE'),
  $key => $deal->getData(),
 ]);
}

dump([
 '$description_3' => $description_3,
 'создан контакт ' => $newContactItem->getId()
]);

dump([
 'description' => $deleteResultData['description'],
 'success' => $deleteResultData['success'],
 'errors' => $deleteResultData['errors'],
]);

dump([
 '$description_3' => $description_3,
 'national_number' => $r->getNationalNumber(),
 'national_prefix' => $r->getNationalPrefix(),
]);
?>
<div class="sandbox-section">
 <div class="sandbox-section-header">Примеры работы с CRM Bitrix24 box через код</div>
 <div class="sandbox-section-body">
  <p class="sandbox-text">
   Ниже собраны простые учебные примеры работы с CRM через D7 API Bitrix24:
   получение элементов смарт-процесса, чтение сделки, создание, обновление и удаление контакта,
   а также разбор телефонного номера.
  </p>
 </div>
</div>

<div class="sandbox-section">
 <div class="sandbox-section-header">1. Подключение CRM и получение фабрики</div>
 <div class="sandbox-section-body">
  <p class="sandbox-text">
   Сначала подключаем модуль <b>crm</b> и получаем фабрику нужной сущности.
   Фабрика — это точка входа для работы с конкретным типом CRM-элемента:
   сделкой, контактом, компанией или смарт-процессом.
  </p>

  <pre class="sandbox-code"><code>&lt;?php
use Bitrix\Crm\Service\Container;
use Bitrix\Main\Loader;

Loader::includeModule('crm');

$dealFactory = Container::getInstance()-&gt;getFactory(\CCrmOwnerType::Deal);
$contactFactory = Container::getInstance()-&gt;getFactory(\CCrmOwnerType::Contact);
?&gt;</code></pre>
 </div>
</div>

<div class="sandbox-section">
 <div class="sandbox-section-header">2. Получение элементов смарт-процесса по родительской сделке</div>
 <div class="sandbox-section-body">
  <p class="sandbox-text">
   Этот пример получает элементы смарт-процесса с <b>entityTypeId = 1038</b>,
   которые связаны со сделкой <b>ID = 5</b>.
   Из найденных элементов выбираются только поля <b>ID</b> и <b>TITLE</b>.
  </p>

  <pre class="sandbox-code"><code>&lt;?php
use Bitrix\Crm\Service\Container;

$domainProlongationFactory = Container::getInstance()-&gt;getFactory(1038);

$itemsFactory = $domainProlongationFactory-&gt;getItems([
    'filter' =&gt; ['PARENT_ID_' . \CCrmOwnerType::Deal =&gt; 5],
    'select' =&gt; ['ID', 'TITLE'],
]);

foreach ($itemsFactory as $item) {
    dump([
        'ID' =&gt; $item-&gt;getId(),
        'TITLE' =&gt; $item-&gt;get('TITLE'),
    ]);
}
?&gt;</code></pre>
 </div>
</div>

<div class="sandbox-section">
 <div class="sandbox-section-header">3. Получение сделки по ID</div>
 <div class="sandbox-section-body">
  <p class="sandbox-text">
   Здесь мы берём фабрику сделок и получаем сделку с <b>ID = 5</b>.
   Метод <b>getItems()</b> возвращает массив объектов Item, с которыми дальше можно работать
   через <b>getId()</b>, <b>get('TITLE')</b>, <b>getData()</b> и другие методы.
  </p>

  <pre class="sandbox-code"><code>&lt;?php
$dealFactory = Container::getInstance()-&gt;getFactory(\CCrmOwnerType::Deal);

$itemsDealFactory = $dealFactory-&gt;getItems([
    'filter' =&gt; ['ID' =&gt; 5],
]);

foreach ($itemsDealFactory as $deal) {
    dump([
        'ID' =&gt; $deal-&gt;getId(),
        'TITLE' =&gt; $deal-&gt;get('TITLE'),
        'DATA' =&gt; $deal-&gt;getData(),
    ]);
}
?&gt;</code></pre>
 </div>
</div>

<div class="sandbox-section">
 <div class="sandbox-section-header">4. Создание нового контакта</div>
 <div class="sandbox-section-body">
  <p class="sandbox-text">
   Пример создаёт новый объект контакта, заполняет ему ИНН, имя и фамилию,
   а потом готовит операцию добавления. Для безопасности запуск сохранения оставлен закомментированным.
  </p>

  <pre class="sandbox-code"><code>&lt;?php
$contactFactory = Container::getInstance()-&gt;getFactory(\CCrmOwnerType::Contact);

$newContactItem = $contactFactory-&gt;createItem();
$newContactItem-&gt;setUfCrmInn('500100732259');
$newContactItem-&gt;setName('Александр');
$newContactItem-&gt;setLastName('БЮ.');

$addOperationNewContactItem = $contactFactory-&gt;getAddOperation($newContactItem);

// $addResult = $addOperationNewContactItem-&gt;launch(); // раскомментируй, если хочешь реально создать контакт

dump([
    'message' =&gt; 'Контакт подготовлен к созданию',
    'data' =&gt; $newContactItem-&gt;getData(),
]);
?&gt;</code></pre>
 </div>
</div>

<div class="sandbox-section">
 <div class="sandbox-section-header">5. Обновление существующего контакта</div>
 <div class="sandbox-section-body">
  <p class="sandbox-text">
   Пример получает контакт по ID, меняет его поля и готовит операцию обновления.
   Если контакт не найден, выводится сообщение.
  </p>

  <pre class="sandbox-code"><code>&lt;?php
$contactItem = $contactFactory-&gt;getItem(8);

if ($contactItem) {
    $contactItem-&gt;setUfCrmInn('500100732259');
    $contactItem-&gt;setName('БЮ');
    $contactItem-&gt;setLastName('АЛЕКСАНДРОВ.');

    $updateOperation = $contactFactory-&gt;getUpdateOperation($contactItem);

    // $updateResult = $updateOperation-&gt;launch(); // раскомментируй, если хочешь реально обновить контакт

    dump([
        'message' =&gt; 'Контакт подготовлен к обновлению',
        'data' =&gt; $contactItem-&gt;getData(),
    ]);
} else {
    dump('Контакт с ID 8 не найден');
}
?&gt;</code></pre>
 </div>
</div>

<div class="sandbox-section">
 <div class="sandbox-section-header">6. Удаление контакта</div>
 <div class="sandbox-section-body">
  <p class="sandbox-text">
   Этот пример показывает безопасный вариант удаления через CRM-операцию.
   Удаление лучше делать не прямым <b>delete()</b>, а через <b>getDeleteOperation()</b>.
  </p>

  <pre class="sandbox-code"><code>&lt;?php
$contactItemForDelete = $contactFactory-&gt;getItem(8);

if ($contactItemForDelete) {
    $deleteOperation = $contactFactory-&gt;getDeleteOperation($contactItemForDelete);

    // $deleteResult = $deleteOperation-&gt;launch(); // раскомментируй, если хочешь реально удалить контакт

    dump([
        'message' =&gt; 'Контакт подготовлен к удалению',
        'id' =&gt; $contactItemForDelete-&gt;getId(),
    ]);
} else {
    dump('Контакт с ID 8 не найден');
}
?&gt;</code></pre>
 </div>
</div>

<div class="sandbox-section">
 <div class="sandbox-section-header">7. Разбор телефонного номера</div>
 <div class="sandbox-section-body">
  <p class="sandbox-text">
   Встроенный парсер Bitrix умеет разбирать телефон на составные части.
   В этом примере мы получаем национальный номер и национальный префикс.
  </p>

  <pre class="sandbox-code"><code>&lt;?php
$p = \Bitrix\Main\PhoneNumber\Parser::getInstance();
$r = $p-&gt;parse('8 (995) 113-80-18');

dump([
    'national_number' =&gt; $r-&gt;getNationalNumber(),
    'national_prefix' =&gt; $r-&gt;getNationalPrefix(),
]);
?&gt;</code></pre>
 </div>
</div>
<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
?>