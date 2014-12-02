<?php

namespace TB\Bundle\FrontendBundle\Util;

use Doctrine\ORM\EntityManager;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\Editorial;
use TB\Bundle\FrontendBundle\Entity\Event;
use TB\Bundle\FrontendBundle\Entity\BrandProfile;
use Gaufrette\Filesystem;

/**
 * 
 */
class ImageGenerator
{
    
    protected $em;
    protected $mediaFilesystem;
    protected $assetsFilesystem;
    
    public function __construct(EntityManager $em, Filesystem $mediaFilesystem, Filesystem $assetsFilesystem)
    {
        $this->em = $em;
        $this->mediaFilesystem = $mediaFilesystem;
        $this->assetsFilesystem = $assetsFilesystem;
    }
    
    public function createRouteShareImage(Route $route)
    {
        if ($route->getFavouriteMedia() === null) {
            return false;
        }
        
        // Get the image to create the share image and the watermark
        $media = $route->getFavouriteMedia();
        
        $imagePath = $media->getPath();
        $watermarkPath = realpath(__DIR__ . '/../DataFixtures/Media/watermark/fb_share_trail_1200x630.png');
        // Construct the share image filepath
        $pathParts = pathinfo($media->getPath());        
        $shareImagePath = sprintf('/%s/%s_share.%s', $route->getId(), $pathParts['filename'], $pathParts['extension']);
        
        $this->createShareImage($imagePath, $shareImagePath, $watermarkPath, $this->mediaFilesystem);
        
        // Update the Media object and set the share image path
        $media->setSharePath($shareImagePath);
        $this->em->persist($media);
        $this->em->flush($media);

        return true;
    }
    
    public function createEditorialShareImage(Editorial $editorial)
    {
        if ($editorial->getImage() === null) {
            return false;
        }
        
        // Get the image to create the share image and the watermark
        $imagePath = sprintf('images/editorial/%s/%s', $editorial->getSlug(), $editorial->getImage());
        if (!$this->assetsFilesystem->has($imagePath)) {
            throw new \Exception(sprintf('Missing Editorial image: %s', $imagePath));
        }
        
        // Construct the share image filepath
        $pathParts = pathinfo($imagePath);        
        $shareImagePath = sprintf('images/editorial/%s/%s_share.%s', $editorial->getSlug(), $pathParts['filename'], $pathParts['extension']);
        
        $watermarkPath = realpath(__DIR__ . '/../DataFixtures/Media/watermark/fb_share_inspire_1200x630.png');
        
        $this->createShareImage($imagePath, $shareImagePath, $watermarkPath, $this->assetsFilesystem);
        
        // Update the Media object and set the share image path
        $editorial->setShareImage($shareImagePath);
        $this->em->persist($editorial);
        $this->em->flush($editorial);

        return true;
    }
    
    public function createEventShareImage(Event $event)
    {
        if ($event->getImage() === null) {
            return false;
        }
        
        // Get the image to create the share image and the watermark
        $imagePath = sprintf('images/event/%s/%s', $event->getSlug(), $event->getImage());
        if (!$this->assetsFilesystem->has($imagePath)) {
            throw new \Exception(sprintf('Missing Event image: %s', $imagePath));
        }
        
        // Construct the share image filepath
        $pathParts = pathinfo($imagePath);        
        $shareImagePath = sprintf('images/event/%s/%s_share.%s', $event->getSlug(), $pathParts['filename'], $pathParts['extension']);
        
        $watermarkPath = realpath(__DIR__ . '/../DataFixtures/Media/watermark/fb_share_event_1200x630.png');
        $logoPath = realpath(__DIR__ . '/../DataFixtures/Media/watermark/fb_share_event_1200x630.png');
        
        $logoPath = sprintf('images/event/%s/%s', $event->getSlug(), $event->getLogo());
        if (!$this->assetsFilesystem->has($logoPath)) {
            throw new \Exception(sprintf('Missing Event logo: %s', $logoPath));
        }
        
        $this->createShareImage($imagePath, $shareImagePath, $watermarkPath, $this->assetsFilesystem, $logoPath);
        
        // Update the Media object and set the share image path
        $event->setShareImage($shareImagePath);
        $this->em->persist($event);
        $this->em->flush($event);

        return true;
    }
    
    public function createBrandProfileShareImage(BrandProfile $profile)
    {
        if ($profile->getHeaderImage() === null) {
            return false;
        }
        
        // Get the image to create the share image and the watermark
        $imagePath = sprintf('images/profile/%s/%s', $profile->getUsername(), $profile->getHeaderImage());
        if (!$this->assetsFilesystem->has($imagePath)) {
            throw new \Exception(sprintf('Missing BrandProfile image: %s', $imagePath));
        }
        
        // Construct the share image filepath
        $pathParts = pathinfo($imagePath);        
        $shareImagePath = sprintf('images/profile/%s/%s_share.%s', $profile->getUsername(), $pathParts['filename'], $pathParts['extension']);
        
        $watermarkPath = realpath(__DIR__ . '/../DataFixtures/Media/watermark/fb_share_event_1200x630.png');
        $logoPath = realpath(__DIR__ . '/../DataFixtures/Media/watermark/fb_share_event_1200x630.png');
        
        $logoPath = sprintf('images/profile/%s/%s', $profile->getUsername(), $profile->getAvatar());
        if (!$this->assetsFilesystem->has($logoPath)) {
            throw new \Exception(sprintf('Missing BrandProfile avatar: %s', $logoPath));
        }
        
        $this->createShareImage($imagePath, $shareImagePath, $watermarkPath, $this->assetsFilesystem, $logoPath);
        
        // Update the Media object and set the share image path
        $profile->setShareImage($shareImagePath);
        $this->em->persist($profile);
        $this->em->flush($profile);

        return true;
    }
    
    protected function createShareImage($imagePath, $shareImagePath, $watermarkPath, $filesystem, $logoPath = null)
    {
        $image = imagecreatefromstring($filesystem->read($imagePath));
        $watermark = imagecreatefrompng($watermarkPath);
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
        
        // Create the share image
        imagecopy($image, $watermark, imagesx($image) - $watermarkWidth, imagesy($image) - $watermarkHeight, 0, 0, $watermarkWidth, $watermarkHeight);
        
        if ($logoPath !== null) {
            $logo = imagecreatefromstring($filesystem->read($logoPath));
            $logoWidth = imagesx($logo);
            $logoHeight = imagesy($logo);
            if ($logoWidth < 250) {
                // put logo in the upper right corner with a margin of 20 to top and right
                imagecopy($image, $logo, imagesx($image) - $logoWidth - 20, 20, 0, 0, $logoWidth, $logoHeight);
            } else {
                // put in the center, vertically a little bit higher than the middle because of the watermark at the bottom
                imagecopy($image, $logo, (imagesx($image) / 2) - ($logoWidth / 2), (imagesy($image) / 2) - ($logoHeight / 2) - 25, 0, 0, $logoWidth, $logoHeight);
            }
            
        }
        
        // Read the share images content to a variable
        ob_start();
        imagejpeg($image);
        $shareImageData = ob_get_contents();
        ob_end_clean();
        
        // Store the new image data to the filesystem, overwrite if file exists
        $adapter = $filesystem->getAdapter();
        // Set Metadata to S3 (doesn't work in unit tests when using memory filesystem)
        if ($adapter instanceof \Gaufrette\Adapter\MetadataSupporter) {
            $adapter->setMetadata($shareImagePath, array('ContentType' => 'image/jpeg', 'ACL' => 'public-read'));
        }
        $adapter->write($shareImagePath, $shareImageData);
        
        return true;
    }
    
}
