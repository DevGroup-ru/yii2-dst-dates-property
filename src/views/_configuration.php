<?php
/**
 * @var $this \yii\web\View
 */
echo $form->field($model, 'dateDisplayFormat');
?>
<div class="panel box box-primary">
    <div class="box-header with-border">
        <i class="fa fa-info"></i>
        <h3 class="box-title"><?= Yii::t('dotplant.dates-property', 'Help tips')?></h3>
    </div>
    <div class="box-body">
        <blockquote>
            <?= Yii::t('dotplant.dates-property', 'Please refer to {link} for correct usage', ['link' => '<a href="http://php.net/manual/ru/function.date.php" rel="nofollow" target="_blank">http://php.net/manual/ru/function.date.php</a>'])?>
        </blockquote>
    </div>
</div>