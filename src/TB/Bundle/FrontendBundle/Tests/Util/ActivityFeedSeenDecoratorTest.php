<?php 

namespace TB\Bundle\FrontendBundle\Tests\Util;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Util\ActivityFeedSeenDecorator;
use TB\Bundle\FrontendBundle\Entity\UserProfile;

class ActivityFeedSeenDecoratorTest extends AbstractFrontendTest
{

    /**
     * Test that decorate() adds the seen flag to to activity feed data array
     */
    public function testDecorate()
    {
        date_default_timezone_set('UTC');
        
        $publishedDate = new \DateTime('now');
        $item = ['published' => $publishedDate->format('Y-m-d\TH:i:s\Z')];
        
        // Test activityLastViewed = null
        $user = new UserProfile();
        $decorator = new ActivityFeedSeenDecorator($user);
        $item = $decorator->decorate($item);
        $this->assertFalse($item['seen'], 'The seen flag is false for activityLastViewed = null');
        
        // Test activityLastViewed smaller published
        $user = new UserProfile();
        $viewedDate = new \DateTime('now');
        $viewedDate->sub(new \DateInterval('PT2M')); // substract 2 minutes
        $user->setActivityLastViewed($viewedDate);
        
        $publishedDate = new \DateTime('now');
        $publishedDate->sub(new \DateInterval('PT1M')); // substract 1 minute
        $item = ['published' => $publishedDate->format('Y-m-d\TH:i:s\Z')];
        
        $decorator = new ActivityFeedSeenDecorator($user);
        $item = $decorator->decorate($item);
        $this->assertFalse($item['seen'], 'The seen flag is false for activityLastViewed smaller published');
        

         // Test activityLastViewed larger published
        $user = new UserProfile();
        $viewedDate = new \DateTime('now');
        $viewedDate->sub(new \DateInterval('PT1M')); // substract 1 minute
        
        $publishedDate = new \DateTime('now');
        $publishedDate->sub(new \DateInterval('PT2M')); // substract 2 minutes
        
        $user->setActivityLastViewed($viewedDate);
        $item = ['published' => $publishedDate->format('Y-m-d\TH:i:s\Z')];
        
        $decorator = new ActivityFeedSeenDecorator($user);
        $item = $decorator->decorate($item);
        $this->assertTrue($item['seen'], 'The seen flag is true for activityLastViewed larger published');
    }
}
