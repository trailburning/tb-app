<?php 

namespace TB\Bundle\FrontendBundle\Service;

use Doctrine\ORM\EntityManager;

/**
 * Helper Class to query events
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
            SELECT count(e.id) FROM TBFrontendBundle:Event e WHERE e.publish = true
        ');
        $count = $query->getSingleScalarResult();
        
        $query = $this->em->createQuery('
            SELECT e FROM TBFrontendBundle:Event e
            LEFT JOIN TBFrontendBundle:Region r WITH e.regionId=r.id
            WHERE e.publish = true
            ORDER BY e.date ASC
        ');
        
        $events = $query
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getResult();
            
        return $events;
    }
    
}


