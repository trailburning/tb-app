<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BitlyUrlCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:bitly:create')
            ->setDescription('Create bitly URL\'s')
            ->addArgument('type', InputArgument::OPTIONAL, 'The type to generate URL\'s for')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->output = $output;
        
        switch ($type) {
            case 'route':
                $this->generateRouteUrls();
                break;
            case 'event':
                $this->generateEventUrls();
                break;
            case 'campaign':
                $this->generateCampaignUrls();
                break;
            case 'editorial':
                $this->generateEditorialUrls();
                break;
            case null:
                $this->generateRouteUrls();
                $this->generateEventUrls();
                $this->generateCampaignUrls();
                $this->generateEditorialUrls();
                break;                                    
            default:
                $output->writeln(sprintf('<error>Unknown type "%s"</error>', $type));
                break;
        }
    }
    
    protected function generateRouteUrls() 
    {
        $routes = $this->em->createQuery('
            SELECT r FROM TBFrontendBundle:Route r
            WHERE r.publish = true')
            ->getResult();
        
        $count = 0;
        foreach ($routes as $route) {
            if ($route->getBitlyUrl() == null) {
                $route->setBitlyUrl($this->generateBitlyUrl('trail', ['trailSlug' => $route->getSlug()]));
                $this->em->persist($route);
                // Don't exceed the bitly API limit
                usleep(500000);
            }
        }
        $this->em->flush();
        $this->output->writeln(sprintf('Generated Bitly URL\'s for %s Routes', $count));
    }
    
    protected function generateEventUrls() 
    {
        $events = $this->em->createQuery('
            SELECT e FROM TBFrontendBundle:Event e
            WHERE e.publish = true')
            ->getResult();
        
        $count = 0;
        foreach ($events as $event) {
            if ($event->getBitlyUrl() == null) {
                $event->setBitlyUrl($this->generateBitlyUrl('event', ['slug' => $event->getSlug()]));
                $this->em->persist($route);
                // Don't exceed the bitly API limit
                usleep(500000);
            }
        }
        $this->em->flush();
        $this->output->writeln(sprintf('Generated Bitly URL\'s for %s Routes', $count));
    }
    
    protected function generateBitlyUrl($route, $parameters) 
    {
        $bitly = $this->getContainer()->get('tb.bitly_client');
        $router = $this->getContainer()->get('router');
        
        $url = 'http://www.trailburning.com' . $router->generate($route, $parameters);
        
        $response= $bitly->shorten([
            'longUrl' => $url,
        ]);

        return $response['url'];
    }
    
}