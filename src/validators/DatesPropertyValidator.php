<?php

namespace DotPlant\DatesProperty\validators;

use yii\validators\Validator;
use Yii;

/**
 * Class DatesPropertyValidator
 * @package DotPlant\DatesProperty\validators
 */
class DatesPropertyValidator extends Validator
{
    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        foreach ($model->$attribute as $row) {
            if (false === isset($row['date_from'])
                || false === isset($row['date_to'])
                || false === isset($row['days_from'])
                || false === isset($row['days_to'])
                || false === isset($row['price'])
            ) {
                $model->addError($attribute,
                    Yii::t('dotplant.dates-property', 'There was an error while trying to add values! Try to flush cache first.')
                );
                return;
            }
            if (false === self::isTs($row['date_from'])) {
                $model->addError($attribute, Yii::t('dotplant.dates-property', 'Date from value must be valid date!'));
            }
            if (false === self::isTs($row['date_to'])) {
                $model->addError($attribute, Yii::t('dotplant.dates-property', 'Date to value must be valid date!'));
            }
            if ((int)$row['date_from'] > (int)$row['date_to']) {
                $model->addError($attribute, Yii::t('dotplant.dates-property', 'Date to value must be equal or greater than Date from!'));
            }
            if (true === empty($row['days_from'])) {
                $model->addError($attribute,
                    Yii::t('dotplant.dates-property', 'Days from') . ' '
                    . Yii::t('dotplant.dates-property', 'attribute must be set!')
                );
            }
            if (true === empty($row['days_to'])) {
                $model->addError($attribute,
                    Yii::t('dotplant.dates-property', 'Days to') . ' '
                    . Yii::t('dotplant.dates-property', 'attribute must be set!')
                );
            }
            if ((int)$row['days_from'] > (int)$row['days_to']) {
                $model->addError($attribute, Yii::t('dotplant.dates-property', 'Days to value must be equal or greater than Days from!'));
            }
            if (true === empty($row['price'])) {
                $model->addError($attribute,
                    Yii::t('dotplant.dates-property', 'Price') . ' '
                    . Yii::t('dotplant.dates-property', 'attribute must be set!')
                );
            }
        }
    }

    /**
     * Checks is given value contains correct timestamp
     *
     * @param $ts
     * @return bool
     */
    protected static function isTs($ts)
    {
        try {
            $nts = date('U', $ts);
        } catch (\Exception $e) {
            $nts = false;
        }
        return $nts === $ts;
    }
}
