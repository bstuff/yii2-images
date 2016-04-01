<?php

namespace bstuff\yii2images\controllers;

use Yii;

use yii\web\Controller;
use bstuff\yii2images\models\Image;
use bstuff\yii2images\models\PlaceHolder;
use bstuff\yii2images\ModuleTrait;

class ApiController extends Controller
{
    use ModuleTrait;
		
    public function actionIndex()
    {
        echo "Hello, man. It's ok, dont worry.";
    }
    
    public function actionGetImages($id=null, $name=null, $modelTableName=null) {
			Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
	    $response = [];
	    $images = Image::find()
	    	->andFilterWhere([
		    	'name' => $name,
		    	'modelTableName' => $modelTableName,
	    	])
	    	->orderBy(['id'=>SORT_DESC])
	    	->limit(1000)
	    	->all();
	    if($images) foreach ($images as &$img) {
		    $response[] = [
			    'id' => $img->id,
			    'link' => $img->getUrl(),
			    'thumbnail' => $img->getUrl(['x'=>150, 'y'=>150]),
			    'isMain' => $img->isMain,
			    'name' => $img->name,
			    'modelTableName' => $img->modelTableName,
		    ];
	    }
	    
	    return $response;
    }
}