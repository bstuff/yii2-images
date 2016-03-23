<?php

namespace bstuff\yii2images\controllers;

use yii\web\Controller;
use yii;
use bstuff\yii2images\models\Image;
use bstuff\yii2images\ModuleTrait;

class ImagesController extends Controller
{
    use ModuleTrait;

    public function actionIndex()
    {
        echo "Hello, man. It's ok, dont worry.";
    }
    
    public function actionGetImage($id, $x=null, $y=null, $fit=null) {
      $params = [
        'x' => $x,
        'y' => $y,
        'fit' => $fit,
      ];
      
      if ($image = Image::findOne($id)) {
        return \Yii::$app->response->sendFile($image->getPath($params));
      } else {
          throw new \yii\web\HttpException(404, 'There is no image');
      }
    }
}