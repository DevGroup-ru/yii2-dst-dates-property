<?php
/**
 * @var \yii\db\ActiveRecord $model
 * @var \DevGroup\DataStructure\models\Property $property
 * @var yii\web\View $this
 */

use DotPlant\DatesProperty\DatesPropertyModule;
use kartik\touchspin\TouchSpin;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;

/** @var \yii\db\ActiveQuery $dataQuery */
\DotPlant\DatesProperty\assets\DatesPropertyAsset::register($this);
$ajaxActionLink = \yii\helpers\Url::to(['/dates-ranges/action']);
$errorMessage = Yii::t('dotplant.dates-property', 'attribute must be set!');
$dateFromLabel = Yii::t('dotplant.dates-property', 'Date from');
$dateToLabel = Yii::t('dotplant.dates-property', 'Date to');
$daysFromLabel = Yii::t('dotplant.dates-property', 'Days from');
$daysToLabel = Yii::t('dotplant.dates-property', 'Days to');
$wrongRangeMessage = Yii::t('dotplant.dates-property', 'Date to value must be equal or greater than Date from!');
$wrongDaysCount = Yii::t('dotplant.dates-property', 'Days to value must be equal or greater than Days from!');
$emptyDatesMessage = Yii::t('dotplant.dates-property', 'Set at least one dates range first!');
$datesAlreadyExists = Yii::t('dotplant.dates-property', 'Dates range with given dates already exists!');
$daysAlreadyExists = Yii::t('dotplant.dates-property', 'Given days range already exists!');
$modelApplicableName = substr(get_class($model), strrpos(get_class($model), '\\'));
$propertyKey = $property->key;
$js = <<<JS
 window.datesProperty = window.datesProperty || {};
 window.datesProperty.actionUrl = '$ajaxActionLink';
 window.datesProperty.errorMessage = '$errorMessage';
 window.datesProperty.dateFromLabel = '$dateFromLabel';
 window.datesProperty.dateToLabel = '$dateToLabel';
 window.datesProperty.wrongRangeMessage = '$wrongRangeMessage';
 window.datesProperty.daysFromLabel = '$daysFromLabel';
 window.datesProperty.daysToLabel = '$daysToLabel';
 window.datesProperty.wrongDaysCount = '$wrongDaysCount';
 window.datesProperty.emptyDatesMessage = '$emptyDatesMessage';
 window.datesProperty.propertyKey = '$propertyKey';
 window.datesProperty.modelApplicableName = '$modelApplicableName';
 window.datesProperty.datesAlreadyExists = '$datesAlreadyExists';
 window.datesProperty.daysAlreadyExists = '$daysAlreadyExists';
JS;
$this->registerJs($js, \yii\web\View::POS_HEAD);
$format = 'php:' . DatesPropertyModule::module()->dateDisplayFormat;
$propertyName = $modelApplicableName . '[' . $propertyKey . ']';
$tLabel = Yii::t('dotplant.dates-property', 'Days') . '&nbsp;\\&nbsp;' . Yii::t('dotplant.dates-property', 'Dates');
$data = $model->{$property->key};
$th = ['0' => "<thead>\n<tr>\n<th style='width: 100px'>{$tLabel}</th>\n"];
$tr = [];
$index = 1;
if (false === empty($data)) {
    $workingCols = [];
    foreach ($data as $column) {
        $key = $column['date_from'] . '%' . $column['date_to'];
        if (false === isset($th[$key])) {
            $workingCols[] = ['date_from' => $column['date_from'], 'date_to' => $column['date_to']];
            $th[$key] = "<th data-dates-date-from-ts='{$column['date_from']}' data-dates-date-to-ts='{$column['date_to']}'>"
                . Yii::$app->formatter->asDate($column['date_from'], $format)
                . "&nbsp;&rarr;&nbsp;"
                . Yii::$app->formatter->asDate($column['date_to'], $format)
                . "&nbsp;<button type='button' class='btn btn-danger btn-xs pull-right' data-range-action='delete-col'><i class='fa fa-close'></i></button>"
                . "</th>";
        }
    }
    foreach ($workingCols as $i => $colData) {
        foreach ($data as $row) {
            $bodyKey = $row['days_from'] . '%' . $row['days_to'];
            if ($row['date_from'] == $colData['date_from'] && $row['date_to'] == $colData['date_to']) {
                $trRow = "<td data-range-index='{$index}'>\n"
                    . "<input type='hidden' value='{$row['days_from']}' name='{$propertyName}[{$index}][days_from]'>\n"
                    . "<input type='hidden' value='{$row['days_to']}' name='{$propertyName}[{$index}][days_to]'>\n"
                    . "<input type='hidden' value='{$row['date_from']}' name='{$propertyName}[{$index}][date_from]'>\n"
                    . "<input type='hidden' value='{$row['date_to']}' name='{$propertyName}[{$index}][date_to]'>\n"
                    . "<input type='text' value='{$row['price']}' name='{$propertyName}[{$index}][price]'>\n" .
                    "</td>";
                if (false === isset($tr[$bodyKey])) {
                    $tr[$bodyKey] = [
                        "<td data-dates-days-from='{$row['days_from']}' data-dates-days-to='{$row['days_to']}'>"
                        . "{$row['days_from']}&nbsp;&rarr;&nbsp;{$row['days_to']}"
                        . "&nbsp;<button type='button' class='btn btn-danger btn-xs pull-right' data-range-action='delete-row'><i class='fa fa-close'></i></button>"
                        . "</td>\n",
                        $trRow,
                    ];
                } else {
                    $tr[$bodyKey][] = $trRow;
                }
            }
            $index++;
        }
    }
}
$th[] = "</tr>\n</thead>";
$th = implode("\n", $th);
$tbody = '';
foreach ($tr as $one) {
    $tbody .= "<tr>\n" . implode("\n", $one) . "</tr>\n";
}
?>
<div class="box" id="ranges-container">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('dotplant.dates-property', 'Ranges table') ?></h3>
    </div>
    <div class="box-body">
        <table class="table table-bordered" id="ranges-grid">
            <?= $th ?>
            <tbody>
            <?= $tbody ?>
            </tbody>
        </table>
    </div>
    <div class="box-footer clearfix">
        <div class="btn-group">
            <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#add-row-modal">
                <i class="fa fa-plus"></i>&nbsp;<?= Yii::t('dotplant.dates-property', 'Add row') ?>
            </button>
            <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#add-col-modal">
                <i class="fa fa-plus"></i>&nbsp;<?= Yii::t('dotplant.dates-property', 'Add col') ?>
            </button>
            <button type="button" class="btn btn-xs btn-danger" data-range-action="reset-grid">
                <i class="fa fa-trash-o"></i>&nbsp;<?= Yii::t('dotplant.dates-property', 'Remove all') ?>
            </button>
        </div>
    </div>
</div>
<?php
Modal::begin([
    'header' => '<h3>' . Yii::t('dotplant.dates-property', 'Choose range dates') . '</h3>',
    'options' => [
        'id' => 'add-col-modal',
    ],
]);
?>
<div class="row" style="margin-bottom: 8px">
    <div class="col-sm-6">
        <label class="control-label" for="date_from"><?= Yii::t('dotplant.dates-property', 'Date from') ?></label>
        <?= DatePicker::widget(
            [
                'options' => [
                    'id' => 'date-from',
                ],
                'name' => 'date_from',
                'convertFormat' => true,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => $format,
                ],
                'pluginEvents' => [
                    'changeDate' => '$.datePickerRules',
                ],
            ]
        ); ?>
    </div>
    <div class="col-sm-6">
        <label class="control-label" for="date_to"><?= Yii::t('dotplant.dates-property', 'Date to') ?></label>
        <?= DatePicker::widget(
            [
                'options' => [
                    'id' => 'date-to',
                ],
                'name' => 'date_to',
                'convertFormat' => true,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => $format,
                    'useCurrent' => false,
                ],
                'pluginEvents' => [
                    'changeDate' => '$.datePickerRules',
                ],
            ]); ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-10">
        <span class="help-block"></span>
    </div>
    <div class="col-sm-2">
        <button type="button" class="btn btn-info pull-right" data-range-action="add-col">
            <?= Yii::t('dotplant.dates-property', 'Done') ?>
        </button>
    </div>
</div>
<?php Modal::end(); ?>

<?php
Modal::begin([
    'header' => '<h3>' . Yii::t('dotplant.dates-property', 'Choose days count') . '</h3>',
    'options' => [
        'id' => 'add-row-modal',
    ],
]);
?>
<div class="row" style="margin-bottom: 8px">
    <div class="col-sm-6">
        <label class="control-label" for="date_from"><?= Yii::t('dotplant.dates-property', 'Days from') ?></label>
        <?= TouchSpin::widget([
            'options' => [
                'id' => 'days-from',
            ],
            'name' => 'days_from',
            'pluginOptions' => [
                'min' => 1,
            ],
            'pluginEvents' => [
                'change' => '$.changeSpinner',
            ],
        ]) ?>
    </div>
    <div class="col-sm-6">
        <label class="control-label" for="date_to"><?= Yii::t('dotplant.dates-property', 'Days to') ?></label>
        <?= TouchSpin::widget([
            'options' => [
                'id' => 'days-to',
            ],
            'name' => 'days_to',
            'pluginOptions' => [
                'min' => 1,
            ],
            'pluginEvents' => [
                'change' => '$.changeSpinner',
            ],
        ]) ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-10">
        <span class="help-block"></span>
    </div>
    <div class="col-sm-2">
        <button type="button" class="btn btn-info pull-right" data-range-action="add-row">
            <?= Yii::t('dotplant.dates-property', 'Done') ?>
        </button>
    </div>
</div>
<?php Modal::end(); ?>
