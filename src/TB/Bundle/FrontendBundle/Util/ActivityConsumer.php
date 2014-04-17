<?php

namespace TB\Bundle\FrontendBundle\Util;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Doctrine\ORM\EntityManager;
use TB\Bundle\FrontendBundle\Util\ActivityFeedGenerator;

/**
 * Creates the activity feed for each user
 */
class ActivityConsumer implements ConsumerInterface
{
    protected $em;
    protected $activityFeedGenerator;

    public function __construct(EntityManager $em, ActivityFeedGenerator $activityFeedGenerator)
    {
        $this->em = $em;
        $this->activityFeedGenerator = $activityFeedGenerator;
    }
    
    public function execute(AMQPMessage $msg)
    {
        
        $obj = json_decode($msg->body);
        
        try {
            $activity = $this->em->getRepository('TBFrontendBundle:Activity')->findOneById($obj->id);
            $this->activityFeedGenerator->createFeedFromActivity($activity);
            $isSuccess = true;
        } catch (\Exception $e) {
            $isSuccess = false;    
        }
        
        // When $isSuccess is false, the message will be rejected by the consumer and requeued by RabbitMQ.
        // Any other value not equal to false will acknowledge the message and remove it from the queue
        return $isSuccess;
    }
}