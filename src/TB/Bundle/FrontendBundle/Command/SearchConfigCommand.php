<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchConfigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:search:config')
            ->setDescription('Updates the settings of the index');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('tb.elasticsearch.client');
        
        // Close the index
        $params = [
            'index' => 'trailburning',
        ];
        $client->indices()->close($params);
        
        
        // Update the index
        $params = [
            'index' => 'trailburning',
            'body' => [
                'index' => [
                    'analysis' => [
                        'analyzer' => [
                            'autocomplete_edge' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'stop', 'kstem'],
                            ],    
                        ],
                    ],
                ],
            ],
        ];
        
        $client->indices()->putSettings($params);
        
        // Open the index
        $params = [
            'index' => 'trailburning',
        ];
        $client->indices()->open($params);
    }
    
}