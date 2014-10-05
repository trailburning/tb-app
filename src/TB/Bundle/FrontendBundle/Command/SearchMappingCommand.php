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
            case 'brand_profile':
                $this->populateBrandProfileMapping();
                break;  
            case 'event':
                $this->populateEventMapping();
                break;                    
            case 'editorial':
                $this->populateEditorialMapping();
                break;  
            case 'all':
                $this->populateRouteMapping();
                $this->populateUserProfileMapping();
                $this->populateEventMapping();
                $this->populateEditorialMapping();
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
                                'suggest_engram_part', 
                                'suggest_engram_full', 
                                'suggest_phon'
                            ],
                        ],
                        'suggest_engram_part' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_engram_part',
                            'search_analyzer' => 'autocomplete_engram_part_q',
                        ],
                        'suggest_engram_full' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_engram_part',
                            'search_analyzer' => 'autocomplete_engram_part_q',
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
                                'suggest_engram_part', 
                                'suggest_engram_full', 
                                'suggest_phon'
                            ],
                        ],
                        'suggest_engram_part' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_engram_part',
                            'search_analyzer' => 'autocomplete_engram_part_q',
                        ],
                        'suggest_engram_full' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_engram_full',
                            'search_analyzer' => 'autocomplete_engram_full_q',
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
    
    protected function populateBrandProfileMapping()
    {   
        // Create the new index 
        $params = [
            'index' => 'trailburning',
            'type' => 'brand_profile',
            'body' => [
                'brand_profile' => [
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
                                'suggest_engram_part', 
                                'suggest_engram_full', 
                                'suggest_phon'
                            ],
                        ],
                        'suggest_engram_part' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_engram_part',
                            'search_analyzer' => 'autocomplete_engram_part_q',
                        ],
                        'suggest_engram_full' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_engram_full',
                            'search_analyzer' => 'autocomplete_engram_full_q',
                        ],
                        'suggest_phon' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'analyzer' => 'phonetic_text',
                        ],
                        'name' => [
                            'type' => 'string', 
                        ],
                        'display_name' => [
                            'type' => 'string', 
                        ],
                        'subtitle' => [
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
    
    protected function populateEventMapping()
    {   
        // Create the new index 
        $params = [
            'index' => 'trailburning',
            'type' => 'event',
            'body' => [
                'event' => [
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
                                'suggest_engram_part', 
                                'suggest_engram_full', 
                                'suggest_phon'
                            ],
                        ],
                        'suggest_engram_part' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_engram_part',
                            'search_analyzer' => 'autocomplete_engram_part_q',
                        ],
                        'suggest_engram_full' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_engram_full',
                            'search_analyzer' => 'autocomplete_engram_full_q',
                        ],
                        'suggest_phon' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'analyzer' => 'phonetic_text',
                        ],
                        'title' => [
                            'type' => 'string', 
                        ],
                        'title2' => [
                            'type' => 'string', 
                        ],
                        'slug' => [
                            'type' => 'string', 
                            'index' => 'not_analyzed',
                        ],
                        'logo_small' => [
                            'type' => 'string', 
                            'index' => 'not_analyzed',
                        ],
                    ],
                ],
            ],
        ];
        
        $this->client->indices()->putMapping($params);
    }
    
    protected function populateEditorialMapping()
    {   
        // Create the new index 
        $params = [
            'index' => 'trailburning',
            'type' => 'editorial',
            'body' => [
                'editorial' => [
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
                                'suggest_engram_part', 
                                'suggest_engram_full', 
                                'suggest_phon'
                            ],
                        ],
                        'suggest_engram_part' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_engram_part',
                            'search_analyzer' => 'autocomplete_engram_part_q',
                        ],
                        'suggest_engram_full' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'index_analyzer' => 'autocomplete_engram_full',
                            'search_analyzer' => 'autocomplete_engram_full_q',
                        ],
                        'suggest_phon' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'analyzer' => 'phonetic_text',
                        ],
                        'title' => [
                            'type' => 'string', 
                        ],
                        'slug' => [
                            'type' => 'string', 
                            'index' => 'not_analyzed',
                        ],
                        'image' => [
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