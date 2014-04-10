<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class UserTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    /**
     * Test that setEmail sets the username field
     */
    public function testSetEmail()
    {
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        
        $this->assertEmpty('', $user->getUsername());
        $user->setEmail('e@mail');
        $this->assertEquals('e@mail', $user->getUsername());
        $this->assertEquals('e@mail', $user->getEmail());
    }
    
    /**
     * Test that setEmailCanonical sets the usernameCanonical field
     */
    public function testSetEmailCanonical()
    {
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        
        $this->assertEmpty('', $user->getUsernameCanonical());
        $user->setEmailCanonical('e@mail');
        $this->assertEquals('e@mail', $user->getUsernameCanonical());
        $this->assertEquals('e@mail', $user->getEmailCanonical());
    }
    
    /**
     * Test setLocation() with Point Object
     */
    public function testSetLocationPoint()
    {   
        $point = new Point(52.5234051, 13.4113999, 4326);
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        $user->setlocation($point);
        $this->assertEquals($point, $user->getLocation(), 
            'The set Point object is returned');
    }
    
    /**
     * Test setLocation() with a string representation of a Point
     */
    public function testSetLocationString()
    {   
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        $user->setlocation('(52.5234051, 13.4113999)');
        $this->assertInstanceOf('CrEOF\Spatial\PHP\Types\Geometry\Point', $user->getLocation(),
            'A Point Object was created');
        $user->setlocation('(-34.92862119999999, 138.5999594)');
        $this->assertInstanceOf('CrEOF\Spatial\PHP\Types\Geometry\Point', $user->getLocation(),
            'A Point Object was created');
    }
    
    
    /**
     * Test that an Exception is thrown when passing an invalid Point string to setLocation()
     *
     * @expectedException Exception
     * @@expectedExceptionMessage Invalid location string format: invalid format
     */
    public function testSetLocationStringThrowsLocation()
    {   
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        $user->setlocation('invalid format');
    }
    
    public function testUpdateAvatarGravatar()
    {
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        $user->setEmail('info@cynova.net'); // email that has a gravatar profile
        $user->updateAvatarGravatar();
        $this->assertEquals('http://www.gravatar.com/avatar/0410852438283d8bec95c3eef1fe0814', $user->getAvatarGravatar());
    }
    
    public function testUpdateAvatarGravatarUnavailable()
    {
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        $user->setEmail('email@withoutgravatar'); // email that has no gravatar profile
        $user->updateAvatarGravatar();
        $this->assertEquals('', $user->getAvatarGravatar());
    }
    
    public function testIsFollowing()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user1 = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user1) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $user2 = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('paultran');
        if (!$user2) {
            $this->fail('Missing User to follow with name "paultran" in test DB');
        }
        
        $this->assertFalse($user1->isFollowing($user2), 'user1 is not following user2');
        
        $user1->addIFollow($user2);
        $em->persist($user1);
        $em->flush();
        
        $this->assertTrue($user1->isFollowing($user2), 'user1 is following user2');
        
        // Test the other way of adding follower
        $this->assertFalse($user2->isFollowing($user1), 'user2 is not following user1');
        
        $user1->addMyFollower($user2);
        $em->persist($user1);
        $em->flush();
        
        $this->assertTrue($user2->isFollowing($user1), 'user2 is following user1');
        
    }
    
    public function testGetMainAvatar()
    {
        $stub = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $this->assertEquals('assets/img/avatar_man.jpg', $stub->getMainAvatar());
        
        $stub->setAvatarGravatar('gravatar');
        $this->assertEquals('gravatar', $stub->getMainAvatar());
        
        $stub->setName('test');
        $stub->setAvatar('avatar.png');
        $this->assertEquals('https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/profile/test/avatar.jpg', $stub->getMainAvatar());
        
        
    }
}