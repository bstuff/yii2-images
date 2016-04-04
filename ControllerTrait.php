<?php

namespace bstuff\yii2images;

use Yii;
use yii\base\Exception;


trait ControllerTrait
{
    public function actionUploadImage($id) {
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      
	    $model = $this->findModel($id);
	    $name = Yii::$app->request->post('imageName', '0');
	    
	    $uploadedFile = new \yii\web\UploadedFile;
	    $file = $uploadedFile->getInstanceByName($name);
	    
	    if (!$file) $file = $uploadedFile->getInstanceByName(0);
	    if (!$file) $file = $uploadedFile->getInstanceByName(array_keys($_FILES)[0]);
	    if (!$file) throw new \yii\base\ErrorException('uploaded file not found');
	    
	    $model->attachImage($file->tempName, [
  	    'main' => Yii::$app->request->post('isMain', false),
  	    'name' => ($name === '0') ? null : $name,
	    ]);
	    
	    return true;
    }
}
