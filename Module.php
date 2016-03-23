<?php

namespace bstuff\yii2images;


use bstuff\yii2images\models\PlaceHolder;
use yii;
use bstuff\yii2images\models\Image;

class Module extends \yii\base\Module
{
    public $imagesStorePath = '@app/web/store';

    public $imagesCachePath = '@app/web/imgCache';

    public $graphicsLibrary = 'GD';

    public $controllerNamespace = 'bstuff\yii2images\controllers';

    public $placeHolderPath;

    public $waterMark = false;

    public $className;


    public function getStorePath()
    {
        return Yii::getAlias($this->imagesStorePath);
    }


    public function getCachePath()
    {
        return Yii::getAlias($this->imagesCachePath);

    }

    public function getModelSubDir($model)
    {     
        $modelName = $this->getShortClass($model);
        $modelDir = \yii\helpers\Inflector::pluralize($modelName).'/'. $modelName . $model->id;
        return $modelDir;
    }

    public function init()
    {
        parent::init();
        if (!$this->imagesStorePath
            or
            !$this->imagesCachePath
            or
            $this->imagesStorePath == '@app'
            or
            $this->imagesCachePath == '@app'
        )
            throw new \Exception('Setup imagesStorePath and imagesCachePath images module properties!!!');
        // custom initialization code goes here
    }

    public function getPlaceHolder(){

        if($this->placeHolderPath){
            return new PlaceHolder();
        }else{
            return null;
        }
    }
}
