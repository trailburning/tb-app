<?php 

namespace TB\Bundle\FrontendBundle\Util;

use Doctrine\ORM\EntityManager;
use TB\Bundle\FrontendBundle\Entity\Route;


/**
 * 
 */
class ImageGenerator
{
    
    protected $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function createRouteShareImage(Route $route)
    {
        
    }
    
}
