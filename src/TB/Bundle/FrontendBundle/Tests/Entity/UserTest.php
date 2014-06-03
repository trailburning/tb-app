<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use TB\Bundle\FrontendBundle\Entity\User;

class UserTest extends AbstractFrontendTest
{
    
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
        $user1 = $this->getUser('mattallbeury');
        
        $user2 = $this->getUser('paultran');
        
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
    
    public function testGetAvatarUrl()
    {
        $this->loadFixtures([]);
        
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $this->assertEquals('http://assets.trailburning.com/images/icons/avatars/avatar_man.jpg', $user->getAvatarUrl($user),
            'Returns the avatar_man.jpg image with no avatar and gender set');
        
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $user->setGender(User::GENDER_NONE);
        $this->assertEquals('http://assets.trailburning.com/images/icons/avatars/avatar_man.jpg', $user->getAvatarUrl($user),
                'Returns the avatar_man.jpg image with no avatar and gender set to none');
        
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $user->setGender(User::GENDER_MALE);
        $this->assertEquals('http://assets.trailburning.com/images/icons/avatars/avatar_man.jpg', $user->getAvatarUrl($user),
                'Returns the avatar_man.jpg image with no avatar and gender set to male');
                
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $user->setGender(User::GENDER_FEMALE);
        $this->assertEquals('http://assets.trailburning.com/images/icons/avatars/avatar_woman.jpg', $user->getAvatarUrl($user),
                'Returns the avatar_woman.jpg image with no avatar and gender set to female');        
                
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $user->setAvatarGravatar('gravatar.jpg');
        $this->assertEquals('gravatar.jpg', $user->getAvatarUrl($user),
                'Returns the gravatar.jpg image with gravatar image set');
        
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $user->setName('name');
        $user->setAvatar('avatar.jpg');
        $this->assertEquals('http://assets.trailburning.com/images/profile/name/avatar.jpg', $user->getAvatarUrl($user),
                'Returns the avatar.jpg image with avatar image set');    
                
    }
}