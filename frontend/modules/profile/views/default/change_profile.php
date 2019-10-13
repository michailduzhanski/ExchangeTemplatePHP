<?php
/**
 * @var $this \yii\web\View
 */
use \yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use \common\widgets\PrettyLabelField;
use \common\modules\drole\models\webtools\JSONRegistryFactory;
use \common\modules\imageStorage\widgets\FileInputAjax;
?>
<?php Pjax::begin([
    'id' => 'form-profile-change',
    'enablePushState' => false,
    'clientOptions' => ['showUserCard' => true]
]); ?>
<?php $form = ActiveForm::begin([
    'id' => 'edit-profile-form',
    'action' => ['/profile/default/edit-profile-form'],
    'validationUrl' => ['/profile/default/edit-profile-form-validate'],
    'enableAjaxValidation' => true,
    'options' => [
        'data-pjax' => true,
        'class' => 'form-pretty-label',
        'enctype' => 'multipart/form-data'
    ]
]) ?>

<div class="row">
    <div class="col">

        <div class="user-data">
            <?= $form->field($model, 'photo')
                ->widget(FileInputAjax::class, [
                    'owner' => 'user_photo',
                    'objectId' => '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24',
                    'recordId' => Yii::$app->user->id,
                    'dynamicModel' => false
                ])->label(
                    Yii::t('profile_page', 'Photo')
                ); ?>
        </div>
    </div>
    <div class="col">
        <div class="user-data">
            <?=$form->field($model, 'firstname', [
                'class' => PrettyLabelField::class
            ])->textInput()->label(
                Yii::t('profile_page', 'Firstname')
            ) ?>
        </div>
        <div class="user-data">
            <?=$form->field($model, 'secondname', [
                'class' => PrettyLabelField::class
            ])->textInput()->label(
                Yii::t('profile_page', 'Lastname')
            ) ?>
        </div>

        <div class="user-data">
            <?=$form->field($model, 'facebook', [
                'class' => PrettyLabelField::class
            ])->textInput() ?>
        </div>

        <div class="user-data">
            <?=$form->field($model, 'twitter', [
                'class' => PrettyLabelField::class
            ])->textInput() ?>
        </div>

        <div class="user-data">
            <?=$form->field($model, 'linkedin', [
                'class' => PrettyLabelField::class
            ])->textInput() ?>
        </div>
    </div>
</div>
<!--<div class="row">
    <div class="col">
        <button id="change-profile-back" type="button" class="btn btn-block" data-toggle="modal" data-target=".modal-change-password">
            Back
        </button>
    </div>
    <div class="col">
        <?/*=\yii\helpers\Html::submitButton('Save', [
            'id' => 'change-profile',
            'class' => 'btn btn-warning btn-block',
            'data-toggle' => 'modal',
            'data-target' => '.modal-change-password'
        ]) */?>
    </div>
</div>-->

<?php ActiveForm::end() ?>
<?php Pjax::end(); ?>

<?php
$objectId = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';
Yii::$app->params['templateMaps']['profile-card'] = [

    "a8654798-0aac-4d06-a409-eeb6fae2ed79", //email
    "c896b5a6-8640-4103-ba22-70a0bc6c06fe", //firstname
    "d280fa03-48cf-44d7-be2c-bcee54cfe89d", //lastname
    "6fb8deeb-9403-49cf-b3e1-f0c8886300b5" //contactslinks
];

$json = JSONRegistryFactory::getRecordsListFromObject(true, $objectId, '');
$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);

$js = <<<JS
var idNodes = [    
    { dataId: 'a8654798-0aac-4d06-a409-eeb6fae2ed79', nodeId: 'profile-email' },
    { dataId: 'c896b5a6-8640-4103-ba22-70a0bc6c06fe', nodeId: 'profile-firstname' },
    { dataId: 'd280fa03-48cf-44d7-be2c-bcee54cfe89d', nodeId: 'profile-secondname' },
    { dataId: '6fb8deeb-9403-49cf-b3e1-f0c8886300b5', nodeIds:
        [
            {name: 'facebook', nodeId: 'profile-facebook'},
            {name: 'twitter', nodeId: 'profile-twitter'},
            {name: 'linkedin', nodeId: 'profile-linkedin'}
        ]         
    },    
];

function getListObjectContentIds(url, request, element, idNodes){    
    $.post(url, {json: JSON.stringify(request)}).done(function (data) {
        var html = '';
        var structure = data['data']['structure']['data'];
        var structureMap = getStructureIDMapWithCheck(structure, data['data']['work']['stime'], ' ' + "_" + element);
        var neededElements = templateMaps[element];
        
        $.each(data['data']['data'], function () {
            var item = this;                                                                
            $.each(neededElements, function (key, value) {            
                var indexItem = neededElements[key];                
                var fieldValue = getSingleElementFromDataByIDMap(item, structureMap, indexItem);                     
                $.each(idNodes, function(idNodesKey, idNodesOpt){                        
                    if(idNodesOpt.nodeIds !== undefined){                        
                        var contactlinksIndex = structureMap[idNodesOpt.dataId]                        
                        $.each(idNodesOpt.nodeIds, function(arrKey, arrObj){
                            var contactLinksData = item[contactlinksIndex[0]];
                            $.each(contactLinksData, function(contactLinksDataId, contactLinksDataValue){
                                if(arrObj.name == contactLinksDataValue[3]){                                    
                                    $('#' + arrObj.nodeId).val(contactLinksDataValue[4]);
                                    $('#' + arrObj.nodeId).attr('data-field-id', contactLinksDataValue[0]);
                                }                                
                            });                                                        
                        });
                        
                    } else {
                        if(idNodesOpt.dataId == value){
                            $('#'+idNodesOpt.nodeId).val(fieldValue);
                            $('#'+idNodesOpt.nodeId).attr('data-field-id', value);
                        }
                    }                                        
                });                
            });            
        });        
    });
}

getListObjectContentIds('$apiRequestURL/drole/default/get-info', $json, 'profile-card', idNodes);

$(document).on('click', '#change-profile-back', function(){    
    $('#table-profile-card').html(backHtml);        
});

$('#form-profile-change input').on('change', function(){
    $.ajax({
        method: 'POST',
        url: '/profile/default/edit-profile-form',
        data: {
            id: $(this).data('field-id'),
            name: $(this).attr('name'),
            value: $(this).val()
        },
        success: function(data){
            
        }
    });
    
    return false;
});

JS;

$this->registerJs($js, \yii\web\View::POS_READY);

?>

