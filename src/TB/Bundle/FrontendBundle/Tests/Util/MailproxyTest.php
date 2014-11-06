<?php 

namespace TB\Bundle\FrontendBundle\Tests\Util;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;

class MailproxyTest extends AbstractFrontendTest
{
    
    public function setUp() 
    {
        $this->mailproxy = $this->getContainer()->get('tb.mailproxy');   
    }
    
    public function testAddNewsletterSubscriber()
    {
        // $this->mailproxy->addNewsletterSubscriber('patrick@trailburning.com');
    }
    
    public function testSendWelcomeMail()
    {
        $this->mailproxy->sendWelcomeMail('patrick@trailburning.com', 'Patrick');
    }
    
}