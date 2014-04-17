<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use PhpAmqpLib\Message\AMQPMessage;

class ActivityConsumerTest extends AbstractFrontendTest
{
    
    /**
     * Test that the activity is fetched from the DB and passed to the ActivityFeedGenerator::createFeedFromActivity() method
     */
    public function testExecute()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\ActivityStreamData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
    
        $query = $em->createQuery('SELECT a FROM TBFrontendBundle:Activity a ORDER BY a.id ASC');
        try {
            $activity = $query->setMaxResults(1)->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $this->fail('No Activity found in Test DB');
        }
        
        $message = new AMQPMessage(json_encode($activity->exportMessage()));
        
        // replace the ActivityFeedGenerator service with a stub
        $generator = $this->getServiceMockBuilder('activity_feed_generator')->getMock();
        // The createFeedFromActivity method should be called onceinside the execute() method
        $generator->expects($this->exactly(1))
            ->method('createFeedFromActivity')
            ->will($this->returnCallback(array($this, 'assertCreateFeedFromActivity'))); // Verify the parameter passed to the method
        
        $this->getContainer()->set('activity_feed_generator', $generator);
        
        // get the consumer service, it refeences the ActivityFeedGenerator stub gthat we just created
        $consumer = $this->getContainer()->get('activity_consumer');
        
        $result = $consumer->execute($message);
        
        $this->assertTrue($result, 'execute returns true');
    }
    
    public function assertCreateFeedFromActivity($activity)
    {
        $this->assertInstanceOf('TB\Bundle\FrontendBundle\Entity\Activity', $activity,
            'An Activity object was passed to ActivityFeedGenerator::createFeedFromActivity()');
    }
    
    /**
     * Simulate the case when the execute fails for some reason
     */
    public function testExecuteFails()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\ActivityStreamData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
    
        $query = $em->createQuery('SELECT a FROM TBFrontendBundle:Activity a ORDER BY a.id ASC');
        try {
            $activity = $query->setMaxResults(1)->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $this->fail('No Activity found in Test DB');
        }
        
        $message = new AMQPMessage(json_encode($activity->exportMessage()));
        
        // replace the ActivityFeedGenerator service with a stub
        $generator = $this->getServiceMockBuilder('activity_feed_generator')->getMock();
        // The createFeedFromActivity method should be called onceinside the execute() method
        $generator->expects($this->exactly(1))
            ->method('createFeedFromActivity')
            ->will($this->throwException(new \Exception()));
        
        $this->getContainer()->set('activity_feed_generator', $generator);
        
        // get the consumer service, it refeences the ActivityFeedGenerator stub gthat we just created
        $consumer = $this->getContainer()->get('activity_consumer');
        
        $result = $consumer->execute($message);
        
        $this->assertFalse($result, 'execute returns false');
    }

}    