<?php

namespace TB\Bundle\FrontendBundle\Tests\FOSUserbundle\Controller;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 *
 */
class SecurityControllerTest extends AbstractFrontendTest
{
        
    /**
     * Test Trail created by UserProfile, no Event, no Editorial
     */
    public function testLogin()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/login');        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'StatusCode 200 for GET /login');
        
        $this->assertTrue($client->getContainer()->get('security.context')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY'),
            'User is authenticated anonymously');
                
        $form = $crawler->filter('#_submit')->form(array(
            '_username'  => 'email@mattallbeury',
            '_password' => 'password',
            ));     
            
        $client->submit($form);
                
        $this->assertTrue($client->getResponse()->isRedirect(), 
            'User is redirected after login');

        $crawler = $client->followRedirect();
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(), 
            'StatusCode 200 after redirect');

        $this->assertTrue($client->getContainer()->get('security.context')->isGranted('ROLE_USER'), 
            'User is authenticated after login with role ROLE_USER');
        
    }    

}
