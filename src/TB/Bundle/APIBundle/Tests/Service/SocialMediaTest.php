<?php 

namespace TB\Bundle\ApiBundle\Tests\Service;

use TB\Bundle\APIBundle\Tests\AbstractApiTest;

class SocialMediaTest extends AbstractApiTest
{
    
    public function testSearch() 
    {
        $socialMedia = $this->getContainer()->get('tb.socialmedia');
        
        $result = $socialMedia->search('trailburning');

        $this->assertInternalType('array', $result);
        $this->assertEquals(3, count($result));
    }
    
    public function testFormatText() 
    {
        $socialMedia = $this->getContainer()->get('tb.socialmedia');
        
        $text = 'This is a Text with a URL that goes http://www.trailburning.com/ and some more text';
        $expected = 'This is a Text with a URL that goes <a href="http://www.trailburning.com/" target="_blank">http://www.trailburning.com/</a> and some more text';
        
        $text = $this->callProtectedMethod($socialMedia, 'formatText', [$text]);
        $this->assertEquals($expected, $text);
    }
}    