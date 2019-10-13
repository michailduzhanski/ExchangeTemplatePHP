<?php
use \yii\helpers\Html;

if (!Yii::$app->user->isGuest):?>
<div class="menu-on-left">
    <div class="sidebar" data-color="blue" data-image="/img/sidebar-4.jpg">

        <div class="sidebar-wrapper">
            <div class="logo">
                <a href="" class="simple-text">
                    <?=Yii::$app->user->identity->getContact()->login ?>
                </a>
            </div>

            <ul class="nav">
                <li class="nav-item">
                    <?=Html::a('<i class="pe-7s-graph"></i><p>'. Yii::t('backend', 'Mainpage') .'</p>', ['/site/index'], ['class' => 'nav-link']) ?>
                </li>
                <li class="nav-item">
                    <a href="#subnav_internationalization" class="nav-link" data-toggle="collapse" aria-expanded="false">
                        <i class="pe-7s-flag"></i>
                        <p>i18n <b class="caret"></b></p>
                    </a>
                    <div class="collapse" id="subnav_internationalization">
                        <ul class="nav">
                            <li class="nav-item">
                                <?=Html::a(
                                        '<i class="pe-7s-network"></i>' . Yii::t('backend', 'All languages'), ['/internationalization/language/index'])
                                ?>
                            </li>
                            <li class="nav-item">
                                <?=Html::a(
                                        '<i class="pe-7s-network"></i>' . Yii::t('backend', 'Dictionary categories'),
                                        ['/internationalization/lang-category/index'])
                                ?>
                            </li>
                            <li class="nav-item">
                                <?=Html::a(
                                        '<i class="pe-7s-network"></i>' . Yii::t('backend', 'Dictionaries'),
                                        ['/internationalization/lang-dictionary/index'])
                                ?>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="#subnav_settings" class="nav-link" data-toggle="collapse" aria-expanded="false">
                        <i class="pe-7s-settings"></i>
                        <p><?=Yii::t('backend', 'Settings')?> <b class="caret"></b></p>
                    </a>
                    <div class="collapse" id="subnav_settings">
                        <ul class="nav">
                            <li class="nav-item">
                                <?=Html::a('<i class="pe-7s-network"></i>'. Yii::t('backend', 'Data objects') , ['/site/objects-list']) ?>
                            </li>
                            <li class="nav-item">
                                <?=Html::a('<i class="pe-7s-network"></i>' . Yii::t('backend', 'Assemblies'), ['/site/assemblies-list']) ?>
                            </li>
                            <li class="nav-item">
                                <?=Html::a('<i class="pe-7s-network"></i>' . Yii::t('backend', 'Filters'), ['/site/filters-list']) ?>
                            </li>
                            <li class="nav-item">
                                <?=Html::a('<i class="pe-7s-network"></i>' . Yii::t('backend', 'Sort'), ['/site/sorting-list']) ?>
                            </li>
                            <li class="nav-item">
                                <?=Html::a('<i class="pe-7s-network"></i>' . Yii::t('backend', 'Access Rules'), ['/site/accessrules-list']) ?>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>