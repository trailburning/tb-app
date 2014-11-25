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
        if ($name == 'admin') {
            throw $this->createNotFoundException(
                sprintf('The admin has no profile.')
            );
        }
        
        $breadcrumb = [];
        
        $user = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName($name);

        if (!$user) {
            throw $this->createNotFoundException(
                sprintf('User %s not found.', $name)
            );
        } 
        
        // Get trail i like for the UserProfile Page
        if ($user instanceof \TB\Bundle\FrontendBundle\Entity\UserProfile) {
       
            $query = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT r FROM TBFrontendBundle:Route r
                    INNER JOIN r.routeLikes ul WITH r.id = ul.routeId
                    WHERE r.publish = true
                    AND ul.userId = :userId
                    ORDER BY ul.date DESC')
                ->setParameter('userId', $user->getId());
            $trailsILike = $query->setMaxResults(3)->getResult();              
       
        } else {
            $trailsILike = [];
        }
        
        if ($this->getUser() !== null && $this->getUser()->getId() == $user->getId()) {
            // Display the users "My Profile" view when he is signed in and visits his own profile
            
            $headers = ['Trailburning-User-ID' => $this->getUser()->getId()];
            $client = $this->get('rest_client');
            $request = $client->get('v1/routes/my?publish=true', $headers);
            $response = $request->send();
            $jsonObj = json_decode($response->getBody());
        
            if ($response->getStatusCode() !== 200) {
                throw new \Exception(sprintf('API error: %s', $jsonObj->usermsg));
            }
            $data = $response->json();
            $publishedRoutes = $data['value']['routes'];
            
            $request = $client->get('v1/routes/my?publish=false', $headers);
            $response = $request->send();
            $jsonObj = json_decode($response->getBody());
        
            if ($response->getStatusCode() !== 200) {
                throw new \Exception(sprintf('API error: %s', $jsonObj->usermsg));
            }
            $data = $response->json();
            $unpublishedRoutes = $data['value']['routes'];
            
            $breadcrumb[] = [
                'name' => 'profile',
                'label' => 'My Profile', 
                'params' => ['name' => $user->getName()],
            ];
            
            return $this->render(
                'TBFrontendBundle:Profile:my.html.twig', [
                    'user' => $user, 
                    'publishedRoutes' => $publishedRoutes, 
                    'unpublishedRoutes' => $unpublishedRoutes, 
                    'breadcrumb' => $breadcrumb, 
                    'trailsILike' => $trailsILike,
                ]
            );
        } elseif ($user instanceof \TB\Bundle\FrontendBundle\Entity\UserProfile) {            
            // Display UserProfile view
            
            $client = $this->get('rest_client');
            $request = $client->get('v1/routes/user/' . $user->getId());
            $response = $request->send();
            $response->getBody();
        
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API error');  
            }
            $data = $response->json();
            $routes = $data['value']['routes'];

            $breadcrumb[] = [
                'name' => 'profile',
                'label' => $user->getFirstName() . ' ' . $user->getLastName(), 
                'params' => ['name' => $user->getName()],
            ];
            
            return $this->render(
                'TBFrontendBundle:Profile:user.html.twig', [
                    'user' => $user, 
                    'routes' => $routes, 
                    'breadcrumb' => $breadcrumb,
                    'trailsILike' => $trailsILike,
                ]
            );
        } elseif ($user instanceof \TB\Bundle\FrontendBundle\Entity\BrandProfile) {
            // Display BrandProfile view
            
            $events = $this->getDoctrine()
                ->getRepository('TBFrontendBundle:Event')
                    ->findByUser($user);
            
            $client = $this->get('rest_client');
            $request = $client->get('v1/routes/user/' . $user->getId());
            $response = $request->send();
            $response->getBody();
        
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API error');  
            }
            $data = $response->json();
            $routes = $data['value']['routes'];
            
            $breadcrumb[] = [
                'name' => 'profile',
                'label' => $user->getDisplayName(), 
                'params' => ['name' => $user->getName()],
            ];
            
            return $this->render(
                'TBFrontendBundle:Profile:brand.html.twig', [
                    'brand' => $user, 
                    'routes' => $routes, 
                    'events' => $events, 
                    'breadcrumb' => $breadcrumb
                ]
            );
        } else {
            throw new \Exception(sprintf('Unknown User of class %s', get_class($user)));
        }
        
    }
    
    /**
     * @Template()
     */
    public function homepageUserAction()
    {
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT u FROM TBFrontendBundle:User u
                WHERE u.avatar IS NOT NULL OR u.avatarGravatar IS NOT NULL OR u.avatarFacebook IS NOT NULL
                ORDER BY u.registeredAt DESC');
        $users = $query->getResult();  
        
        return [
            'users' => $users,
        ];
    }
}
