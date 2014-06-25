<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RouteRatingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:route:set-rating')
            ->setDescription('Calculates the rating for all Routes based on user likes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        // $routes = $em->getRepository('TBFrontendBundle:Route')->findAll();
        $query = $em->createQuery('
                SELECT r, rl FROM TBFrontendBundle:Route r
                LEFT JOIN r.routeLikes rl WITH r.id = rl.routeId');
        $routes = $query->getResult();  
        
        foreach ($routes as $route) {
            $count = $route->getRouteLikes()->count();
            if ($count >= 40) {
                $rating = 5;
            } elseif ($count >= 25) {
                $rating = 4.5;
            } elseif ($count >= 20) {
                $rating = 4;
            } elseif ($count >= 15) {
                $rating = 3.5;
            } elseif ($count >= 10) {
                $rating = 3;
            } elseif ($count >= 6) {
                $rating = 2.5;
            } elseif ($count >= 4) {
                $rating = 2;
            } elseif ($count >= 2) {
                $rating = 1.5;
            } elseif ($count >= 1) {
                $rating = 1;
            } else {
                $rating = null;
            }
            
            $em->createQuery('UPDATE TBFrontendBundle:Route r SET r.rating = :rating WHERE r.id = :id')
                ->setParameter('rating', $rating)
                ->setParameter('id', $route->getId())
                ->execute();

        }
        
        
    }
}