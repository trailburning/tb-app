<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:search:index')
            ->setDescription('Indexes all entities a specified type')
            ->addArgument('type', InputArgument::REQUIRED, 'The type to index')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->client = $this->getContainer()->get('tb.elasticsearch.client');
        $type = $input->getArgument('type');
        
        switch ($type) {
            case 'suggest':
            $this->initSuggestType();
                break;
            default:
                $output->writeln(sprintf('<error>Unknown type "%s"</error>', $type));
                break;
        }
    }
    
    protected function initSuggestType()
    {
        // Create the new index 
        $params = [
            'index' => 'trailburning',
            'type' => 'suggest',
            'body' => [
                'index' => [
                    '_id' => [],
                ],
            ],
        ];
        
        $routes = $this->em->createQuery('
                SELECT r FROM TBFrontendBundle:Route r
                WHERE r.publish = true AND r.approved = true')
            ->getResult();
        
        $i = 0;   
        $body = [];
        foreach ($routes as $route) {
            $i++;
            $id = sprintf('route_%s', $route->getId());
            $doc = [
                'title' => $route->getName(),
            ];
            
            $body[] = [
                'index' => [
                    '_id' => $id,
                ],
                'doc' => $doc,
            ];
            
            if ($i >= 100) {
                $bulk = $params;
                $bulk['body'] = $body;
                $this->client->bulk($bulk);
                $i = 0;
                $body = [];
            }    
        }
        
        if (count($body) > 0) {
            $bulk = $params;
            $bulk['body'] = $body;
            $this->client->bulk($bulk);
        }
        
    }
}