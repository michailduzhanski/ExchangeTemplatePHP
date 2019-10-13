<?php
/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;


?>
<?php $this->beginPage() ?>
<?php //AppAsset::register($this) ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <?= $this->render('@app/views/layouts/header', $_params_) ?>
    <body>
        <?php $this->beginBody() ?>
        
        <div class="wrapper">
            <?= $this->render('@app/views/layouts/leftmenu', $_params_) ?>
            <div class="main-panel">
                <?php
                NavBar::begin([
                    'innerContainerOptions' => ['class' => 'container-fluid'],
                    'brandLabel' => Yii::$app->name,
                    'brandUrl' => Yii::$app->homeUrl,
                    'options' => [
                        'class' => 'navbar navbar-default navbar-fixed',
                    ],
                ]);
                $menuItems = [
                    ['label' => Yii::t('app', 'Home'), 'url' => ['/site/index']],
                ];
                $menuItems[] = common\modules\lng\widgets\ListWidget::widget();
                if (Yii::$app->user->isGuest) {
                    $menuItems[] = ['label' => Yii::t('app', 'Login'), 'url' => ['/site/login']];
                } else {
                    $menuItems[] = '<li>'
                            . Html::beginForm(['/site/logout'], 'post')
                            . Html::submitButton(
                                    Yii::t('app', 'Logout ({username})', [
                                            'username' =>  Yii::$app->user->identity->getContact()->login
                                    ]), ['class' => 'btn btn-link logout ']
                            )
                            . Html::endForm()
                            . '</li>';
                }
                $menuItems[] = '<li class="separator hidden-lg"></li>';
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-right'],
                    'items' => $menuItems,
                ]);
                NavBar::end();
                ?>

                <div class="content">
                    <div class="container-fluid">
                        <?= Alert::widget() ?>
                        <?= $content ?>
                    </div>
                </div>
                <?= $this->render('@app/views/layouts/footer', $_params_) ?>
            </div>
        </div>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
