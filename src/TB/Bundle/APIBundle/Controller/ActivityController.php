<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TB\Bundle\APIBundle\Service\ApiException;

class ActivityController extends AbstractRestController
{
    /**
     * Get the activity feed for a user
     *
     * @Route("/activity/feed")
     * @Method("GET")
     */
    public function getActivityByUserAction()
    {
        $request = $this->getRequest();
        if (!$request->headers->has('Trailburning-User-ID')) {
            throw new ApiException('Header Trailburning-User-ID is not set', 400);
        }
        $userId = $request->headers->get('Trailburning-User-ID');
        $user = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($userId);

        if (!$user) {
            throw new ApiException(sprintf('User with id "%s" does not exist', $userId), 404);
        }
        
        $activityFeedGenerator = $this->get('tb.activity.feed.generator');
        
        $feed = $activityFeedGenerator->getFeedForUser($user->getId());
        
        return $this->getRestResponse($feed);
    }
}
