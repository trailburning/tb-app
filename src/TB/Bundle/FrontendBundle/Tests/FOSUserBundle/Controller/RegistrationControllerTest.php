<?php

namespace TB\Bundle\FrontendBundle\Tests\FOSUserbundle\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 *
 */
class RegistrationControllerTest extends WebTestCase
{
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    /**
     * Test Trail created by UserProfile, no Event, no Editorial
     */
    public function testRegistration()
    {
        $this->loadFixtures([]);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/register/');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // User is authenticated anonymously
        $this->assertTrue($client->getContainer()->get('security.context')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY'));
                
        $form = $crawler->filter('#signup')->form(array(
            'fos_user_registration_form[email]'  => 'test@trailburning.com',
            'fos_user_registration_form[plainPassword][first]' => 'password',
            'fos_user_registration_form[plainPassword][second]' => 'password',
            'fos_user_registration_form[firstName]'  => 'first',
            'fos_user_registration_form[lastName]'  => 'last',
            ));     
            
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/register/confirmed'));
        
        $crawler = $client->followRedirect();
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // check that user was created
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('firstlast');
        if (!$user) {
            $this->fail('user was not created in registration');
        }
        
        // user is authenticated with role ROLE_USER after registration
        $this->assertTrue($client->getContainer()->get('security.context')->isGranted('ROLE_USER'));
        
    }    

}
