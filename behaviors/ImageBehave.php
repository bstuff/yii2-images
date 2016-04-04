<?php

namespace bstuff\yii2images\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use bstuff\yii2images\ModuleTrait;
use bstuff\yii2images\models\Image;
use bstuff\yii2images\models\PlaceHolder;


class ImageBehave extends Behavior
{
    use ModuleTrait;
    
    public $placeholderName = 'default';
    
    /**
     * @var ActiveRecord|null Model class, which will be used for storing image data in db, if not set default class(models/Image) will be used
     */

    /**
     *
     * Method copies image file to module store and creates db record.
     *
     * @param $absolutePath
     * @param bool $isFirst
     * @return bool|Image
     * @throws \Exception
     */
    public function attachImage($absolutePath, $params = [])
    {
        if(!preg_match('#http#', $absolutePath)){
            if (!file_exists($absolutePath)) {
                throw new \Exception('File not exist! :'.$absolutePath);
            }
        }

        if (!$this->owner->primaryKey) {
            throw new \Exception('Owner must have primaryKey when you attach image!');
        }
        
        $newFile = $this->getNewFilename($absolutePath);

        FileHelper::createDirectory($newFile['absoluteDir'], 0775, true);

        copy($absolutePath, $newFile['newAbsolutePath']);

        if (!file_exists($newFile['newAbsolutePath'])) {
            throw new \Exception('Cant copy file! ' . $absolutePath . ' to ' . $newFile['newAbsolutePath']);
        }

        $name = isset($params['name']) ? $params['name'] : null;
        $modelTableName = preg_replace('/[{}%]/', '', $this->owner->tableName());
        $filePath = $newFile['pictureSubDir'] . DIRECTORY_SEPARATOR . $newFile['filename'];
        $image = new Image([
          'itemId' => $this->owner->primaryKey,
          'filePath' => $filePath,
          'modelTableName' => $modelTableName,
          'name' => $name,
        ]);
        $image->sortOrder = $image->find()->where(['itemId' => $image->itemId, 'modelTableName' => $image->modelTableName, 'name' => $image->name])->max('sortOrder') + 1;

        if(!$image->save()){
            $ar = array_shift($image->getErrors());

            unlink($newFile['newAbsolutePath']);
            throw new \Exception(array_shift($ar));
            return false;
        }

        $img = $this->owner->getImage();
        
        //If main image not exists
        $isMain = isset($params['main']) ? $params['main'] : false;
        if(
            is_object($img) && get_class($img)=='bstuff\yii2images\models\PlaceHolder'
            or
            $img == null
            or
            $isMain
        ){
            $this->setMainImage($image);
        }

        return $image;
    }

    /**
     * Sets main image of model
     * @param $img
     * @throws \Exception
     */
    public function setMainImage($img)
    {
        if ($this->owner->primaryKey != $img->itemId) {
            throw new \Exception('Image must belong to this model');
        }

        /* @var $img Image */
        $img->setMain(true);
        $img->save();

        $this->owner->clearImagesCache();
    }


    /**
     * Clear all images cache (and resized copies)
     * @return bool
     */
    public function clearImagesCache()
    {
        return true;
        $cachePath = $this->getModule()->getCachePath();
        $subdir = $this->getModule()->getModelSubDir($this->owner);

        $dirToRemove = $cachePath . '/' . $subdir;
        edump($dirToRemove);

        if (preg_match('/' . preg_quote($cachePath, '/') . '/', $dirToRemove)) {
            FileHelper::removeDirectory($dirToRemove);
            //exec('rm -rf ' . $dirToRemove);
            return true;
        } else {
            return false;
        }
    }


    /**
     * Returns model images
     * First image alwats must be main image
     * @return array|yii\db\ActiveRecord[]
     */
    public function getImages($params = [])
    {
	    	$query = Image::find()
          ->where([
            'itemId' => $this->owner->primaryKey,
            'modelTableName' => preg_replace('/[{}%]/', '', $this->owner->tableName()),
          ])
          ->orderBy(['isMain' => SORT_DESC, 'sortOrder' => SORT_ASC]);
          
        if (isset($params['name']) ) {
	        $query->andWhere(['name' => $params['name']]);
        }
        
        $imgs = $query
          ->all();

        if(!$imgs){
            return [$this->getModule()->getPlaceHolder()];
        }

        return $imgs;
    }


    /**
     * returns main model image
     * @return array|null|ActiveRecord
     */
    public function getImage()
    {
        $img = Image::find()
          ->where([
            'itemId' => $this->owner->primaryKey,
            'modelTableName' => preg_replace('/[{}%]/', '', $this->owner->tableName()),
          ])
          ->orderBy(['isMain' => SORT_DESC, 'sortOrder' => SORT_ASC])
          ->one();

        if(!$img){
            return $this->getPlaceHolder();
        }

        return $img;
    }

    /**
     * returns model image by name
     * @return array|null|ActiveRecord
     */
    public function getImageByName($name)
    {
        if ($this->getModule()->className === null) {
            $imageQuery = Image::find();
        } else {
            $class = $this->getModule()->className;
            $imageQuery = $class::find();
        }
        $finder = $this->getImagesFinder(['name' => $name]);
        $imageQuery->where($finder);
        $imageQuery->orderBy(['isMain' => SORT_DESC, 'id' => SORT_ASC]);

        $img = $imageQuery->one();
        if(!$img){
            return $this->getModule()->getPlaceHolder();
        }

        return $img;
    }

    /**
     * Remove all model images
     */
    public function removeImages()
    {
        $images = $this->owner->getImages();
        if (count($images) < 1) {
            return true;
        } else {
            foreach ($images as $image) {
                $this->owner->removeImage($image);
            }
        }
    }

    /**
     * removes concrete model's image
     * @param Image $img
     * @throws \Exception
     */
    public function removeImage(Image $img)
    {
        if ($this->owner->primaryKey != $img->itemId) {
            throw new \Exception('Image must belong to this model');
        }
        
        return $img->delete();
    }
    
    private function getNewFilename($absolutePath){
        $basename = md5_file($absolutePath);
        $ext = pathinfo($absolutePath, PATHINFO_EXTENSION) ? ('.' . pathinfo($absolutePath, PATHINFO_EXTENSION)) : '.jpg';
        
        $depth = $this->getModule()->fileStoreDepth;

        $subPath = [];
        for ($i = 0; $i < $depth; $i++){
          $subPath[] = substr($basename, 0, 2);
          $basename = substr($basename, 2);
        }
        $pictureSubDir = implode(DIRECTORY_SEPARATOR, $subPath);
        array_unshift($subPath, $this->getModule()->getStorePath($this->owner));
        $storePath = implode(DIRECTORY_SEPARATOR, $subPath);

        $pictureFileName = $basename . $ext;

        return [
          'newAbsolutePath' => $storePath . DIRECTORY_SEPARATOR . $pictureFileName,
          'filename' => $pictureFileName,
          'pictureSubDir' => $pictureSubDir,
          'absoluteDir' => $storePath,
        ];
    }
    
    public function getPlaceHolder() {
      return new PlaceHolder([
        'placeholderName' => $this->placeholderName,
      ]);
    }
}


