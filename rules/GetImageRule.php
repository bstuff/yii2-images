<?php

namespace bstuff\yii2images\rules;

use yii\web\UrlRuleInterface;
use yii\base\Object;
use bstuff\yii2images\models\Image;

class GetImageRule extends Object implements UrlRuleInterface
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
        
        if ($route === 'yii2images/images/placeholder') {
            if (isset($params['name']) ) {
              return $this->prefix . '/placeholders/' . $params['name'] . Image::getImageSuffix($params);
            }
        }
        return false;  // this rule does not apply
    }

    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (preg_match("/" . $this->prefix . "\/(\d+)_([x\d]+)?(_fit)?/", $pathInfo, $matches)) {
          $params = [];
          if (Image::find($matches[1])->exists()) {
            $params['id'] = $matches[1];
            if (isset($matches[2])) {
              if (preg_match('/((^)|(\d+))x(($)|(\d+))/', $matches[2], $size)) {
                if (isset($size[3])) if($size[3]) $params['x'] = (int)$size[3];
                if (isset($size[4])) if($size[4]) $params['y'] = (int)$size[4];
              }
                if (isset($matches[3])) if($matches[3]=='_fit') $params['fit'] = true;
            }
            return ['/yii2images/images/get-image', $params];
          }
        }


        if (preg_match("/" . $this->prefix . "\/placeholders\/(\w+)/", $pathInfo, $matches)) {
          $str = $matches[1];
          $params = [];
          if (substr($str, -4) == '_fit') {
            $params['fit'] = true;
            $str = substr($str, 0, -4);
          }
          if (preg_match("/\w+_([\dx]+)/", $str, $matches)) {
            if (isset($matches[1])){
              if ($this->checkValidSizeSuffix($matches[1])) {
                $str = substr($str, 0, -1-strlen($matches[1]));
                $params = array_merge($params, $this->parseSizeSuffix($matches[1]));
              }
            }
          }
          $params['name'] = $str;

          return ['/yii2images/images/placeholder', $params];
        }

        return false;  // this rule does not apply
    }
    
    private function checkValidSizeSuffix($suffix) {
      return preg_match("/\d+x\d+/", $suffix)
        || preg_match("/x\d+/", $suffix)
        || preg_match("/\d+x/", $suffix);
    }
    
    private function parseSizeSuffix($suffix) {
      $params = [];
      if (preg_match('/((^)|(\d+))x(($)|(\d+))/', $suffix, $size)) {
        if (isset($size[3])) if($size[3]) $params['x'] = (int)$size[3];
        if (isset($size[4])) if($size[4]) $params['y'] = (int)$size[4];
      }
      return $params;
    }
}  
  
