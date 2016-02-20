<?php

use DotPlant\DatesProperty\propertyStorage\DatesPropertyStorage;
use DotPlant\DatesProperty\propertyHandler\DatesPropertyHandler;
use DevGroup\DataStructure\models\ApplicablePropertyModels;
use DevGroup\DataStructure\models\PropertyPropertyGroup;
use DevGroup\DataStructure\models\PropertyHandlers;
use DevGroup\DataStructure\models\PropertyStorage;
use DotPlant\DatesProperty\DatesPropertyModule;
use DevGroup\DataStructure\models\Property;
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
        $handlerId = PropertyHandlers::findOne(['class_name' => DatesPropertyHandler::class])->id;
        $propIds = Property::find()
            ->select('id')
            ->where(['property_handler_id' => $handlerId])
            ->column();
        $this->delete(
            PropertyPropertyGroup::tableName(),
            ['property_id' => $propIds]
        );
        $this->delete(
            Property::tableName(),
            ['id' => $propIds]
        );
        $this->delete(
            PropertyHandlers::tableName(),
            ['class_name' => DatesPropertyHandler::class]
        );
        $this->delete(
            PropertyStorage::tableName(),
            ['class_name' => DatesPropertyStorage::class]
        );
        $classes = ApplicablePropertyModels::find()
            ->select('class_name')
            ->column();
        foreach ($classes as $className) {
            $tableName = DatesPropertyModule::buildTableName($className);
            $tableSchema = $this->db->schema->getTableSchema($tableName);
            if (null !== $tableSchema) {
                $this->truncateTable($tableName);
            }
        }
        Yii::$app->cache->flush();

    }
}
