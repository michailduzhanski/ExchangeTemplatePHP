<?php


namespace common\modules\imageStorage\components;

use common\modules\imageStorage\helpers\ImageStorageHelper;
use Yii;
use yii\db\Query;

class DbSaver
{
    public function save(ResponseData $response)
    {

        $objectId = $response->objectId;
        $recordId = $response->recordId;
        $field = $response->attribute;
        $image = $response->basename;

        return $this->update($objectId, $recordId, $field, $image);
    }

    public function remove($objectId, $recordId, $attribute)
    {
        return $this->update($objectId, $recordId, $attribute, null);
    }

    public function update($objectId, $recordId, $field, $image)
    {
        $serviceId = Yii::$app->params['service_id'];
        $contactId = Yii::$app->user->id;
        $drole = Yii::$app->user->identity->auth['drole'];

        $struct = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck(
            $objectId, $drole);
        $data = [
            'id' => $recordId,
            $field => $image
        ];
        $jsonIncomBody = \common\modules\drole\models\webtools\JSONRegistryFactory::updateObject(
            false,
            $objectId,
            \frontend\helpers\DroleHelper::createUpdateParams($struct, $data)
        );

        $jsonIncomBody['permission']['service_id'] = $serviceId;
        $jsonIncomBody['permission']['contact_id'] = $contactId;
        $jsonIncomBody['permission']['drole_id'] = $drole;

        $result = \common\modules\drole\models\gate\UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);


        if($result['result'] == 200)
            return true;
        else
            return false;
    }


    public function getData($table, $recordId, $field)
    {

        $row = (new Query())
            ->select($field)
            ->from($table)
            ->where(['id' => $recordId])->one();

        if(isset($row[$field])){
            return $row[$field];
        }

        return false;
    }
}