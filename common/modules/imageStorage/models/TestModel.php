<?php
namespace common\modules\imageStorage\models;

use Yii;
use common\modules\imageStorage\behaviors\ImageStorage;

/**
 * Class TestModel
 * @package common\modules\imageStorage\models
 * @method imageStorageUpload()
 * @method loadDataImage()
 * @method getImages()
 * @method removeImages();
 * @method getUploadedSrc($field, $size = false)
 * @method getUploadResponse();
 */
class TestModel extends \yii\base\Model
{
    public $file;

    public $photo;


    public function behaviors()
    {
        return [
            'imageStorage' => [
                'class' => ImageStorage::class,
                'uploadType' => 'POST',
                'uploadFields' => [
                    'photo' => 'user_photo',
                ],
                'tableFields' => [
                    'photo' => ['contact_data_use' => Yii::$app->user->id],
                ],
                'objects' => [
                    'photo' => '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24',
                ]
            ]
        ];
    }

    public function save()
    {

        $serviceId = Yii::$app->params['service_id'];
        $contactId = Yii::$app->user->id;
        $drole = Yii::$app->user->identity->auth['drole'];
        $objectId = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';

        $struct = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck(
            $objectId, $drole);
        $data = [
            'id' => $contactId,
            'photo' => $this->photo,
        ];
        $jsonIncomBody = \common\modules\drole\models\webtools\JSONRegistryFactory::updateObject(
            false,
            $objectId,
            \frontend\helpers\DroleHelper::createUpdateParams($struct, $data)
        );

        $jsonIncomBody['permission']['service_id'] = $serviceId;
        $jsonIncomBody['permission']['contact_id'] = $contactId;
        $jsonIncomBody['permission']['drole_id'] = $drole;

        $res = \common\modules\drole\models\gate\UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);
        if(isset($res['result']) && $res['result'] == 200)
            return true;

        return false;
    }

}