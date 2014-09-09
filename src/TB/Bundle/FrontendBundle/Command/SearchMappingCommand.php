<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchMappingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:search:mapping')
            ->setDescription('Sets or updates the mapping for a specified type')
            ->addArgument('type', InputArgument::REQUIRED, 'The type to populate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client = $this->getContainer()->get('tb.elasticsearch.client');
        $type = $input->getArgument('type');
        
        switch ($type) {
            case 'suggest':
            $this->populateSuggestMapping();
                break;
            default:
                $output->writeln(sprintf('<error>Unknown type "%s"</error>', $type));
                break;
        }
    }
    
    protected function populateSuggestMapping()
    {   
        // Create the new index 
        $params = [
            'index' => 'trailburning',
            'type' => 'suggest',
            'body' => [
                'suggest' => [
                    '_source' => [
                        'enabled' => true,
                    ],
                    'properties' => [
                        'id' => [
                            'type' => 'string', 
                            'analyzer' => 'standard',
                        ],
                        'text' => [
                            'type' => 'text',
                            'analyzer' => 'standard',
                        ],
                        'textng' => [
                            'type' => 'text',
                            'analyzer' => 'standard',
                        ],
                        'textnge' => [
                            'type' => 'text',
                            'analyzer' => 'standard',
                        ],
                    ],
                ],
            ],
        ];
        
        $this->client->indices()->putMapping($params);
    }
}