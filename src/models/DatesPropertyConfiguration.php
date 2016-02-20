<?php
namespace DotPlant\DatesProperty\models;

use DevGroup\ExtensionsManager\models\BaseConfigurationModel;
use DotPlant\DatesProperty\commands\DatesPropertyController;
use DotPlant\DatesProperty\controllers\DatesRangesController;
use DotPlant\DatesProperty\DatesPropertyModule;
use Yii;

class DatesPropertyConfiguration extends BaseConfigurationModel
{
    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $attributes = [
            'dateDisplayFormat'
        ];

        parent::__construct($attributes, $config);
        /** @var DatesPropertyModule $module */
        $module = DatesPropertyModule::module();
        $this->dateDisplayFormat = $module->dateDisplayFormat;
    }

    /**
     * Validation rules for this model
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['dateDisplayFormat', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dateDisplayFormat' => Yii::t('dotplant.dates-property', 'Dates display format'),
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    public function webApplicationAttributes()
    {
        return [
            'controllerMap' => [
                'dates-ranges' => DatesRangesController::class,
            ]
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
     *
     * @return array
     */
    public function consoleApplicationAttributes()
    {
        return [
            'controllerMap' => [
                'dates-property' => DatesPropertyController::class,
            ]
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web and console.
     *
     * @return array
     */
    public function commonApplicationAttributes()
    {
        return [
            'bootstrap' => [
                'dates-property',
            ],
            'components' => [
                'i18n' => [
                    'translations' => [
                        'dotplant.dates-property' => [
                            'class' => 'yii\i18n\PhpMessageSource',
                            'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'messages',
                        ]
                    ]
                ],
            ],
            'modules' => [
                'dates-property' => [
                    'class' => DatesPropertyModule::class,
                    'dateDisplayFormat' => $this->dateDisplayFormat,
                ],
                'datecontrol' => [
                    'class' => 'kartik\datecontrol\Module',
                ]
            ],
        ];
    }

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    public function appParams()
    {
        return [];
    }

    /**
     * Returns array of aliases that should be set in common config
     *
     * @return array
     */
    public function aliases()
    {
        return [
            '@DotPlant/DatesProperty' =>  realpath(dirname(__DIR__)),
        ];
    }
}
