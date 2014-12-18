<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserUpdateNewsletterListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:user:update-newsletter-list')
            ->setDescription('Updates the Newsletter list at Mandrill and sets firstname and lastname of subscribed users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailproxy = $this->getContainer()->get('tb.mailproxy');   
        foreach ($this->getNewsletterSubscriber() as $user) {
            $mailproxy->editNewsletterSubscriber($user->getEmail(), $user->getFirstName(), $user->getLastName());
        }       
    }
    
    protected function getNewsletterSubscriber() 
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery('
                SELECT u FROM TBFrontendBundle:User u
                WHERE u.newsletter = true');
        
        return $query->getResult();  
    }
}