<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TB\Bundle\FrontendBundle\Entity\CampaignRoute;

class CampaignRouteRemoveCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:campaign:route:remove')
            ->setDescription('Remove a Route from a Campaign')
            ->addArgument('campaignId', InputArgument::REQUIRED, 'The id of the Campaign')
            ->addArgument('routeId', InputArgument::REQUIRED, 'The id of the Route')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $campaignId = $input->getArgument('campaignId');
        $routeId = $input->getArgument('routeId');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $query = $em->createQuery('
                SELECT cr FROM TBFrontendBundle:CampaignRoute cr
                WHERE cr.campaignId = :campaignId
                AND cr.routeId = :routeId')
            ->setParameter('campaignId', $campaignId)
            ->setParameter('routeId', $routeId);
        try {
            $campaignRoute = $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw new \Exception(sprintf('CampaignRoute %s not found for campaignId %s and routeId %s', $campaignId, $routeId));
        }
        
        $em->remove($campaignRoute);
        $em->flush();

        $output->writeln(sprintf('Route %s was removed from campaign %s', $campaignRoute->getRoute()->getSlug(), $campaignRoute->getCampaign()->getSlug()));
    }
}