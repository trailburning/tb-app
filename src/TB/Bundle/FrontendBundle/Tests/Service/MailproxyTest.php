<?php 

namespace TB\Bundle\FrontendBundle\Tests\Service;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;

class MailproxyTest extends AbstractFrontendTest
{
    
    public function setUp()
    {
        parent::setUp();
        $this->mailproxy = $this->getContainer()->get('tb.mailproxy');   
    }
    
    public function testAddNewsletterSubscriber()
    {
        // $result = $this->mailproxy->addNewsletterSubscriber('patrick@trailburning.com');
        // $this->assertTrue($result);
    }
    
    public function testRemoveNewsletterSubscriber()
    {
        // $result = $this->mailproxy->removeNewsletterSubscriber('patrick@trailburning.com');
        // $this->assertTrue($result);
    }
    
    public function testSendWelcomeMail()
    {
        // $this->mailproxy->sendWelcomeMail('patrick@trailburning.com', 'Patrick');
    }
   
}