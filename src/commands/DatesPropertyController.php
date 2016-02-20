<?php

namespace DotPlant\DatesProperty\commands;


use DotPlant\DatesProperty\helpers\DatesPropertyTableGenerator;
use yii\console\Controller;

class DatesPropertyController extends Controller
{
    public function actionGenerate($className)
    {
        if (false === class_exists($className)) {
            $this->stderr("Class \"$className\" not found!");
            return 1;
        }
        DatesPropertyTableGenerator::getInstance()->generate($className);
    }

    public function actionDrop($className)
    {
        if (false === class_exists($className)) {
            $this->stderr("Class \"$className\" not found!");
            return 1;
        }
        DatesPropertyTableGenerator::getInstance()->drop($className);
    }
}