<?php

namespace bstuff\yii2images;

use yii;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Color;
use Imagine\Image\Point;

class Imagine extends \yii\imagine\Image
{
    public static function fitted($filename, $width, $height, $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND)
    {
        $img = static::getImagine()->open(Yii::getAlias($filename));
        $fit = ($mode == ManipulatorInterface::THUMBNAIL_OUTBOUND);

        if (!($width || $height)) {
          return $img;
        }
        
        $sourceBox = $img->getSize();
        $thumbnailBox = static::getThumbnailBox($sourceBox, $width, $height);

        // create empty image to preserve aspect ratio of thumbnail
        $thumb = static::getImagine()->create($thumbnailBox, new Color(static::$thumbnailBackgroundColor, static::$thumbnailBackgroundAlpha));

        if (($width && !$height) || (!$width && $height)) {

          if ($width && ($width > $sourceBox->getWidth())) {
            return $fit ? $img->resize($thumbnailBox) : $img;
          }
          
          if ($height && ($height > $sourceBox->getHeight())) {
            return $fit ? $img->resize($thumbnailBox) : $img;
          }
          
          $img = $img->thumbnail($thumbnailBox, $mode);
  
          // calculate points
          $startX = 0;
          $startY = 0;
          if ($sourceBox->getWidth() < $width) {
              $startX = ceil($width - $sourceBox->getWidth()) / 2;
          }
          if ($sourceBox->getHeight() < $height) {
              $startY = ceil($height - $sourceBox->getHeight()) / 2;
          }
  
          $thumb->paste($img, new Point($startX, $startY));
          return $thumb;
        }

        if ($width && $height) {
          if(!$fit) {
            return $img->thumbnail($thumbnailBox, $mode);
          } else {
            if ($width > $sourceBox->getWidth() || $height > $sourceBox->getHeight()) {
              $k = max([$width/$sourceBox->getWidth(), $height/$sourceBox->getHeight()]);
              
              $startX = ($width < $k*$sourceBox->getWidth()) ? ceil(.5*($k*$sourceBox->getWidth() - $width)) : 0;
              $startY = ($height < $k*$sourceBox->getHeight()) ? ceil(.5*($k*$sourceBox->getHeight() - $height)) : 0;
              $img = $img->resize(new Box($k*$sourceBox->getWidth(), $k*$sourceBox->getHeight()))
                ->crop(new Point($startX,$startY), new Box($width, $height));
            }
          }
        }
        return $img;
    }
}
