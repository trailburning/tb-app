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
class RegistrationControllerTest extends AbstractFrontendTest
{
    
    /**
     * Test user registration form
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
            'fos_user_registration_form[email]' => 'test@trailburning.com',
            'fos_user_registration_form[plainPassword][first]' => 'password',
            'fos_user_registration_form[plainPassword][second]' => 'password',
            'fos_user_registration_form[firstName]' => 'first',
            'fos_user_registration_form[lastName]' => 'last',
            'fos_user_registration_form[location]' => '(52.5234051, 13.4113999)',
            ));     
            
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/register/confirmed'));
        
        $crawler = $client->followRedirect();
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // check that user was created
        $user = $this->getUser('firstlast');
        if (!$user) {
            $this->fail('user was not created in registration');
        }
        
        // user is authenticated with role ROLE_USER after registration
        $this->assertTrue($client->getContainer()->get('security.context')->isGranted('ROLE_USER'));
        
    }    

}
