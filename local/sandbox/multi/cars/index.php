<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('Связываение моделей - Country');

use Bitrix\Main\Loader;

Loader::includeModule('iblock');

$iblockCode = 'country';
$iblock = CIBlock::GetList([], ['CODE' => 'country'])->Fetch();
$iblockId = (int)$iblock['ID'];

$arFilter = [
    'IBLOCK_ID' => $iblockId,
    'CODE' => 'rossiya',
];
$arSelect = ['ID', 'NAME', 'CODE'];
$res = CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
$iblockElementId = null;
if ($arElement = $res->Fetch()) {
    $iblockElementId = (int)$arElement['ID'];
}

/* Блок получает элемент инфоблока country по символьному коду, затем через ORM Bitrix D7 подгружает его валюту, столицу и связанные города вместе с полями NAME, EN и DE у связанных элементов.**/
$description_1 = 'Блок получает элемент инфоблока country по символьному коду, затем через ORM Bitrix D7 подгружает его валюту, столицу и связанные города вместе с полями NAME, EN и DE у связанных элементов.';;
$countryId = $iblockElementId; // Element(Country)Table

if ($iblockElementId) {
} else {
    dd('Элемент с кодом "rossiya" не найден в инфоблоке "country"');
}

$country = \Bitrix\Iblock\Elements\ElementCountryTable::getByPrimary(
    $countryId,
    array(
        'select' => [
            '*',
            'NAME',
            'CURRENCY',
            'CAPITAL',
            'CAPITAL.ELEMENT.ID',
            'CAPITAL.ELEMENT.NAME',
            'CAPITAL.ELEMENT.EN',
            'CAPITAL.ELEMENT.DE',
            'CITIES.ELEMENT.ID',
            'CITIES.ELEMENT.NAME',
            'CITIES.ELEMENT.EN',
            'CITIES.ELEMENT.DE',
        ]
    )
)->fetchObject();

/** Метод fetch() возвращает результат в виде плоского массива и плохо подходит для работы с множественными свойствами инфоблока, потому что при таком способе выборки обычно видно только одно значение свойства, а не всю коллекцию связанных элементов. */
$description_2 = 'Метод fetch() возвращает результат в виде плоского массива и плохо подходит для работы с множественными свойствами инфоблока, потому что при таком способе выборки обычно видно только одно значение свойства, а не всю коллекцию связанных элементов.';

$countryArr = \Bitrix\Iblock\Elements\ElementCountryTable::getByPrimary(
    $countryId,
    array(
        'select' => [
            // '*',
            'NAME',
            'CURRENCY',
            'CAPITAL',
            'CAPITAL.ELEMENT.ID',
            'CAPITAL.ELEMENT.NAME',
            'CAPITAL.ELEMENT.EN',
            'CAPITAL.ELEMENT.DE',
            'CITIES.ELEMENT.ID',
            'CITIES.ELEMENT.NAME',
            'CITIES.ELEMENT.EN',
            'CITIES.ELEMENT.DE',
        ]
    )
)->fetch();

/** Метод fetchCollection() получает коллекцию ORM-объектов стран, благодаря чему удобно работать с одиночными и множественными свойствами инфоблока, а также со связанными элементами столицы и городов без потери данных. **/
$description_3 = 'Метод fetchCollection() получает коллекцию ORM-объектов стран, благодаря чему удобно работать с одиночными и множественными свойствами инфоблока, а также со связанными элементами столицы и городов без потери данных.';
$countryCollection = \Bitrix\Iblock\Elements\ElementCountryTable::getList([

    'select' => [
        // '*',
        'NAME',
        'CURRENCY',
        'CAPITAL',
        'CAPITAL.ELEMENT.ID',
        'CAPITAL.ELEMENT.NAME',
        'CAPITAL.ELEMENT.EN',
        'CAPITAL.ELEMENT.DE',
        'CITIES.ELEMENT.ID',
        'CITIES.ELEMENT.NAME',
        'CITIES.ELEMENT.EN',
        'CITIES.ELEMENT.DE',
    ],
    'filter' => [

        // Включаем фильтр по ID, чтобы получить коллекцию, содержащую только одну страну. Это нужно для демонстрации работы с коллекцией и связанными элементами. В реальной задаче можно использовать более широкий фильтр для получения нескольких стран и их связанных данных.

        // 'ID' => $countryId,
        // 'ACTIVE' => 'Y'
    ],
])->fetchCollection();


/** Метод fetchAll() возвращает результат ORM-запроса как плоский массив строк. При выборке стран без фильтра по конкретному ID и с множественным свойством CITIES каждая строка результата соответствует не одной стране целиком, а отдельной комбинации "страна + столица + один связанный город". Поэтому в выводе получается 17 записей: страны, у которых несколько городов, повторяются в нескольких строках, по одной строке на каждый город. **/
$description_4 = 'Метод fetchAll() возвращает результат ORM-запроса как плоский массив строк. При выборке стран без фильтра по конкретному ID и с множественным свойством CITIES каждая строка результата соответствует не одной стране целиком, а отдельной комбинации "страна + столица + один связанный город". Поэтому в выводе получается 17 записей: страны, у которых несколько городов, повторяются в нескольких строках, по одной строке на каждый город.';

$countryArrFetchAll = \Bitrix\Iblock\Elements\ElementCountryTable::getList([

    'select' => [
        // '*',
        'NAME',
        'CURRENCY',
        'CAPITAL',
        'CAPITAL.ELEMENT.ID',
        'CAPITAL.ELEMENT.NAME',
        'CAPITAL.ELEMENT.EN',
        'CAPITAL.ELEMENT.DE',
        'CITIES.ELEMENT.ID',
        'CITIES.ELEMENT.NAME',
        'CITIES.ELEMENT.EN',
        'CITIES.ELEMENT.DE',
    ],
    'filter' => [

        // Включаем фильтр по ID, чтобы получить коллекцию, содержащую только одну страну. Это нужно для демонстрации работы с коллекцией и связанными элементами. В реальной задаче можно использовать более широкий фильтр для получения нескольких стран и их связанных данных.

        'ID' => $countryId,
        'ACTIVE' => 'Y'
    ],
])->fetchAll();

?>
<style>
    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: "Open Sans", Arial, sans-serif;
    }

    .sandbox-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px 16px 48px;
    }

    .sandbox-hero {
        background: #f8fafc;
        border: 1px solid #dfe5ec;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .sandbox-title {
        margin: 0 0 12px;
        font-size: 28px;
        line-height: 36px;
        font-weight: 700;
        color: #1f2d3d;
    }

    .sandbox-text {
        margin: 0;
        font-size: 15px;
        line-height: 24px;
        color: #525c69;
        max-width: 820px;
    }

    .sandbox-section {
        background: #ffffff;
        border: 1px solid #dfe5ec;
        border-radius: 16px;
        margin-bottom: 24px;
        overflow: hidden;
    }

    .sandbox-section-header {
        padding: 18px 24px;
        border-bottom: 1px solid #eef2f4;
        font-size: 20px;
        line-height: 28px;
        font-weight: 600;
        color: #1f2d3d;
        background: #fff;
    }

    .sandbox-section-body {
        padding: 24px;
    }

    .sandbox-actions {
        margin: 0;
        padding: 0;
        list-style: none;
        border: 1px solid #eef2f4;
        border-radius: 12px;
        overflow: hidden;
    }

    .sandbox-actions-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 16px 18px;
        border-bottom: 1px solid #eef2f4;
        background: #fff;
    }

    .sandbox-actions-item:last-child {
        border-bottom: none;
    }

    .sandbox-actions-content {
        flex: 1;
    }

    .sandbox-actions-label {
        margin: 0 0 4px;
        font-size: 15px;
        line-height: 22px;
        font-weight: 600;
        color: #2f3b47;
    }

    .sandbox-actions-description {
        margin: 0;
        font-size: 14px;
        line-height: 21px;
        color: #6b7280;
    }

    .sandbox-top-actions {
        margin-bottom: 16px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .sandbox-title {
            font-size: 24px;
            line-height: 32px;
        }

        .sandbox-actions-item {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
<div class="sandbox-page">
    <div class="sandbox-top-actions">
        <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
    </div>

    <div class="sandbox-hero">
        <h1>Связывание моделей — Country</h1>
        <p>Эксперименты со связыванием моделей через инфоблок Country.</p>
    </div>

    <div class="sandbox-content">
        <p>Здесь будут эксперименты со связыванием моделей для работы с инфоблоками, связанными со странами.</p>

        <?php if ($iblockId): ?>
            <div style="padding:14px 18px; margin:16px 0; border-radius:10px; background:#f0fff4; color:#1f7a3d; border:1px solid #b7ebc6; font-family:Arial,sans-serif;">
                <b>Инфоблок найден</b><br>
                ID инфоблока 'country': <?= htmlspecialcharsbx($iblockId) ?>
            </div>
        <?php else: ?>
            <div style="padding:14px 18px; margin:16px 0; border-radius:10px; background:#fff5f5; color:#c53030; border:1px solid #f5b5b5; font-family:Arial,sans-serif;">
                <b>Ошибка</b><br>
                Инфоблок с кодом 'country' не найден
            </div>
        <?php endif; ?>

        <?php if ($iblockElementId): ?>
            <div style="max-width: 30%; margin:12px 0;padding:10px 14px;border-radius:8px;font:14px/1.4 Arial,sans-serif;background:#f0fff4;color:#1f7a3d;border:1px solid #b7ebc6;">
                <b>ID элемента rossiya:</b> <?= htmlspecialcharsbx($iblockElementId) ?>
            </div>
        <?php else: ?>
            <div style="max-width: 30%; margin:12px 0;padding:10px 14px;border-radius:8px;font:14px/1.4 Arial,sans-serif;background:#fff5f5;color:#c53030;border:1px solid #f5b5b5;">
                <b>Ошибка:</b> Элемент с кодом rossiya не найден
            </div>
        <?php endif; ?>

    </div>
</div>

<?php

// description_1
if ($country) {
    $citiesData = [];
    foreach (($country->getCities()?->getAll() ?? []) as $city) {
        $element = $city->getElement();
        $citiesData[] = [
            'id' => $element?->getId(),
            'name' => $element?->getName(),
            'en' => $element?->getEn()?->getValue(),
            'de' => $element?->getDe()?->getValue(),
        ];
    }
    dump([
        'description_1' => $description_1,
        'citiesData' => $citiesData,
        'country_GetId()' => $country->getId(),
        'country_GetName()' => $country->getName(),
        'country_GetCode()' => $country->getCode(),
        'country_GetCurrency()' => $country->getCurrency()->getValue(),
        'country_GetCapitalId()' => $country->getCapital()?->getElement()?->getId(),
        'country_GetCapitalName()' => $country->getCapital()?->getElement()?->getName(),
        'country_GetCapitalNameEn()' => $country->getCapital()?->getElement()?->getEn()?->getValue(),
        'country_GetCapitalNameDe()' => $country->getCapital()?->getElement()?->getDe()?->getValue(),
    ]);
}
// description_2
if ($countryArr) {
    dump([
        '$description_2' => $description_2,
        '$countryArr' => $countryArr
    ]);
}

// description_3
if ($countryCollection) {
    $countryMap = [];
    foreach ($countryCollection as $key) {


        $countryMap[] = [
            'NAME' => $key->getName(),
            'CURRENCY' => $key->getCurrency()->getValue(),
            'CAPITAL_ID' => $key->getCapital()?->getElement()?->getId(),
            'CAPITAL_NAME' => $key->getCapital()?->getElement()?->getName(),
            'CAPITAL_NAME_EN' => $key->getCapital()?->getElement()?->getEn()?->getValue(),
            'CAPITAL_NAME_DE' => $key->getCapital()?->getElement()?->getDe()?->getValue(),
            'CITIES' => array_map(function ($city) {
                return [
                    'ID' => $city?->getElement()?->getId(),
                    'NAME' => $city?->getElement()?->getName(),
                    'EN' => $city?->getElement()?->getEn()?->getValue(),
                    'DE' => $city?->getElement()?->getDe()?->getValue(),
                ];
            }, $key->getCities()?->getAll() ?? []),
        ];
    }
    dump([
        '$description_3' => $description_3,
        '$countryMap' => $countryMap,
    ]);
}

// description_4
if ($countryArrFetchAll) {
    $countryFetchAllMap = [];

    foreach ($countryArrFetchAll as $item) {
        $countryFetchAllMap[] = [
            'COUNTRY_NAME' => $item['NAME'],
            'CURRENCY' => $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CURRENCY_VALUE'],
            'CAPITAL_ID' => $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CAPITAL_ELEMENT_ID'],
            'CAPITAL_NAME' => $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CAPITAL_ELEMENT_NAME'],
            'CAPITAL_EN' => $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CAPITAL_ELEMENT_EN_VALUE'],
            'CAPITAL_DE' => $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CAPITAL_ELEMENT_DE_VALUE'],
            'CITY_ID' => $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CITIES_ELEMENT_ID'],
            'CITY_NAME' => $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CITIES_ELEMENT_NAME'],
            'CITY_EN' => $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CITIES_ELEMENT_EN_VALUE'],
            'CITY_DE' => $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CITIES_ELEMENT_DE_VALUE'],
        ];
    }
    $rows = [];

    foreach ($countryArrFetchAll as $item) {
        $rows[] =
            $item['NAME'] . ' | ' .
            $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CURRENCY_VALUE'] . ' | ' .
            $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CAPITAL_ELEMENT_NAME'] . ' | ' .
            $item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CITIES_ELEMENT_NAME'];
    }

    dump([
        '$description_4' => $description_4,
        '$countryFetchAllMap' => $countryFetchAllMap,
        'rows' => $rows,
        '$countryArrFetchAll' => $countryArrFetchAll,
    ]);
}

?>
<!-- ............................................................. -->
<style>
    .sandbox-note {
        margin: 0 0 16px;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid #dfe5ec;
        background: #f8fafc;
        color: #525c69;
        font-size: 14px;
        line-height: 22px;
    }

    .sandbox-faq {
        display: grid;
        gap: 16px;
    }

    .sandbox-faq-item {
        border: 1px solid #eef2f4;
        border-radius: 12px;
        padding: 18px;
        background: #fff;
    }

    .sandbox-faq-title {
        margin: 0 0 10px;
        font-size: 17px;
        line-height: 24px;
        font-weight: 600;
        color: #1f2d3d;
    }

    .sandbox-faq-text {
        margin: 0 0 12px;
        font-size: 14px;
        line-height: 22px;
        color: #525c69;
    }

    .sandbox-code {
        margin: 0;
        padding: 14px 16px;
        border-radius: 10px;
        border: 1px solid #eef2f4;
        background: #f6f8fa;
        color: #2f3b47;
        font-size: 13px;
        line-height: 21px;
        overflow-x: auto;
        white-space: pre-wrap;
        word-break: break-word;
        font-family: "JetBrains Mono", "Consolas", monospace;
    }

    .sandbox-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0 0 14px;
    }

    .sandbox-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #eef2f4;
        color: #525c69;
        font-size: 12px;
        line-height: 16px;
        font-weight: 600;
    }

    .sandbox-warning {
        margin: 12px 0 0;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid #ffe29a;
        background: #fff9e5;
        color: #8c6b00;
        font-size: 13px;
        line-height: 20px;
    }
</style>

<div class="sandbox-section">
    <div class="sandbox-section-header">FAQ / разбор файла</div>
    <div class="sandbox-section-body">
        <p class="sandbox-note">
            Этот файл — учебный стенд для экспериментов с инфоблоком <b>country</b>.
            Он не реализует отдельную бизнес-функцию, а показывает, как по-разному получать одну и ту же сущность страны:
            как ORM-объект, как плоский массив, как коллекцию объектов и как набор строк через <code>fetchAll()</code>.
        </p>

        <div class="sandbox-badge-row">
            <span class="sandbox-badge">Bitrix header/footer</span>
            <span class="sandbox-badge">CIBlock / CIBlockElement</span>
            <span class="sandbox-badge">D7 ORM</span>
            <span class="sandbox-badge">fetchObject()</span>
            <span class="sandbox-badge">fetch()</span>
            <span class="sandbox-badge">fetchCollection()</span>
            <span class="sandbox-badge">fetchAll()</span>
            <span class="sandbox-badge">dump / dd</span>
        </div>

        <div class="sandbox-faq">
            <div class="sandbox-faq-item">
                <h3 class="sandbox-faq-title">1. Что делает файл целиком</h3>
                <p class="sandbox-faq-text">
                    Сначала файл подключает Bitrix и модуль <code>iblock</code>, затем ищет инфоблок <code>country</code>,
                    находит в нём элемент с кодом <code>rossiya</code>, а после этого несколькими способами вытягивает
                    его данные: валюту, столицу и связанные города.
                </p>
                <pre class="sandbox-code">Loader::includeModule('iblock');

                $iblock = CIBlock::GetList([], ['CODE' => 'country'])->Fetch();
                $iblockId = (int)$iblock['ID'];

                $res = CIBlockElement::GetList([], [
                    'IBLOCK_ID' => $iblockId,
                    'CODE' => 'rossiya',
                ], false, ['nTopCount' => 1], ['ID', 'NAME', 'CODE']);</pre>
            </div>

            <div class="sandbox-faq-item">
                <h3 class="sandbox-faq-title">2. Зачем сначала старый API, а потом ORM</h3>
                <p class="sandbox-faq-text">
                    В файле используется связка старого API и D7 ORM. Старый API нужен, чтобы быстро найти ID элемента по символьному коду,
                    а ORM — чтобы уже удобно разбирать связанные свойства и работать с моделью страны как с объектом.
                </p>
                <pre class="sandbox-code">$countryId = $iblockElementId;

                $country = \Bitrix\Iblock\Elements\ElementCountryTable::getByPrimary(
                    $countryId,
                    ['select' => ['NAME', 'CURRENCY', 'CAPITAL', 'CITIES.ELEMENT.NAME']]
                )->fetchObject();</pre>
            </div>

            <div class="sandbox-faq-item">
                <h3 class="sandbox-faq-title">3. Что показывает fetchObject()</h3>
                <p class="sandbox-faq-text">
                    <code>fetchObject()</code> возвращает ORM-объект. Это самый наглядный вариант для сложных связей:
                    можно обращаться к данным через методы, а не через длинные ключи массива.
                </p>
                <pre class="sandbox-code">$country->getName();
                $country->getCurrency()->getValue();
                $country->getCapital()?->getElement()?->getName();</pre>
                <p class="sandbox-faq-text">
                    В этом файле именно этот блок лучше всего показывает работу со столицей и городами как со связанными сущностями.
                </p>
            </div>

            <div class="sandbox-faq-item">
                <h3 class="sandbox-faq-title">4. Что показывает fetch()</h3>
                <p class="sandbox-faq-text">
                    <code>fetch()</code> возвращает плоский массив. Для простых выборок это нормально, но для множественных свойств
                    такой формат неудобен: структура получается менее естественной, и работать с ней сложнее, чем с ORM-объектом.
                </p>
                <pre class="sandbox-code">$countryArr = \Bitrix\Iblock\Elements\ElementCountryTable::getByPrimary(
                    $countryId,
                    ['select' => ['NAME', 'CURRENCY', 'CAPITAL', 'CITIES.ELEMENT.NAME']]
                )->fetch();</pre>
                <div class="sandbox-warning">
                    Здесь файл специально показывает не лучший, а сравнительный вариант — чтобы было видно, почему объектный подход удобнее.
                </div>
            </div>

            <div class="sandbox-faq-item">
                <h3 class="sandbox-faq-title">5. Что показывает fetchCollection()</h3>
                <p class="sandbox-faq-text">
                    <code>fetchCollection()</code> возвращает коллекцию ORM-объектов. Это удобно, когда нужно получить не одну страну,
                    а список стран со связанными данными. В текущем файле фильтр по ID закомментирован, поэтому запрос фактически
                    демонстрирует работу именно с коллекцией элементов.
                </p>
                <pre class="sandbox-code">$countryCollection = \Bitrix\Iblock\Elements\ElementCountryTable::getList([
                    'select' => ['NAME', 'CURRENCY', 'CAPITAL', 'CITIES.ELEMENT.NAME'],
                    'filter' => [
                        // 'ID' => $countryId,
                        // 'ACTIVE' => 'Y'
                    ],
                ])->fetchCollection();</pre>
            </div>

            <div class="sandbox-faq-item">
                <h3 class="sandbox-faq-title">6. Почему fetchAll() даёт дубли строк</h3>
                <p class="sandbox-faq-text">
                    <code>fetchAll()</code> возвращает массив строк результата. Если у страны несколько связанных городов,
                    то одна и та же страна повторяется в нескольких строках — по одной строке на каждый связанный город.
                    Это удобно для анализа SQL-подобного результата, но неудобно как финальная доменная структура.
                </p>
                <pre class="sandbox-code">$countryArrFetchAll = \Bitrix\Iblock\Elements\ElementCountryTable::getList([
                    'select' => ['NAME', 'CURRENCY', 'CAPITAL', 'CITIES.ELEMENT.NAME'],
                    'filter' => [
                        'ID' => $countryId,
                        'ACTIVE' => 'Y'
                    ],
                ])->fetchAll();</pre>
            </div>

            <div class="sandbox-faq-item">
                <h3 class="sandbox-faq-title">7. Зачем здесь dump() и dd()</h3>
                <p class="sandbox-faq-text">
                    <code>dd()</code> используется как жёсткая остановка, если тестовый элемент не найден.
                    <code>dump()</code> — это отладочный вывод, который нужен, чтобы сравнить структуру результата у разных методов выборки.
                </p>
                <pre class="sandbox-code">if (!$iblockElementId) {
                    dd('Элемент с кодом "rossiya" не найден');
                }

                dump([
                    'description_1' => $description_1,
                    'citiesData' => $citiesData,
                ]);</pre>
            </div>

            <div class="sandbox-faq-item">
                <h3 class="sandbox-faq-title">8. Что здесь самое важное для понимания</h3>
                <p class="sandbox-faq-text">
                    Основная идея файла — не просто получить страну, а сравнить формы результата:
                </p>
                <pre class="sandbox-code">fetchObject()      → один ORM-объект
                fetch()            → один плоский массив
                fetchCollection()  → коллекция ORM-объектов
                fetchAll()         → массив плоских строк</pre>
                <p class="sandbox-faq-text">
                    Для связанных сущностей и множественных свойств в учебных и рабочих задачах Bitrix D7 обычно удобнее
                    <code>fetchObject()</code> и <code>fetchCollection()</code>.
                </p>
            </div>
        </div>
    </div>
</div>
<!-- ............................................................. -->
<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
?>