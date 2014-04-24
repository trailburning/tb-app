<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ActivityCreateFeedCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('activity:create-feed')
            ->setDescription('Create the UserActivity feed for an Activity item')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the Activity item')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $activity = $em->getRepository('TBFrontendBundle:Activity')->findOneById($id);
        if ($activity) {
            $this->getContainer()->get('activity_feed_generator')->createFeedFromActivity($activity);   
        } else {
            throw new \Exception(sprintf('Activity with id %s not found', $id));
        }

        $output->writeln('OK');
    }
}