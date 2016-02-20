<?php

use DotPlant\DatesProperty\propertyStorage\DatesPropertyStorage;
use DotPlant\DatesProperty\propertyHandler\DatesPropertyHandler;
use DevGroup\DataStructure\models\PropertyHandlers;
use DevGroup\DataStructure\models\PropertyStorage;
use DotPlant\DatesProperty\models\DatesRange;
use yii\db\Migration;

class m160210_074428_initial extends Migration
{
    public function up()
    {
        $this->insert(
            PropertyHandlers::tableName(),
            [
                'name' => 'Dates property',
                'class_name' => DatesPropertyHandler::class,
                'sort_order' => 4,
            ]
        );

        $this->insert(
            PropertyStorage::tableName(),
            [
                'name' => 'Dates property',
                'class_name' => DatesPropertyStorage::class,
                'sort_order' => 4,
            ]
        );
        Yii::$app->cache->flush();
    }

    public function down()
    {
        $this->delete(
            PropertyHandlers::tableName(),
            ['class_name' => DatesPropertyHandler::class]
        );
        $this->delete(
            PropertyStorage::tableName(),
            ['class_name' => DatesPropertyStorage::class]
        );
        Yii::$app->cache->flush();
    }
}
