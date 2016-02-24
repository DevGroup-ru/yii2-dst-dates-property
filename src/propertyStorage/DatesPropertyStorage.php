<?php

namespace DotPlant\DatesProperty\propertyStorage;


use DevGroup\DataStructure\propertyStorage\AbstractPropertyStorage;
use DevGroup\DataStructure\helpers\PropertiesHelper;
use DotPlant\DatesProperty\DatesPropertyModule;
use DotPlant\DatesProperty\models\DatesRange;
use DevGroup\DataStructure\models\Property;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Query;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

class DatesPropertyStorage extends AbstractPropertyStorage
{
    /**
     * @inheritdoc
     */
    public function deleteAllProperties(&$models)
    {
        /** @var \yii\db\Command $command */
        if (count($models) === 0) {
            return;
        }
        /** @var \yii\db\ActiveRecord|\DevGroup\DataStructure\traits\PropertiesTrait $firstModel */
        $firstModel = reset($models);
        $command = $firstModel->getDb()->createCommand()
            ->delete(DatesPropertyModule::buildTableName(get_class($firstModel)), PropertiesHelper::getInCondition($models));
        $command->execute();
    }

    /**
     * Fills $models array models with corresponding binded properties.
     * Models in $models array should be the the same class name.
     *
     * @param ActiveRecord[]|\DevGroup\DataStructure\traits\PropertiesTrait[]|\DevGroup\DataStructure\behaviors\HasProperties[] $models
     *
     * @return ActiveRecord[]|\DevGroup\DataStructure\traits\PropertiesTrait[]|\DevGroup\DataStructure\behaviors\HasProperties[]
     */
    public function fillProperties(&$models)
    {
        /** @var \yii\db\ActiveRecord|\DevGroup\DataStructure\traits\PropertiesTrait|\DevGroup\TagDependencyHelper\TagDependencyTrait $firstModel */
        $firstModel = reset($models);
        $tags = [];
        foreach ($models as $model) {
            $tags[] = $model->objectTag();
        }
        $datesRows = Yii::$app->cache->lazy(function () use ($firstModel, $models) {
            $query = new Query();
            $datesRangeObjectTable = DatesPropertyModule::buildTableName(get_class($firstModel));
            $rows = $query
                ->select('*')
                ->from($datesRangeObjectTable)
                ->where(PropertiesHelper::getInCondition($models))
                ->orderBy([
                    'model_id' => SORT_ASC,
                    'sort_order' => SORT_ASC
                ])
                ->all($firstModel->getDb());
            $values = ArrayHelper::map(
                $rows,
                'id',
                function ($item) {
                    return $item;
                },
                'model_id'
            );
            return $values;
        }, PropertiesHelper::generateCacheKey($models, 'date_ranges'), 86400, $tags);

        foreach ($models as &$model) {
            if (isset($datesRows[$model->id])) {
                $groupedByProperty = ArrayHelper::map(
                    $datesRows[$model->id],
                    'id',
                    function ($item) {
                        return $item;
                    },
                    'property_id'
                );
                foreach ($groupedByProperty as $propertyId => $propertyRows) {
                    /** @var Property $property */
                    $property = Property::loadModel($propertyId);
                    if (null === $property) {
                        continue;
                    }
                    $model->{$property->key} = $propertyRows;
                }
            }
        }
    }

    /**
     * @param ActiveRecord[]|\DevGroup\DataStructure\traits\PropertiesTrait[]|\DevGroup\DataStructure\behaviors\HasProperties[] $models
     *
     * @return boolean
     */
    public function storeValues(&$models)
    {
        if (0 === count($models)) {
            return true;
        }
        $insertArray = [];
        $deleteRows = [];
        /** @var ActiveRecord|\DevGroup\DataStructure\traits\PropertiesTrait $firstModel */
        $firstModel = reset($models);
        $datesTable = DatesPropertyModule::buildTableName(get_class($firstModel));
        foreach ($models as $model) {
            foreach ($model->changedProperties as $propertyId) {
                /** @var Property $propertyModel */
                $propertyModel = Property::loadModel($propertyId);
                if (null === $propertyModel) {
                    continue;
                }
                if ($propertyModel->storage_id === $this->storageId) {
                    if (isset($deleteRows[$model->id])) {
                        $deleteRows[$model->id][] = $propertyId;
                    } else {
                        $deleteRows[$model->id] = [$propertyId];
                    }
                    $insertArray += self::buildInsertArray($model, $propertyModel);
                }
            }
        }
        if (false === empty($deleteRows)) {
            $firstModel->getDb()->createCommand()->delete(
                $datesTable,
                self::buildRemoveCommand($deleteRows)
            )->execute();
        }
        if (true === empty($insertArray)) {
            return true;
        }
        $cmd = $firstModel->getDb()->createCommand();
        return $cmd
            ->batchInsert(
                $datesTable,
                [
                    'model_id', 'property_id', 'date_from', 'date_to', 'days_from', 'days_to', 'price', 'sort_order'
                ],
                $insertArray
            )->execute() > 0;
    }

    /**
     * Builds remove condition
     *
     * @param $deleteRows
     * @return array
     */
    private static function buildRemoveCommand($deleteRows)
    {
        $condition = ['or'];
        foreach ($deleteRows as $modelId => $propertyIds) {
            foreach ($propertyIds as $pId) {
                $condition[] = ['model_id' => $modelId, 'property_id' => $pId];
            }
        }
        return $condition;
    }

    /**
     * Builds array of rows for batch insert
     *
     * @param ActiveRecord $model
     * @param Property $property
     * @return array
     */
    private static function buildInsertArray(ActiveRecord $model, Property $property)
    {
        $data = [];
        $values = $model->{$property->key};
        if (false === empty($values) && true === is_array($values)) {
            foreach ($values as $i => $row) {
                $data[] = [
                    $model->id,
                    $property->id,
                    (int)$row['date_from'],
                    (int)$row['date_to'],
                    (int)$row['days_from'],
                    (int)$row['days_to'],
                    (float)$row['price'],
                    $i,
                ];
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function afterPropertyDelete(Property &$property)
    {
        $classNames = static::getApplicablePropertyModelClassNames($property->id);
        foreach ($classNames as $className) {
            $tableName = DatesPropertyModule::buildTableName($className);
            $className::getDb()
                ->createCommand()
                ->delete(
                    $tableName,
                    ['property_id' => $property->id]
                )
                ->execute();
        }
    }
}