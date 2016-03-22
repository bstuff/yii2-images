<?php

namespace bstuff\yii2images;

use yii\base\Exception;


trait ModuleTrait
{
    /**
     * @var null|\bstuff\yii2images\Module
     */
    private $_module;

    /**
     * @return null|\bstuff\yii2images\Module
     */
    protected function getModule()
    {
        if ($this->_module == null) {
            $this->_module = \Yii::$app->getModule('yii2images');
        }

        if(!$this->_module){
            throw new Exception("Yii2 images module not found, may be you didn't add it to your config?");
        }

        return $this->_module;
    }
}