<?php

namespace TB\Bundle\FrontendBundle\Util;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Doctrine\ORM\EntityManager;

/**
 * Creates the activity feed for each user
 */
class ActivityConsumer implements ConsumerInterface
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function execute(AMQPMessage $msg)
    {
        
        $isSuccess = true;
        
        if (!$isSuccess) {
            // the message will be rejected by the consumer and requeued by RabbitMQ.
            // Any other value not equal to false will acknowledge the message and remove it from the queue
            return false;
        }
    }
}