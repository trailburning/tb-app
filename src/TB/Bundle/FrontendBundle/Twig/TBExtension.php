<?php 

namespace TB\Bundle\FrontendBundle\Twig;

use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use TB\Bundle\FrontendBundle\Entity\User;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\Campaign;

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
                if ($substance instanceof \TB\Bundle\FrontendBundle\Entity\UserProfile 
                    || (property_exists($substance, 'discr') && $substance->discr == 'user')) {
                    return true;
                } else {
                    return false;
                }
            }),
            new \Twig_SimpleTest('BrandProfile', function ($substance) { 
                if ($substance instanceof \TB\Bundle\FrontendBundle\Entity\BrandProfile
                    || (property_exists($substance, 'discr') && $substance->discr == 'brand')) {
                    return true;
                } else {
                    return false;
                }
            })
        ];
    }
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('url_truncate', array($this, 'urlTruncateFilter')),
            new \Twig_SimpleFilter('url_shareable', array($this, 'urlShareableFilter')),
            new \Twig_SimpleFilter('dimension_format', array($this, 'dimensionFormatFilter')),   
            new \Twig_SimpleFilter('kmDistance', array($this, 'kmDistance')),   
            //             'ceil' => new \Twig_Filter_Method($this, 'ceil'),         
        );
    }
    
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('user_is_following', array($this, 'userIsFollowing')),
            new \Twig_SimpleFunction('user_is_following_campaign', array($this, 'userIsFollowingCampaign')),
            new \Twig_SimpleFunction('route_has_user_like', array($this, 'routeHasUserLike')),
            new \Twig_SimpleFunction('extract_entity', array($this, 'extractEntity')),
            new \Twig_SimpleFunction('get_share_media', array($this, 'getShareMedia')),
            new \Twig_SimpleFunction('get_user_avatar_url', array($this, 'getUserAvatarUrl')),
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
     * Formats a number as rounded up km for showing trail length
     *
     * @param integer The length to convert
     */
   	public function kmDistance($number)
    {
        return ceil($number / 1000);
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
    
    /**
     * Tests if User A is following User B
     * 
     * @param User $userA The User to lookup
     * @param User $userB The User to test for following 
     * @return boolean true is the User A is following User B, false if not
     */
    public function userIsFollowing(User $userA, User $userB)
    {
        return $userA->isFollowingUser($userB);
    }
    
    /**
     * Tests if a User is following a Campaign
     * 
     * @param User $user The User to lookup
     * @param Campaign $campaign The Campaign to test for following 
     * @return boolean true is the User is following the Campaign, false if not
     */
    public function userIsFollowingCampaign(User $user, Campaign $campaign)
    {
        return $user->isFollowingCampaign($campaign);
    }
    
    /**
     * Test if a Route is liked by a User
     * 
     * @param Route $route The Route to check
     * @param User $user The User to test for like
     * @return boolean true is the the user likes the Route, false if not
     */
    public function routeHasUserLike(Route $route, User $user)
    {
        return $route->hasUserLike($user);
    }
    
    public function getShareMedia(Route $route)
    {
        return $route->getShareMedia();
    }
    
    public function getUserAvatarUrl(User $user)
    {
        $url = '';
        
        return $user->getAvatarUrl();
    }

    public function getName()
    {
        return 'tb_extension';
    }
}
