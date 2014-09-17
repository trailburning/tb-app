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
            case 'route':
                $this->populateRouteMapping();
                break;
            case 'user_profile':
                $this->populateUserProfileMapping();
                break;    
            default:
                $output->writeln(sprintf('<error>Unknown type "%s"</error>', $type));
                break;
        }
    }
    
    protected function populateRouteMapping()
    {   
        // Create the new index 
        $params = [
            'index' => 'trailburning',
            'type' => 'route',
            'body' => [
                'route' => [
                    '_id' => [
                        'path' => 'id',
                    ],
                    'dynamic' => 'strict',
                    'properties' => [
                        'id' => [
                            'type' => 'string', 
                            'index' => 'not_analyzed',
                        ],
                        'suggest_text' => [
                            'type' => 'string',
                            'copy_to' => [
                                'suggest_ng', 
                                'suggest_nge', 
                                'suggest_phon'
                            ],
                        ],
                        'suggest_ng' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_ngram',
                            'search_analyzer' => 'whitespace_analyzer',
                        ],
                        'suggest_nge' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_edge',
                            'search_analyzer' => 'whitespace_analyzer',
                        ],
                        'suggest_phon' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'analyzer' => 'phonetic_text',
                        ],
                        'name' => [
                            'type' => 'string', 
                        ],
                        'short_name' => [
                            'type' => 'string', 
                        ],
                        'region' => [
                            'type' => 'string', 
                        ],
                        'slug' => [
                            'type' => 'string', 
                            'index' => 'not_analyzed',
                        ],
                        'media' => [
                            'type' => 'string', 
                            'index' => 'not_analyzed',
                        ],
                    ],
                ],
            ],
        ];
        
        $this->client->indices()->putMapping($params);
    }
    
    protected function populateUserProfileMapping()
    {   
        // Create the new index 
        $params = [
            'index' => 'trailburning',
            'type' => 'user_profile',
            'body' => [
                'user_profile' => [
                    '_id' => [
                        'path' => 'id',
                    ],
                    'dynamic' => 'strict',
                    'properties' => [
                        'id' => [
                            'type' => 'string', 
                            'index' => 'not_analyzed',
                        ],
                        'suggest_text' => [
                            'type' => 'string',
                            'copy_to' => [
                                'suggest_ng', 
                                'suggest_nge', 
                                'suggest_phon'
                            ],
                        ],
                        'suggest_ng' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_ngram',
                            'search_analyzer' => 'whitespace_analyzer',
                        ],
                        'suggest_nge' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_edge',
                            'search_analyzer' => 'whitespace_analyzer',
                        ],
                        'suggest_phon' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'analyzer' => 'phonetic_text',
                        ],
                        'name' => [
                            'type' => 'string', 
                        ],
                        'first_name' => [
                            'type' => 'string', 
                        ],
                        'last_name' => [
                            'type' => 'string', 
                        ],
                        'location' => [
                            'type' => 'string', 
                        ],
                        'avatar' => [
                            'type' => 'string', 
                            'index' => 'not_analyzed',
                        ],
                    ],
                ],
            ],
        ];
        
        $this->client->indices()->putMapping($params);
    }
}