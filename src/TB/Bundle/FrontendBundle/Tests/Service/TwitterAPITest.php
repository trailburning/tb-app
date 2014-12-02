<?php 

namespace TB\Bundle\FrontendBundle\Tests\Service;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;

class TwitterAPITest extends AbstractFrontendTest
{

    public function testSearchTweets()
    {
        $this->loadFixtures([]); 
        $twitter = $this->getContainer()->get('twitter_api');
        $result = $twitter->searchTweets(['q' => '#trailrunning', 'result_type' => 'recent', 'count' => 2]);
        $this->assertEquals(2, count($result->statuses));
    }
    
    public function testBuildGetField() 
    {
        $twitter = $this->getContainer()->get('twitter_api');
        $parameters = [
            'q' => '#trailrunning', 
            'result_type' => 'recent', 
            'count' => 2
        ];
        $result = $this->callProtectedMethod($twitter, 'buildGetField', [$parameters]);
        $this->assertEquals('?q=%23trailrunning&result_type=recent&count=2', $result);
    }
    
}    