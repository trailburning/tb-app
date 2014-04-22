<?php

namespace TB\Bundle\FrontendBundle\Util;

use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use PhpAmqpLib\Message\AMQPMessage;
use Doctrine\ORM\EntityManager;
use TB\Bundle\FrontendBundle\Util\ActivityFeedGenerator;
use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Extends the Consumer to to set a max consumer count and a time after the consumer stops
 */
class MainConsumer extends Consumer
{
    protected $consumerCount;
    
    protected $maxConsumerCount;
    
    protected $ttl;
    
    protected $start;
    
    public function __construct(AMQPConnection $conn, AMQPChannel $ch = null, $consumerTag = null)
    {
        $this->setExchangeOptions(['name' => 'main', 'type' => 'direct']);
        $this->setQueueOptions(['name' => 'main']);
        $this->callback = array($this, 'execute');
        $this->maxConsumerCount = 3;
        $this->ttl = 300;
        $this->start = time();
        
        $this->setIdleTimeout($this->ttl);
        
        parent::__construct($conn, $ch, $consumerTag);
    }
    
    protected function queueDeclare()
    {
        if (null !== $this->queueOptions['name']) {
            list($queueName, ,$consumerCount) = $this->getChannel()->queue_declare($this->queueOptions['name'], $this->queueOptions['passive'],
                $this->queueOptions['durable'], $this->queueOptions['exclusive'],
                $this->queueOptions['auto_delete'], $this->queueOptions['nowait'],
                $this->queueOptions['arguments'], $this->queueOptions['ticket']);
            
            if (null !== $this->maxConsumerCount && $consumerCount >= $this->maxConsumerCount) {
                // Max consumer count reached
                exit;
            }
            
            if (isset($this->queueOptions['routing_keys']) && count($this->queueOptions['routing_keys']) > 0) {
                foreach ($this->queueOptions['routing_keys'] as $routingKey) {
                    $this->getChannel()->queue_bind($queueName, $this->exchangeOptions['name'], $routingKey);
                }
            } else {
                $this->getChannel()->queue_bind($queueName, $this->exchangeOptions['name'], $this->routingKey);
            }

            $this->queueDeclared = true;
        }
    }
    
    protected function maybeStopConsumer()
    {
        // stop consuming when ttl is reached
        if (null !== $this->ttl && ($this->start + $this->ttl) < time()) {
            $this->getChannel()->basic_cancel($this->getConsumerTag());
        }
        
        // calculate a new timeout based on the time the script is already running
        if (null !== $this->ttl) {
            $this->setIdleTimeout($this->ttl - (time() - $this->start));
        }
        
        parent::maybeStopConsumer();
    }
    
    public function execute(AMQPMessage $msg)
    {
        $obj = json_decode($msg->body);
        
        switch ($obj->type) {
            case 'activity':
                $isSuccess = $this->callCommand(sprintf('activity:create-feed %s', $obj->id));
                break;
            default:
                $isSuccess = false;
                break;
        }
        
        // When $isSuccess is false, the message will be rejected by the consumer and requeued by RabbitMQ.
        // Any other value not equal to false will acknowledge the message and remove it from the queue
        return $isSuccess;
    }
    
    public function callCommand($command)
    {
        $console = realpath(__DIR__ . '/../../../../../app/console');
        $handle = popen(sprintf('%s %s', $console, $command), 'r');
        $output = fread($handle, 2096);
        
        return ($output == 'OK') ? true : false;
    }
    
}