<?php


/**
 * This is the model class for table "image".
 *
 * @property integer $id
 * @property string $filePath
 * @property integer $itemId
 * @property integer $isMain
 * @property string $modelName
 * @property string $urlAlias
 */

namespace bstuff\yii2images\models;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\FileHelper;
use bstuff\yii2images\ModuleTrait;
use bstuff\yii2images\Imagine;


class Image extends \yii\db\ActiveRecord
{
    use ModuleTrait;

    //! del
    public function clearCache(){
        $subDir = $this->getSubDur();

        $dirToRemove = $this->getModule()->getCachePath().DIRECTORY_SEPARATOR.$subDir;

        if(preg_match('/'.preg_quote($this->modelName, '/').'/', $dirToRemove)){
            FileHelper::removeDirectory($dirToRemove);

        }

        return true;
    }

    public function getPathToOrigin(){

        $base = $this->getModule()->getStorePath();

        $filePath = $base.DIRECTORY_SEPARATOR.$this->filePath;

        return $filePath;
    }


    public function getSizes()
    {
        $sizes = false;
        if($this->getModule()->graphicsLibrary == 'Imagick'){
            $image = new \Imagick($this->getPathToOrigin());
            $sizes = $image->getImageGeometry();
        }else{
            $image = new \abeautifulsite\SimpleImage($this->getPathToOrigin());
            $sizes['width'] = $image->get_width();
            $sizes['height'] = $image->get_height();
        }

        return $sizes;
    }

    public function getSizesWhen($sizeString){

        $size = $this->getModule()->parseSize($sizeString);
        if(!$size){
            throw new \Exception('Bad size..');
        }



        $sizes = $this->getSizes();

        $imageWidth = $sizes['width'];
        $imageHeight = $sizes['height'];
        $newSizes = [];
        if(!$size['width']){
            $newWidth = $imageWidth*($size['height']/$imageHeight);
            $newSizes['width'] = intval($newWidth);
            $newSizes['height'] = $size['height'];
        }elseif(!$size['height']){
            $newHeight = intval($imageHeight*($size['width']/$imageWidth));
            $newSizes['width'] = $size['width'];
            $newSizes['height'] = $newHeight;
        }

        return $newSizes;
    }

    public function setMain($isMain = true){
        if($isMain){
            $this->isMain = 1;
        }else{
            $this->isMain = 0;
        }

    }

    protected function getSubDur(){
        return \yii\helpers\Inflector::pluralize($this->modelName).'/'.$this->modelName.$this->itemId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%image}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filePath', 'itemId', 'modelTableName'], 'required'],
            [['itemId', 'sortOrder'], 'integer'],
            [['filePath', 'modelName', 'modelTableName'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 64],
            ['isMain', 'boolean'],
            [['sortOrder'], 'default', 'value' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'filePath' => 'File Path',
            'itemId' => 'Item ID',
            'isMain' => 'Is Main',
        ];
    }

    public function getUrl($params = []){
        $params['id'] = $this->id;
        
        $url = Url::toRoute(array_merge(['/'.$this->getModule()->id.'/images/get-image'], $params));
        return $url;
    }

    public function getPath($params = []){
        $filePath = FileHelper::normalizePath($this->getFilepath($params));

/*
if(file_exists($filePath)){
try {
unlink($filePath);
} catch (Exception $e) {}
}
*/
        
        if(!file_exists($filePath)){
            $this->createVersion($params);

            if(!file_exists($filePath)){
                throw new \Exception('Problem with image creating.');
            }
        }
        return $filePath;
    }
    
    public function getExtension(){
        $ext = pathinfo($this->getPathToOrigin(), PATHINFO_EXTENSION);
        return $ext;
    }

    public function getMimeType(){
        return FileHelper::getMimeType($this->getPathToOrigin());
    }

    public function getFilename(){
        $fn = pathinfo($this->getPathToOrigin(), PATHINFO_FILENAME);
        return $fn;
    }

    private function getFilepath($params = []){
        $pathinfo = pathinfo($this->filePath);
        return $this->getModule()->getCachePath()
          .DIRECTORY_SEPARATOR
          .$pathinfo['dirname']
          .DIRECTORY_SEPARATOR
          .$this->getFilename()
          .$this->getImageSuffix($params)
          .'.'.$this->getExtension();
    }

    public function getImageSuffix($params = []) {
      
      $x = isset($params['x']) ? $params['x'] : null;
      $y = isset($params['y']) ? $params['y'] : null;
      
      if (!($x || $y)) return '';
      
      $suffix = '_' . $x . 'x' . $y;      
      $suffix .= isset($params['fit']) ? '_fit' : '';
      return $suffix;
    }

    private function parseSuffix($suffix = '') {
      $params = [
        'x' => null,
        'y' => null,
        'fit' => false,
      ];
      $matches = 0;

      if ($matches = preg_split('/_+/', $suffix) ) {
        while($item = array_pop($matches)) {
          if (stristr($item, 'fit')) {
            $params['fit'] = true;
          } elseif (stristr($item, 'x')) {
            $size = preg_split('/x/', $item);
            $params['y'] = array_pop($size);
            $params['x'] = array_pop($size);
          }
        }
      }

      return $params;
    }

    public function createVersion($params)
    {
      $x = isset($params['x']) ? $params['x'] : null;
      $y = isset($params['y']) ? $params['y'] : null;
      $fit = \Imagine\Image\ManipulatorInterface::THUMBNAIL_OUTBOUND;

      if (isset($params['fit'])) {
        if($params['fit']) {
          $fit = \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET;
        }
      }

      $filePath = $this->getFilepath($params);

      if (!is_dir( pathinfo($filePath, PATHINFO_DIRNAME) )) {
        FileHelper::createDirectory(pathinfo($filePath, PATHINFO_DIRNAME),
            0775, true);
      }
      
      Imagine::fitted($this->getPathToOrigin(), $x, $y, $fit)
        ->save($filePath);

    }
}
