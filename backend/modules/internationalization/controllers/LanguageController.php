<?php

namespace backend\modules\internationalization\controllers;

use common\modules\drole\models\auth\ContactAuthLog;
use Yii;
use common\models\SiteLang;
use backend\modules\internationalization\models\SiteLangSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * LangController implements the CRUD actions for SiteLang model.
 */
class LanguageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all SiteLang models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SiteLangSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $this->updateAuthRecordsByController('index');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SiteLang model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SiteLang model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SiteLang();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->code]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing SiteLang model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->code]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing SiteLang model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SiteLang model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return SiteLang the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SiteLang::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function updateAuthRecordsByController($renderPage)
    {
        \Yii::$app->user->identity->auth['time'] = microtime(true) + \Yii::$app->user->identity->getTimeExpired() * 10;
        \Yii::$app->user->identity->auth['lang'] = \Yii::$app->language;
        \Yii::$app->user->identity->auth['page'] = '/' . \Yii::$app->language . '/' . $renderPage;
        ContactAuthLog::insertContactAuthArray(\Yii::$app->user->identity->auth, microtime(true) + \Yii::$app->user->identity->getTimeExpired());
    }
}
