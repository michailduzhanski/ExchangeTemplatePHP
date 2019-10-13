<?php

namespace common\modules\drole\controllers;

use common\modules\drole\models\exchange\ExchangeChartHandler;
use common\modules\drole\models\gate\AccessRulesHandler;
use common\modules\drole\models\gate\APIHandler;
use common\modules\drole\models\gate\CommonGateModel;
use common\modules\drole\models\gate\DataObjectAPIHandler;
use common\modules\drole\models\gate\FiltersObjectHandler;
use common\modules\drole\models\gate\GetRemoteTransactionsHandler;
use common\modules\drole\models\gate\RecordObjectHandler;
use common\modules\drole\models\gate\StructureOperationHandler;
use common\modules\drole\models\gate\TransferFundsHandler;
use common\modules\drole\models\gate\UpdateDataObjectHandler;
use common\modules\drole\models\implemented\RecordUpdate;
use common\modules\drole\models\implemented\StructureUpdate;
use common\modules\drole\models\object\DataTableWizard;
use common\modules\drole\models\object\ObjectAssemblyUseModel;
use common\modules\drole\models\object\ObjectStructureModel;
use common\modules\drole\models\object\ObjectTablesWizardPostgres;
use common\modules\drole\models\object\SimpleObjectHandler;
use common\modules\drole\models\registry\DynamicAssemblyForObject;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\registry\DynamicRoleModelWithContacts;
use common\modules\drole\models\registry\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\RegistryObjectValues;
use common\modules\drole\models\UUIDGenerator;
use common\modules\drole\models\wactions\PerfectDeposit;
use yii\data\SqlDataProvider;
use yii\web\Controller;

class DefaultController extends Controller
{

    public static $currentWorkObjectName = "";
    public static $companyID = '2ed029b6-d745-4f85-8d9f-2dccd2a7da37';
    public static $serviceID = '3db2f640-e01a-42ac-904e-87a46e0373fd';
    public static $roleID = '97086af0-956b-4380-a385-ea823cff377a';
    public static $contactID = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';
    public static $droleID = 'drole';
    public static $payID = 'fc98affc-9164-4b57-b1a0-e9169305dfce';
    private static $companyFieldName = 'company_id';
    private static $serviceFieldName = 'service_id';
    //droles static:
    private static $roleFieldName = 'role_id';
    private static $contactFieldName = 'contact_id';
    private static $objectFieldName = 'object_id';
    private static $dynamicRoleFieldName = 'drole_id';
    private static $assemblyFieldName = 'assembly_id';
    private static $activeFieldName = 'active';

    public function actionShowContacts()
    {
        $result = AccessRulesHandler::getBodyForAccessRules(json_decode('{"permission":{"object_id":"5cb705ea-6c8c-4dae-a620-248545acab14","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"7d82bde3-7740-41d7-9610-8d1fc75db803","drole_id":"62900a19-88a9-4655-a7ac-71488070b659"},"work":{"set":1,"operation":0,"ctime":1529679526.8311,"value":[]},"filters":[{"common":""}]}', true));
        echo json_encode($result['data'][0]);
        //echo json_encode(AccessRulesHandler::getListRules('62900a19-88a9-4655-a7ac-71488070b659', "coin"));
        //AccessRulesHandler::getClassAndValue("5cb705ea-6c8c-4dae-a620-248545acab14", "964940de-430d-4a57-a7ec-ff125372ae09",
        //    '62900a19-88a9-4655-a7ac-71488070b659');
        exit;
        RecordUpdate::deleteImplementedRecordsFromOriginObjectWithEmptyParentID("fd27729c-0f30-444b-a124-e3e16069e7d0",
            "descriptioncoin", "5cb705ea-6c8c-4dae-a620-248545acab14", "coin");
        exit;
        /*$jsonIncomBody = json_decode('{"permission":{"object_id":"5cb705ea-6c8c-4dae-a620-248545acab14","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"d1474e71-f436-4cc2-94a3-8ff038100f18","drole_id":"62900a19-88a9-4655-a7ac-71488070b659"},"work":{"set":1,"operation":2,"ctime":1538390182.3974,"value":{"record":[{"field":"d2a47321-e0da-4ee5-bc76-110a4e67090c","map":"0","value":"8628879d-6df6-41ab-bd25-c2ba1783378f"},{"field":"d68f2806-79b7-47f9-b805-5b5fd459aeb8","map":"3","value":"ECO' . rand(10, 100) . '"}]}},"filters":[{"common":""}]}', true);
        //echo json_encode(UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody));
        echo json_encode(UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody));
        exit;*/
        $sql = "update coin_data_use set name = 'ECOCoin" . rand(10, 100) . "' where id = '8628879d-6df6-41ab-bd25-c2ba1783378f'";
        \Yii::$app->db->createCommand($sql)->execute();
        RecordUpdate::updateAllImplementedRecords("5cb705ea-6c8c-4dae-a620-248545acab14", "coin",
            "8628879d-6df6-41ab-bd25-c2ba1783378f", "62900a19-88a9-4655-a7ac-71488070b659");
        exit;
        StructureUpdate::updateStructuresByInnerObjects("5cb705ea-6c8c-4dae-a620-248545acab14", "coin", "62900a19-88a9-4655-a7ac-71488070b659", true);
        exit;
        echo(StructureOperationHandler::getFastStructureWithCheck('fd27729c-0f30-444b-a124-e3e16069e7d0', '88286f5e-ecd7-48d6-b2d1-69bed835a8c1'));
        exit;
        echo json_encode(TransferFundsHandler::transferFunds('8628879d-6df6-41ab-bd25-c2ba1783378f', ('-' . rand(20, 15) . '.00'), 'ff16249a5218eefb049d0b75ff806148'));
        $startTime = microtime(true);
        //self::getTimeMarker("start actionShowContacts", $startTime);
        $companyID = 'af09ea17-d47c-452d-93de-2c89157b9d5b';
        $serviceID = 'b56b99b6-2c6f-4103-849a-e914e8594869';
        $roleID = '1d021b86-41c6-47c1-a38e-0aa89b98dc28';
        $contactID = '7d82bde3-7740-41d7-9610-8d1fc75db803';
        $firstObject = 'a89c5b6f-80c0-47ca-8b30-8647a5efbfe5';
        $active = '1';
        $requestArray = ['object' => 'a89c5b6f-80c0-47ca-8b30-8647a5efbfe5',
            '2ed029b6-d745-4f85-8d9f-2dccd2a7da37' => 'af09ea17-d47c-452d-93de-2c89157b9d5b',
            '3db2f640-e01a-42ac-904e-87a46e0373fd' => 'b56b99b6-2c6f-4103-849a-e914e8594869',
            '97086af0-956b-4380-a385-ea823cff377a' => '1d021b86-41c6-47c1-a38e-0aa89b98dc28',
            '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24' => '7d82bde3-7740-41d7-9610-8d1fc75db803', 'active_id' => ''];
        $droleID = '62900a19-88a9-4655-a7ac-71488070b659';
        //$droleID = '50f9cd9b-8b93-49c0-9dec-5677d3f9ed0d';
        $recordID = '2b021b86-41c6-47c1-a38e-0aa89b98dc29';
        //$apiModel = new RegistryAPIHandler();
        //$apiModel->updateObjectNameDescription('444c5b6f-80c0-47ca-8b30-8647a5efbf22', $droleID, $contactID, 'test11', 'test11');
        //echo print_r(DynamicRoleModel::checkConstantRolesForContact('7d82bde3-7740-41d7-9610-8d1fc75db803'));
        //StructureOperationHandler::updateStructureFieldNameDescription('37bfbd59-ee35-405d-b4dc-72bcf75755c3', $droleID, $contactID, UUIDGenerator::v4(), 'testField1', '00251e04-c11f-44a1-a5ee-c51e6834c3f3', 'description new Field.');
        //echo print_r((StructureOperationHandler::getFastStructureWithCheck('97086af0-956b-4380-a385-ea823cff377a', $droleID)), true); exit;
        //var_dump(json_decode(StructureOperationHandler::getFastStructureWithCheck('97086af0-956b-4380-a385-ea823cff377a', $droleID), true));
        //$jsonIncomBody = JSONRegistryFactory::getRecordsListFromObject(false, '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24', '');
        /*$jsonIncomBody['filters']['special'] = json_decode('[{"map":"4","comp":"6","value":"test@mail.ru"},{"map":"3","comp":"6","value":"email"}]', true);
        //$jsonIncomBody['work']['operation'] = 0;
        $jsonIncomBody = json_decode('{"permission":{"object_id":"7052a1e5-8d00-43fd-8f57-f2e4de0c8b24","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"7d82bde3-7740-41d7-9610-8d1fc75db803","drole_id":"62900a19-88a9-4655-a7ac-71488070b659"},"work":{"set":1,"operation":0,"ctime":1529679526.8311,"value":[]},"filters":[{"common":""},{"special":[{"map":4,"id":"060f16c7-7573-413f-8f38-fe8d4bf177aa","value":"te"}]}]}', true);//exit;
        echo json_encode(APIHandler::parseQuery($jsonIncomBody));
        exit;
        //echo $jsonIncomBody; exit;
        $jsonIncomBody = JSONRegistryFactory::updateObject(false, '9cb00590-997d-43dd-b5b2-a1dabb35f74b',
            json_decode('[{"field":"ed8bba6e-abfc-4781-bbb5-7a7cc323c2b5","map":"0","value":"281854b5-62fc-43c6-85c3-b78cb10ff525"},{"field":"58769641-9f24-49b7-9c55-f1f3eaf14bb5","map":"3","value":"email"},{"field":"15ef921e-a117-4875-bc38-5b45f0a973c5","map":"4","value":"' . time() . '@gmail.com"}]', true));
        //echo FiltersObjectHandler::getSubscriberWhereByJsonFilter(json_decode('[{"map":"4","comp":"6","value":"test@mail.ru"},{"map":"3","comp":"6","value":"email"}]', true));exit;
        $jsonIncomBody = JSONRegistryFactory::updateObject(false, '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24',
            json_decode('[{"field":"d2a47321-e0da-4ee5-bc76-110a4e67090c","map":"0","value":"038f9770-29bb-42d7-9db3-43588c169d48"},{"field":"58769641-9f24-49b7-9c55-f1f3eaf14bb5","map":"3.0","value":"281854b5-62fc-43c6-85c3-b78cb10ff525"}]', true));
        *///echo json_encode($jsonIncomBody); exit;

        //$jsonIncomBody = json_decode('{"permission":{"object_id":"9cb00590-997d-43dd-b5b2-a1dabb35f74b","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"7052a1e5-8d00-43fd-8f57-f2e4de0c8b24","drole_id":"f8547413-0af7-4679-aff1-b598f4f6d2e2"},"work":{"set":1,"operation":2,"value":{"record":[{"field":"ed8bba6e-abfc-4781-bbb5-7a7cc323c2b5","map":"0","value":"0008d2c8-35fb-4537-86c2-5444dcdb8d00"},{"field":"58769641-9f24-49b7-9c55-f1f3eaf14bb5","map":"3","value":"email"},{"field":"15ef921e-a117-4875-bc38-5b45f0a973c5","map":"4","value":"test123@mail.ru"}]}}}', true);
        //echo json_encode(CompaniesContactDataUse::getMD5ForCurrent('af09ea17-d47c-452d-93de-2c89157b9d5b', 'b56b99b6-2c6f-4103-849a-e914e8594869'));
        //exit;
        //update order
        $recordArray = '{"field":"d2a47321-e0da-4ee5-bc76-110a4e67090c","map":"0","value":"46791d5b-a216-466a-b3b2-5c7c66c48259"},
        {"field":"d280fa03-48cf-44d7-be2c-bcee54cfe89d","map":"7","value":""},{"field":"a8654798-0aac-4d06-a409-eeb6fae2ed79","map":"11","value":"tututtu"}';
        $jsonIncomBody = json_decode('{"permission":{"object_id":"7052a1e5-8d00-43fd-8f57-f2e4de0c8b24","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"d2a47321-e0da-4ee5-bc76-110a4e67090c","drole_id":"88286f5e-ecd7-48d6-b2d1-69bed835a8c1"},"work":{"set":1,"operation":2,"value":{"record":[' . $recordArray . ']}}}', true);
        //$jsonIncomBody = json_decode('{"permission":{"object_id":"7052a1e5-8d00-43fd-8f57-f2e4de0c8b24","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"4d1ffd8c-1a14-42b7-b1aa-f595e338c3e0","drole_id":"88286f5e-ecd7-48d6-b2d1-69bed835a8c1"},"work":{"set":1,"operation":2,"ctime":1537965905.083,"value":{"record":[{"field":"d2a47321-e0da-4ee5-bc76-110a4e67090c","map":"0","value":"4d1ffd8c-1a14-42b7-b1aa-f595e338c3e0"},{"field":"060f16c7-7573-413f-8f38-fe8d4bf177aa","map":"3","value":"roman1"},{"field":"c896b5a6-8640-4103-ba22-70a0bc6c06fe","map":"6","value":"test"},{"field":"d280fa03-48cf-44d7-be2c-bcee54cfe89d","map":"7","value":""},{"field":"a8654798-0aac-4d06-a409-eeb6fae2ed79","map":"11","value":"redweb7@gmail.com"}]}},"filters":[{"common":""}]}', true);
        $baseCurrencyID = '964940de-430d-4a57-a7ec-ff125372ae09';
        $currencyID = '8628879d-6df6-41ab-bd25-c2ba1783378f';
        /*echo (\common\modules\drole\models\exchange\ExchangeEngine::createOrder($contactID, $companyID, $serviceID, $currencyID, $baseCurrencyID, 2000, 0.0000065, 1));
        echo json_encode(\common\modules\drole\models\exchange\ExchangeEngine::createOrder($contactID, $companyID, $serviceID, $currencyID, $baseCurrencyID, 2000, 0.0000064, 1));
        echo json_encode(\common\modules\drole\models\exchange\ExchangeEngine::createOrder($contactID, $companyID, $serviceID, $currencyID, $baseCurrencyID, 2000, 0.0000063, 1));
        echo json_encode(\common\modules\drole\models\exchange\ExchangeEngine::createOrder($contactID, $companyID, $serviceID, $currencyID, $baseCurrencyID, 2000, 0.0000062, 1));
        exit;*/
        /*\common\modules\drole\models\exchange\ExchangeEngine::updateAllRequestsFromList($companyID, $serviceID);
        exit;
        echo(\common\modules\drole\models\exchange\ExchangeEngine::createOrder("7dc79c0b-14ea-4039-914e-e26bb4a708f6", $companyID, $serviceID, $currencyID, $baseCurrencyID, 2500, 0.000007, 0));
        exit;*/
        //update coin name
        //echo json_encode(WithdrawalFundsHandler::sendFunds('8628879d-6df6-41ab-bd25-c2ba1783378f', ('-' . rand(500, 15) . '.00'), 'LQPGJZCUDDjrUEXZgpwDBGxNgtmowPiCSL'));
        //
        //GetRemoteTransactionsHandler::updateAllTransactions();
        /*exit;
        CreateAllWallets::createWalletsForContact('af09ea17-d47c-452d-93de-2c89157b9d5b', 'b56b99b6-2c6f-4103-849a-e914e8594869', "7d82bde3-7740-41d7-9610-8d1fc75db803");*/


        /*$companyID = 'af09ea17-d47c-452d-93de-2c89157b9d5b';
        $serviceID = 'b56b99b6-2c6f-4103-849a-e914e8594869';
        \common\modules\drole\models\exchange\ExchangeEngine::updateAllRequestsFromList($companyID, $serviceID);
        exit;*/
        $objectID = 'debc1348-d852-4d5a-835b-5a2bc4c5ead4';
        $newRecordID = "b61846d7-4d55-444a-a04d-c4865ab0a18a";//UUIDGenerator::v4();
        $fieldID = "ef9bba25-acd3-44b9-932f-59a960c0e908";
        $contactID = "7d82bde3-7740-41d7-9610-8d1fc75db803";
        $recordArray = '{"field":"' . $fieldID . '","map":"0","value":"' . $newRecordID . '"},
        {"field":"81426e36-34e5-47c2-a91a-95791aa47ac3","map":"7.0","value":"b8081ab2-8044-49a0-b9bc-439ba57ce21c"}';

        /*echo json_encode(SpecialQueryHandler::getBodyForSetObjectListValues(JSONRegistryFactory::getRecordsListFromObject(false, $objectID, '')));
        exit;*/
        $jsonIncomBody = json_decode('{"permission":{"object_id":"7052a1e5-8d00-43fd-8f57-f2e4de0c8b24","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"d1474e71-f436-4cc2-94a3-8ff038100f18","drole_id":"88286f5e-ecd7-48d6-b2d1-69bed835a8c1"},"work":{"set":1,"operation":2,"ctime":1538390182.3974,"value":{"record":[{"field":"d2a47321-e0da-4ee5-bc76-110a4e67090c","map":"0","value":"d1474e71-f436-4cc2-94a3-8ff038100f18"},{"field":"c896b5a6-8640-4103-ba22-70a0bc6c06fe","map":"6","value":"forever"},{"field":"d280fa03-48cf-44d7-be2c-bcee54cfe89d","map":"7","value":"dust"}]}},"filters":[{"common":""}]}', true);
        //echo json_encode(UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody));
        echo json_encode(UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody, $startTime));
        exit;
        for ($i = 0; $i < 1; $i++) {
            $newSerLinkID = UUIDGenerator::v4();
            $newContactID = UUIDGenerator::v4();
            $jsonIncomBody = json_decode('{"permission":{"object_id":"9cb00590-997d-43dd-b5b2-a1dabb35f74b","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"' . $newContactID . '","drole_id":"f8547413-0af7-4679-aff1-b598f4f6d2e2"},"work":{"set":1,"operation":2,"value":{"record":[{"field":"ed8bba6e-abfc-4781-bbb5-7a7cc323c2b5","map":"0","value":"' . $newSerLinkID . '"},{"field":"58769641-9f24-49b7-9c55-f1f3eaf14bb5","map":"3","value":"email"},{"field":"15ef921e-a117-4875-bc38-5b45f0a973c5","map":"4","value":"tututu@i.ru"}]}}}', true);
            //echo json_encode(UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody));
            UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody, $startTime);
            $jsonIncomBody = json_decode('{"permission":{"object_id":"7052a1e5-8d00-43fd-8f57-f2e4de0c8b24","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"' . $newContactID . '","drole_id":"62900a19-88a9-4655-a7ac-71488070b659"},"work":{"set":1,"operation":2,"value":{"record":[{"field":"d2a47321-e0da-4ee5-bc76-110a4e67090c","map":"0","value":"' . $newContactID . '"},{"field":"060f16c7-7573-413f-8f38-fe8d4bf177aa","map":"3","value":"test_' . self::random_gen(5) . '"},{"field":"6fb8deeb-9403-49cf-b3e1-f0c8886300b5","map":"7.0","value":"' . $newSerLinkID . '"}]}}}', true);
            echo json_encode($jsonIncomBody);
//        echo json_encode(UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody, $startTime));
            UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody, $startTime);
            APIHandler::setUserAsCustomerForHysiope($newContactID);
        }
        //echo json_encode(UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody));
        self::getTimeMarker("end actionShowContacts", $startTime);
        exit;
        echo json_encode(DataObjectAPIHandler::getValuesForCompanyFilterRole('836f65ad-271e-49a7-81d5-a113d6593f0f'));
        exit;

        //echo print_r(json_decode($jsonIncomBody, true)); exit;
        $jsonIncomBody = json_decode($jsonIncomBody, true);
        echo json_encode(APIHandler::parseQuery($jsonIncomBody));
        exit;
        echo StructureOperationHandler::getFastStructureTreeForAssembly($droleID, "role", "5719c67e-179e-4ddb-9a32-f91b8a24bca7");
        exit;
        $sql = "select * from role_assembly_fields_use where id = '5719c67e-179e-4ddb-9a32-f91b8a24bca7' and field = 'f6b3718f-d02d-46f6-83ab-218974293fa8'";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        $objectsArray = $providerAllObjects->getModels();
        $index = 4;
        if ($objectsArray[0]['turn'] == 4) {
            $index = 3;
        }
        echo json_encode(StructureOperationHandler::updatePositionAndPermission('97086af0-956b-4380-a385-ea823cff377a', $droleID, $contactID, '5719c67e-179e-4ddb-9a32-f91b8a24bca7', 'f6b3718f-d02d-46f6-83ab-218974293fa8', $index, true, true, false, false, false));
        exit;

        $drolesMap = ['62900a19-88a9-4655-a7ac-71488070b659'];

        $accessArray = DataObjectAPIHandler::dataAccessValues($jsonIncomBody, 'role', $jsonIncomBody['permission']['drole_id']);
        echo json_encode($accessArray);
        if (isset($accessArray['result'])) {
            //echo ' error: ' . json_encode($accessArray);
            return json_encode($accessArray);
        }
        $newDrole = AccessRulesHandler::getNewDrole($accessArray);
        while ($newDrole) {
            if (in_array($newDrole, $drolesMap)) {
                array_push($drolesMap, $newDrole);
                return json_encode(APIHandler::getErrorArray(404, "Wrong way. Recursion of the dynamics role found. " . print_r($drolesMap, true)));
            }
            array_push($drolesMap, $newDrole);
            $accessArray = DataObjectAPIHandler::dataAccessValues($jsonIncomBody, "role", $newDrole);
            if (isset($accessArray['result'])) {
                return json_encode($accessArray);
            }
            $newDrole = AccessRulesHandler::getNewDrole($accessArray);
            //echo "============= found drole: " . $newDrole;
        }
        $filterGroup = AccessRulesHandler::getFiltersValues($accessArray);
        //echo "[" . print_r($filterGroup, true) . "]"; exit;
        $whereFilter = '';
        if ($filterGroup) {
            $whereFilter = '(' . FiltersObjectHandler::getSubqueryFromDBForFilterGroup("role", $filterGroup) . ')';
        }
        echo ' whereFilter: ' . $whereFilter;
        //echo print_r(DataObjectAPIHandler::dataAccessValues(json_decode($jsonIncomBody, true), 'role'), true); exit;
        //$jsonDataRecord = ObjectOperationsHandler::getJsonDataFromJsonStructureArray('97086af0-956b-4380-a385-ea823cff377a', $droleID, $recordID, json_decode(StructureOperationHandler::getFastStructureWithCheck('97086af0-956b-4380-a385-ea823cff377a', $droleID), true));
        //echo $jsonDataRecord; exit;
        //ObjectOperationsHandler::setFastRecord('97086af0-956b-4380-a385-ea823cff377a', $droleID, $recordID, $jsonDataRecord);
        exit;
        //echo "[start " . date("Y-m-d H:i:s") . "]";
        /* $valueGateModel = ObjectOperationsHandler::returnIndexedJSON(StructureOperationHandler::getFastStructureJsonForAssembly('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', 'e444d7b4-2393-43e9-8780-80aa8cd5845c'));
          echo ObjectOperationsHandler::returnIndexedJSON(ObjectOperationsHandler::getJsonDataFromJsonStructureArray('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '62900a19-88a9-4655-a7ac-71488070b659', '1c785f31-9a9f-42b9-b633-d0543b04c9b4', ArrayHelper::toArray(json_decode($valueGateModel))));
          exit;
         */

        $gateModel = new CommonGateModel();

        echo FiltersObjectHandler::getSubqueryFromDBForFilterGroup('orderlist', '1b3248a5-54bd-443b-aecd-d4f132f3e3b5');
        exit;
        $rulesArray = AccessRulesHandler::getAccessRulesForObjectByIncomingArray('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', $droleID, $contactID, 'orderlist');
        echo "[check access to Data Object: " . AccessRulesHandler::checkAccesToDataObject($rulesArray) . "]";
        /* foreach ($rulesArray as $rule) {
          echo "[" . AccessRulesHandler::checkAccessRule($rule) . "]";
          } */
        exit;

        //$gateModel->updateStructureFieldNameDescription('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', $droleID, $contactID, '00000000-de80-43b4-bcb8-8168a4fedbd4', 'test1', 'a1d5b5c0-de80-43b4-bcb8-8168a4fedbd4', 'tututtu');
        //ObjectOperationsHandler::updateFastRecord('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '62900a19-88a9-4655-a7ac-71488070b659',
        //        '1c785f31-9a9f-42b9-b633-d0543b04c9b4', '7d82bde3-7740-41d7-9610-8d1fc75db803', '4.0.4.0.1', 'Guantamo');
        //exit;
        $fieldInternalArray = $gateModel->getFieldAllParamsForAssembly('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '62900a19-88a9-4655-a7ac-71488070b659')->getModels();
        //RecordObjectHandler::setNullRecord('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', $droleID, $contactID);
        RecordObjectHandler::updateValueInObjectRecord('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', $droleID, '1c785f31-9a9f-42b9-b633-d0543b04c9b4', $contactID, 'status', '4');
        //echo '$fieldInternalArray: ' . print_r($fieldInternalArray, true);
        exit;
        $valueGateModel = $gateModel->getFastStructureTree('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '62900a19-88a9-4655-a7ac-71488070b659', $fieldInternalArray);

        $gateModel->deleteFastStructure('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '62900a19-88a9-4655-a7ac-71488070b659');
        $gateModel->setFastStructure('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '62900a19-88a9-4655-a7ac-71488070b659', $valueGateModel);
        $dataUseValue = $gateModel->getJsonDataFromJsonStructure('62900a19-88a9-4655-a7ac-71488070b659', 'a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '1c785f31-9a9f-42b9-b633-d0543b04c9b4', json_decode($valueGateModel));
        echo $dataUseValue;
        $gateModel->deleteFastRecord('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '1c785f31-9a9f-42b9-b633-d0543b04c9b4');
        $gateModel->setFastRecord('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '62900a19-88a9-4655-a7ac-71488070b659', '1c785f31-9a9f-42b9-b633-d0543b04c9b4', $dataUseValue);
        exit;
        $structure = $gateModel->getFastStructure('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '62900a19-88a9-4655-a7ac-71488070b659')->getModels();
        $data = $gateModel->getFastRecord('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', '62900a19-88a9-4655-a7ac-71488070b659', '1c785f31-9a9f-42b9-b633-d0543b04c9b4')->getModels();
        //echo $valueGateModel;
        //exit;
        //$result = $gateModel->updateFastRecord('a89c5b6f-80c0-47ca-8b30-8647a5efbfe5', 'af09ea17-d47c-452d-93de-2c89157b9d5b', '51b579f6-4917-4385-9438-f2d733d19974', '62900a19-88a9-4655-a7ac-71488070b659', '1c785f31-9a9f-42b9-b633-d0543b04c9b4', '7d82bde3-7740-41d7-9610-8d1fc75db803', '4.0.4.0.1', 'Guantamo');
        //$result = $gateModel->getAllCasesOfUse('8101a4d2-9c11-46bf-9821-cbdacd760d5d', '8ba5645f-f446-4cfd-9eee-02f690384440');
        echo print_r($result, true);
        exit;
        $droleData = $this->getDroleAndAssemblyFromRequest($requestArray);
        //print_r(json_encode($droleAccessArray->getModels()));
        echo '$drole_id: ' . print_r($droleData, true);
        //echo "[end " . date("Y-m-d H:i:s") . "]";
        exit;
        // block create new tables for object alias
        /* $alias = 'pay';
          //create tables for module
          $this->createStructureSpaceForAlias($alias);
          //create data table for structure
          $this->createDataTableForAlias($alias);
          exit;
         */

        //$gateModel = new CommonGateModel();
        //update record:
        /* $requestArray['map'] = '$.customer.homeaddress.addvalue';
          $requestArray['rowid'] = '7937e9fd-8b21-4cc8-890b-33d9a7dbdb8f';
          $requestArray['rowvalue'] = 'wtf1';
          $gateModel->updateRecord($requestArray);
          exit; */
        //get all records
        //
        //$gateModel->getRecords($requestArray);
        //exit;
        $requestArray['drole'] = $droleData['drole'];
        $simpleObject = new SimpleObjectHandler($requestArray);
        $simpleObject->getAllDataFromObject();
        exit;
        //$structureFieldModel->setTable('contacts_structure_fields');
        //$structureFieldModel::
        //echo "[tablename: " . $structureFieldModel->getTableName() . "]";
        $dynamicRoleModel = DynamicRoleModel::findOne(self::createParams($companyID, $serviceID, $roleID))->id;
        //$dynamicRoleModel = DynamicRoleModel::getDynamicRoleForContactWithLocalization('33', 'serviceid01', '7')->id;
        if ($dynamicRoleModel == '' || $dynamicRoleModel === false) {
            echo "[not found drole. exit]";
            return;
        } else {
            echo "[drole: " . $dynamicRoleModel . "]";
        }
        $assemblyForObject = DynamicAssemblyForObject::findOne([self::$dynamicRoleFieldName => $dynamicRoleModel, self::$objectFieldName => $firstObject, self::$activeFieldName => $active])->assembly_id;
        if ($assemblyForObject == '' || $assemblyForObject === false) {
            echo "[not found. exit]";
            return;
        } else {
            echo "[assembly: " . $assemblyForObject . "]";
        }
        $extObjectName = RegistryObjectValues::findOne([id => $firstObject])->name;
        if ($extObjectName == '' || $extObjectName === false) {
            echo "[not found. exit]";
            return;
        } else {
            echo "[" . $extObjectName . "]";
        }
        $assemblyUseModel = new ObjectAssemblyUseModel($extObjectName);
        $sqlProviderAssembly = $assemblyUseModel::getAssemblyUsingFieldsObject($assemblyForObject);
        print_r($sqlProviderAssembly->getModels());
        exit;

        /* $pagination = new Pagination([
          'defaultPageSize' => 2,
          'totalCount' => count($structureFields),
          ]); */

        //$structureFields = $query->all();
        /* $structureFields = $query->orderBy('name')
          //->offset($pagination->offset)
          //->limit($pagination->limit)
          ->all();
         */
        echo "[first step]";
        //response->format=RESPONSE::FORMAT_JSON;
        return $this->render('show-contacts', [
            'dataProvider' => $structureFields
            //,'pagination' => $pagination,
        ]);
    }

    private static function random_gen($length)
    {
        $random = "";
        srand((double)microtime() * 1000000);
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        $char_list .= "1234567890";
        // Add the special characters to $char_list if needed

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($char_list, (rand() % (strlen($char_list))), 1);
        }
        return $random;
    }

    private static function getTimeMarker($title, $startTime)
    {
        //$t = microtime(true);
        //$micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        //return "[" . $title . " : " . date_format(new DateTime(date('Y-m-d H:i:s.' . $micro, $t)), 'hh:mi:ss.mmm') . "]";
        echo "[" . $title . " : " . (microtime(true) - $startTime) . "]";
    }

    private function getDroleAndAssemblyFromRequest($requestArray)
    {
        $droleResult = '';
        $found = false;
        echo "[before dynamic " . date("Y-m-d H:i:s") . "]";
        $dynamicRoleModel = new DynamicRoleModelWithContacts($requestArray);
        echo "[after dynamic " . date("Y-m-d H:i:s") . "]";
        $droleAccessArray = [];
        if (array_key_exists(self::$droleID, $requestArray) && array_key_exists(self::$contactID, $requestArray)) {
            $varArray = $requestArray;
            $droleAccessArray = $dynamicRoleModel->checkPresentDroleForContact()->getModels();
            foreach ($droleAccessArray as $key => $value) {
                //echo print_r($value,true);
                switch ($value['meta_key']) {
                    case self::$companyID:
                    case self::$serviceID:
                    case self::$roleID:
                        $varArray[$value['meta_key']] = $value['value'];
                        break;
                    case self::$contactID:
                    case self::$droleID:
                        //do nothing
                        break;
                    default :
                        $varArray[$value['meta_key']] = $value['value'];
                        break;
                }
            }
            $droleResult = $this->checkDroleWithShortRequestData($varArray, $droleAccessArray);
            $pos = strpos($droleResult, '{"result"');
            if ($pos === false) {
                $found = true;
                $requestArray = $varArray;
            }
        }
        if (!$found) {
            $droleAccessArray = $dynamicRoleModel->getDynamicRoleForContactWithLocalization()->getModels();
            foreach ($droleAccessArray as $key => $value) {
                //echo print_r($value,true);
                switch ($value['meta_key']) {
                    case self::$companyID:
                    case self::$serviceID:
                    case self::$roleID:
                        $requestArray[$value['meta_key']] = $value['value'];
                        break;
                    case self::$contactID:
                    case self::$droleID:
                        //do nothing
                        break;
                    default :
                        $requestArray[$value['meta_key']] = $value['value'];
                        break;
                }
            }
            echo "[before assembly " . date("Y-m-d H:i:s.m") . "]";
            $droleResult = $this->checkDroleWithFullRequestData($requestArray, $droleAccessArray);
            $pos = strpos($droleResult, '{"result"');
            if ($pos === false) {
                $found = true;
            } else {
                return $droleResult;
            }
        }
        //die(print_r($droleAccessArray, true));
        $assemblyArray = $dynamicRoleModel->getAssemblyForDroleAndObject($droleResult)->getModels();
        //die(print_r($assemblyArray[0]['assembly_id'], true));
        if ($assemblyArray[0]['assembly_id'] == '') {
            return '{"result":"error","message":"not found assembly for input array"}';
        }
        $requestArray['drole'] = $droleResult;
        $requestArray['assembly'] = $assemblyArray[0]['assembly_id'];
        return $requestArray; //['drole' => $droleResult, 'assembly' => $assemblyArray[0]['assembly_id']];
    }

    private function checkDroleWithShortRequestData($requestArray, $droleAccessArray)
    {

        //print_r($droleAccessArray);
        //echo "[count: " . count($structureFields) . "]";
        $droleID = $droleAccessArray[0]['drole_id'];
        $respondCheckArray = [];
        foreach ($droleAccessArray as $key => $value) {
            //echo print_r($value,true);
            switch ($value['meta_key']) {
                case self::$companyID:
                case self::$serviceID:
                case self::$roleID:
                case self::$contactID:
                    array_push($respondCheckArray, '{"' . $value['meta_key'] . '":"200"}');
                    break;
                case self::$payID:
                    $checkObject = new PayCV(self::$payID, 'pay');
                    $result = $checkObject->checkCriterion($droleID, $requestArray['object'], $requestArray);
                    array_push($respondCheckArray, $result);
                    $pos = strpos($result, '{"result":"4');
                    if ($pos === false) {
                        break;
                    }
                    return json_encode($respondCheckArray);
                default:
                    array_push($respondCheckArray, '{"' . $value['meta_key'] . '":"400"}');
                    return json_encode($respondCheckArray);
            }
        }
        return $droleID;
    }

    private function checkDroleWithFullRequestData($requestArray, $droleAccessArray)
    {
        //$droleAccessArray = $dynamicRoleModel->getDynamicRoleForContactWithLocalization()->getModels();
        //print_r($droleAccessArray);
        //echo "[count: " . count($structureFields) . "]";
        $droleID = $droleAccessArray[0]['drole_id'];
        $respondCheckArray = [];
        foreach ($droleAccessArray as $key => $value) {
            //echo print_r($value,true);
            switch ($value['meta_key']) {
                case self::$companyID:
                case self::$serviceID:
                case self::$roleID:
                case self::$contactID:
                    array_push($respondCheckArray, '{"' . $value['meta_key'] . '":"200"}');
                    break;
                case self::$payID:
                    $checkObject = new PayCV(self::$payID, 'pay');
                    $result = $checkObject->checkCriterion($droleID, $requestArray['object'], $requestArray);
                    array_push($respondCheckArray, $result);
                    $pos = strpos($result, '{"result":"4');
                    if ($pos === false) {
                        break;
                    }
                    return json_encode($respondCheckArray);
                default:
                    array_push($respondCheckArray, '{"' . $value['meta_key'] . '":"400"}');
                    return json_encode($respondCheckArray);
            }
        }
        return $droleID;
    }

    private function createParams($companyID, $serviceID, $roleID)
    {
        //for anonimous role has one assembly for all companies
        if ($roleID == '' || $roleID == null) {
            $roleID = RegistryDescriptionRolesModel::$anonimus;
        }
        $resultParams = [self::$serviceFieldName => $serviceID, self::$roleFieldName => $roleID];
        if ($roleID != RegistryDescriptionRolesModel::$anonimus) {
            $resultParams[self::$companyFieldName] = $companyID;
        }
        return $resultParams;
    }

    public function actionGetInfo()
    {
        $jsonHandler = new APIHandler();
        return $jsonHandler->parseQuery();
    }

    public function actionExchange()
    {
        $companyID = 'af09ea17-d47c-452d-93de-2c89157b9d5b';
        $serviceID = 'b56b99b6-2c6f-4103-849a-e914e8594869';
        \common\modules\drole\models\exchange\ExchangeEngine::updateAllRequestsFromList($companyID, $serviceID);
    }

    public function actionTrunks()
    {
        GetRemoteTransactionsHandler::updateAllTransactions();
    }

    public function actionGetmarketpair()
    {
        $jsonIncoming = \Yii::$app->request->post('json', '{"query":"test"}');
        if ($jsonIncoming == '{"query":"test"}') {
            return json_encode(APIHandler::getErrorArray(404, "Not found values."));
        } else {
            $jsonIncomingObject = json_decode($jsonIncoming, true);
            if (!isset($jsonIncomingObject['permission']) || !isset($jsonIncomingObject['work']) ||
                ($jsonIncomingObject['permission']['object_id'] != '5c1a5894-f6df-4c96-a84d-6679f3375bb7' &&
                    $jsonIncomingObject['permission']['object_id'] != 'fd27729c-0f30-444b-a124-e3e16069e7d0') ||
                $jsonIncomingObject['work']['set'] != 1 || $jsonIncomingObject['work']['operation'] != 0) {
                return json_encode(APIHandler::getErrorArray(404, "Permission denied."));
            }
            $droleArray = DynamicRoleModel::getArrayOfDynamicRole($jsonIncomingObject['permission']['drole_id']);
            if ($droleArray['role_id'] != '69ebe402-022a-4fb1-9472-f16c4b768c26') {
                return json_encode(APIHandler::getErrorArray(404, "Permission denied."));
            }
            $jsonIncomingObject['permission']['contact_id'] = '00000000-0000-0000-0000-000000000000';
            $jsonHandler = new DataObjectAPIHandler();
            return json_encode($jsonHandler->parseQuery($jsonIncomingObject));
        }
    }

    public function actionChart()
    {
        /*if (\Yii::$app->user->isGuest ) {
            echo json_encode(APIHandler::getErrorArray(404, "Authorisation is failed."));
            return;
        }*/
        if (!UUIDGenerator::isUUID(\Yii::$app->request->post('companyid')) || !UUIDGenerator::isUUID(\Yii::$app->request->post('serviceid'))
            || !UUIDGenerator::isUUID(\Yii::$app->request->post('currentcurrencyid')) || !UUIDGenerator::isUUID(\Yii::$app->request->post('basecurrencyid'))) {
            return '{"data": {"status": "error", "type": "null", "chartdata": [' . \Yii::$app->request->post() . ']}}';
        }

        return ExchangeChartHandler::getChart(\Yii::$app->request->post('companyid'),
            \Yii::$app->request->post('serviceid'), \Yii::$app->request->post('currentcurrencyid'),
            \Yii::$app->request->post('basecurrencyid'), \Yii::$app->request->post('type'));
    }

    public function actionPmdeposit()
    {
        PerfectDeposit::updateRequest();
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            // For cross-domain AJAX request
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    // restrict access to domains:
                    'Origin' => static::allowedDomains(),
                    'Access-Control-Request-Method' => ['POST'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                    // Cache (seconds)
                ],
            ],
        ]);
    }

    public static function allowedDomains()
    {
        return [
            // '*',                        // star allows all domains
            'hysiope.com',
        ];
    }

    private function createStructureSpaceForAlias($alias)
    {
        //create tables for module
        $wizardObject = new ObjectTablesWizardPostgres($alias);
        $wizardObject->createObjectTables();
    }

    private function createDataTableForAlias($alias)
    {
        //create data table for structure
        $structureFieldModel = new ObjectStructureModel($alias);
        $structureFields = $structureFieldModel->getDataFromTable();
        $value = new DataTableWizard($alias);
        $value->createTable($structureFields);
    }

}
