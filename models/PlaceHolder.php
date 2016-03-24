<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 05.08.14
 * Time: 18:21
 *
 * TODO: check that placeholder is enable in module class
 * override methods
 */

namespace bstuff\yii2images\models;

/**
 * TODO: check path to save and all image method for placeholder
 */

use yii;

class PlaceHolder extends Image
{
    public $placeholderName = 'default';
    private $itemId = '';

    public function init() {
      parent::init();
      $this->filePath = $this->getModule()->placeholders[$this->placeholderName];
      }
    public function getPathToOrigin()
    {
        $url = Yii::getAlias($this->getModule()->placeHolderPath) . DIRECTORY_SEPARATOR . $this->filePath;;
        
        if (!$url) {
            throw new \Exception('PlaceHolder image must have path setting!!!');
        }
        return $url;
    }

    public function setMain($isMain = true){
        throw new yii\base\Exception('You must not set placeHolder as main image!!!');
    }

    public function save($runValidation = true, $attributeNames = NULL){
        throw new yii\base\Exception('You must not save placeHolder!');
    }
}

