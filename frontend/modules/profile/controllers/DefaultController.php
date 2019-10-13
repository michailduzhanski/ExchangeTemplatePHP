<?php

namespace frontend\modules\profile\controllers;

use common\models\UUIDGenerator;
use common\modules\drole\models\auth\CompaniesContactDataUse;
use common\modules\drole\models\auth\ContactAuth;
use common\modules\drole\models\auth\ContactAuthLog;
use common\modules\drole\models\gate\StructureOperationHandler;
use common\modules\drole\models\gate\TransferFundsHandler;
use common\modules\drole\models\webtools\JSONRegistryFactory;
use frontend\modules\droleYii\DroleYii;
use frontend\modules\droleYii\models\DroleYiiModel;
use frontend\modules\profile\models\AddCoinModel;
use frontend\modules\profile\models\ChangePassword;
use frontend\modules\profile\models\Profile;
use frontend\modules\profile\models\Transfer;
use frontend\modules\profile\models\Windraw;
use Yii;
use yii\base\DynamicModel;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\modules\drole\models\gate\WithdrawalFundsHandler;

/**
 * Default controller for the `profile` module
 */
class DefaultController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'view' => '@frontend/views/site/error'
            ],
        ];
    }

    /**
     * Страница профиля
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('profile');
    }

    public function actionExchangeOld()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        $renderPage = 'exchange/exchange-old';
        $this->updateAuthRecordsByController($renderPage);
        return $this->render($renderPage);
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
        \Yii::$app->user->identity->auth['hash'] = \Yii::$app->session->id;
        ContactAuth::insertContactAuthArray(\Yii::$app->user->identity->auth, microtime(true) + \Yii::$app->user->identity->getTimeExpired());
        ContactAuthLog::insertContactAuthArray(\Yii::$app->user->identity->auth, microtime(true) + \Yii::$app->user->identity->getTimeExpired());
    }

    public function actionExchange()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
        	$this->layout = '/profile-guest';
            $renderPage = 'exchange/exchange-lite';
        } else {
            $renderPage = 'exchange/exchange';
            $this->updateAuthRecordsByController($renderPage);
        }
        return $this->render($renderPage);
    }

    public function actionShowUserCard()
    {
        return $this->renderAjax('profile_form');
    }

    public function actionShowProfileInfo()
    {
        return $this->renderAjax('profile_card');
    }

    public function actionShowPasswordForm()
    {
        $model = new ChangePassword();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                echo '<div class="data-change-success verify-status verify-success">' . Yii::t('frontend', 'Password changed successfully') . '</div>';
                exit;
            } else {
                echo '<div class="data-change-error verify-status label-danger">' . Yii::t('frontend', 'Something wrong! Please, try again.') . '</div>';
                exit;
            }
        }

        return $this->renderAjax('change_password', [
            'model' => $model
        ]);
    }

    public function actionEditProfileFormValidate()
    {
        $model = new Profile();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }

    public function actionEditProfileForm()
    {

        $model = new Profile();

        if($post = Yii::$app->request->post()){
            if(isset($post['name']) && isset($post['value'])){
                $post['id'] = isset($post['id']) ? $post['id'] : null;
                $attribute = $model->getAttributeName($post['name']);
                if($model->save($post['id'], $attribute, $post['value'])){
                    exit;
                }
            }
            echo '<div class="data-change-error verify-status label-danger">' . Yii::t('frontend', 'Something wrong! Please, try again.') . '</div>';
            exit;
        }

        return $this->renderAjax('change_profile', [
            'model' => $model
        ]);
        exit;
    }


    public function actionHtransaction()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('history/htransaction');
    }

    public function actionWtransaction()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('history/wtransaction');
    }

    public function actionDtransaction()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('history/dtransaction');
    }

    public function actionGtransaction()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('history/gtransaction');
    }

    public function actionCtransaction()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('history/ctransaction');
    }

    public function actionWcrypto()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('actions/withdrawal/withdrawalcrypto');
    }

    public function actionDcrypto()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('actions/deposit/depositcrypto');
    }

    public function actionPerfdone()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('actions/deposit/perfectdone');
    }

    public function actionResultpayment()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('actions/deposit/payment');
    }

    public function actionDdone()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('actions/deposit/depositdone');
    }

    public function actionTcrypto()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('actions/transfer/transfercrypto');
    }

    public function actionWlist(){
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }
        return $this->render('wallets/wlist');
    }

    public function actionTdone()
    {
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }

        $model = new Transfer();    	
        if($currency = Yii::$app->request->get('currency')){
            $model->currency = $currency;
        }       
        if($model->load(Yii::$app->request->post()) && $model->validate()){                  	
            //$result = WithdrawalFundsHandler::sendFunds($model->currency, $model->total, $model->address);
            $result = TransferFundsHandler::transferFunds($model->currency, $model->amount, $model->address);

            if(isset($result['result'])){
                if($result['result'] == '200'){
                    $json['status'] = 'success';
                    Yii::$app->Pincode->regenerate(false);
                } else {
                    $json['status'] = 'error';
                }
                $json['message'] = $result['message'];
            } else {
                $json['status'] = 'error';                
                if(isset($result['message'])){
                    $json['message'] = $result['message'];
                } else {
                    $json['message'] = 'Something wrong! Please, try again!';
                }
            }
            echo json_encode($json);
            exit;                
        }
        return $this->render('actions/transfer/transferdone', [
            'model' => $model
        ]);
    }

    public function actionTdoneValidate()
    {
        $model = new Transfer();
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        if(Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
            echo json_encode(ActiveForm::validate($model));
        }
        exit;
    }

    public function actionWdone()
    {
        $json = [];
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            return $this->goHome();
        }

        $model = new Windraw();
        if($currency = Yii::$app->request->get('currency')){
            $model->currency = $currency;
        }        

        if($model->load(Yii::$app->request->post()) && $model->validate()){                    
            $result = WithdrawalFundsHandler::sendFunds($model->currency, $model->total, $model->address);
            if(isset($result['code'])){
                if($result['code'] == '200'){
                    $json['status'] = 'success';
                    Yii::$app->Pincode->regenerate(false);
                } else {
                    $json['status'] = 'error';
                }
                $json['message'] = $result['message'];
            } else {
                $json['status'] = 'error';                
                if(isset($result['message'])){
                    $json['message'] = $result['message'];
                } else {
                    $json['message'] = 'Something wrong! Please, try again!';
                }
            }

            echo json_encode($json);
            exit;                
        }

        return $this->render('actions/withdrawal/withdrawaldone', [
            'model' => $model
        ]);
    }

    public function actionWdoneValidate()
    {
        $model = new Windraw();        
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        if(Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){  
            echo json_encode(ActiveForm::validate($model));
        }
        exit;
    }

    //change it
    public function actionCoinlist()
    {    	
        if (Yii::$app->user->isGuest || $this->workWithExpiration()) {
            $this->layout = '/profile-guest';
        }
        return $this->render('descriptions/coinpage');
    }

    public function actionSupport(){
        return $this->render('conversation/support');
    }

    public function actionFaq(){
        return $this->render('conversation/faq');
    }

    public function actionAddCoinPublish()
    {
        if($id = Yii::$app->request->post('id')){
            $objectId = 'fd27729c-0f30-444b-a124-e3e16069e7d0';
            $model = new AddCoinModel($objectId, false, [
                'order' => [
                    'coinname__image' => -10,
                    'descriptionru' => -9,
                    'descriptionen' => -9,
                ],
                'defaultValues' => [
                    //'coinname__name' => 'test'
                ],
                'defaultValuesFromField' => [
                    'currencyid' => 'coinname__id'
                ],
                'disableFields' => ['currencyid', 'price_adding'],
                'imageProfile' => [
                    'coinname__image' => 'coin'
                ]
            ]);

            if($id){
                $model->loadByID($id);
            }
            $model->attachImageBehavior();
            $model->addRule('coinname__symbol', 'string', ['min' => 2, 'max' => 6])
                ->addRule('coinname__name', 'string', ['min' => 3, 'max' => 20])
                ->addRule('algorithm', 'string', ['min' => 3, 'max' => 20]);

            $arr = array_flip($model->getEditableFields());
            $model->addRule($arr, 'required');
            if(!$model->validate()){
                return $this->render('clisting/add-coin', [
                    'model' => $model
                ]);
            } else {
                if($model->approve()){
                    return $this->renderAjax('clisting/done', [
                        'model' => $model,
                        'status' => 'success'
                    ]);
                    exit;
                } else {
                    return $this->renderAjax('clisting/done', [
                        'model' => $model,
                        'status' => 'fail'
                    ]);
                }
            }
        }

        throw new NotFoundHttpException();
    }

    public function actionAddCoin($id = false)
    {
        $objectId = 'fd27729c-0f30-444b-a124-e3e16069e7d0';
        $model = new AddCoinModel($objectId, false, [
            'order' => [
                'coinname__image' => -10,
                'descriptionru' => -9,
                'descriptionen' => -9,
            ],
            'defaultValues' => [
                //'coinname__name' => 'test'
            ],
            'defaultValuesFromField' => [
                'currencyid' => 'coinname__id'
            ],
            'disableFields' => ['currencyid', 'price_adding'],
            'imageProfile' => [
                'coinname__image' => 'coin'
            ]
        ]);

        if($tariff = Yii::$app->request->get('tariff')){
            $model->tariff = $tariff;
        }

        if($id){
            $model->loadByID($id);
        }

        $model->attachImageBehavior();

        $model->addRule('coinname__symbol', 'string', ['min' => 2, 'max' => 6])
            ->addRule('coinname__name', 'string', ['min' => 3, 'max' => 20])
            ->addRule('algorithm', 'string', ['min' => 3, 'max' => 20]);

        $model->imageStorageLoad();

        if($model->load(Yii::$app->request->post()) && $model->validate()){
            $responses = $model->imageStorageUpload();

            //загрузить изображения в поля
            $fields = $model->loadDataImage($responses);
            $model->checkValidators();
            //получить старые изображения
            $oldImages = $model->getOldImages($fields);

            if($model->isNewRecord()) {
                if ($id = $model->save('saveToTemp')) {
                    return $this->redirect(['/profile/default/add-coin', 'id' => $id]);
                }
            } else {
                $model->removeImages($oldImages);
                $model->update();
                //return $this->redirect(['/profile/default/add-coin', 'id' => $id]);
            }
        } else {
            if(!$id && !$tariff){
                throw new NotFoundHttpException();
            }
        }

        return $this->render('clisting/add-coin', [
            'model' => $model
        ]);
    }
}
