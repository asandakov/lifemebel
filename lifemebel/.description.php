<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Bitrix\Main\Localization\Loc as Loc;
Loc::loadMessages(__FILE__); 
$arComponentDescription = array(
	"NAME" => Loc::getMessage('NAME_LIFEMEDEL_SITE'),
	"DESCRIPTION" => Loc::getMessage('DESCRIPTION_LIFEMEDEL_SITE'),
	"ICON" => '/images/icon.gif',
	"SORT" => 20,
	"PATH" => array(
		"ID" => 'LIFEMEDEL',
		"NAME" => Loc::getMessage('GROUP_LIFEMEDEL_SITE'),
		"SORT" => 10
	),
);
?>