<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TB\Bundle\FrontendBundle\Entity\CampaignRoute;

class CampaignRouteAddCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:campaign:route:add')
            ->setDescription('Add a Route to a Campaign')
            ->addArgument('campaignId', InputArgument::REQUIRED, 'The id of the Campaign')
            ->addArgument('routeId', InputArgument::REQUIRED, 'The id of the Route')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $campaignId = $input->getArgument('campaignId');
        $routeId = $input->getArgument('routeId');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $campaign = $em->getRepository('TBFrontendBundle:Campaign')->findOneById($campaignId);
        if (!$campaign) {
            throw new \Exception(sprintf('Campaign with id %s not found', $campaignId));
        }
        
        $route = $em->getRepository('TBFrontendBundle:Route')->findOneById($routeId);
        if (!$route) {
            throw new \Exception(sprintf('Route with id %s not found', $routeId));
        }
        
        $campaignRoute = new CampaignRoute();
        $campaignRoute->setCampaign($campaign);
        $campaignRoute->setRoute($route);
        
        $em->persist($campaignRoute);
        $em->flush();

        $output->writeln(sprintf('Route %s was added to campaign %s', $route->getSlug(), $campaign->getSlug()));
    }
}