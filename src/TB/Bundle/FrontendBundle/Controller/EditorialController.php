<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class EditorialController extends Controller
{
    /**
     * @Route("/editorial/{slug}") 
     */
    public function legacyEditorialAction($slug)
    { 
        return $this->redirect($this->generateUrl('editorial', ['slug' => $slug]), 301);
    }
    
    /**
     * @Route("/inspire/{slug}", name="editorial")
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
                SELECT er FROM TBFrontendBundle:EditorialRoute er
                JOIN er.route r
                WHERE er.editorialId=:editorialId
                ORDER BY er.order ASC, r.id ASC')
            ->setParameter('editorialId', $editorial->getId());
        $editorialRoutes = $query->getResult();
        
        return [
            'editorial' => $editorial,
            'editorialRoutes' => $editorialRoutes,
        ];
    }
    
    /**
     * @Route("/editorials") 
     */
    public function legacyEditorialsAction()
    { 
        return $this->redirect($this->generateUrl('editorials'), 301);
    }
    
    /**
     * @Route("/inspire", name="editorials")
     * @Template()  
    */
    public function editorialsAction()
    {
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT e FROM TBFrontendBundle:Editorial e
                WHERE e.publish = true
                ORDER BY e.date DESC');
        $editorials = $query->getResult();
        
        return [
            'editorials' => $editorials,
        ];
    }
}
