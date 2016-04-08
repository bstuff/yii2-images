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
    //сколько директорий находится выше файла (0-15);
    public $fileStoreDepth = 1;

    public $controllerNamespace = 'bstuff\yii2images\controllers';
    public $className;
    public $placeHolderPath;

    public $thumbnailSize = '150x150';
    public $waterMark = false;
    public $placeholders;

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
        return '';
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
        
        if ($this->fileStoreDepth > 15) $this->fileStoreDepth = 15;
        if ($this->fileStoreDepth < 0) $this->fileStoreDepth = 0;
        $this->placeholders = array_merge((array)$this->placeholders, ['default' => 'placeholder.png']);
    }

    public function getPlaceHolder(){

        if($this->placeHolderPath){
            return new PlaceHolder();
        }else{
            return null;
        }
    }
    
    // '150x100_fit'
    public function parseSuffix($suffix = '') {
        if (preg_match("/([x\d]+)?(_fit)?/", $suffix, $matches)) {
        $params = [];
          if (isset($matches[1])) {
            if (preg_match('/((^)|(\d+))x(($)|(\d+))/', $matches[1], $size)) {
              if (isset($size[3])) if($size[3]) $params['x'] = (int)$size[3];
              if (isset($size[4])) if($size[4]) $params['y'] = (int)$size[4];
            }
              if (isset($matches[3])) if($matches[3]=='_fit') $params['fit'] = true;
          }
          return $params;
        }
        return false;
    }
}
