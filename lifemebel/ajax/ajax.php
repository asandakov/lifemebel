<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

global $DB,$USER,$APPLICATION;
CBitrixComponent::includeComponentClass("site:lifemebel");

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

$signer = new \Bitrix\Main\Security\Sign\Signer;
try
{
	$params = $signer->unsign($request->get('signedParamsString'), 'lifemebel');
	
	$params = unserialize(base64_decode($params));
}
catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
{	
	die();
}


$component = new LifeMebelSite();
$component->arParams = $component->onPrepareComponentParams($params);
$component->executeComponent();

?>
