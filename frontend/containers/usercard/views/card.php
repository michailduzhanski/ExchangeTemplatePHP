<div id="profile-card">
<?=$profileForm?>
</div>
<?php
$templates = isset(Yii::$app->params['templates']) ? json_encode(Yii::$app->params['templates']) : '[]';
$templateMaps = isset(Yii::$app->params['templateMaps']) ? json_encode(Yii::$app->params['templateMaps']) : '[]';
$json = isset(Yii::$app->params['json']) ? json_encode(Yii::$app->params['json']) : '[]';
?>

