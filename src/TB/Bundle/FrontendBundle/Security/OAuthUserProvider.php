<?php

namespace TB\Bundle\FrontendBundle\Security;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;
use TB\Bundle\FrontendBundle\Exception\IncompleteOAuthUserException;
use TB\Bundle\FrontendBundle\Entity\User;

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
                if ($email != '') {
                    $user->setEmail($email);
                }
                if (isset($data['first_name'])) {
                    $user->setFirstName($data['first_name']);
                }
                if (isset($data['last_name'])) {
                    $user->setLastName($data['last_name']);
                }
                if (isset($data['gender']) && $data['gender'] == 'male') {
                    $user->setGender(User::GENDER_MALE);
                } elseif (isset($data['gender']) && $data['gender'] == 'female') {
                    $user->setGender(User::GENDER_FEMALE);
                }
                
                $user->setOAuthService($response->getResourceOwner()->getName());
                $user->setOAuthId($userId);
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