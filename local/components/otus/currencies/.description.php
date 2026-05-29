<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

$arComponentDescription = [
	"NAME" => GetMessage("COMP_NAME"),
	"DESCRIPTION" => GetMessage("COMP_DESCR"),
	"ICON" => "/images/news_list.gif",
	"SORT" => 20,
	"CACHE_PATH" => "Y",
	"PATH" => [
		"ID" => "otus",
		"CHILD" => [
			"ID" => "currencies",
			"NAME" => GetMessage("COMP_CATEGORY"),
		],
	],
];
