<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;
 
$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('doc')
    ->exclude('tests')
    ->exclude('vendor')
    ->in('.')
;
return new Sami($iterator, array(
    'title'                => 'Tb-restapi',
    'build_dir'            => __DIR__.'/doc/sami/',
    'cache_dir'            => __DIR__.'/doc/cache',
    'default_opened_level' => 2,
)); 
