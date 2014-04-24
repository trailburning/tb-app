<?php 

namespace TB\Bundle\FrontendBundle\Util;

use Doctrine\ORM\EntityManager;

/**
 * Helper Calss to query events
 */
class EventsFinder
{

    protected $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    /**
     * 
     */
    public function search($limit = 10, $offset = 0, &$count = 0)
    {
        $query = $this->em->createQuery('
            SELECT count(e.id) FROM TBFrontendBundle:Event e
        ');
        $count = $query->getSingleScalarResult();
        
        $query = $this->em->createQuery('
            SELECT e FROM TBFrontendBundle:Event e
            LEFT JOIN TBFrontendBundle:Region r WITH e.regionId=r.id
            ORDER BY e.id DESC
        ');
        
        $events = $query
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getResult();
            
        return $events;
    }
    
}

