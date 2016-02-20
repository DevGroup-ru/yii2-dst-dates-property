<?php
namespace DotPlant\DatesProperty;

use DevGroup\AdminUtils\events\ModelEditForm;
use DevGroup\DataStructure\Properties\actions\EditProperty;
use DotPlant\DatesProperty\propertyHandler\DatesPropertyHandler;
use yii\base\Module;
use Yii;
use yii\web\View;

class DatesPropertyModule extends Module
{
    const DATES_TBL_SUFFIX = '_dates';

    public $dateDisplayFormat = 'd-m-Y';

    /**
     * @return self Module instance in application
     */
    public static function module()
    {
        $module = Yii::$app->getModule('dates-property');
        if ($module === null) {
            $module = new self('dates-property');
        }
        return $module;
    }

    /**
     * @param $className
     * @return string
     */
    public static function buildTableName($className)
    {
        /** @var \yii\db\ActiveRecord|\DevGroup\DataStructure\traits\PropertiesTrait $className */
        return '{{%' . preg_replace('%[\{\}`\'\"\%]%', '', $className::tableName()) . self::DATES_TBL_SUFFIX . '}}';
    }
}
