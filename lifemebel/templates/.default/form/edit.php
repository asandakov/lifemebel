<?
include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$arResult = $_POST;
if ($arResult["TYPE"] == "edit") {

    CBitrixComponent::includeComponentClass("site:lifemebel");
    $signer = new \Bitrix\Main\Security\Sign\Signer;
    try {
        $params = $signer->unsign($request->get('signedParamsString'), 'lifemebel');
        $params = unserialize(base64_decode($params));
    } catch (\Bitrix\Main\Security\Sign\BadSignatureException $e) {

    }
    if (!empty($params) && $arResult["ID"] > 0) {
        $component = new LifeMebelSite();
        $component->arParams = $component->onPrepareComponentParams($params);
        $component->checkComponent();
        $arCityData = $component->getСity(array("ID" => $arResult["ID"]), 1);
        $arCity = array_shift($arCityData["LIST"]);
        if ($arCity["ID"] > 0) {
            ?>
            <div class="modal-header">
                <div class="modal-title" id="actionModalLabel">Изменить город <?= $arCity["NAME"] ?><span></span>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="msg"></div>
            <form name="form-action" class="needs-validation" id="form-action" method="GET">
                <input type="hidden" name="city-id" value="<?= $arResult["ID"] ?>">
                <input type="hidden" name="type" value="<?= $arResult["TYPE"] ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nameForm">Название</label>
                        <input type="text" name="name" class="form-control form-control-lg"
                               value="<?= $arCity["NAME"] ?>" data-type='required' id="nameForm" required>
                        <div class="invalid-feedback">
                            Поле обязательно для заполнения
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal">Отмена</button>
                    <button type="button" name="send" class="btn btn-driver btn-lg btn-driver-save-status">Изменить
                    </button>
                </div>
            </form>
        <? }
    }
} ?>