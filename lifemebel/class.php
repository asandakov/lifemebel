<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use Bitrix\Main\Web\Json;
use \Bitrix\Main\Localization\Loc as Loc;


class LifeMebelSite extends CBitrixComponent
{

    // ПРОВЕРКА НА РАБОТОСПОСОБНОСТЬ
    public function checkComponent()
    {
        if (!Main\Loader::includeModule('iblock'))
            throw new Main\LoaderException(Loc::getMessage('IB_LIFEMEDEL_SITE'));

        if ($this->arParams["IBLOCK_ID"] <= 0)
            throw new Main\LoaderException(Loc::getMessage('NO_IBLOCK_ID_LIFEMEDEL_SITE'));

    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams["IBLOCK_ID"] = (int)$arParams["IBLOCK_ID"];

        $arParams["NEWS_COUNT"] = (int)$arParams["NEWS_COUNT"];
        if ($arParams["NEWS_COUNT"] <= 0)
            $arParams["NEWS_COUNT"] = 20;

        if (!isset($arParams["CACHE_TIME"]))
            $arParams["CACHE_TIME"] = 3600;

        return $arParams;
    }

    //ВЫЗОВ МЕТОДА
    protected function doAction($action)
    {
        if (is_callable(array($this, $action))) {
            call_user_func(
                array($this, $action)
            );
        }
    }


    //КАКОЙ МЕТОД
    protected function prepareAction()
    {
        $action = $this->request->get('action');

        if (empty($action)) {
            $action = 'showPage';
        }

        return $action;
    }

    //ГОРОД - ИНФОРМАЦИЯ
    public function getСity($arFilter = array(), $limit = 20)
    {
        global $CACHE_MANAGER;

        $arResult = array("LIST" => array(), "CACHE" => false);

        $arRealFilter = array("IBLOCK_ID" => $this->arParams["IBLOCK_ID"]);
        if (count($arFilter) > 0) $arRealFilter = array_merge($arRealFilter, $arFilter);

        $arData = array(
            "filter" => $arRealFilter,
            'select' => array('ID', 'NAME', 'IBLOCK_ID'),
            "limit" => $limit
        );

        if ($limit == 0) {
            unset($arData["limit"]);
        }

        $cacheTime = "3600000";
        $cacheId = md5(serialize($arData));
        $cacheDir = '/' . SITE_ID . '/lifemebel/city';
        $cache = Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $arResult = $cache->getVars();
            $arResult["CACHE"] = true;

        } elseif ($cache->startDataCache()) {

            $result = \Bitrix\Iblock\ElementTable::getList($arData);
            while ($arFields = $result->fetch()) {
                $arResult["LIST"][] = $arFields;
            }

            if (count($arResult["LIST"]) == 0) {
                $cache->abortDataCache();
            }

            $CACHE_MANAGER->StartTagCache($cacheDir);
            $CACHE_MANAGER->RegisterTag("iblock_id_" . $this->arParams["IBLOCK_ID"]);
            $CACHE_MANAGER->EndTagCache();

            $cache->endDataCache($arResult);
        }

        return $arResult;
    }

    //НАЛИЧИЯ ГОРОДА
    private function isCity($arFilter = array())
    {
        $arResult = array("ID" => 0, "ERROR" => "");

        $arFilter["NAME"] = trim($arFilter["NAME"]);
        if (!empty($arFilter["NAME"])) {

            $arCityData = $this->getСity($arFilter, 1);
            $arCity = array_shift($arCityData["LIST"]);
            if ($arCity["ID"] > 0) {
                $arResult["ID"] = $arCity["ID"];
            }

        } else {
            $arResult["ERROR"] = "Не задан название города";
        }
        return $arResult;
    }

    //ДОБАВИТЬ ГОРОД
    private function addCity($arData = array(), $isCity = true)
    {
        $arResult = array("ID" => 0, "ERROR" => "");

        $flagAdd = true;

        if ($isCity) {
            $arIsCity = $this->isCity(array("NAME" => $arData["NAME"]));
            if ($arIsCity["ID"] > 0) {
                $flagAdd = false;
            }
        }

        if ($flagAdd) {
            $el = new CIBlockElement;
            $arRealData = array(
                "IBLOCK_ID" => $this->arParams["IBLOCK_ID"]
            );
            if (count($arData) > 0) $arRealData = array_merge($arRealData, $arData);

            if ($idElement = $el->Add($arRealData)) {
                $arResult["ID"] = $idElement;
            } else {
                $arResult["ERROR"] = 'ERROR ADD CITY' . $el->LAST_ERROR;
            }
        } else {
            $arResult["ERROR"] = "Город #" . $arIsCity["ID"] . " уже есть в БД";
        }

        return $arResult;
    }

    //РЕДАКТИРОВАНИЕ ГОРОДА
    private function editCity($idCity = 0, $arData = array(), $isCity = true)
    {
        $arResult = array("ID" => 0, "ERROR" => "");

        $idCity = (int)$idCity;
        if ($idCity > 0) {

            $flagUpd = true;
            if ($isCity) {
                $arIsCity = $this->isCity(array("!ID" => $idCity, "NAME" => $arData["NAME"]));
                if ($arIsCity["ID"] > 0) {
                    $flagUpd = false;
                }
            }

            if ($flagUpd) {
                $el = new CIBlockElement;
                $arRealData = array(
                    "IBLOCK_ID" => $this->arParams["IBLOCK_ID"]
                );
                if (count($arData) > 0) $arRealData = array_merge($arRealData, $arData);
                $flagUpd = $el->Update($idCity, $arRealData);
                if ($flagUpd) {
                    $arResult["ID"] = $idCity;
                } else {
                    $arResult["ERROR"] = 'ERROR EDIT CITY' . $el->LAST_ERROR;
                }
            } else {
                $arResult["ERROR"] = "Не задан id города";
            }
        } else {
            $arResult["ERROR"] = "Город #" . $arIsCity["ID"] . " уже есть в БД";
        }
        return $arResult;
    }

    //УДАЛЕНИЕ ГОРОДА
    private function delCity($idCity = 0)
    {
        $arResult = array("ID" => 0, "ERROR" => "");

        $idCity = (int)$idCity;
        if ($idCity > 0) {
            $flagDelete = CIBlockElement::Delete($idCity);
            if ($flagDelete) {
                $arResult["ID"] = $idCity;
            } else {
                $arResult["ERROR"] = 'ERROR DELETE CITY';
            }
        } else {
            $arResult["ERROR"] = "Не задан id города";
        }
        return $arResult;
    }


    //ФОРМА С ГОРОДАМИ
    public function sendCityFormAjax()
    {
        global $APPLICATION;

        $arResult = array("MESSAGE" => "", "ERROR" => "");

        parse_str($this->request->get('data'), $arParams);

        switch ($arParams["type"]) {
            case "add":
                $arData = array(
                    "NAME" => trim($arParams["name"])
                );
                $arCity = $this->addCity($arData);
                $msg = "Город добавлен успешно";
                break;
            case "edit":
                $arData = array(
                    "NAME" => trim($arParams["name"])
                );
                $arCity = $this->editCity($arParams["city-id"], $arData);
                $msg = "Город изменен успешно";
                break;
            case "delete":
                $arCity = $this->delCity($arParams["city-id"]);
                $msg = "Город удален успешно";
                break;
        }

        if ($arCity["ID"] > 0) {
            $arResult["MESSAGE"] = $msg;
        } elseif (!empty($arCity["ERROR"])) {
            $arResult["ERROR"] = $arCity["ERROR"];
        } else {
            $arResult["ERROR"] = "Ошибка. Попробуйте еще раз.";
        }

        $result = array(
            'msg' => $arResult["MESSAGE"],
            'error' => $arResult["ERROR"],
            'action' => $this->request->get('action')
        );

        $APPLICATION->RestartBuffer();

        echo Json::encode($result);

        CMain::FinalActions();

        die();
    }


    //ПО УМОЛЧАНИЮ
    protected function showPage()
    {
        $arNavParams = array(
            "nPageSize" => $this->arParams["NEWS_COUNT"],
            "bShowAll" => false
        );
        $arNavigation = \CDBResult::GetNavParams($arNavParams);

        if ($this->startResultCache(false, array($arNavigation), $this->getSiteId() . $this->getRelativePath())) {

            $this->arResult["ITEMS"] = array();
            $rsElement = \CIBlockElement::GetList(array("ID" => "DESC"), array("IBLOCK_ID" => $this->arParams["IBLOCK_ID"]), false, $arNavParams, array("ID", "IBLOCK_ID", "NAME"));
            while ($arFields = $rsElement->GetNext()) {
                $this->arResult["ITEMS"][] = $arFields;
            }

            if (count($this->arResult["ITEMS"]) == 0) {
                $this->AbortResultCache();
            }

            $this->arResult["NAV_STRING"] = $rsElement->GetPageNavStringEx(
                $navComponentObject,
                "",
                "modern",
                false,
                $this
            );

            $this->setResultCacheKeys(array(
                "NAV_STRING",
            ));

            $this->IncludeComponentTemplate();
        }
    }


    public function executeComponent()
    {
        try {
            $this->checkComponent();
            $action = $this->prepareAction();
            $this->doAction($action);
        } catch (Exception $e) {
            $this->AbortResultCache();
            ShowError($e->getMessage());
        }

    }

}

?>