<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use TB\Bundle\FrontendBundle\Entity\RoutePublishActivity;
use TB\Bundle\FrontendBundle\Entity\UserFollowActivity;
use TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity;

class ActivityFeedGeneratorTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }

    /**
     * Test JSON serialization of RoutePublishActivity
     */
    public function testSerializeRoutePublishActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\ActivityStreamData',
        ]); 
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
    
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('paultran');
    
        if (!$user) {
            $this->fail('Missing User with name "paultran" in test DB');
        }
        
        $generator = $this->getContainer()->get('activity_feed_generator');
        
        $feed = $generator->getFeedForUser($user->getId());
            
        $this->assertTrue(isset($feed['items']), 'Activity contains items array');
        $this->assertInternalType('array', $feed['items'], 'Activity field items is of type array');
        $this->assertTrue(isset($feed['totalItems']), 'Activity contains totalItems field');
        $this->assertEquals(2, $feed['totalItems'], 'Activity totalItems field value is"2"');
    }

}    