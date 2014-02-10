<?php 

namespace TB\Bundle\FrontendBundle\Twig;

use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;

/**
* 
*/
class TBExtension extends \Twig_Extension
{
    
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('UserProfile', function ($substance) { 
                return $substance instanceof \TB\Bundle\FrontendBundle\Entity\UserProfile;
            }),
            new \Twig_SimpleTest('BrandProfile', function ($substance) { 
                return $substance instanceof \TB\Bundle\FrontendBundle\Entity\BrandProfile; 
            })
        ];
    }
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('url_truncate', array($this, 'urlTruncateFilter')),
            new \Twig_SimpleFilter('url_shareable', array($this, 'urlShareableFilter')),
            new \Twig_SimpleFilter('dimension_format', array($this, 'dimensionFormatFilter')),            
        );
    }

    public function urlTruncateFilter($url)
    {
        $url = preg_replace('/http(s?):\/\//', '', $url);
        $url = preg_replace('/www./', '', $url);

        return $url;
    }
    
    public function urlShareableFilter($url)
    {
        $url = preg_replace('/http(s?):\/\//', '', $url);
        $url = urlencode($url);

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
