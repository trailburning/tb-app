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
                        'filter' => [
                            'punctiation_replace' => [
                                'type' => 'pattern_replace',
                                'pattern' => '([\.,;:-_])',
                                'replacement' => '',
                            ],
                            'nonealpha_replace' => [
                                'type' => 'pattern_replace',
                                'pattern' => '([^\w\d\*æøåÆØÅ ])',
                                'replacement' => '',
                            ],
                            'max_toke_length' => [
                                'type' => 'pattern_replace',
                                'pattern' => '(.{30})(.*)?',
                                'replacement' => '',
                            ],
                            'edge_ngram' => [
                                'type' => 'edgeNGram',
                                'min_gram' => 1,
                                'max_gram' => 30,
                            ],
                            'ngram' => [
                                'type' => 'edgeNGram',
                                'min_gram' => 1,
                                'max_gram' => 20,
                            ],
                            'ngram_delimiter' => [
                                'type' => 'word_delimiter',
                                'generate_word_parts' => true,
                                'generate_number_parts' => true,
                                'catenate_words' => false,
                                'catenate_numbers' => false,
                                'catenate_all' => false,
                                'split_on_case_change',
                            ],
                        ], 
                        'analyzer' => [
                            'autocomplete_edge' => [
                                'index' => [
                                    'type' => 'custom',
                                    'char_filter' => [],
                                    'tokenizer' => 'standard',
                                    'filter' => ['lowercase', 'punctiation_replace', 'edge_ngram', 'nonealpha_replace'],
                                ], 
                                'query' => [
                                    'type' => 'custom',
                                    'char_filter' => [],
                                    'tokenizer' => 'standard',
                                    'filter' => ['ngram_delimiter', 'lowercase', 'punctiation_replace', 'edge_ngram', 'nonealpha_replace', 'max_toke_length'],
                                ], 
                            ],
                            'autocomplete_ngram' => [
                                'index' => [
                                    'type' => 'custom',
                                    'char_filter' => [],
                                    'tokenizer' => 'standard',
                                    'filter' => ['ngram_delimiter', 'lowercase', 'ngram', 'nonealpha_replace'],
                                ], 
                                'query' => [
                                    'type' => 'custom',
                                    'char_filter' => [],
                                    'tokenizer' => 'standard',
                                    'filter' => ['ngram_delimiter', 'lowercase', 'ngram', 'nonealpha_replace', 'max_toke_length'],
                                ], 
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