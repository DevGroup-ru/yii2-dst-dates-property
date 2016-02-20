<?php

namespace DotPlant\DatesProperty\models;

use DevGroup\DataStructure\models\Property;
use DevGroup\TagDependencyHelper\TagDependencyTrait;
use DotPlant\DatesProperty\DatesPropertyModule;
use Yii;
use arogachev\sortable\behaviors\numerical\ContinuousNumericalSortableBehavior;
use DevGroup\TagDependencyHelper\CacheableActiveRecord;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * Class DatesRange
 * @package DotPlant\DatesProperty\models
 *
 * @property integer id
 * @property integer property_id
 * @property integer date_from
 * @property integer date_to
 * @property integer days_from
 * @property integer days_to
 * @property float price
 * @property integer sort_order
 */
class DatesRange extends ActiveRecord
{
    use TagDependencyTrait;

    /**
     * @param Property|null $property
     * @param array $config
     */
    public function __construct(Property $property = null, $config = [])
    {
        if (null !== $property) {
            $this->property_id = $property->id;
        }
        parent::__construct($config);
    }

    public static function tableName()
    {
        return '{{%dates_range}}';
    }

    public function behaviors()
    {
        return [
            'CacheableActiveRecord' => [
                'class' => CacheableActiveRecord::className(),
            ],
            'ContinuousNumericalSortableBehavior' => [
                'class' => ContinuousNumericalSortableBehavior::className(),
                'sortAttribute' => 'sort_order'
            ]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.dates-property', 'ID'),
            'property_id' => Yii::t('dotplant.dates-property', 'Property ID'),
            'date_from' => Yii::t('dotplant.dates-property', 'Date from'),
            'date_to' => Yii::t('dotplant.dates-property', 'Date to'),
            'days_from' => Yii::t('dotplant.dates-property', 'Days from'),
            'days_to' => Yii::t('dotplant.dates-property', 'Days to'),
            'price' => Yii::t('dotplant.dates-property', 'Price'),
            'sort_order' => Yii::t('dotplant.dates-property', 'Sort order'),
        ];
    }

    public function rules()
    {
        return [
            [['property_id', 'date_from', 'date_to', 'days_from', 'days_to', 'price'], 'required'],
            [['date_from', 'date_to', 'sort_order'], 'integer'],
            ['price', 'double', 'min' => 0],
            [['property_id', 'id'], 'integer', 'on' => 'search'],
            [['days_from', 'days_to'], 'integer', 'min' => 1],
            [['days_from'], 'compare', 'compareAttribute' => 'days_to', 'operator' => '<=', 'skipOnEmpty' => true],
            [['days_to'], 'compare', 'compareAttribute' => 'days_from', 'operator' => '>='],
            [['date_from'], 'compare', 'compareAttribute' => 'date_to', 'operator' => '<=', 'skipOnEmpty' => true],
            [['date_to'], 'compare', 'compareAttribute' => 'date_from', 'operator' => '>='],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProperty()
    {
        return $this->hasOne(Property::class, ['id' => 'property_id']);
    }

    /**
     * @param null $propertyId
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($propertyId = null, $params)
    {
        $query = self::find();
        if ($propertyId !== null) {
            $query->where(['property_id' => $propertyId]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 8,
            ],
        ]);
        if (false === $this->load($params)) {
            return $dataProvider;
        }
        $format = '!' . DatesPropertyModule::module()->dateDisplayFormat;
        // perform filtering
        $query->andFilterWhere(['id' => $this->id]);
        if (false !== $dateTo = \DateTime::createFromFormat($format, $this->date_to)) {
            $query->andFilterWhere(['date_to' => $dateTo->getTimestamp()]);
        }
        if (false !== $dateFrom = \DateTime::createFromFormat($format, $this->date_from)) {
            $query->andFilterWhere(['date_from' => $dateFrom->getTimestamp()]);
        }
        $query->andFilterWhere(['sort_order' => $this->sort_order]);
        return $dataProvider;
    }

    /**
     * @param $propertyId
     * @return $this
     */
    public static function getRanges($propertyId)
    {
        return self::find()->where(['property_id' => $propertyId]);
    }

    public function afterDelete()
    {
        parent::afterDelete();

    }


}