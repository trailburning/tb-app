<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ProfileController extends Controller
{
    /**
     * @Route("/profile/{name}", name="profile")
     * @Template()
     */
    public function profileAction($name)
    {
        $user = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName($name);

        if (!$user) {
            throw $this->createNotFoundException(
                sprintf('User %s not found.', $name)
            );
        }
        
        if ($user instanceof \TB\Bundle\FrontendBundle\Entity\UserProfile) {
            $client = $this->get('rest_client');
            $request = $client->get('v1/routes/user/' . $user->getId());
            $response = $request->send();
            $response->getBody();
        
            if ($response->getStatusCode() !== 200) {
                throw new HttpException(500, 'API error');  
            }
            $data = $response->json();
            $routes = $data['value']['routes'];
            
            return $this->render(
                'TBFrontendBundle:Profile:user.html.twig',
                array('user' => $user, 'routes' => $routes)
            );
        } elseif ($user instanceof \TB\Bundle\FrontendBundle\Entity\BrandProfile) {
            
            $events = $this->getDoctrine()
                ->getRepository('TBFrontendBundle:Event')
                    ->findByUser($user);
            
            return $this->render(
                'TBFrontendBundle:Profile:brand.html.twig',
                array('brand' => $user, 'events' => $events)
            );
        } else {
            throw new \Exception(sprintf('Unknown User of class %s', get_class($user)));
        }
        
    }
}
