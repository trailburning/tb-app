<?php

namespace TB\Bundle\FrontendBundle\Security;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;
use TB\Bundle\FrontendBundle\Exception\IncompleteOAuthUserException;

/**
 * Class OAuthUserProvider
 * @package Owl\UserBundle\Security\User\Provider
 */
class OAuthUserProvider extends BaseClass
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $userId = $response->getUsername();
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $userId));
        
        $email = $response->getEmail();
        $username = $response->getNickname() ?: $response->getRealName();
        
        if (null === $user) {
            $user = $this->userManager->findUserByUsernameAndEmail($username, $email);

            if (null === $user || !$user instanceof UserInterface) {
                $user = $this->userManager->createUser();
                $data = $response->getResponse();
                if ($data['email']) {
                    $user->setEmail($data['email']);
                }
                if ($data['first_name']) {
                    $user->setFirstname($data['first_name']);
                }
                if ($data['last_name']) {
                    $user->setLastname($data['last_name']);
                }
                if ($data['last_name']) {
                    $user->setLastname($data['last_name']);
                }
                
                $user->setOAuthService($response->getResourceOwner()->getName());
                $user->setOAuthId($userId);
                $user->setOAuthAccessToken($response->getAccessToken());
                $user->setOAuthAccessToken($response->getAccessToken());
                $e = new IncompleteOAuthUserException('Incomplete User data');
                $e->setUser($user);
                
                throw $e;
                               
            } else {
                throw new AuthenticationException('Username or email has been already used.');
            }
        } else {
            $checker = new UserChecker();
            $checker->checkPreAuth($user);
        }

        return $user;
    }
}