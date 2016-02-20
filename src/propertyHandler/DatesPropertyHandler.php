<?php

namespace DotPlant\DatesProperty\propertyHandler;


use DevGroup\DataStructure\propertyHandler\AbstractPropertyHandler;
use DotPlant\DatesProperty\validators\DatesPropertyValidator;
use DevGroup\DataStructure\models\Property;

/**
 * Class DatesPropertyHandler
 *
 * @package DotPlant\DatesProperty\propertyHandler
 */
class DatesPropertyHandler extends AbstractPropertyHandler
{
    /**
     * Get validation rules for a property.
     *
     * @param Property $property
     * @return array of ActiveRecord validation rules
     */
    public function getValidationRules(Property $property)
    {
        return [
            [$property->key, DatesPropertyValidator::class, 'skipOnEmpty' => true],
        ];
    }
}