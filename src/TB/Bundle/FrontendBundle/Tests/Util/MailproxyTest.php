<?php 

namespace TB\Bundle\FrontendBundle\Tests\Util;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;

class MediaproxyTest extends AbstractFrontendTest
{
    
    public function testPost()
    {
        $mediaproxy = $this->getContainer()->get('tb.mailproxy');
        $mediaproxy->post('patrick@trailburning.com');
    }
    
}