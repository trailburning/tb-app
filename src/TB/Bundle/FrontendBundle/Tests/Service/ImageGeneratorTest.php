<?php 

namespace TB\Bundle\FrontendBundle\Tests\Service;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\Media;
use TB\Bundle\FrontendBundle\Entity\Editorial;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageGeneratorTest extends AbstractFrontendTest
{

    public function testCreateRouteShareImage()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $imageGenerator = $this->getContainer()->get('tb.image.generator'); 
        $filesystem = $this->getContainer()->get('trail_media_files_filesystem'); 
        $debugFilesystem = $this->getContainer()->get('debug_filesystem'); 
        $route = $this->getRoute('grunewald');
        
        $mediaFile = new UploadedFile(
            realpath(__DIR__ . '/../../DataFixtures/Media/grunewald/P5250773.jpg'),
            'P5250773.jpg'
        );
        
        $media = new Media();
        $media->setRoute($route);
        $media->setFile($mediaFile);
        $media->upload($filesystem);
        $em->persist($media);
        
        $route->setMedia($media);
        $em->persist($route);
        $em->flush();    
        
        $result = $imageGenerator->createRouteShareImage($route);
        $this->assertTrue($result, 'ImageGenerator::createRouteShareImage() returns true');
        
        $em->refresh($media);
        $this->assertRegExp('/.*_share.jpg$/', $media->getSharePath(), 
            'The Media sharePath field was set');
        $this->assertTrue($filesystem->has($media->getSharePath()),
            'The share file was created');
        
        // Copy the file to a local filesystem for debug
        $debugFilesystem->write('route_share.jpg', $filesystem->read($media->getSharePath()), true);
    }
    
    public function testCreateEditorialShareImage()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\EditorialData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $imageGenerator = $this->getContainer()->get('tb.image.generator');
        $filesystem = $this->getContainer()->get('asset_files_filesystem'); 
        $debugFilesystem = $this->getContainer()->get('debug_filesystem'); 
        
        $editorial = $this->getEditorial('alps');
        
        // Copy the editorial test image to the editorial filesystem at the expected path
        $filesystem->write(sprintf('images/editorial/%s/%s', $editorial->getSlug(), $editorial->getImage()), file_get_contents(realpath(__DIR__ . '/../../DataFixtures/Media/editorial/' . $editorial->getImage())));
        
        $result = $imageGenerator->createEditorialShareImage($editorial);
        $this->assertTrue($result, 'ImageGenerator::createEditorialShareImage() returns true');
        
        $em->refresh($editorial);
        $this->assertRegExp('/.*_share.jpg$/', $editorial->getShareImage(), 
            'The Editorial shareImage field was set');
        $this->assertTrue($filesystem->has($editorial->getShareImage()),
            'The share image was created');
        
        // Copy the file to a local filesystem for debug
        $debugFilesystem->write('inspire_share.jpg', $filesystem->read($editorial->getShareImage()), true);
    }
    
    public function testCreateEventShareImage()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\EventData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $imageGenerator = $this->getContainer()->get('tb.image.generator');
        $filesystem = $this->getContainer()->get('asset_files_filesystem'); 
        $debugFilesystem = $this->getContainer()->get('debug_filesystem'); 
        
        $event = $this->getEvent('eiger');
        
        // Copy the editorial test image to the editorial filesystem at the expected path
        $filesystem->write(sprintf('images/event/%s/%s', $event->getSlug(), $event->getImage()), file_get_contents(realpath(__DIR__ . '/../../DataFixtures/Media/event/' . $event->getImage())));
        
        // Copy the editorial test logo to the editorial filesystem at the expected path
        $filesystem->write(sprintf('images/event/%s/%s', $event->getSlug(), $event->getLogo()), file_get_contents(realpath(__DIR__ . '/../../DataFixtures/Media/event/' . $event->getLogo())));
        
        $result = $imageGenerator->createEventShareImage($event);
        $this->assertTrue($result, 'ImageGenerator::createEventShareImage() returns true');
        
        $em->refresh($event);
        $this->assertRegExp('/.*_share.jpg$/', $event->getShareImage(), 
            'The Event shareImage field was set');
        $this->assertTrue($filesystem->has($event->getShareImage()),
            'The share image was created');
        
        // Copy the file to a local filesystem for debug
        $debugFilesystem->write('event_share.jpg', $filesystem->read($event->getShareImage()), true);
    }
    
    public function testCreateCampaignShareImage()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $imageGenerator = $this->getContainer()->get('tb.image.generator');
        $filesystem = $this->getContainer()->get('asset_files_filesystem'); 
        $debugFilesystem = $this->getContainer()->get('debug_filesystem'); 
        
        $campaign = $this->getCampaign('urbantrails-london');
        
        // Copy the campaign test image to the test filesystem at the expected path
        $filesystem->write($campaign->getImage(), file_get_contents(realpath(__DIR__ . '/../../DataFixtures/Media/campaign/image.jpg')));
        
        // Copy the campaign test watermark to the test filesystem at the expected path
        $filesystem->write($campaign->getWatermarkImage(), file_get_contents(realpath(__DIR__ . '/../../DataFixtures/Media/campaign/watermark.png')));
        
        $result = $imageGenerator->createCampaignShareImage($campaign);
        $this->assertTrue($result, 'ImageGenerator::createCampaignShareImage() returns true');
        
        $em->refresh($campaign);
        $this->assertRegExp('/.*_share.jpg$/', $campaign->getShareImage(), 
            'The Campaign shareImage field was set');
        $this->assertTrue($filesystem->has($campaign->getShareImage()),
            'The share image was created');
        
        // Copy the file to a local filesystem for debug
        $debugFilesystem->write('campaign_share.jpg', $filesystem->read($campaign->getShareImage()), true);
    }
    
}    