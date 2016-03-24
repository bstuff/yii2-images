<?php

namespace bstuff\yii2images\controllers;

use Yii;

use yii\web\Controller;
use bstuff\yii2images\models\Image;
use bstuff\yii2images\models\PlaceHolder;
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
        $path = $image->getPath($params);

        header('Content-Type: ' . $image->getMimeType());
        echo(file_get_contents($path));
        exit;

        return Yii::$app->response->sendFile($image->getPath($params));
      } else {
          throw new \yii\web\HttpException(404, 'There is no image');
      }
    }
    
    public function actionPlaceholder($name='default', $x=null, $y=null, $fit=null) {
      $params = [
        'x' => $x,
        'y' => $y,
        'fit' => $fit,
      ];
      
      $placeHolder = new PlaceHolder([
        'placeholderName' => $name,
        'filePath' => $this->getModule()->placeholders[$name],
      ]);
      header('Content-Type: ' . $placeHolder->getMimeType());
      echo(file_get_contents($placeHolder->getPath($params)));
      exit;
    }
}