<?php 

namespace TB\Bundle\FrontendBundle\Twig;

/**
* 
*/
class TBExtension extends \Twig_Extension
{
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('url_truncate', array($this, 'urlTruncateFilter')),
            new \Twig_SimpleFilter('dimension_format', array($this, 'dimensionFormatFilter')),            
        );
    }

    public function urlTruncateFilter($url)
    {
        $url = preg_replace('/http(s?):\/\//', '', $url);
        $url = preg_replace('/www./', '', $url);

        return $url;
    }
    
    public function dimensionFormatFilter($dimension, $unit = '', $base = 1)
    {
        $dimension = $dimension / $base;        
        $dimension = round($dimension);
        $dimension = number_format($dimension, 0, '.', '’');        
                
        return sprintf('%s %s', $dimension, $unit);
    }

    public function getName()
    {
        return 'tb_extension';
    }
}
