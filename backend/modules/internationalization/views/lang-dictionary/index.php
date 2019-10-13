<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\internationalization\models\SiteLangDictionarySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Dictionaries');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-lang-dictionary-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('backend', 'Add message'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
                'attribute' => 'category',
                'filter' => \common\models\SiteLangCategory::find()->select(['name', 'code'])->indexBy('code')->column(),
                'value' => function (\common\models\SiteLangDictionary $model){
                    return $model->category0->name;
                }
            ],
/*            [
                'attribute' => 'language',
                'filter' => \common\models\SiteLang::find()->select(['name', 'code'])->indexBy('code')->column(),
                'value' => function (\common\models\SiteLangDictionary $model){
                    return $model->language0->name;
                }
            ],*/
            'text:ntext',

            [
                'class' => 'yii\grid\ActionColumn',
                'buttons' => [
                    'view' => function($url) {
                        return Html::a(
                            '<i class="fa fa-database"></i>',
                            $url,
                            []
                        );
                    },
                    'edit' => function($url) {
                        return Html::a(
                            '<i class="pe-7s-pen"></i>',
                            $url,
                            []
                        );
                    },
                    'delete' => function($url) {
                        return Html::a(
                            '<i class="pe-7s-trash"></i>',
                            $url,
                            []
                        );
                    }
                ]
            ],
        ],
    ]); ?>
</div>
