<?php

namespace frontend\modules\auth\controllers;

use Codeception\Exception\ConfigurationException;
use common\models\ResponsiveUserIdentity;
use common\models\User;
use common\modules\drole\models\auth\ContactAuthLog;
use common\modules\drole\models\gate\APIHandler;
use common\modules\drole\models\wactions\CreateAllWallets;
use frontend\modules\auth\models\ForgotPasswordForm;
use frontend\modules\auth\models\LoginForm;
use frontend\modules\auth\models\RegisterForm;
use frontend\modules\auth\models\ResetPasswordForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `auth` module
 */
class DefaultController extends Controller
{

    public $layout = '/auth';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'denyCallback' => function ($rule, $action) {
                    return $this->goHome();
                },
                'rules' => [
                    [
                        'actions' => ['login', 'registration', 'forgot-password', 'reset-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'user-verification'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ]
            ],
        ];
    }

    public function getViewPath()
    {
        return Yii::getAlias('@frontend/modules/auth/views');
    }


    /**
     * Страница входа
     * @return string
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate() && $model->signIn()) {

                return $this->renderAjax('_login_form2', [
                    'model' => $model
                ]);
            } else {
                if ($model->pincode) {
                    $reg = Yii::$app->session->get('reg');
                    if ($reg && isset($reg['login']) && isset($reg['password'])) {
                        $model->login = $reg['login'];
                        $model->password = $reg['password'];
                        $model->scenario = $model::SCENARIO_SIGNIN;
                        if ($model->validate() && $model->login()) {
                            Yii::$app->Pincode->sendInterval = 0;
                            Yii::$app->Pincode->regenerate(false, false);
                            Yii::$app->session->set('reg', null);
                            return $this->goBack();
                        } else {
                            return $this->renderAjax('_login_form2', [
                                'model' => $model
                            ]);
                        }
                    } else {
                        return $this->render('login', [
                            'model' => $model
                        ]);
                    }
                }
                $model->getError();
            }
        }

        return $this->render('login', [
            'model' => $model
        ]);
    }

    /**
     * Страница регистрации
     * @return string
     */
    public function actionRegistration()
    {
        $model = new RegisterForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->signup()) {
                $loginForm = new LoginForm();
                $loginForm->login = $model->login;
                $loginForm->password = $model->password;
                $loginForm->scenario = $loginForm::SCENARIO_LOGIN;
                if ($loginForm->login())
                    return $this->goHome();
            } else {
                Yii::$app->session->setFlash('message', Yii::t('app', 'Something wrong! Please try again!'));
            }
        }

        return $this->render('registration', [
            'model' => $model
        ]);
    }

    /**
     * Страница Забыли пароль
     * @return string
     */
    public function actionForgotPassword()
    {
        $model = new ForgotPasswordForm();
        $status = false;
        $resetToken = false;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($resetToken = $model->sendResetToken()) {
                    $status = 'success';
                } else {
                    $status = 'error';
                }
            } else {
                $model->getError();
            }
        }

        return $this->render('forgot-password', ['model' => $model, 'status' => $status, 'resetToken' => $resetToken]);
    }

    /**
     * Страница Новый пароль
     * @param bool $token
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionResetPassword($token = false)
    {
        $status = 'success';
        $model = new ResetPasswordForm();
        $model->token = $token;
        $forgotPasswordKey = Yii::$app->session->get('forgotPasswordKey');

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->updatePassword()) {
                    return $this->render('reset-password', [
                        'model' => $model,
                        'status' => 'success-update'
                    ]);
                }
            } else {
                return $this->render('reset-password', [
                    'model' => $model,
                    'status' => $status
                ]);
            }
        }

        if ($token && $forgotPasswordKey) {
            if ($forgotData = $model->getForgotPasswordData()) {
                if ($user = ResponsiveUserIdentity::findIdentity($forgotData->id)) {
                    if (ResponsiveUserIdentity::validateForgotPasswordKey($forgotPasswordKey, $user->getContact()->login)) {
                        return $this->render('reset-password', [
                            'model' => $model,
                            'status' => $status
                        ]);
                    }
                }
            }
        }

        $status = 'error';
        return $this->render('reset-password', [
            'model' => $model,
            'status' => $status
        ]);
    }

    /**
     * Выйти
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        $renderPage = $this->action->id;
        $this->updateAuthRecordsByController($renderPage);
        Yii::$app->user->logout();
        return $this->redirect('/');
    }

    public function updateAuthRecordsByController($renderPage)
    {
        \Yii::$app->user->identity->auth['time'] = microtime(true) + \Yii::$app->user->identity->getTimeExpired() * 10;
        \Yii::$app->user->identity->auth['lang'] = \Yii::$app->language;
        \Yii::$app->user->identity->auth['page'] = '/' . \Yii::$app->language . '/' . $renderPage;
        ContactAuthLog::insertContactAuthArray(\Yii::$app->user->identity->auth, microtime(true) + \Yii::$app->user->identity->getTimeExpired());
    }

    /**
     * Верификация по email
     * @param $key
     * @return \yii\web\Response
     * @throws ConfigurationException
     * @throws NotFoundHttpException
     */
    public function actionUserVerification($key)
    {
        $this->layout = '/main';
        if ($userByToken = ResponsiveUserIdentity::findByAttribute('emailverificationtoken', $key)) {
            if ($userByToken->getId() == Yii::$app->user->id) {
                $user = new User();
                $user->id = Yii::$app->user->id;
                $user->verification = User::STATUS_VERIFED;
                $user->dRole = User::ROLE_SUPERADMIN;
                $createWallets = CreateAllWallets::createWalletsForContact('af09ea17-d47c-452d-93de-2c89157b9d5b', Yii::$app->params['service_id'], $user->id);
                if ($user->updateVerificationStatus() && isset($createWallets['result']) && $createWallets['result'] == 200) {
                    APIHandler::setUserAsCustomerForHysiope($user->id);
                    APIHandler::updateDroleForVerified();
                    return $this->redirect(['/profile/default/index']);
                } else {
                    throw new ConfigurationException('Something wrong');
                }
            } else {
				echo 'Token: ' . $userByToken->getId() . ' != ' . Yii::$app->user->id; exit;
                //throw new ConfigurationException('Token: ' . $userByToken->getId() . ' != ' . Yii::$app->user->id);
            }
        }
        throw new NotFoundHttpException('Page not found');
    }

}
