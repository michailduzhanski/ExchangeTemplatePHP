<?php

namespace backend\controllers;

use backend\models\assemblies\AssemblyAddRoleToCurrent;
use backend\models\assemblies\AssemblyDelete;
use backend\models\assemblies\AssemblyEditNameDescription;
use backend\models\assemblies\AssemblyRolesListByServiceCompany;
use backend\models\assemblies\AssemblyStructureFieldValues;
use backend\models\assemblies\AssemblySetRoleAsMain;
use backend\models\dataobjects\DataObjectDelete;
use backend\models\dataobjects\EditNameDescription;
use backend\models\dataobjects\StructureFieldDelete;
use backend\models\dataobjects\StructureFieldValues;
use common\models\LoginForm;
use backend\models\TextFieldModel;
use common\modules\drole\models\auth\ContactAuth;
use common\modules\drole\models\auth\ContactAuthLog;
use common\modules\drole\models\gate\APIHandler;
use common\modules\drole\models\gate\RegistryAPIHandler;
use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\DynamicRoleModel;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Site controller
 */
class SiteController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [
                    'logout',
                    'signup'
                ],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest && !$this->workWithExpiration()) {
            return \Yii::$app->getResponse()->redirect(['/objects-list']);
        }
        return $this->render('index');
    }

    private function workWithExpiration()
    {
        if (self::checkExpirationAuth() < microtime(true)) {
            return $this->actionLogout();
        }
        return false;
    }

    public static function checkExpirationAuth()
    {
        $contactAuthRecord = ContactAuth::getContactAuthByID(\Yii::$app->user->getId());
        if (!$contactAuthRecord) {
            return -1;
        } else {
            return $contactAuthRecord->time;
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        $renderPage = 'logout';
        $this->updateAuthRecordsByController($renderPage);
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function updateAuthRecordsByController($renderPage)
    {
        \Yii::$app->user->identity->auth['time'] = microtime(true) + \Yii::$app->user->identity->getTimeExpired() * 10;
        \Yii::$app->user->identity->auth['lang'] = \Yii::$app->language;
        \Yii::$app->user->identity->auth['page'] = '/' . \Yii::$app->language . '/' . $renderPage;
        ContactAuthLog::insertContactAuthArray(\Yii::$app->user->identity->auth, microtime(true) + \Yii::$app->user->identity->getTimeExpired());
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            //return $this->goBack();
            return \Yii::$app->getResponse()->redirect(['/objects-list']);
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionObjectsList()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        $renderPage = 'objects-list';
        $this->updateAuthRecordsByController($renderPage);
        return $this->render($renderPage);
    }

    public function actionObjectsEdit()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        $renderPage = 'objects-edit';
        $this->updateAuthRecordsByController($renderPage);
        $id = Yii::$app->request->get('id');
        if ($id !== null) {
            $object = RegistryAPIHandler::getDataObjectRegistryElement($id);
        } else {
            $object = null;
        }
        $editNameDescription = new EditNameDescription();
        //echo json_encode(Yii::$app->request->post());
        if ($editNameDescription->load(Yii::$app->request->post())) {
            //$editNameDescription->save();
            //return $this->render('objects-edit', ['id' => $editNameDescription->save()]);
            //'/site/dataobjects/cardnamedescription'
            return $this->render('/site/dataobjects/cardnamedescription', [
                'id' => $editNameDescription->save(),
                'object' => $object,
                'editNameDescription' => $editNameDescription
            ]);
        } else {
            $editNameDescription->setAttributes($object);
        }
        /*
          $dataObjectFieldForm = new DataObjectFieldForm();
          if ($dataObjectFieldForm->load(Yii::$app->request->post()) && $dataObjectFieldForm->save()) {
          return $this->refresh();
          }
         */
        return $this->render($renderPage, [
            'object' => $object,
            'editNameDescription' => $editNameDescription
        ]);
    }

    public function actionObjectsOperations()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            echo json_encode(APIHandler::getErrorArray(404, "Authorisation is failed."));
            return;
        }
        $superAdmin = false;
        $admin = false;
        $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
        if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
            $superAdmin = true;
        } else if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['admin']) {
            $admin = true;
        }
        if (isset(Yii::$app->request->post()['EditNameDescription'])) {
            if (!$superAdmin) {
                echo json_encode(APIHandler::getErrorArray(404, "Permission denied."));
                return;
            }
            $editNameDescription = new EditNameDescription();

            if ($editNameDescription->load(Yii::$app->request->post())) {
                $editNameDescription->save();
                echo json_encode($editNameDescription);
            } else {
                echo json_encode(APIHandler::getErrorArray(404, "not found id of the object."));
            }
        } else if (isset(Yii::$app->request->post()['StructureFieldValues'])) {
            if (!$superAdmin) {
                echo json_encode(APIHandler::getErrorArray(404, "Permission denied."));
                return;
            }
            $structureFieldValues = new StructureFieldValues();
            if ($structureFieldValues->load(Yii::$app->request->post())) {
                //echo '{"start": ' . json_encode($structureFieldValues) . '}'; return;
                $structureFieldValues->save();
                echo json_encode($structureFieldValues);
            } else {
                echo json_encode(APIHandler::getErrorArray(404, "not found id of the object."));
            }
        } else if (isset(Yii::$app->request->post()['StructureFieldDelete'])) {
            if (!$superAdmin) {
                echo json_encode(APIHandler::getErrorArray(404, "Permission denied."));
                return;
            }
            $structureFieldDelete = new StructureFieldDelete();
            if ($structureFieldDelete->load(Yii::$app->request->post())) {
                //echo '{"start": ' . json_encode($structureFieldValues) . '}'; return;
                echo json_encode($structureFieldDelete->save());
            } else {
                echo json_encode(APIHandler::getErrorArray(404, "not found id of the object."));
            }
        } else if (isset(Yii::$app->request->post()['DataObjectDelete'])) {
            if (!$superAdmin) {
                echo json_encode(APIHandler::getErrorArray(404, "Permission denied."));
                return;
            }
            $dataObjectDelete = new DataObjectDelete();
            if ($dataObjectDelete->load(Yii::$app->request->post())) {
                //echo '{"start": ' . json_encode($structureFieldValues) . '}'; return;
                echo json_encode($dataObjectDelete->save());
            } else {
                echo json_encode(APIHandler::getErrorArray(404, "not found id of the object."));
            }
        } else if (isset(Yii::$app->request->post()['AssemblyDelete'])) {
            $assemblyDelete = new AssemblyDelete();
            if ($assemblyDelete->load(Yii::$app->request->post())) {
                //echo '{"start": ' . json_encode($structureFieldValues) . '}'; return;
                echo json_encode($assemblyDelete->save());
            } else {
                echo json_encode(APIHandler::getErrorArray(404, "not found id of the object."));
            }
        } else echo json_encode(APIHandler::getErrorArray(403, "Not found query type."));
    }

    public function actionAssembliesList()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        $renderPage = 'assemblies-list';
        $this->updateAuthRecordsByController($renderPage);
        return $this->render($renderPage);
    }

    public function actionAssembliesEdit()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        $renderPage = 'assemblies-edit';
        $this->updateAuthRecordsByController($renderPage);

        $objectid = Yii::$app->request->get('objectid');
        if ($objectid !== null) {
            $object = RegistryAPIHandler::getDataObjectRegistryElement($objectid);
        } else {
            $object = null;
        }
        $assemblyid = Yii::$app->request->get('id');
        if ($assemblyid !== null) {
            $assembly = RegistryAPIHandler::getAssemblyRegistryElement($assemblyid);
        } else {
            $assembly = null;
        }
        $assemblyEditNameDescription = new AssemblyEditNameDescription();
        //echo json_encode(Yii::$app->request->post());
        if ($assemblyEditNameDescription->load(Yii::$app->request->post())) {
            //$editNameDescription->save();
            //return $this->render('objects-edit', ['id' => $editNameDescription->save()]);
            //'/site/dataobjects/cardnamedescription'
            return $this->render('/site/dataobjects/cardnamedescription', [
                'id' => $assemblyEditNameDescription->save(),
                'object' => $object,
                'assembly' => $assembly,
                'assemblyEditNameDescription' => $assemblyEditNameDescription
            ]);
        } else {
            $assemblyEditNameDescription->setAttributes($object);
        }
        return $this->render($renderPage, [
            'object' => $object,
            'assembly' => $assembly,
            'assemblyEditNameDescription' => $assemblyEditNameDescription
        ]);
    }

    public function actionAssemblyOperations()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            echo json_encode(APIHandler::getErrorArray(404, "Authorisation is failed."));
            return;
        }
        $access = false;
        $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
        if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
            $access = true;
        } else if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['admin']) {
            $access = true;
        }
        if (!$access) {
            echo json_encode(APIHandler::getErrorArray(404, "Permission denied."));
            return;
        }
        if (isset(Yii::$app->request->post()['AssemblyEditNameDescription'])) {
            $assemblyEditNameDescription = new AssemblyEditNameDescription();
            if ($assemblyEditNameDescription->load(Yii::$app->request->post())) {
                $result = $assemblyEditNameDescription->save();
                if (is_array($result))
                    echo json_encode($result);
                else
                    echo json_encode($assemblyEditNameDescription);
            } else {
                echo json_encode(APIHandler::getErrorArray(404, "not found id of the object."));
            }
        } else if (isset(Yii::$app->request->post()['AssemblyStructureFieldValues'])) {
            $assemblyStructureFieldValues = new AssemblyStructureFieldValues();
            if ($assemblyStructureFieldValues->load(Yii::$app->request->post())) {
                $result = $assemblyStructureFieldValues->save();
                if (is_array($result))
                    echo json_encode($result);
                else
                    echo json_encode($assemblyStructureFieldValues);
            } else {
                echo json_encode(APIHandler::getErrorArray(404, "not found id of the object."));
            }
        } else if (isset(Yii::$app->request->post()['AssemblyRolesListByServiceCompany'])) {
            $assemblyRolesListByServiceCompany = new AssemblyRolesListByServiceCompany();
            if ($assemblyRolesListByServiceCompany->load(Yii::$app->request->post())) {
                $result = $assemblyRolesListByServiceCompany->save();
                if (is_array($result))
                    echo json_encode($result);
                else
                    echo "{}";
            } else {
                echo json_encode(APIHandler::getErrorArray(404, "not found id of the object."));
            }
        } else if (isset(Yii::$app->request->post()['AssemblyAddRoleToCurrent'])) {
            $assemblyAddRoleToCurrent = new AssemblyAddRoleToCurrent();
            if ($assemblyAddRoleToCurrent->load(Yii::$app->request->post())) {
                $result = $assemblyAddRoleToCurrent->save();
                if (is_array($result))
                    echo json_encode($result);
                else
                    echo "{}";
            } else {
                echo json_encode(APIHandler::getErrorArray(404, "not found id of the object."));
            }
        } else if (isset(Yii::$app->request->post()['AssemblySetRoleAsMain'])) {
            $assemblySetRoleAsMain = new AssemblySetRoleAsMain();
            if ($assemblySetRoleAsMain->load(Yii::$app->request->post())) {
                $result = $assemblySetRoleAsMain->save();
                if (is_array($result))
                    echo json_encode($result);
                else
                    echo "{}";
            } else {
                echo json_encode(APIHandler::getErrorArray(404, "not found id of the object."));
            }
        } else echo json_encode(APIHandler::getErrorArray(403, "Not found query type."));
    }

    public function actionObjectsrecordsList()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        $renderPage = 'objectsrecords-list';
        $this->updateAuthRecordsByController($renderPage);
        return $this->render($renderPage);
    }

    public function actionObjectsrecordsEdit()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        $renderPage = 'objectsrecords-edit';
        $this->updateAuthRecordsByController($renderPage);
        return $this->render($renderPage);
    }

    public function actionRenderTextField()
    {
        if(Yii::$app->request->isAjax){
            $post = Yii::$app->request->post();
            $attributes = [$post['field'] => $post['value']];
            $model = new TextFieldModel($attributes);

            return $this->renderAjax('text-field', [
                'model' => $model,
                'post' => $post,
                'field' => $post['field']
            ]);
        }

        throw new NotFoundHttpException();
    }

    public function actionUspmtable()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('uspmtable');
    }

    public function actionAccessrulesList()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        $renderPage = '/site/accessrules/accessrules-list';
        $this->updateAuthRecordsByController($renderPage);
        return $this->render($renderPage);
    }

    /*public function actionAccessrulesEdit()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        $renderPage = '/site/accessrules/accessrules-edit';
        $this->updateAuthRecordsByController($renderPage);
        return $this->render($renderPage);
    }*/

    public function actionAccessrulesEdit()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        $renderPage = '/site/accessrules/accessrules-edit';
        $this->updateAuthRecordsByController($renderPage);

        $objectid = Yii::$app->request->get('objectid');
        if ($objectid !== null) {
            $object = RegistryAPIHandler::getDataObjectRegistryElement($objectid);
        } else {
            $object = null;
        }
        $assemblyid = Yii::$app->request->get('id');
        if ($assemblyid !== null) {
            $assembly = RegistryAPIHandler::getAssemblyRegistryElement($assemblyid);
        } else {
            $assembly = null;
        }
        $assemblyEditNameDescription = new AssemblyEditNameDescription();
        //echo json_encode(Yii::$app->request->post());
        if ($assemblyEditNameDescription->load(Yii::$app->request->post())) {
            //$editNameDescription->save();
            //return $this->render('objects-edit', ['id' => $editNameDescription->save()]);
            //'/site/dataobjects/cardnamedescription'
            return $this->render('/site/accessrules/editcards/cardnamedescription', [
                'id' => $assemblyEditNameDescription->save(),
                'object' => $object,
                'assembly' => $assembly,
                'assemblyEditNameDescription' => $assemblyEditNameDescription
            ]);
        } else {
            $assemblyEditNameDescription->setAttributes($object);
        }
        return $this->render($renderPage, [
            'object' => $object,
            'assembly' => $assembly,
            'assemblyEditNameDescription' => $assemblyEditNameDescription
        ]);
    }
}
