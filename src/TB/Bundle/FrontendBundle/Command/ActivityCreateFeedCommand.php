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
            ->setName('tb:activity:create-feed')
            ->setDescription('Create the UserActivity feed for an Activity item')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the Activity item')
            ->addOption('fault-tolerant', 'f', InputOption::VALUE_OPTIONAL, 'If set to true, no exception is thrown', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $faultTolerant = $input->getOption('fault-tolerant');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $activity = $em->getRepository('TBFrontendBundle:Activity')->findOneById($id);
        if (!$activity) {
            if ($faultTolerant === false) {
                throw new \Exception(sprintf('Activity with id %s not found', $id));
            } else {
                $output->writeln(sprintf('Activity with id %s not found', $id));
                $output->writeln('OK');
                
                return true;
            }
        }
        
        $this->getContainer()->get('tb.activity.feed.generator')->createFeedFromActivity($activity);   

        $output->writeln('OK');
    }
}