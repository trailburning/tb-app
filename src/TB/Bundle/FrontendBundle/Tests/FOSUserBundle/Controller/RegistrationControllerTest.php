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

        // TODO: mock doesn't work
        //// Replace the Mailproxy Service with a Stub
        // $mailproxy = $this->getMockBuilder('TB\Bundle\FrontendBundle\Service\Mailproxy')
        //     ->disableOriginalConstructor()
        //     ->getMock();
        //// Test that the sendWelcomeMail() method gets called once
        // $mailproxy
        //     ->expects($this->once())
        //     ->method('sendWelcomeMail');
        // $client->getContainer()->set('tb.mailproxy', $mailproxy);

        $crawler = $client->request('GET', '/register/');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // User is authenticated anonymously
        $this->assertTrue($client->getContainer()->get('security.context')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY'));
                
        $form = $crawler->filter('#signup')->form([
            'fos_user_registration_form[email]' => 'test@trailburning.com',
            'fos_user_registration_form[plainPassword][first]' => 'password',
            'fos_user_registration_form[plainPassword][second]' => 'password',
            'fos_user_registration_form[firstName]' => 'first',
            'fos_user_registration_form[lastName]' => 'last',
            'fos_user_registration_form[location]' => '(52.5234051, 13.4113999)',
            'fos_user_registration_form[about]' => 'about me text',
            'fos_user_registration_form[gender]' => 1,
            'fos_user_registration_form[newsletter]' => 1,
            ]);     
            
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
        $this->assertEquals('about me text', $user->getAbout());
        $this->assertEquals(1, $user->getNewsletter());
        $this->assertEquals(1, $user->getGender());
        $this->assertEquals(1, $user->getGender());
        $this->assertNotNull($user->getRegisteredAt()->format('Y-m-d'));
        $this->assertNotNull($user->getUserRegisterActivity());
    }
    
}
