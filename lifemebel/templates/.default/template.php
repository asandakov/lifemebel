<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?

$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'lifemebel');
$jsParams = array(
    'sign' => $signedParams,
    'ajaxUrl' => CUtil::JSEscape($component->getPath() . '/ajax/ajax.php'),
    'ajaxForm' => CUtil::JSEscape($component->getTemplate()->getFolder() . '/form'),
);

?>

<div class="test-block" id="test-block">
    <div class="nm">Города <i title="Добавить" class="fa fa-plus" aria-hidden="true"></i></div>
    <table class="info-table">
        <thead>
        <tr>
            <th width="10%">ID</th>
            <th width="80%">Название</th>
            <th width="10%">Действие</th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($arResult["ITEMS"] as $arItem) { ?>
            <tr data-id="<?= $arItem["ID"] ?>">
                <td><?= $arItem["ID"] ?></td>
                <td><?= $arItem["NAME"] ?></td>
                <td class="icon">
                    <i class="fa fa-pencil" title="Редактировать" aria-hidden="true"></i>
                    <i class="fa fa-trash" title="Удалить" aria-hidden="true"></i>
                </td>
            </tr>
        <? } ?>
        </tbody>
    </table>
    <?= $arResult["NAV_STRING"] ?>
</div>


<!-- actionModal -->
<div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>

<script>
    BX.ready(function () {
        LifeMebelComponent.init(<?=CUtil::PhpToJSObject($jsParams)?>);
    });
</script>



