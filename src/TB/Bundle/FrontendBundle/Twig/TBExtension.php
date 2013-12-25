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
        );
    }

    public function urlTruncateFilter($url)
    {
        $url = preg_replace('/http(s?):\/\//', '', $url);
        $url = preg_replace('/www./', '', $url);

        return $url;
    }

    public function getName()
    {
        return 'tb_extension';
    }
}
