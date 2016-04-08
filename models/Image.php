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


    public function getPathToOrigin() {

        $base = $this->getModule()->getStorePath();

        $filePath = $base.DIRECTORY_SEPARATOR.$this->filePath;

        return $filePath;
    }


    public function getSizes()
    {
        if ($size = Imagine::getImagine()->open(Yii::getAlias($filename))->getSize()) {
          return [
            'w' => $size->getWidth(),
            'h' => $size->getHeight(),
          ]; 
        }
        return false;
    }

    public function setMain($isMain = true){
        if($isMain){
            $this->isMain = 1;
            Yii::$app->db->createCommand()->update($this->tableName(), ['isMain' => false], [
              'modelTableName' => $this->modelTableName,
              'itemId' => $this->itemId,
              'name' => $this->name
            ])->execute(); 
        }else{
            $this->isMain = 0;
        }
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

    public function fields() 
    {
      return [
        'id', 'filePath', 'thumbnailPath', 'modelTableName', 'name', 'sortOrder' 
      ];
    }
    
    public function getThumbnailPath() {
      if ($thumbnailSize = $this->getModule()->thumbnailSize) {
        $start = strlen(FileHelper::normalizePath($this->getModule()->getCachePath())) + 1;
        return 
          substr(
            FileHelper::normalizePath(
              $this->getFilepath(
                $this->getModule()->parseSuffix(
                  $thumbnailSize
                )
              )
            )
          , $start);
      }
      
      return $this->filePath;
    }
    
    public function getUrl($params = []) {
        $params['id'] = $this->id;

        $filePath = FileHelper::normalizePath($this->getFilepath($params));

        if(!file_exists($filePath)){
            $this->createVersion($params);
            if(!file_exists($filePath)){
                throw new \Exception('Problem with image creating.');
            }
        }
        
        $url = ($this instanceof PlaceHolder) ?
          Url::toRoute(array_merge(['/'.$this->getModule()->id.'/images/placeholder'], $params, ['name' => $this->placeholderName])):
          Url::toRoute(array_merge(['/'.$this->getModule()->id.'/images/get-image'], $params));
        return $url;
    }

    public function getPath($params = []) {
        $filePath = FileHelper::normalizePath($this->getFilepath($params));
        if(!file_exists($filePath)){
            throw new \Exception('Такого размера картинки нет.');
        }
        return $filePath;
    }
    
    public function getExtension() {
        $ext = pathinfo($this->getPathToOrigin(), PATHINFO_EXTENSION);
        return $ext;
    }

    public function getMimeType() {
        return FileHelper::getMimeType($this->getPathToOrigin());
    }

    public function getFilename() {
       return pathinfo($this->getPathToOrigin(), PATHINFO_FILENAME);
    }

    private function getFilepath($params = []) {
        $pathinfo = pathinfo($this->filePath);
        return $this->getModule()->getCachePath()
          .DIRECTORY_SEPARATOR
          .$pathinfo['dirname']
          .DIRECTORY_SEPARATOR
          .$this->getFilename()
          .$this->getImageSuffix($params)
          .'.'.$this->getExtension();
    }

    public static function getImageSuffix($params = []) {
      
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
      $fit = isset($params['fit']) ? ($params['fit'] == true ? true:false) : false;

      $filePath = $this->getFilepath($params);

      if (!is_dir( pathinfo($filePath, PATHINFO_DIRNAME) )) {
        FileHelper::createDirectory(pathinfo($filePath, PATHINFO_DIRNAME),
            0775, true);
      }
      
      Imagine::fitted($this->getPathToOrigin(), $x, $y, $fit)
        ->save($filePath);
    }

    public function clearCache() {
        if ($this instanceof PlaceHolder) return false;
        
        $pathinfo = pathinfo($this->getFilepath());
        $base = FileHelper::normalizePath($pathinfo['dirname']);
      
        if ($files = FileHelper::findFiles($pathinfo['dirname'], [
          'recursive' => false,
        ]) ) {
          $filename = $base . DIRECTORY_SEPARATOR . $pathinfo['filename'];
          foreach ($files as $file) {
            if (!substr_compare($file, $filename, 0, strlen($filename))) {
              unlink($file);
            }
          }
        }

        return true;
    }
    
    public function isUnique() {
      return $this->find()->where(['filePath' => $this->filePath])->count() == 1;
    }
    
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            if ($this->isUnique()) {
              $this->clearCache();
              unlink($this->getModule()->getStorePath() . DIRECTORY_SEPARATOR . $this->filePath);
            }
            return true;
        } else {
            return false;
        }
    }
}
