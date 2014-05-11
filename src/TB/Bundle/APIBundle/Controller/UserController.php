<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TB\Bundle\APIBundle\Util\ApiException;
use TB\Bundle\FrontendBundle\Event\UserFollowEvent;
use TB\Bundle\FrontendBundle\Event\UserUnfollowEvent;

class UserController extends AbstractRestController
{
    /**
     * Follow a User
     *
     * @Route("/user/{userIdToFollow}/follow", requirements={"userIdToFollow" = "\d+"})
     * @Method("PUT")
     */
    public function putUserFollow($userIdToFollow)
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
        
        //check that a user is not following itself
        if ($userId == $userIdToFollow) {
            throw new ApiException('A user cannot follow itself', 400);
        }

        $userToFollow = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($userIdToFollow);

        if (!$userToFollow) {
            throw new ApiException(sprintf('User to follow with id "%s" does not exist', $userIdToFollow), 404);
        }
        
        //check if user is already following
        if ($user->isFollowing($userToFollow)) {
            throw new ApiException(sprintf('User %s is already following user %s', $user->getId(), $userToFollow->getId()), 400);
        }

        $user->addIFollow($userToFollow);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        
        // dispatch tb.user_follow event
        $event = new UserFollowEvent($user, $userToFollow);
        $dispatcher = $this->container->get('event_dispatcher'); 
        $dispatcher->dispatch('tb.user_follow', $event);
        
        $output = array('usermsg' => 'success');
        
        return $this->getRestResponse($output);
    }
    
    /**
     * Unfollow a user
     *
     * @Route("/user/{userIdToUnfollow}/unfollow", requirements={"userIdToUnfollow" = "\d+"})
     * @Method("PUT")
     */
    public function putUserUnfollow($userIdToUnfollow)
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
        
        //check that a user is not unfollowing itself
        if ($userId == $userIdToUnfollow) {
            throw new ApiException('A user cannot unfollow itself', 400);
        }
        
        $userToUnfollow = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($userIdToUnfollow);

        if (!$userToUnfollow) {
            throw new ApiException(sprintf('User to unfollow with id "%s" does not exist', $userIdToUnfollow), 404);
        }
        
        //check if user is following
        if (!$user->isFollowing($userToUnfollow)) {
            throw new ApiException(sprintf('User %s is not following user %s', $user->getId(), $userToUnfollow->getId()), 400);
        }
        
        $user->removeIFollow($userToUnfollow);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        
        // dispath tb.user_follow event
        $event = new UserUnfollowEvent($user, $userToUnfollow);
        $dispatcher = $this->container->get('event_dispatcher'); 
        $dispatcher->dispatch('tb.user_unfollow', $event);
        
        $output = array('usermsg' => 'success');
        
        return $this->getRestResponse($output);
    }
    
    /**
     * Set the User activityLastViewed to now
     *
     * @Route("/user/activity/viewed")
     * @Method("PUT")
     */
    public function putUserActivityViewd()
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
        
        $feedGenerator = $this->get('tb.activity.feed.generator');
        
        $user->setActivityLastViewed(new \DateTime("now"));
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        
        $feedGenerator->updateUserActivityUnseenCount($user);
        
        $output = array('usermsg' => 'success');
        
        return $this->getRestResponse($output);
    }
}
