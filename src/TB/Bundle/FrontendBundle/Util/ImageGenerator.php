<?php

namespace TB\Bundle\FrontendBundle\Util;

use Doctrine\ORM\EntityManager;
use TB\Bundle\FrontendBundle\Entity\Route;
use Gaufrette\Filesystem;

/**
 * 
 */
class ImageGenerator
{
    
    protected $em;
    protected $filesystem;
    
    public function __construct(EntityManager $em, Filesystem $filesystem)
    {
        $this->em = $em;
        $this->filesystem = $filesystem;
    }
    
    public function createRouteShareImage(Route $route)
    {
        if ($route->getFavouriteMedia() === null) {
            return false;
        }
        
        // Get the image to create the share image and the watermakr
        $media = $route->getFavouriteMedia();
        $image = imagecreatefromstring($this->filesystem->read($media->getPath()));

        $watermark = imagecreatefrompng(realpath(__DIR__ . '/../DataFixtures/Media/watermark/fb_share_1200x630.png'));
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);
        $watermarkRatio = $watermarkWidth / $watermarkHeight;
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        
        // Get the ratio of the image, it must be the same than the watermark
        $imageRatio = $imageWidth / $imageHeight;
        if ($imageRatio != $watermarkRatio) {
            // Calculate the new image x and y that conforms to the ratio of the watermark
            if (($imageHeight * $watermarkRatio) > $imageWidth) {
                $newWidth = $imageWidth;
                $newHeight = $imageWidth / $watermarkRatio;
            } else {
                $newWidth = $imageHeight * $watermarkRatio;
                $newHeight = $imageHeight;
            }
            // center the image when cropping
            $newX = ($imageHeight - $newHeight) / 2;
            $newY = ($imageWidth - $newWidth) / 2;
            
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $image, 0, 0, $newY, $newX, $imageWidth, $imageHeight, $imageWidth, $imageHeight);
            $image = $newImage;
            $imageWidth = $newWidth;
            $imageHeight = $newHeight;
        }
        
        
        // Image and watermark template must have the same size, resize either the image or the watermark
        if ($imageWidth > $watermarkWidth) {
            // The image is larger than the watermark, resize the image to the size of the watermark
            $newImage = imagecreatetruecolor($watermarkWidth, $watermarkHeight);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $watermarkWidth, $watermarkHeight, $imageWidth, $imageHeight);
            $image = $newImage;
        } elseif ($imageWidth < $watermarkWidth) {
            // The image is smaller than the watermark, resize the watermark to the size of the image
            $newWatermark = imagecreatetruecolor($imageWidth, $imageHeight);
            imagealphablending($newWatermark, false);
            imagesavealpha($newWatermark, true);
            imagecopyresampled($newWatermark, $watermark, 0, 0, 0, 0, $imageWidth, $imageHeight, $watermarkWidth, $watermarkHeight);
            $watermark = $newWatermark;
            $watermarkWidth = $imageWidth;
            $watermarkHeight = $imageHeight;
        }
        
        // Construct the share image filepath
        $pathParts = pathinfo($media->getPath());        
        $shareImageFilepath = sprintf('/%s/%s_share.%s', $route->getId(), $pathParts['filename'], $pathParts['extension']);
        
        // Create the share image
        imagecopy($image, $watermark, imagesx($image) - $watermarkWidth, imagesy($image) - $watermarkHeight, 0, 0, $watermarkWidth, $watermarkHeight);
        
        // Read the share images content to a variable
        ob_start();
        imagejpeg($image);
        $shareImageData = ob_get_contents();
        ob_end_clean();
        
        // Store the new image data to the filesystem, overwrite if file exists
        $this->filesystem->write($shareImageFilepath, $shareImageData, true);
        
        // Update the Media object and set the share image path
        $media->setSharePath($shareImageFilepath);
        $this->em->persist($media);
        $this->em->flush($media);

        return true;
    }
    
}
