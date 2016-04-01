<?php

namespace bstuff\yii2images;

use yii\base\Exception;


trait ControllerTrait
{
    public function actionUpload($id) {
	    $model = $this->findModel($id);
	    edump($model);
    }
}
