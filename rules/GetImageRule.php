<?php

namespace bstuff\yii2images\rules;

use yii\web\UrlRuleInterface;
use yii\web\UrlRule;
use bstuff\yii2images\models\Image;

class GetImageRule extends UrlRule
{
    public $prefix = 'model-image';
    
    public function createUrl($manager, $route, $params)
    {
        if ($route === 'yii2images/images/get-image') {
            if (isset($params['id']) ) {
              if ($image = Image::find()
                ->where(['id' => $params['id']])
                ->one()) {
                  return $this->prefix . '/' . $image->id . $image->getImageSuffix($params);
                }
            }
        }
        return false;  // this rule does not apply
    }

    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        $params = [];
        if (preg_match("/model-image\/(\d+)_([x\d]+)(_fit)?/", $pathInfo, $matches)) {
          
          if (Image::find($matches[1])->exists()) {
            $params['id'] = $matches[1];
            if (preg_match('/((^)|(\d+))x(($)|(\d+))/', $matches[2], $size)) {
              if (isset($size[3])) if($size[3]) $params['x'] = (int)$size[3];
              if (isset($size[4])) if($size[4]) $params['y'] = (int)$size[4];
            }
              if (isset($matches[3])) if($matches[3]=='_fit') $params['fit'] = true;
            return ['/yii2images/images/get-image', $params];
          }
        }
        return false;  // this rule does not apply
    }
}  
  
