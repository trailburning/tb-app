<?php 

namespace TB\Bundle\FrontendBundle\Twig\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Service\MediaImporter;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use TB\Bundle\FrontendBundle\Entity\RouteLike;
use TB\Bundle\FrontendBundle\Entity\User;

class TBExtensionTest extends AbstractFrontendTest
{
        
    public function setUp()
    {
        $this->extension = $this->getContainer()->get('tb.twig.tb_extension');
    }
    
    public function testUrlTruncateFilter()
    {
        $this->assertEquals('test.de/test', $this->extension->urlTruncateFilter('http://www.test.de/test'),
            'urlTruncateFilter() truncates http:// and www.');
        $this->assertEquals('test.de/test', $this->extension->urlTruncateFilter('https://test.de/test'),
            'urlTruncateFilter() truncates https://');
    }
    
    public function testUrlShareableFilter()
    {
        $this->assertEquals('www.test.de%2Ftest', $this->extension->urlShareableFilter('http://www.test.de/test'),
            'urlTruncateFilter() truncates http://');
        $this->assertEquals('test.de%2Ftest', $this->extension->urlShareableFilter('https://test.de/test'),
            'urlTruncateFilter() truncates https://');
    }
    
    public function testDimensionFormatFilter()
    {
        $this->assertEquals('1’000 m', $this->extension->dimensionFormatFilter(1000, 'm'),
            'thausands separator is included, unit is added');
        $this->assertEquals('10 km', $this->extension->dimensionFormatFilter(1000, 'km', 100),
            'value is relative to the base passed to the function');
        $this->assertEquals('1’001 m', $this->extension->dimensionFormatFilter(1000.5678, 'm'),
            'value is rounded');
    }
    
    public function testExtractEntity()
    {
        // setup Entity mock
        $entity = $this->getMock('Entity', array('getFirstField', 'getSecondField'), array(), '', false);
        $entity->expects($this->exactly(1))
                ->method('getFirstField')
                ->will($this->returnValue('firstValue'));
        $entity->expects($this->exactly(1))
                ->method('getSecondField')
                ->will($this->returnValue('secondValue'));
        
        $expected = ['firstField' => 'firstValue', 'secondField' => 'secondValue'];
        $this->assertEquals($expected, $this->extension->extractEntity($entity, ['firstField', 'secondField']),
            'extractEntity returns an array of values for the requested fields');
    }
    
    public function testUserIsFollowing()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user1 = $this->getUser('mattallbeury');
        $user2 = $this->getUser('paultran');
        
        $this->assertFalse($this->extension->userIsFollowing($user1, $user2), 'user1 is not following user2');
        
        $user1->addUserIFollow($user2);
        $em->persist($user1);
        $em->flush();
        
        $this->assertTrue($this->extension->userIsFollowing($user1, $user2), 'user1 is following user2');
    }
    
    public function testUserIsFollowingCampaign()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $campaign = $this->getCampaign('urbantrails-london');
        $user = $this->getUser('paultran');
        
        $this->assertFalse($this->extension->userIsFollowingCampaign($user, $campaign), 'user is not following the campaign');
        
        $user->addCampaignsIFollow($campaign);
        $em->persist($user);
        $em->flush();
        
        $this->assertTrue($this->extension->userIsFollowingCampaign($user, $campaign), 'user is following campaign');
    }
    
    public function testRouteHasUserLike()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $this->getRoute('grunewald');
        $user = $this->getUser('mattallbeury');
        
        $this->assertFalse($this->extension->routeHasUserLike($route, $user), 'route is not liked by user');
        
        $routeLike = new RouteLike();
        $routeLike->setUser($user);
        $routeLike->setRoute($route);
        
        $em->persist($routeLike);
        $em->flush();
        $em->refresh($route);
        
        $this->assertTrue($this->extension->routeHasUserLike($route, $user), 'route is not liked by user');
    }
        
    public function testGetUserAvatarUrl()
    {
        $this->loadFixtures([]);
        
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $this->assertEquals('http://assets.trailburning.com/images/icons/avatars/avatar_man.jpg', $this->extension->getUserAvatarUrl($user),
            'Returns the avatar_man.jpg image with no avatar and gender set');
        
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $user->setGender(User::GENDER_NONE);
        $this->assertEquals('http://assets.trailburning.com/images/icons/avatars/avatar_man.jpg', $this->extension->getUserAvatarUrl($user),
                'Returns the avatar_man.jpg image with no avatar and gender set to none');
        
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $user->setGender(User::GENDER_MALE);
        $this->assertEquals('http://assets.trailburning.com/images/icons/avatars/avatar_man.jpg', $this->extension->getUserAvatarUrl($user),
                'Returns the avatar_man.jpg image with no avatar and gender set to male');
                
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $user->setGender(User::GENDER_FEMALE);
        $this->assertEquals('http://assets.trailburning.com/images/icons/avatars/avatar_woman.jpg', $this->extension->getUserAvatarUrl($user),
                'Returns the avatar_woman.jpg image with no avatar and gender set to female');        
                
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $user->setAvatarGravatar('gravatar.jpg');
        $this->assertEquals('gravatar.jpg', $this->extension->getUserAvatarUrl($user),
                'Returns the gravatar.jpg image with gravatar image set');
        
        $user = $this->getMockForAbstractClass('TB\\Bundle\\FrontendBundle\\Entity\\User');
        $user->setName('name');
        $user->setAvatar('avatar.jpg');
        $this->assertEquals('http://assets.trailburning.com/images/profile/name/avatar.jpg', $this->extension->getUserAvatarUrl($user),
                'Returns the avatar.jpg image with avatar image set');        
    }
}