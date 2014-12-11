<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CampaignCreateShareImageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:campaign:create-share-image')
            ->setDescription('Create the Campaign share image')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the Campaign')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $campaign = $em->getRepository('TBFrontendBundle:Campaign')->findOneById($id);
        if (!$campaign) {
            throw new \Exception(sprintf('Campaign with id %s not found', $id));
        }
        
        $imageGenerator = $this->getContainer()->get('tb.image.generator');   
        $imageGenerator->createCampaignShareImage($campaign);

        $output->writeln('OK'); // Don't change the output, it would break the RabbitMQ worker
    }
}