<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\UserProfile;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class UserProfileTest extends AbstractFrontendTest
{
    
    /**
     * Test that setEmail sets the username field
     */
    public function testSluggableName()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $user = new UserProfile();
        $user->setEmail('test@trailburning.com');
        $user->setPassword('password');
        $user->setFirstName('first');
        $user->setlastName('last name');
        $user->setNewsletter(false);
        $user->setLocation(new Point(52.508006, 13.257437, 4326));

        // Replace the Mailproxy Service with a Mock
        $mailproxy = $this->getMockBuilder('TB\Bundle\FrontendBundle\Service\Mailproxy')
            ->disableOriginalConstructor()
            ->getMock();
        $mailproxy->method('addNewsletterSubscriber')->willReturn(true);
        $mailproxy->method('removeNewsletterSubscriber')->willReturn(true);
        $mailproxy->method('sendWelcomeMail')->willReturn(true);
        $this->getContainer()->set('tb.mailproxy', $mailproxy);


        $em->persist($user);
        $em->flush();
        
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($user->getId());
        if (!$user) {
            $this->fail('Missing new created user in test DB');
        }    
        
        $this->assertEquals('firstlastname', $user->getName());
        
        // test that name gets not updated on firstName nad lastName changes
        
        $user->setFirstName('new first');
        $user->setLastName('new last name');

        $em->persist($user);
        $em->flush();
        
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($user->getId());
        if (!$user) {
            $this->fail('Missing updated user in test DB');
        }    
        
        $this->assertEquals('firstlastname', $user->getName());
        
        
        // test another user with the same firstName and lastname
        
        $user = new UserProfile();
        $user->setEmail('test2@mail');
        $user->setPassword('password');
        $user->setFirstName('first');
        $user->setlastName('last name');
        $user->setNewsletter(false);
        $user->setLocation(new Point(52.508006, 13.257437, 4326));

        $em->persist($user);
        $em->flush();
        
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($user->getId());
        if (!$user) {
            $this->fail('Missing second new created user in test DB');
        }    
        
        $this->assertEquals('firstlastname1', $user->getName());
        
    }
    
    /**
     * 
     */
    public function testExportAsActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        $user = $this->getUser('mattallbeury');
        
        $expected = [
            'url' => '/profile/mattallbeury',
            'objectType' => 'person',
            'id' => $user->getId(),
            'displayName' => 'Matt Allbeury',
            'image' => [
                'url' => 'http://assets.trailburning.com/images/profile/mattallbeury/avatar_ma.png',
            ],
        ];
        
        $this->assertEquals($expected, $user->exportAsActivity(),
            'UserProfile::exportAsActivity() returns the expected data array');
    }
        
        
    public function testExport() 
    {
        $userProfile = new UserProfile();
        
        $expected = [
          'name' => null,
          'title' => ' ',
          'avatar' => 'http://assets.trailburning.com/images/icons/avatars/avatar_man.jpg',
          'type' => 'user',
          'is_ambassador' => false
        ];
        
        $this->assertEquals($expected, $userProfile->export(),
            'UserProfile::export() returns the expected data array');
    }
}