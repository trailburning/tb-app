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
            ->setName('route:rating')
            ->setDescription('Calculates a rating for all Routes based on user likes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $max = 0;
        $min = null;
        $ratings = [];
        $routes = $em->getRepository('TBFrontendBundle:Route')->findAll();
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT r.id, r.id, rl.date FROM TBFrontendBundle:Route r
                LEFT JOIN r.routeLikes rl WITH r.id = rl.routeId');
        $trails = $query->getResult();  
        foreach ($routes as $route) {
            $count = $route->getRouteLikes()->count()
            if ($count > $max) {
                $max = $count;
            }
            if ($min === null || $count < $min) {
                $min = $count;
            }
            $ratings[$route->getId()] = $count;
        }
        
    }
}