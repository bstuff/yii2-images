<?php

namespace bstuff\yii2images;

use yii;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Color;
use Imagine\Image\Point;

class Imagine extends \yii\imagine\Image
{
    public static function fitted($filename, $width, $height, $fit=false) {
        $img = static::getImagine()->open(Yii::getAlias($filename));

        if (!($width || $height)) {
          return $img;
        }
        
        $sourceBox = $img->getSize();

        if ($width && !$height) {
          
          $k = $width / $sourceBox->getWidth();
          
          if ($fit) {            
            $img = $img->resize(new Box(ceil($k*$sourceBox->getWidth()), ceil($k*$sourceBox->getHeight())));
          } elseif ($k < 1) {
            $img = $img->resize(new Box($k*$sourceBox->getWidth(), $k*$sourceBox->getHeight()));
          }
          
          return $img;
        }

        if (!$width && $height) {
          
          $k = $height / $sourceBox->getHeight();
          
          if ($fit) {            
            $img = $img->resize(new Box(ceil($k*$sourceBox->getWidth()), ceil($k*$sourceBox->getHeight())));
          } elseif ($k < 1) {
            $img = $img->resize(new Box($k*$sourceBox->getWidth(), $k*$sourceBox->getHeight()));
          }
          
          return $img;
        }
        
        $kx = $width / $sourceBox->getWidth();
        $ky = $height / $sourceBox->getHeight();

        if ($fit) {
          $k = min([$kx, $ky]);
          return $img->resize(new Box($k*$sourceBox->getWidth(), $k*$sourceBox->getHeight()));
        } else {
          $k = max([$kx, $ky]);
          
          $startX = ($width < $k*$sourceBox->getWidth()) ? ceil(.5*($k*$sourceBox->getWidth() - $width)) : 0;
          $startY = ($height < $k*$sourceBox->getHeight()) ? ceil(.5*($k*$sourceBox->getHeight() - $height)) : 0;
          $img = $img->resize(new Box($k*$sourceBox->getWidth(), $k*$sourceBox->getHeight()))
            ->crop(new Point($startX,$startY), new Box($width, $height));
        }

        return $img;
    }
}
