<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 */
class GpxFileControllerTest extends AbstractApiTestCase
{
    
    
    /**
     * 
     */
    public function testPostImport()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        // Get User from DB with the slug "mattallbeury"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $gpxfile = new UploadedFile(
            realpath(__DIR__ . '/../../DataFixtures/GpxFiles/example.gpx'),
            'example.gpx'
        );
        
        $client = $this->createClient();
        $crawler = $client->request('POST', '/v1/import/gpx', [], ['gpxfile' => $gpxfile], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        
        echo $client->getResponse()->getContent();
        exit;
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json',  $client->getResponse()->headers->get('Content-Type'));
    }

}
