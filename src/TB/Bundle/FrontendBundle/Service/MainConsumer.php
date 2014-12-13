<?php

namespace TB\Bundle\FrontendBundle\Service;

use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use PhpAmqpLib\Message\AMQPMessage;
use Doctrine\ORM\EntityManager;
use TB\Bundle\FrontendBundle\Service\ActivityFeedGenerator;
use PhpAmqpLib\Connection\AMQPConnection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends the Consumer to to set a max consumer count and a time after the consumer stops
 */
class MainConsumer extends Consumer
{
    protected $container;
    
    protected $consumerCount;
    
    protected $maxConsumerCount;
    
    protected $ttl;
    
    protected $start;
    
    public function __construct(AMQPConnection $conn, ContainerInterface $container, AMQPChannel $ch = null, $consumerTag = null)
    {
        $this->container = $container;
        $this->setExchangeOptions(['name' => $container->getParameter('rabbit_mq_main_queue'), 'type' => 'direct']);
        $this->setQueueOptions(['name' => $container->getParameter('rabbit_mq_main_queue')]);
        $this->callback = array($this, 'execute');
        $this->maxConsumerCount = 3;
        $this->ttl = 1800;
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
                $isSuccess = $this->callCommand(sprintf('tb:activity:create-feed %s --fault-tolerant=true', $obj->id));
                break;
            case 'routeShareImage':
                $isSuccess = $this->callCommand(sprintf('tb:route:create-share-image %s --fault-tolerant=true', $obj->id));
                break;
            case 'routeIndex':
                $isSuccess = $this->callCommand(sprintf('tb:search:index route %s', $obj->id));
                break;
            case 'userIndex':
                $isSuccess = $this->callCommand(sprintf('tb:search:index user_profile %s', $obj->id));
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
        $handle = popen(sprintf('php -dmemory_limit=256M %s %s', $console, $command), 'r');
        $output = '';
        while (!feof($handle)) {
            $output .= fread($handle, 2096);
        }
        
        // The script outputs 'OK' for success, test only the last 2 characters to handle php error messages and other debug output of the command
        return (substr(trim($output), -2) == 'OK') ? true : false;
    }
    
}