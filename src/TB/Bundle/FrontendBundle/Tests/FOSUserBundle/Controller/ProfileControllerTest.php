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
class ProfileControllerTest extends AbstractFrontendTest
{
    
    /**
     * Test user profile edit form
     */
    public function testProfileEdit()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        
        $client = $this->createClient();
        $this->logIn($client, $user->getUsername());
        
        $crawler = $client->request('GET', '/user/edit');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
                
        $form = $crawler->filter('#profile_edit_form')->form([
            'fos_user_profile_form[firstName]' => 'first edited',
            'fos_user_profile_form[lastName]' => 'last edited',
            'fos_user_profile_form[location]' => '(52.5234051, 13.4113999)',
            'fos_user_profile_form[about]' => 'about me text edited',
            'fos_user_profile_form[gender]' => 1,
            'fos_user_profile_form[newsletter]' => 1,
            ]);     
            
        $client->submit($form);
        
        $this->assertTrue($client->getResponse()->isRedirect('/profile/mattallbeury'));
        
        $crawler = $client->followRedirect();
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // check that user was edited
        $em->refresh($user);
        
        // user is authenticated with role ROLE_USER after registration
        $this->assertEquals('first edited', $user->getFirstName());
        $this->assertEquals('last edited', $user->getLastName());
        $this->assertEquals('about me text edited', $user->getAbout());
        $this->assertEquals(1, $user->getNewsletter());
        $this->assertEquals(1, $user->getGender());
    }
    
}
