<?php 

namespace TB\Bundle\FrontendBundle\Twig;

use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;

/**
* 
*/
class TBExtension extends \Twig_Extension
{
    
    /**
     * Adds tests for UserProfile and BrandProfile objects
     */
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
    
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('extract_entity',  array($this, 'extractEntity')),
        );
    }
    
    /**
     * Formats a url trac in analytics
     *
     * @param string $url The URL to format
     * @return string the formated URL
     */
    public function urlTruncateFilter($url)
    {
        $url = preg_replace('/http(s?):\/\//', '', $url);
        $url = preg_replace('/www./', '', $url);

        return $url;
    }
    
    /**
     * Formats a url to share on social networks
     *
     * @param string $url The URL to format
     * @return string the formated URL
     */
    public function urlShareableFilter($url)
    {
        $url = preg_replace('/http(s?):\/\//', '', $url);
        $url = urlencode($url);

        return $url;
    }
    
    /**
     * Formats value to display it in the template
     * 
     * @param mixed $dimension
     * @param string The unit to append to the value string
     * @param integer The decimal base of the value
     * @return string The formated string
     */
    public function dimensionFormatFilter($value, $unit = '', $base = 1)
    {
        $value = $value / $base;        
        $value = round($value);
        $value = number_format($value, 0, '.', '’');        
                
        return sprintf('%s %s', $value, $unit);
    }
    
    /**
     * extracts values from a passed object (entity). Expects public getter methods for all requested fields
     *
     * @param mixed $entity The object to extract values from
     * @param array $fields The fields to extract
     * @throws Exception When a getter method for a field is missing 
     * @return array Array of extracted values
     */
    public function extractEntity($entity, array $fields)
    {
        $values = [];
        foreach ($fields as $field) {
            $method = 'get' . strtoupper($field);
            if (!method_exists($entity, $method)) {
                throw new \Exception(sprintf('Missing method %s in entity %', $entity, $method));
            }
            $values[$field] = $entity->$method(); 
        }
        
        return $values;
    }

    public function getName()
    {
        return 'tb_extension';
    }
}
