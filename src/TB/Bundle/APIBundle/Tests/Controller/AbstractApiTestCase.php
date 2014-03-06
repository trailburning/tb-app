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
abstract class AbstractApiTestCase extends WebTestCase
{
    
    protected function isValidJson($json)
    {
        $ob = json_decode($json);
        if ($ob === null) {
            return false;
        } else {
            return true;
        }
    }
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/api/AppKernel.php';

        return 'AppKernel';
    }
    
    protected function assertJsonResponse($client)
    {
        $this->assertEquals('application/json',  $client->getResponse()->headers->get('Content-Type'),
            'Content-Type Header is "application/json"');  
        $this->assertTrue($this->isValidJson($client->getResponse()->getContent()), 
            'Response is Valid JSON');
    }
    
}