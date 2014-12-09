<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TB\Bundle\APIBundle\Service\ApiException;
use TB\Bundle\FrontendBundle\Event\CampaignFollowEvent;
use TB\Bundle\FrontendBundle\Event\CampaignUnfollowEvent;

class CampaignController extends AbstractRestController
{
    /**
     * Follow a Campaign
     *
     * @Route("/campaign/{campaignId}/follow", requirements={"campaignId" = "\d+"})
     * @Method("PUT")
     */
    public function putCampaignFollow($campaignId)
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

        $campaign = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Campaign')
            ->findOneById($campaignId);

        if (!$campaign) {
            throw new ApiException(sprintf('Campaign to follow with id "%s" does not exist', $campaignId), 404);
        }
        
        //check if user is already following
        if ($user->isFollowingCampaign($campaign)) {
            throw new ApiException(sprintf('User %s is already following Campaign %s', $user->getId(), $campaign->getId()), 400);
        }

        $user->addCampaignsIFollow($campaign);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        
        // dispatch tb.user_follow event
        $event = new CampaignFollowEvent($user, $campaign);
        $dispatcher = $this->container->get('event_dispatcher'); 
        $dispatcher->dispatch('tb.campaign_follow', $event);
        
        $output = array('usermsg' => 'success');
        
        return $this->getRestResponse($output);
    }
    
    /**
     * Unfollow a campaign
     *
     * @Route("/campaign/{campaignId}/unfollow", requirements={"campaignId" = "\d+"})
     * @Method("PUT")
     */
    public function putCampaignUnfollow($campaignId)
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
        
        $campaign = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Campaign')
            ->findOneById($campaignId);

        if (!$campaign) {
            throw new ApiException(sprintf('Campaign to unfollow with id "%s" does not exist', $campaignId), 404);
        }
        
        //check if user is following
        if (!$user->isFollowingCampaign($campaign)) {
            throw new ApiException(sprintf('User %s is not following Campaign %s', $user->getId(), $campaign->getId()), 400);
        }
        
        $user->removeCampaignsIFollow($campaign);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        
        // dispath tb.campaign_unfollow event
        $event = new CampaignUnfollowEvent($user, $campaign);
        $dispatcher = $this->container->get('event_dispatcher'); 
        $dispatcher->dispatch('tb.campaign_unfollow', $event);
        
        $output = array('usermsg' => 'success');
        
        return $this->getRestResponse($output);
    }
    
}
