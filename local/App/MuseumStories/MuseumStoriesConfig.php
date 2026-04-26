<?php

declare(strict_types=1);

namespace App\MuseumStories;

/**
 * Централизованные коды и настройки инфоблока музейных зданий.
 */
final class MuseumStoriesConfig
{
  /**
   * Стандартный тип инфоблоков для универсальных списков Bitrix.
   */
  public const IBLOCK_TYPE_ID = 'lists';

  /**
   * Символьный код инфоблока зданий.
   */
  public const IBLOCK_CODE = 'museum_buildings';

  /**
   * Символьный API-код инфоблока зданий.
   */
  public const IBLOCK_API_CODE = 'museumBuildings';

  /**
   * Название инфоблока зданий.
   */
  public const IBLOCK_NAME = 'Музейные здания';

  /**
   * Описание инфоблока зданий.
   */
  public const IBLOCK_DESCRIPTION = 'Музейные здания';

  /**
   * Символьный код инфоблока залов.
   */
  public const HALLS_IBLOCK_CODE = 'museum_halls';

  /**
   * Символьный API-код инфоблока залов.
   */
  public const HALLS_IBLOCK_API_CODE = 'museumHalls';

  /**
   * Название инфоблока залов.
   */
  public const HALLS_IBLOCK_NAME = 'Музейные Залы';

  /**
   * Описание инфоблока залов.
   */
  public const HALLS_IBLOCK_DESCRIPTION = 'Залы музея';

  /**
   * Символьный код инфоблока типов.
   */
  public const TYPES_IBLOCK_CODE = 'museum_types';

  /**
   * Символьный API-код инфоблока типов.
   */
  public const TYPES_IBLOCK_API_CODE = 'museumTypes';

  /**
   * Название инфоблока типов.
   */
  public const TYPES_IBLOCK_NAME = 'Музейные Типы';

  /**
   * Описание инфоблока типов.
   */
  public const TYPES_IBLOCK_DESCRIPTION = 'Типы экспонатов';

  /**
   * Сайты, к которым привязывается инфоблок по умолчанию.
   *
   * @var list<string>
   */
  public const DEFAULT_SITE_IDS = ['s1'];

  /**
   * Код свойства источника.
   */
  public const PROPERTY_SOURCE_ID = 'SOURCE_ID';

  /**
   * Код свойства адреса.
   */
  public const PROPERTY_ADDRESS_RU = 'ADDRESS_RU';

  /**
   * Код свойства координат на карте.
   */
  public const PROPERTY_MAP_COORDS = 'MAP_COORDS';

  /**
   * Код свойства признака закрытия.
   */
  public const PROPERTY_CLOSED_FLAG = 'CLOSED_FLAG';

  /**
   * Код свойства ссылки на билеты.
   */
  public const PROPERTY_TICKET_URL = 'TICKET_URL';

  /**
   * Код свойства пути к картинке.
   */
  public const PROPERTY_PICTURE_PATH = 'PICTURE_PATH';

  /**
   * Код свойства таймлайна.
   */
  public const PROPERTY_TIMELINE_RU = 'TIMELINE_RU';

  /**
   * Код свойства привязки зала к зданию.
   */
  public const HALL_BUILDING_REF = 'BUILDING_REF';

  /**
   * Код свойства источника здания.
   */
  public const HALL_BUILDING_SOURCE_ID = 'BUILDING_SOURCE_ID';

  /**
   * Код свойства источника этажа.
   */
  public const HALL_FLOOR_SOURCE_ID = 'FLOOR_SOURCE_ID';

  /**
   * Код свойства номера этажа.
   */
  public const HALL_FLOOR_NUMBER = 'FLOOR_NUMBER';

  /**
   * Код свойства номера зала.
   */
  public const HALL_HALL_NUMBER = 'HALL_NUMBER';

  /**
   * Код свойства плана зала.
   */
  public const HALL_PLAN_PATH = 'PLAN_PATH';

  /**
   * Код свойства изображения зала.
   */
  public const HALL_IMAGE_PATH = 'IMAGE_PATH';

  /**
   * Код свойства виртуального тура.
   */
  public const HALL_VIRTUAL_TOUR_URL = 'VIRTUAL_TOUR_URL';

  /**
   * Код свойства типа экспоната.
   */
  public const TYPE_TYPE_CODE = 'TYPE_CODE';

  /**
   * Запрещает создание экземпляров класса-конфига.
   */
  private function __construct()
  {
  }
}
