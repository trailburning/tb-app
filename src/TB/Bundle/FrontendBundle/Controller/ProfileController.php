<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ProfileController extends Controller
{
    /**
     * @Route("/profile/{name}")
     * @Template()
     */
    public function profileAction($name)
    {
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT u.id, u.name, u.firstName, u.lastName, u.avatar, u.synopsis, u.about, ST_AsText(u.location) AS location
             FROM TBFrontendBundle:User u WHERE u.name=:name'
        )->setParameter('name', $name);

        $users = $query->getResult();

        if (!isset($users[0])) {
            throw $this->createNotFoundException(
                sprintf('User %s not found.', $name)
            );
        }   
        
        $user = $users[0];
        $point = explode(" ", substr(trim($user['location']), 6, -1));
        $user['long'] = $point[0];
        $user['lat'] = $point[1];
        
        $client = $this->get('rest_client');
        $request = $client->get('v1/routes/user/' . $user['id']);
        $response = $request->send();
        $response->getBody();
        
        if ($response->getStatusCode() !== 200) {
            throw new HttpException(500, 'API error');  
        }
        $data = $response->json();
        $routes = $data['value']['routes'];
        
        return array('user' => $user, 'routes' => $routes);
    }
}
