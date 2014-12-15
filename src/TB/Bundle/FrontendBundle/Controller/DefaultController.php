<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Query;
use TB\Bundle\FrontendBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }           

    /**
     * @Route("/tour", name="tour")
     * @Template()
     */
    public function tourAction()
    {
        return [];
    }
    
    /**
     * @Route("/about", name="about")
     * @Template()
     */
    public function aboutAction()
    {
      	$breadcrumb[] = [
            'name' => 'about',
            'label' => 'Discover Trailburning', 
            'params' => [],
        ];
        
        return array(
            'breadcrumb' => $breadcrumb
        );    	
    }
    
    /**
     * @Route("/competition", name="competition")
     * @Template()
     */
    public function competitionAction()
    {
      	$breadcrumb[] = [
            'name' => 'competition',
            'label' => 'Competition', 
            'params' => [],
        ];
        
        return array(
            'breadcrumb' => $breadcrumb
        );    	
    }
    
    /**
     * @Route("/presskit", name="presskit")
     * @Template()
     */
    public function presskitAction()
    {
      	$breadcrumb[] = [
            'name' => 'presskit',
            'label' => 'Press Kit', 
            'params' => [],
        ];
        
        return array(
            'breadcrumb' => $breadcrumb
        );    	
    }
    
    /**
     * @Route("/gpxguide", name="gpxguide")
     * @Template()
     */
    public function gpxguideAction()
    {
        return [];
    }
	
    /**
     * @Route("/campaigntour", name="campaigntour")
     * @Template()
     */
    public function campaigntourAction()
    {
        return [];
    }	
}
