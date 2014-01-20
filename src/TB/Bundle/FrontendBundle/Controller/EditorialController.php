<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class EditorialController extends Controller
{
    /**
     * @Route("/editorial/{slug}", name="editorial")
     * @Template()
     */
    public function editorialAction($slug)
    {
        $editorial = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Editorial')
            ->findOneBySlug($slug);

        if (!$editorial) {
            throw $this->createNotFoundException(
                sprintf('Editorial %s not found', $slug)
            );
        }
        
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT r FROM TBFrontendBundle:Route r
                JOIN r.editorials e
                WHERE e.id=:editorialId
                ORDER BY r.id')
            ->setParameter('editorialId', $editorial->getId());
        $routes = $query->getResult();
        
        return [
            'editorial' => $editorial,
            'routes' => $routes,
        ];
    }
    
    /**
     * @Route("/editorials", name="editorials")
     * @Template()  
    */
    public function editorialsAction()
    {
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT e FROM TBFrontendBundle:Editorial e
                ORDER BY e.date DESC');
        $editorials = $query->getResult();
        
        return [
            'editorials' => $editorials,
        ];
    }
}
