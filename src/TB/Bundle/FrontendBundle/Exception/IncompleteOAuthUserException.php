<?php

namespace TB\Bundle\FrontendBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;

/**
 * IncompleteUserException is thrown when the user isn't fully registered (e.g.: missing some informations).
 *
 * @author Alessandro Tagliapietra http://www.alexnetwork.it/
 */
class IncompleteOAuthUserException extends AuthenticationException implements OAuthAwareExceptionInterface
{
    private $user;
    private $accessToken;
    private $resourceOwnerName;
    private $rawToken;
    private $refreshToken;
    private $expiresIn;
    private $tokenSecret;

    /**
     * {@inheritdoc}
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setRawToken($rawToken)
    {
        $this->rawToken = $rawToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawToken()
    {
        return $this->rawToken;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setExpiresIn($expiresId)
    {
        $this->expiresId = $expiresId;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        return $this->expiresId;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setTokenSecret($tokenSecret)
    {
        $this->tokenSecret = $tokenSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerName()
    {
        return $this->resourceOwnerName;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceOwnerName($resourceOwnerName)
    {
        $this->resourceOwnerName = $resourceOwnerName;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser($user)
    {
        return $this->user;
    }

    public function serialize()
    {
        return serialize(array(
            $this->user,
            $this->accessToken,
            $this->resourceOwnerName,
            parent::serialize(),
        ));
    }

    public function unserialize($str)
    {
        list(
            $this->user,
            $this->accessToken,
            $this->resourceOwnerName,
            $parentData
        ) = unserialize($str);
        parent::unserialize($parentData);
    }
}