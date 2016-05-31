<?php

namespace bstuff\yii2images;

use Yii;
use yii\base\Exception;
use bstuff\yii2images\models\Image;


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

    public function actionDeleteImage() {
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

	    if ($imageId = Yii::$app->request->post('imageId')) {
	      $model = Image::findOne($imageId);
	    } else {
  	    throw new Exception('Не нашли такую картинку');
	    }

	    return $model->delete();
    }
    
    public function actionMoveImage() {
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      $direction = Yii::$app->request->post('direction');
      
	    if ($imageId = Yii::$app->request->post('imageId')) {
	      $image = Image::findOne($imageId);
	    } else {
  	    throw new Exception('Не нашли такую картинку');
	    }
      
			if (!$image->isMain) {
				$image2 = Image::find()
					->andWhere([($direction == 'up') ? '<' : '>', 'sortOrder', $image->sortOrder])
					->andWhere(['=', 'modelTableName', $image->modelTableName])
					->andWhere(['=', 'itemId', $image->itemId])
					->andWhere('`isMain` != 1 OR  `isMain` is NULL')
					->orderBy(['sortOrder' => ($direction == 'up') ? SORT_DESC : SORT_ASC])
					->one()
				;

				if ($image2) Image::swapImages($image2, $image);
			}

	    return true;
    }



    public function actionMoveUp($id)
    {
			$image = RicoImage::findOne($id);
			if ($image) {
				if (!$image->isMain) {
					$image2 = RicoImage::find()
						->andWhere(['<', 'id', $image->id])
						->andWhere(['=', 'modelName', $image->modelName])
						->andWhere(['=', 'itemId', $image->itemId])
						->andWhere('`isMain` != 1 OR  `isMain` is NULL')
						->orderBy(['id' => SORT_DESC])
						->one()
					;
					
					if ($image2) Image::swapImages($image2, $image);
				}
			}
        return $this->redirect(['post/view-gallery', 'id' => $image->itemId]);
    }

}
