<?php

namespace DotPlant\DatesProperty\helpers;

use DevGroup\DataStructure\models\ApplicablePropertyModels;
use DotPlant\DatesProperty\DatesPropertyModule;
use yii\db\Migration;
use Yii;

/**
 * Class DatesRangeTableGenerator
 *
 * @package DotPlant\DatesProperty\helpers
 */
class DatesPropertyTableGenerator extends Migration
{
    /**
     * @var DatesPropertyTableGenerator
     */
    public static $instance = null;

    /**
     * @codeCoverageIgnore
     * @return DatesPropertyTableGenerator
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new self;
        }
        return static::$instance;
    }

    /**
     * @param \yii\db\Connection|string|null $db
     * @codeCoverageIgnore
     */
    private function setDb($db)
    {
        if ($db === null) {
            $db = Yii::$app->db;
        } elseif (is_string($db)) {
            $db = Yii::$app->get($db);
        }
        $this->db = $db;
    }

    /**
     * Generates all properties tables for specified $className model
     * @param string $className
     * @param \yii\db\Connection|string|null $db
     */
    public function generate($className, $db = null)
    {
        /** @var \yii\db\ActiveRecord|\DevGroup\DataStructure\traits\PropertiesTrait $className */
        $this->setDb($db);

        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;

        $tableName = DatesPropertyModule::buildTableName($className);
        $this->createTable(
            $tableName,
            [
                'id' => $this->primaryKey(),
                'model_id' => $this->integer()->notNull(),
                'property_id' => $this->integer()->notNull(),
                'date_from' => $this->integer()->notNull(),
                'date_to' => $this->integer()->notNull(),
                'days_from' => $this->integer()->notNull()->defaultValue(1),
                'days_to' => $this->integer()->notNull()->defaultValue(1),
                'price' => $this->string(255),
                'sort_order' => $this->integer()->notNull()->defaultValue(0),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk' . md5($className) . 'DTS',
            $tableName,
            ['model_id'],
            $className::tableName(),
            ['id'],
            'CASCADE'
        );
        $this->checkApplicableRow($className);
    }

    /**
     * @param string $className
     * @param \yii\db\Connection|string|null $db
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function drop($className, $db = null)
    {
        $tableName = DatesPropertyModule::buildTableName($className);
        $this->setDb($db);
        $this->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();
        $this->dropTable($tableName);
        $this->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();
    }

    /**
     * @return \yii\db\Connection the database connection to be used for schema building.
     */
    protected function getDb()
    {
        return $this->db;
    }

    /**
     * @param $className
     */
    protected function checkApplicableRow($className)
    {
        $condition = ['class_name' => $className];
        if (null === ApplicablePropertyModels::findOne($condition)) {
            $this->insert(
                ApplicablePropertyModels::tableName(),
                [
                    'class_name' => $className,
                    'name' => substr($className, strrpos($className, '\\') + 1),
                ]
            );
        }
    }
}
