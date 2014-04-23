<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use TB\Bundle\APIBundle\Util\ApiException;
use Symfony\Component\HttpFoundation\Request;

class EventController extends AbstractRestController
{
    
    /**
     * @Route("/events/search")
     * @Method("GET")
     */
    public function getEventsSearch(Request $request)
    {
        $limit = $request->query->get('limit', 10);
        $offset = $request->query->get('offset', 0);
        
        $finder = $this->get('tb.events.finder');
        $events = $finder->search($limit, $offset, $count);
        $eventsExport = [];
        foreach ($events as $event) {
            $eventsExport[] = $event->export();
        }
        
        $output = ['usermsg' => 'success', "value" => [
            'events' => $eventsExport,
            'totalCount' => $count,    
        ]];

        return $this->getRestResponse($output);
    }
    
}
