<?php

namespace TB\Bundle\APIBundle\Service;

use Doctrine\ORM\EntityManager;
use Exception;

class MailchimpWebhook
{
    protected $em;

    function __construct(EntityManager $em) 
    {
        $this->em = $em;
    }
    
    public function process($type, $data) 
    {
        switch ($type) {
            case 'subscribe':
                $this->processSubscribe($data);
                break;
            case 'unsubscribe':
                $this->processUnsubscribe($data);
                break;
        }
    }
    
    protected function processSubscribe($data) 
    {
        if (!isset($data['email'])) {
            throw new Exception('Missing email field in subscribe webhook data');
        }
            
        $user = $this->em->getRepository('TBFrontendBundle:User')->findOneByEmailCanonical($data['email']);
        if (!$user) {
            throw new \Exception(sprintf('User not found for email %s', $data['email']));
        }
        
        $user->setNewsletter(true);
        $this->em->persist($user);
        $this->em->flush();
        
        return true;
    }
    
    protected function processUnsubscribe($data) 
    {
        if (!isset($data['email'])) {
            throw new Exception('Missing email field in subscribe webhook data');
        }
            
        $user = $this->em->getRepository('TBFrontendBundle:User')->findOneByEmailCanonical($data['email']);
        if (!$user) {
            throw new Exception(sprintf('User not found for email %s', $data['email']));
        }
        
        $user->setNewsletter(false);
        $this->em->persist($user);
        $this->em->flush();
        
        return true;
    }
    
}
