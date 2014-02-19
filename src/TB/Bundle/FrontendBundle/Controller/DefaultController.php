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
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }           

}
