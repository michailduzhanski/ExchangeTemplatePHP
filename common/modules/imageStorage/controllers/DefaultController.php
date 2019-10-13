<?php

namespace common\modules\imageStorage\controllers;

use common\modules\imageStorage\behaviors\ImageStorage;
use common\modules\imageStorage\components\ResponseData;
use common\modules\imageStorage\helpers\ImageStorageHelper;
use common\modules\imageStorage\models\ImageModel;
use common\modules\imageStorage\models\TestModel;
use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class DefaultController extends Controller
{

    /**
     * @var Model
     */
    protected $model;

    public function actionOpenImageDir($path)
    {
        $fileAbsolutePath = $this->module->absolutePath . '/' . $path;
        return $this->openImage($fileAbsolutePath);
    }

    public function actionOpenImageCache($path)
    {
        $fileCachePath = $this->module->cacheAbsolutePath . '/' . $path;
        return $this->openImage($fileCachePath);
    }

    public function actionNoPhotoImage($path)
    {
        $path = Yii::getAlias('@root/data/nophoto/'.$path);

        if (file_exists($path)) {
            $imginfo = getimagesize($path);
            if (isset($imginfo['mime'])) {
                header("Content-type: " . $imginfo['mime']);
                echo readfile($path);
                exit;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Открыть картинку
     * @param $path
     */
    protected function openImage($filePath)
    {
        if (file_exists($filePath)) {
            $imginfo = getimagesize($filePath);
            if (isset($imginfo['mime'])) {
                header("Content-type: " . $imginfo['mime']);
                echo readfile($filePath);
                exit;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Загрузка с помощью ajax
     */
    public function actionAjaxUpload()
    {
        $response = new ResponseData();
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        \Yii::$app->response->on(Response::EVENT_BEFORE_SEND, function ($event) {
            $response = $event->sender;
            if ($response->data !== null) {
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                if ($response->statusCode !== 200 && isset($response->data['message'])) {
                    $response->statusCode = 200;
                    echo Json::encode($response->data['message']);
                    exit;
                }
            }
        });

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if (isset($post['data'])) {
                $data = ImageStorageHelper::decodeData(Yii::$app->ImageStorage, $post['data']);
            }

            if (isset($data['owner']) && isset($data['class']) &&
                isset($data['attribute']) && isset($data['objectId']) &&
                isset($data['recordId']) && isset($data['table'])) {
                Yii::$app->ImageStorage->setPathTemplateKey('{object_id}', $data['objectId']);

                if(isset($data['dynamicModel'])){
                    $attributes = [$data['attribute'] => null];
                    $this->model = new $data['class']($attributes);
                } else {
                    $this->model = new $data['class'];
                }

                $this->model->attachBehavior('imageStorage', [
                    'class' => ImageStorage::class,
                    'uploadFields' => [$data['attribute'] => $data['owner']],
                    'tableFields' => [$data['attribute'] => [$data['table'] => $data['recordId']]],
                    'objects' => $data['objectId']
                ]);

                $uploadedFile = Yii::$app->ImageStorage->getFileFromRequest();
                if ($uploadedFile instanceof UploadedFile) {
                    $this->model->{$data['attribute']} = $uploadedFile;
                    if ($this->model->validate($data['attribute'])) {
                        $oldFilePath = false;
                        if($oldFile = $this->model->getImage($data['attribute'])){
                            $oldFilePath = $oldFile->fullPath;
                        }
                        $response = Yii::$app->ImageStorage->uploadFromAjaxRequest($data['owner']);
                        $response->objectId = $data['objectId'];
                        $response->recordId = $data['recordId'];
                        $response->attribute = $data['attribute'];

                        $isDbSave = Yii::$app->ImageStorage->dbSaver->save($response);

                        if (!$isDbSave) {
                            Yii::$app->ImageStorage->removeFileByFullPath($response->fullPath);

                            $json['error'] = 'Something wrong! Please try again!';
                            echo Json::encode($json);
                            exit;
                        } else {
                            if($oldFilePath)
                                Yii::$app->ImageStorage->removeFileByFullPath($oldFilePath);
                        }
                    } else {
                        $errors = $this->model->getErrors();
                        $errorMsg = '';
                        foreach ($errors as $fields) {
                            foreach ($fields as $error) {
                                $errorMsg .= $error . '<br/>';
                            }
                        }
                        $json['error'] = $errorMsg;
                        echo Json::encode($json);
                        exit;
                    }
                }
            }
        }

        echo $response->ajaxResponse($response::TYPE_FILEINPUT);
        exit;
    }

    /**
     * Удаление с помощью ajax
     * @return bool
     * @throws NotFoundHttpException
     */
    public function actionDelete()
    {
        $post = Yii::$app->request->post();
        if (isset($post['data'])) {
            $data = ImageStorageHelper::decodeData(Yii::$app->ImageStorage, $post['data']);
            if(isset($data['type']) == 'POST')
                return true;
            if (isset($data['object_id']) && isset($data['record_id']) && isset($data['attribute']) && isset($data['name'])) {
                if (isset($data['full_path'])) {
                    $fullPath = $data['full_path'];
                } else {
                    $fullPath = ImageStorageHelper::getFullPathFromObjectRecord(Yii::$app->ImageStorage, $data['object_id'], $data['name']);
                }

                if (Yii::$app->ImageStorage->dbSaver->remove($data['object_id'], $data['record_id'], $data['attribute'])) {
                    Yii::$app->ImageStorage->removeFileByFullPath($fullPath);

                    return true;
                }

                return false;
            }
        }

        throw new NotFoundHttpException();
    }

    public function getImages()
    {
        echo 'test';
        exit;
    }

    public function actionRenderImageField()
    {
        $data = Yii::$app->request->post();
        if(isset($data['object_id']) && isset($data['record_id']) && isset($data['field'])){
            $attributes = [$data['field'] => null];
            $model = new ImageModel($attributes);

            $model->recordId = $data['record_id'];
            $model->addImageStorageBehavior($data['object_id'], $data['field']);
        }

        return $this->renderAjax('droleAjaxFileInput', [
            'model' => $model,
            'field' => $model->field,
            'table' => $model->table,
            'owner' => $model->owner,
            'objectId' => $model->objectId,
            'recordId' => $model->recordId
        ]);
    }
    /*
     * Тестовая форма загрузка. Удалить потом
     */
    public function actionUploadTest()
    {
        //$this->layout = '/main';
        $this->layout = '/auth';

        $model = new TestModel();

        $model->imageStorageLoad();

        if (Yii::$app->request->isPost) {
            //$model->storage->setPathTemplateKey('{object_id}', '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24');
            $responses = $model->imageStorageUpload();

            //загрузить изображения в поля
            $fields = $model->loadDataImage($responses);

            $model->checkValidators();


            //получить старые изображения
            $oldImages = $model->getImages($fields);

            if($model->save()){
                //удалить старые изображения
                $model->removeImages($oldImages);
            }

        }

        return $this->render('test-upload', ['model' => $model]);
    }
}