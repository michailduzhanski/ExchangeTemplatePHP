<?php

namespace common\modules\drole\models\gate;

use common\modules\drole\models\object\LogObjectHandler;
use common\modules\drole\models\object\ObjectTablesWizardPostgres;
use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\UUIDGenerator;
use Yii;
use yii\data\SqlDataProvider;

class RegistryAPIHandler
{

    public static function getDataObjectRegistryElement($objectID)
    {
        $sql = "select registry_objects.*, (select description from registry_description where registry_description.record_id = registry_objects.id), (select date_change from registry_log where table_name = registry_objects::character varying and record_id = registry_objects.id order by date_change desc limit 1) as ltime from registry_objects where id = '$objectID'";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $objectsArray = $providerAllObjects->getModels();
        if (!$objectsArray) {
            return false;
        }
        return $objectsArray[0];
    }

    public static function updateObjectNameDescription($objectID, $droleID, $contactID, $newObjectName, $newDescription)
    {
        if (!$objectID) {
            die(json_encode(APIHandler::getErrorArray(404, "not found uuid of the object.")));
        }
        if (!ctype_alnum($newObjectName)) {
            die(json_encode(APIHandler::getErrorArray(404, "Only numbers and letters in the name of the object are allowed.")));
        }
        if (!APIHandler::checkPermissionQuery($droleID, $contactID)) {
            die(json_encode(APIHandler::getErrorArray(404, "Permission denied.")));
        }
        self::updateNameObject($objectID, $newObjectName, $droleID, $contactID);
        self::updateDescriptionValues('registry_objects', $objectID, $newDescription, $droleID, $contactID);
        StructureOperationHandler::updateNamesInFilters($objectID, $newObjectName, 1);
    }

    private static function updateNameObject($objectID, $objectName, $droleID, $contactID)
    {
        $sql = "select * from registry_objects where id = '$objectID'";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $objectsArray = $providerAllObjects->getModels();
        $sql = "select * from registry_objects where name = '$objectName'";
        $providerCompaire = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $compareArray = $providerCompaire->getModels();
        if (!$compareArray) {
            $oldName = '';
            if (!$objectsArray) {
                //insert
                $creatorObject = new ObjectTablesWizardPostgres($objectName);
                $creatorObject->createObjectTables($objectID);
                $creatorObject->createAdminAssemblyForObject($droleID, $objectID, $objectName, $contactID);
            } else {
                //update
                $sql = "update registry_objects set name = '" . $objectName . "' where id = '$objectID'";
                Yii::$app->db->createCommand($sql)->execute();
                $oldName = $objectsArray[0]['name'];
                self::updateNameForTablesOfObject($objectID, $oldName, $objectName, $droleID, $contactID);
            }
            LogObjectHandler::updateLogRecordForRegistry('registry_objects', $objectID, 'name', $oldName, $objectName, $droleID, $contactID, $ipAddress = '0.0.0.0');
        } else {
            return "name is presented yet.";
        }
    }

    private static function updateNameForTablesOfObject($objectID, $oldName, $newName, $droleID, $contactID)
    {
        $arrayTokens = ObjectTablesWizardPostgres::getTokensOfTables();
        foreach ($arrayTokens as $token) {
            $sql = "ALTER TABLE " . $oldName . $token . " RENAME TO " . $newName . $token . ";";
            Yii::$app->db->createCommand($sql)->execute();
        }
    }

    private static function updateDescriptionValues($objectTable, $objectID, $newDescription, $droleID, $contactID)
    {
        $oldDescription = self::getDescriptionCurrentValue($objectTable, $objectID);
        $sql = "delete from registry_description where table_name = '" . $objectTable . "' and record_id = '" . $objectID . "'";
        \Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into registry_description values ('" . UUIDGenerator::v4() . "', '" . $objectTable . "', '" . $objectID . "', '" . $newDescription . "')";
        \Yii::$app->db->createCommand($sql)->execute();
        LogObjectHandler::updateLogRecordForRegistry($objectTable, $objectID, 'description', $oldDescription, $newDescription, $droleID, $contactID);
    }

    private static function getDescriptionCurrentValue($tableName, $recordID)
    {
        $sql = "select description from registry_description where id = '$recordID' and table_name = '$tableName'";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $objectsArray = $provider->getModels();
        if (!$objectsArray) {
            return 'null';
        }
        return $objectsArray[0]['description'];
    }

    /* public function getWorkBlockForObjectRegistry($data){
      $ctime = microtime(true);
      if(isset($jsonIncomBody->work->ctime)){
      $ctime = $jsonIncomBody->work->ctime;
      }
      $resultRecordTime = microtime(true);
      foreach($data as $record){
      if($record['ltime'] != 'null' && $record['ltime'] != '' && $record['ltime'] < $resultRecordTime){
      $resultRecordTime = $record['ltime'];
      }
      }
      $resultArray['ctime'] = $ctime;
      $resultArray['stime'] = $resultRecordTime;
      $resultArray['dtime'] = $resultRecordTime;
      return $resultArray;
      }

     */

    public static function getAssemblyRegistryElement($assemblyID)
    {
        $sql = "select registry_assembly.*, (select description from registry_description where registry_description.record_id = registry_assembly.id), (select date_change from registry_log where table_name = registry_assembly::character varying and record_id = registry_assembly.id order by date_change desc limit 1) as ltime from registry_assembly where id = '$assemblyID'";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $objectsArray = $providerAllObjects->getModels();
        if (!$objectsArray) {
            return false;
        }
        return $objectsArray[0];
    }

    //return array of record

    public static function updateAssemblyNameDescription($objectID, $droleID, $contactID, $assemblyID, $newAssemblyName, $newDescription, $newType)
    {
        if (!$objectID || !$assemblyID) {
            return (APIHandler::getErrorArray(404, "not found uuid of the object."));
        }
        if (!ctype_alnum($newAssemblyName)) {
            return (APIHandler::getErrorArray(404, "Only numbers and letters in the name of the object are allowed."));
        }
        if (!APIHandler::checkPermissionQuery($droleID, $contactID)) {
            return (APIHandler::getErrorArray(404, "Permission denied."));
        }
        $sql = "select * from registry_drole_assembly where drole_id = '$droleID' and active = '1'";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $objectsArray = $providerAllObjects->getModels();
        if (!$objectsArray || $objectsArray[0]['assembly_id'] == $assemblyID) {
            $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
            if ($dynamicRoleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
                return (APIHandler::getErrorArray(404, "Access rights to the assembly are limited."));
            }
        }
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        self::updateDescriptionValues('registry_assembly', $assemblyID, $newDescription, $droleID, $contactID);
        $result = self::updateNameAssembly($objectID, $objectName, $assemblyID, $newAssemblyName, $droleID, $contactID, $newType);
        return $result;
    }

    private static function updateNameAssembly($objectID, $objectName, $assemblyID, $assemblyName, $droleID, $contactID, $typeAssembly = true)
    {
        $sql = "select * from registry_assembly where id = '$assemblyID'";
        $assembliesArray = \Yii::$app->db->createCommand($sql)->queryOne();

        $sql = "select * from registry_assembly where name = '$assemblyName'";
        $providerCompaire = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $compareArray = $providerCompaire->getModels();
        if (!$compareArray) {
            $typeIndex = 1;
            if ($typeAssembly == 'false' || $typeAssembly == false) {
                $typeIndex = 0;
            }
            $oldName = '';
            if (!$assembliesArray || count($assembliesArray) < 1) {
                //insert
                $sql = "insert into registry_assembly values ('" . $assemblyID . "', '$objectID', '$assemblyName', '" . $typeIndex . "')";
                \Yii::$app->db->createCommand($sql)->execute();
                $sql = "insert into " . $objectName . "_assembly_fields_use values ('" . $assemblyID . "', (SELECT id FROM " .
                    $objectName . "_structure_fields WHERE name = 'id'), 0, true, false, false, false, false)";
                \Yii::$app->db->createCommand($sql)->execute();
                $sql = "insert into " . $objectName . "_assembly_fields_use values ('" . $assemblyID . "', (SELECT id FROM " .
                    $objectName . "_structure_fields WHERE name = 'date_create'), 1, true, false, false, false, false)";
                \Yii::$app->db->createCommand($sql)->execute();
                $sql = "insert into " . $objectName . "_assembly_fields_use values ('" . $assemblyID . "', (SELECT id FROM " .
                    $objectName . "_structure_fields WHERE name = 'date_change'), 1, true, false, false, false, false)";
                \Yii::$app->db->createCommand($sql)->execute();
                $sql = "insert into registry_drole_assembly values ('" . UUIDGenerator::v4() . "', '$droleID', '$assemblyID', 
                '$objectID', 0)";
                \Yii::$app->db->createCommand($sql)->execute();
            } else {
                //update
                $sql = "update registry_assembly set name = '" . $assemblyName . "', type = '" . $typeIndex . "' where id = '$assemblyID'";
                \Yii::$app->db->createCommand($sql)->execute();
                $oldName = $assembliesArray['name'];
            }
            $droleIDInternal = DynamicRoleModel::getDroleForAssembly($objectID, $assemblyID);
            if ($typeAssembly != 'false' && $typeAssembly != false && $typeIndex != 0) {
                //delete all from fast structure and fast data use
                //echo "[delete. type is: " . $assembliesArray[0]['type'] . "]";
                $sql = "delete from " . $objectName . "_structure_use_fast where assembly_id = '$assemblyID'";
                \Yii::$app->db->createCommand($sql)->execute();
                $sql = "delete from " . $objectName . "_data_use_fast where assembly_id = '$assemblyID'";
                \Yii::$app->db->createCommand($sql)->execute();
            } else if (($typeAssembly == 'false' || $typeAssembly == false) && $assembliesArray['type'] === 1) {
                //echo "[add. type is: " . $assembliesArray[0]['type'] . "]";
                //add to structure and fast data use
                $structureForAssemblyDrole = StructureOperationHandler::getFastStructureForAssemblyWithCheck($objectID,
                    $droleID, $assemblyID, $objectName);
                if ($droleIDInternal && isset($droleIDInternal[0]['drole_id']) && $structureForAssemblyDrole) {
                    ObjectOperationsHandler::setNewDataUseFastRecordsForAssembly($objectID, $objectName, $assemblyID,
                        $droleID, $structureForAssemblyDrole);
                }
            }

            /*if ($droleIDInternal && isset($droleIDInternal[0]['drole_id']) && StructureOperationHandler::getFastStructureForAssemblyWithCheck(
                    $objectID, $assemblyID, $objectName)) {
                ObjectOperationsHandler::updateAllRecordsRecursivelyAfterUpdateAssembly($objectID, $droleIDInternal[0]['drole_id'], $objectName, true);
            }*/
            LogObjectHandler::updateLogRecordForRegistry('registry_objects', $objectID, 'name',
                $oldName, $assemblyName, $droleID, $contactID, $ipAddress = '0.0.0.0');
            return $assemblyID;
        } else {
            return APIHandler::getErrorArray(403, "Current name is present yet.");
        }
    }

    public static function setRoleToAssembly($objectID, $droleID, $contactID, $assemblyID, $roleID, $companyID, $serviceID, $reverse = "false")
    {
        if (!$objectID || !$assemblyID) {
            return (APIHandler::getErrorArray(404, "not found uuid of the object."));
        }
        if (!APIHandler::checkPermissionQuery($droleID, $contactID)) {
            return (APIHandler::getErrorArray(404, "Permission denied."));
        }
        $sql = "select * from registry_drole_base where company_id = '$companyID' and service_id = '$serviceID' and role_id = '$roleID'";
        $providerDroles = new SqlDataProvider([
            'sql' => $sql
        ]);
        $drolesArray = $providerDroles->getModels();
        if (!$providerDroles || !$providerDroles->getModels()) {
            return APIHandler::getErrorArray(404, "Not found drole for params.");
        }
        $sql = "select * from registry_drole_assembly where drole_id = '" . $drolesArray[0]['id'] . "' and object_id = '$objectID'";
        $providerAssemblyies = new SqlDataProvider([
            'sql' => $sql
        ]);
        $assembliesArray = $providerAssemblyies->getModels();
        if ($reverse == "false") {
            foreach ($assembliesArray as $assemblyUsed) {
                if ($assemblyUsed['assembly_id'] == $assemblyID) {
                    return (APIHandler::getErrorArray(404, "This assembly is present yet."));
                }
            }
            $sql = "insert into registry_drole_assembly values ('" . UUIDGenerator::v4() . "', '" . $drolesArray[0]['id'] . "', '$assemblyID', '$objectID')";
            \Yii::$app->db->createCommand($sql)->execute();
        } else {
            $sql = "delete from registry_drole_assembly where assembly_id = '$assemblyID' and drole_id = '" . $drolesArray[0]['id'] . "' and object_id = '$objectID'";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        return APIHandler::getErrorArray(200, "success");
    }

    public static function setRoleAsMain($objectID, $droleID, $contactID, $assemblyID, $roleID, $companyID, $serviceID, $reverse = "false")
    {
        if (!$objectID || !$assemblyID) {
            return (APIHandler::getErrorArray(404, "not found uuid of the object."));
        }
        if (!APIHandler::checkPermissionQuery($droleID, $contactID)) {
            return (APIHandler::getErrorArray(404, "Permission denied."));
        }
        $sql = "select * from registry_drole_base where company_id = '$companyID' and service_id = '$serviceID' and role_id = '$roleID'";
        $providerDroles = new SqlDataProvider([
            'sql' => $sql
        ]);
        $drolesArray = $providerDroles->getModels();
        if (!$providerDroles || !$providerDroles->getModels()) {
            return APIHandler::getErrorArray(404, "Not found drole for params.");
        }
        $sql = "select * from registry_drole_assembly where drole_id = '" . $drolesArray[0]['id'] . "' and object_id = '$objectID'";
        $providerAssemblyies = new SqlDataProvider([
            'sql' => $sql
        ]);
        $assembliesArray = $providerAssemblyies->getModels();
        $mainAssembly = null;
        foreach ($assembliesArray as $assemblyUsed) {
            if ($reverse == "false" && $assemblyUsed['assembly_id'] == $assemblyID && $assemblyUsed['active'] == 1) {
                return (APIHandler::getErrorArray(404, "This assembly is main yet."));
            }
            if ($assemblyUsed['active'] == 1) {
                $mainAssembly = $assemblyUsed['assembly_id'];
                break;
            }
        }
        $sql = "update registry_drole_assembly values set active = '0' where drole_id = '" . $drolesArray[0]['id'] . "' and object_id = '$objectID'";
        \Yii::$app->db->createCommand($sql)->execute();
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        if ($reverse == "false") {
            $sql = "update registry_drole_assembly values set active = '1' where assembly_id = '$assemblyID' and drole_id = '" . $drolesArray[0]['id'] . "' and object_id = '$objectID'";
            \Yii::$app->db->createCommand($sql)->execute();
            $structureForDroleAssembly = StructureOperationHandler::getFastStructureForAssemblyWithCheck($objectID, $drolesArray[0]['id'], $assemblyID, $objectName);
            if ($structureForDroleAssembly) {
                echo "[" . $structureForDroleAssembly . "]";
                $structureForDroleAssembly = json_decode($structureForDroleAssembly, true);
                ObjectOperationsHandler::setNewDataUseFastRecordsForAssembly($objectID, $objectName, $assemblyID, $drolesArray[0]['id'], $structureForDroleAssembly);
            }
        }

        if ($mainAssembly && trim($mainAssembly) != '') {
            $sql = "delete from " . $objectName . "_data_use_implemented where assembly_id = '$mainAssembly' and drole_id = '$droleID'";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        return APIHandler::getErrorArray(200, "success");
    }

    public static function parseQuery($jsonIncomingObject)
    {
        $resultArray = APIHandler::getErrorArray();
        if (!isset($jsonIncomingObject['permission'])) {
            return $resultArray;
        }
        $permissionBlock = $jsonIncomingObject['permission'];
        $workBlock = $jsonIncomingObject['work'];
        $filtersBlock = $jsonIncomingObject['filters'];
        //permission
        $objectID = $permissionBlock['object_id'];
        $droleID = $permissionBlock['drole_id'];
        //work
        $viewType = $workBlock['set'];
        $operationType = $workBlock['operation'];
        $value = $workBlock['value'];
        //$viewType = 0;
        //$objectID = 'registry_objects';
        if (!DynamicRoleModel::isAccessToRegistryByDrole($droleID)) {
            return false;
        }

        if ($viewType == 0 && $operationType == 0 && $objectID == 'registry') {
            if (isset($value['table'])) {
                if ($value['table'] == 'objects') {
                    $resultArray['result'] = 200;
                    $resultArray['message'] = ["success"];
                    $resultArray['data'] = self::getBodyForSetRegistryObjectsList($jsonIncomingObject);
                    return $resultArray;
                } else if ($value['table'] == 'assembly') {
                    if (isset($value['object']) && UUIDGenerator::isUUID($value['object'])) {
                        //get assemblyes for object
                        $resultArray['result'] = 200;
                        $resultArray['message'] = ["success"];
                        $resultArray['data'] = self::getBodyForSetRegistryObjectAssemblyList($jsonIncomingObject);
                        return $resultArray;
                    }
                } else if ($value['table'] == 'assemblydrole') {
                    if (isset($value['object']) && UUIDGenerator::isUUID($value['object'])) {
                        //get assemblyes for object
                        $resultArray['result'] = 200;
                        $resultArray['message'] = ["success"];
                        $resultArray['data'] = self::getBodyForSetRegistryObjectID($jsonIncomingObject);
                        return $resultArray;
                    }
                } else if ($value['table'] == 'companies') {
                    if (isset($value['object']) && UUIDGenerator::isUUID($value['object'])) {
                        //get assemblyes for object
                        $resultArray['result'] = 200;
                        $resultArray['message'] = ["success"];
                        $resultArray['data'] = self::getBodyForSetObjectListValues($jsonIncomingObject);
                        return $resultArray;
                    }
                } else if ($value['table'] == 'frecords') {
                    if (isset($value['object']) && UUIDGenerator::isUUID($value['object'])) {
                        //get assemblyes for object
                        $resultArray['result'] = 200;
                        $resultArray['message'] = ["success"];
                        $resultArray['data'] = FiltersObjectHandler::getBodyForFilterRecords(json_decode(json_encode($jsonIncomingObject), true));
                        return $resultArray;
                    }
                } else if ($value['table'] == 'fgroups') {
                    if (isset($value['object']) && UUIDGenerator::isUUID($value['object'])) {
                        //get assemblyes for object
                        $resultArray['result'] = 200;
                        $resultArray['message'] = ["success"];
                        $resultArray['data'] = FiltersObjectHandler::getBodyForFilterGroups(json_decode(json_encode($jsonIncomingObject), true));
                        return $resultArray;
                    }
                } else if ($value['table'] == 'access') {
                    if (isset($value['object']) && UUIDGenerator::isUUID($value['object'])) {
                        //get assemblyes for object
                        $resultArray['result'] = 200;
                        $resultArray['message'] = ["success"];
                        $resultArray['data'] = AccessRulesHandler::getBodyForAccessRules(json_decode(json_encode($jsonIncomingObject), true), "coin");
                        return $resultArray;
                    }
                }
            } else if (isset($value['object']) && UUIDGenerator::isUUID($value['object'])) {
                $resultArray['result'] = 200;
                $resultArray['message'] = ["success"];
                $resultArray['data'] = self::getBodyForSetRegistryObjectID($jsonIncomingObject);
                return $resultArray;
            }
            //return $objectsArray;
        } else if ($viewType == 0 && $operationType == 2 && UUIDGenerator::isUUID($objectID)) {
            //get structure of object, assembly etc.
        }
        return $resultArray;
        //return '{"result":"404", "message":[{"error":"data not found"}]}':
    }

    //Data Object registry updates

    private static function getBodyForSetRegistryObjectsList($jsonIncomBody)
    {
        $sql = "select registry_objects.*, (select description from registry_description where registry_description.record_id = registry_objects.id), (select date_change from registry_log where table_name = registry_objects::character varying and record_id = registry_objects.id order by date_change desc limit 1) as ltime from registry_objects";
        $objectsArray = \Yii::$app->db->createCommand($sql)->queryAll();
        //$objectsArray = $providerAllObjects->getModels();
        $resultArray['structure'] = self::getStructureForSetRegistry($jsonIncomBody, $objectsArray);
        for ($i = 0; $i < count($objectsArray);) {
            $accessRulesArray = AccessRulesHandler::getAccessRulesForObjectByIncomingArray($objectsArray[$i]['id'], $jsonIncomBody['permission']['drole_id'], $jsonIncomBody['permission']['contact_id'], $objectsArray[$i]['name']);
            if (!AccessRulesHandler::checkAccesToDataObject($accessRulesArray)) {
                array_splice($objectsArray, $i, 1);
            } else {
                $i++;
            }
        }
        $resultArray['data'] = self::getValueByFilter($jsonIncomBody, self::getDataForSetRegistry($jsonIncomBody, $objectsArray));
        $resultArray['work'] = self::getWorkBlockForObjectRegistry($resultArray['data']);
        return $resultArray;
    }

    /* private function updateLogRecordForRegistry($objectTable, $recordID, $fieldToUpdateID, $oldValue, $newValue, $droleID, $contactID, $ipAddress = '0.0.0.0'){
      $insertQuery = 'INSERT INTO registry_log (id, table_name, record_id, field, value_old, value_new, date_change, drole_id, operator_id, ip_address) VALUES (\'' . UUIDGenerator::v4() . '\', \'' . $objectTable . '\', \'' . $recordID . '\', \'' . $fieldToUpdateID . '\', \'' . $oldValue . '\', \'' . $newValue . '\', \'' . microtime(true) . '\', \'' . $droleID . '\', \'' . $contactID . '\', \'' . $ipAddress . '\')';
      //echo '[' . $insertQuery . ']';
      \Yii::$app->db->createCommand($insertQuery)->execute();
      } */

    private static function getStructureForSetRegistry($jsonIncomBody, $resultProvider)
    {
        if (!$resultProvider) {
            return false;
        }
        $structure = array();
        $index = 0;
        foreach ($resultProvider[0] as $key => $field) {
            //array_push($structure, $key);
            $structure[$index] = $key;
            $index++;
        }
        return $structure;
    }

    private static function getValueByFilter($jsonIncomBody, $data)
    {
        $commonToken = self::getCommonFilterFromWorkParams($jsonIncomBody);
        //$commonToken = 'sta';
        if ($commonToken != '' && strlen(trim($commonToken)) > 1) {
            $resultArray = array();
            foreach ($data as $record) {
                foreach ($record as $value) {
                    if (strpos($value, $commonToken) === false) {
                        //do nothing
                    } else {
                        array_push($resultArray, $record);
                        break;
                    }
                }
            }
        } else {
            return $data;
        }
        return $resultArray;
    }

    private static function getCommonFilterFromWorkParams($jsonIncomBody)
    {
        $commonToken = '';
        if (isset($jsonIncomBody['filters'])) {
            foreach ($jsonIncomBody['filters'] as $filter) {
                if (isset($filter['common'])) {
                    $commonToken = $filter['common'];
                    break;
                }
            }
        }
        return $commonToken;
    }

    private static function getDataForSetRegistry($jsonIncomBody, $resultProvider)
    {
        return $resultProvider;
    }

    public static function getWorkBlockForObjectRegistry($data, $structure = false)
    {
        $cdtime = microtime(true);
        $cstime = $cdtime;
        if (isset($jsonIncomBody['work']['ctime'])) {
            $ctime = $data['work']['ctime'];
        }
        //$resultRecordTime = microtime(true);
        if ($data) {
            foreach ($data as $record) {
                if (isset($record['ltime']) && $record['ltime'] != 'null' && $record['ltime'] != '' && $record['ltime'] < $cdtime) {
                    $cdtime = $record['ltime'];
                }
            }
        }
        //
        if ($structure) {
            foreach ($structure as $record) {
                if ($record['ltime'] != 'null' && $record['ltime'] != '' && $record['ltime'] < $cstime) {
                    $cstime = $record['ltime'];
                }
            }
        } else {
            $cstime = $cdtime;
        }
        $resultArray['ctime'] = $cdtime;
        $resultArray['stime'] = $cstime;
        $resultArray['dtime'] = $cdtime;
        return $resultArray;
    }

    private static function getBodyForSetRegistryObjectAssemblyList($jsonIncomBody)
    {
        $sql = "SELECT DISTINCT on (registry_assembly.id) registry_assembly.*, (SELECT description from registry_description where registry_description.table_name = 
'registry_assembly' and record_id = registry_assembly.id), (SELECT date_change from registry_log where registry_log.table_name = 
'registry_assembly' and record_id = registry_assembly.id order by date_change desc limit 1) as ltime, registry_drole_assembly.drole_id, 
registry_drole_assembly.active, (select company_id from registry_drole_base where registry_drole_base.id = registry_drole_assembly.drole_id), 
(select name from company_data_use where company_data_use.id = (select company_id from registry_drole_base where 
registry_drole_base.id = registry_drole_assembly.drole_id)) as company_name, (select array_agg(name) from role_data_use where role_data_use.id in 
(select role_id from registry_drole_base where registry_drole_base.id in (SELECT registry_drole_assembly.drole_id FROM registry_drole_assembly 
WHERE registry_drole_assembly.assembly_id = registry_assembly.id))) as role_name FROM registry_assembly inner join registry_drole_assembly on 
registry_assembly.id = registry_drole_assembly.assembly_id";
        $suffixSQL = self::getWhereSuffixDataForSetRegistryByDrole($jsonIncomBody['permission']['drole_id']);
        if ($suffixSQL == -1) {
            return APIHandler::getErrorArray(403, "Not found assemblies for current dynamic role.");
        }
        if (isset($jsonIncomBody['work']['value']['object'])) {
            $sql .= " where registry_assembly.object_id = '" . $jsonIncomBody['work']['value']['object'] . "'";
            if ($suffixSQL != '') {
                $sql .= " and ((registry_drole_assembly.drole_id in " . $suffixSQL .
                    " and registry_drole_assembly.drole_id != '" . $jsonIncomBody['permission']['drole_id'] .
                    "') or (registry_drole_assembly.drole_id = '" . $jsonIncomBody['permission']['drole_id'] .
                    "' and registry_drole_assembly.active = '0'))";
            }
        } else {
            return APIHandler::getErrorArray(403, "Not found object id in query.");
        }
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $objectsArray = $providerAllObjects->getModels();
        $resultArray['structure'] = self::getStructureForSetRegistry($jsonIncomBody, $objectsArray);
        $resultArray['data'] = self::getValueByFilter($jsonIncomBody, self::getDataForSetRegistry($jsonIncomBody, $objectsArray));
        $resultArray['work'] = self::getWorkBlockForObjectRegistry($resultArray['data']);
        return $resultArray;
    }

    /*private static function getBodyForSetRegistryObjectAssemblyListForAnotherDrole($jsonIncomBody)
    {
        $sql = "SELECT registry_assembly.*, (SELECT description from registry_description where registry_description.table_name = 
'registry_assembly' and record_id = registry_assembly.id), (SELECT date_change from registry_log where registry_log.table_name = 
'registry_assembly' and record_id = registry_assembly.id order by date_change desc limit 1) as ltime FROM registry_assembly";
        $suffixSQL = self::getWhereSuffixDataForSetRegistryByDrole($jsonIncomBody['permission']['drole_id']);
        if ($suffixSQL == -1) {
            return APIHandler::getErrorArray(403, "Not found assemblies for current dynamic role.");
        }
        if (isset($jsonIncomBody['work']['value']['object'])) {
            $sql .= " where object_id = '" . $jsonIncomBody['work']['value']['object'] . "'";
            if ($suffixSQL != '') {
                $sql .= " and id in " . $suffixSQL;
            }
        } else {
            return APIHandler::getErrorArray(403, "Not found object id in query.");
        }
        echo $sql; exit;
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $objectsArray = $providerAllObjects->getModels();
        $resultArray['structure'] = self::getStructureForSetRegistry($jsonIncomBody, $objectsArray);
        $resultArray['data'] = self::getValueByFilter($jsonIncomBody, self::getDataForSetRegistry($jsonIncomBody, $objectsArray));
        $resultArray['work'] = self::getWorkBlockForObjectRegistry($resultArray['data']);
        return $resultArray;
    }*/

    private static function getWhereSuffixDataForSetRegistryByDrole($droleID)
    {
        $arrayOfDrole = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        if (RegistryDescriptionRolesModel::$rolesArray['superadmin'] == $arrayOfDrole['role_id'] ||
            RegistryDescriptionRolesModel::$rolesArray['superuserglobal'] == $arrayOfDrole['role_id']) {
            return "";
        }
        if (RegistryDescriptionRolesModel::$rolesArray['admin'] == $arrayOfDrole['role_id'] ||
            RegistryDescriptionRolesModel::$rolesArray['superuserlocal'] == $arrayOfDrole['role_id']) {
            //return assemblies only for company assemblies
            return "(SELECT id FROM registry_drole_base WHERE company_id = (SELECT company_id FROM registry_drole_base 
WHERE registry_drole_base.id = '" . $droleID . "' limit 1) and registry_drole_base.role_id != '" . RegistryDescriptionRolesModel::$rolesArray['superadmin'] . "' and 
registry_drole_base.role_id != '" . RegistryDescriptionRolesModel::$rolesArray['superuserglobal'] . "')";
            /*return "SELECT assembly_id FROM registry_drole_assembly WHERE drole_id in (SELECT id FROM registry_drole_base WHERE
company_id = (SELECT company_id FROM registry_drole_base WHERE id = '" . $droleID . "' and active = 1 limit 1))";*/
        }
        return -1;
    }

    private static function getBodyForSetRegistryObjectID($jsonIncomBody)
    {
        //id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text
        $resultArray['structure'] = ['id', 'field', 'turn', 'usef', 'visible', 'edit', 'delete', 'name', 'class', 'type', 'description', 'ltime'];
        $resultArray['data'] = self::getStructureOfObject($jsonIncomBody);
        $resultArray['work'] = self::getWorkBlockForObjectRegistry($resultArray['data']);
        return $resultArray;
    }

    //for companies

    private static function getStructureOfObject($jsonIncomBody)
    {
        $droleID = $jsonIncomBody['permission']['drole_id'];
        $objectID = $jsonIncomBody['work']['value']['object'];
        $token = self::getCommonFilterFromWorkParams($jsonIncomBody);
        $sql = "select * from getstructureforfilter('$objectID', '$droleID') as ds(id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text, description text, ltime double precision)";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        $objectsArray = $providerAllObjects->getModels();
        if (!$objectsArray) {
            return false;
        }
        if (isset($jsonIncomBody['work']['value']['work_id']) && $jsonIncomBody['work']['value']['work_id'] != NULL && $jsonIncomBody['work']['value']['work_id'] != '') {
            $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
            $sql = "SELECT " . $objectName . "_assembly_fields_use.*, " . $objectName . "_structure_fields.name, " . $objectName . "_structure_fields.class, 
(case when (SELECT lower(registry_classes.name) from registry_classes where registry_classes.id = (SELECT " . $objectName . "_structure_fields.class FROM " . $objectName . "_structure_fields where " . $objectName .
                "_structure_fields.id = " . $objectName . "_assembly_fields_use.field)) is NULL then (SELECT lower(registry_objects.name) from registry_objects where registry_objects.id = (SELECT " .
                $objectName . "_structure_fields.class FROM " . $objectName . "_structure_fields where " . $objectName . "_structure_fields.id = " . $objectName . "_assembly_fields_use.field)) else (SELECT lower(registry_classes.name) 
                from registry_classes where registry_classes.id = (SELECT " . $objectName . "_structure_fields.class FROM " . $objectName . "_structure_fields where " . $objectName . "_structure_fields.id = " . $objectName .
                "_assembly_fields_use.field)) end) as type, (select description from " . $objectName . "_description where table_name = 'structure_fields' and record_id = " . $objectName . "_structure_fields.id) as description  
FROM " . $objectName . "_assembly_fields_use inner join " . $objectName . "_structure_fields on " . $objectName . "_assembly_fields_use.field = " . $objectName .
                "_structure_fields.id where " . $objectName . "_assembly_fields_use.id = '" . $jsonIncomBody['work']['value']['work_id'] . "' order by " . $objectName . "_assembly_fields_use.turn";
            $provider = new SqlDataProvider([
                'sql' => $sql
            ]);
            $allAssemblyFields = $provider->getModels();
            $objectsArray = self::updateResultArray($allAssemblyFields, $objectsArray);
        } else {
            //return APIHandler::getErrorArray(404, "Assembly id is empty.");
        }
        //
        //echo print_r($objectsArray); exit;
        //echo $objectsArray[0]['usef']; exit;
        if ($objectsArray[0]['usef'] != 1) {
            return false;
        }
        if ($token != '' && strlen(trim($token)) > 1) {
            $resultArray = array();
            foreach ($objectsArray as $record) {
                if (!(strpos($record['name'], $token) === false && strpos($record['type'], $token) === false)) {
                    array_push($resultArray, $record);
                }
            }
            return $resultArray;
        }
        return $objectsArray;
    }

    private static function updateResultArray($resultArray, $mustArray)
    {
        //echo "result array: " . print_r($resultArray, true);
        //echo "must array: " . print_r($mustArray, true);
        foreach ($mustArray as $mustRecord) {
            $isPresent = false;
            foreach ($resultArray as $fieldRecord) {
                if ($fieldRecord['field'] == $mustRecord['field']) {
                    $isPresent = true;
                    break;
                }
            }
            if (!$isPresent) {
                array_push($resultArray, self::getEmptyField($mustRecord, count($resultArray)));
            }
        }
        return $resultArray;
    }

    private static function getEmptyField($fieldRecord, $index)
    {
        $fieldRecord['turn'] = $index;
        $fieldRecord['usef'] = false;
        $fieldRecord['visible'] = false;
        $fieldRecord['edit'] = false;
        $fieldRecord['delete'] = false;
        $fieldRecord['insert'] = false;
        return $fieldRecord;
    }

    private static function getBodyForSetObjectListValues($jsonIncomBody)
    {
        //id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text
        $resultArray['structure'] = self::getAdminListObjectFastStructure($jsonIncomBody);
        $resultArray['data'] = self::getAdminListObjectFastData($jsonIncomBody);
        $resultArray['work'] = self::getWorkBlockForObjectRecords($resultArray['data'], $resultArray['structure']);
        return $resultArray;
    }

    private static function getAdminListObjectFastStructure($jsonIncomBody)
    {
        $droleID = $jsonIncomBody['permission']['drole_id'];
        $objectID = RegistryDescriptionRolesModel::getObjectForRegistry($jsonIncomBody['work']['value']['table']);
        $token = self::getCommonFilterFromWorkParams($jsonIncomBody);
        $sql = "select * from getfaststructurewithltime('$objectID', '$droleID') as ds(json_structure jsonb, ltime double precision)";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        $objectsArray = $providerAllObjects->getModels();
        if (!$objectsArray) {
            return false;
        }
        //
        //echo $objectsArray[0]['usef']; exit;
        return $objectsArray[0];
    }

    private static function getAdminListObjectFastData($jsonIncomBody)
    {
        $droleID = $jsonIncomBody['permission']['drole_id'];
        $objectID = RegistryDescriptionRolesModel::getObjectForRegistry($jsonIncomBody['work']['value']['table']);
        $token = self::getCommonFilterFromWorkParams($jsonIncomBody);
        $whereFilter = '';
        if (strlen($token) > 0) {
            $whereFilter = 'and json_field::text like \'\'%' . $token . '%\'\'';
        }
        $sql = "select * from getallfastdatause('$objectID', '$droleID', '" . $whereFilter . "') as ds(json_field jsonb, ltime double precision)";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        $objectsArray = $providerAllObjects->getModels();
        if (!$objectsArray) {
            return false;
        }
        //
        //echo print_r($objectsArray); exit;
        //echo $objectsArray[0]['usef']; exit;
        return $objectsArray;
    }

    public static function getWorkBlockForObjectRecords($data, $structure = false)
    {
        $cdtime = microtime(true);
        $cstime = $cdtime;
        if (isset($data['work']['ctime'])) {
            $ctime = $data['work']['ctime'];
        }
        //$resultRecordTime = microtime(true);
        /* if ($data) {
          foreach ($data as $record) {
          if ($record['ltime'] != 'null' && $record['ltime'] != '' && $record['ltime'] < $cdtime) {
          $cdtime = $record['ltime'];
          }
          }
          } */
        //
        if ($structure) {
            if ($structure['ltime'] != 'null' && $structure['ltime'] != '' && $structure['ltime'] < $cstime) {
                $cstime = $structure['ltime'];
            }
        } else {
            $cstime = $cdtime;
        }
        $resultArray['ctime'] = $cdtime;
        $resultArray['stime'] = $cstime;
        $resultArray['dtime'] = $cdtime;
        return $resultArray;
    }

    private static function updateDescriptionJson($jsonIncomBody)
    {
        $recordID = $jsonIncomBody['permission']['record_id'];
        $objectTable = $jsonIncomBody['permission']['object_id'];
        $newValue = $jsonIncomBody['work']['value']['description'];
        $droleID = $jsonIncomBody['permission']['drole_id'];
        $contactID = $jsonIncomBody['permission']['contact_id'];
        self::updateDescriptionValues($objectTable, $recordID, $newValue, $droleID, $contactID);
    }

    private static function deleteDataObject($objectID, $droleID, $contactID)
    {
        //check permission droleID
        $checkDynamicRole = DynamicRoleModel::isAccessToRegistryByDrole($droleID);
        //check permission objectID
        $checkObjectID = RegistryDescriptionRolesModel::compaireUpdatesDataArrays($objectID);
    }

}

?>