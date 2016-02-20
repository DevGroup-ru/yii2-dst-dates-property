<?php

namespace DotPlant\DatesProperty\assets;


use yii\web\AssetBundle;
use Yii;


class DatesPropertyAsset extends AssetBundle
{
    public $sourcePath = '@DotPlant/DatesProperty/assets/';
    public $js = [
        'js/dates-property.js',
    ];
    public $depends = [
        'kartik\date\DatePickerAsset',
    ];
}