<?php

namespace TB\Bundle\FrontendBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Routing\Router;
use TB\Bundle\FrontendBundle\Exception\IncompleteOAuthUserException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Override default autentificatino handler to redirect to the signup form after login from external authentification handler
 */
class OAuthAuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    private $router;

    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options, Router $router)
    {
        $this->router = $router;
        
        parent::__construct($httpKernel, $httpUtils, $options);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof IncompleteOAuthUserException) {
            $request->getSession()->set(SecurityContext::AUTHENTICATION_ERROR, $exception);
            $response = new RedirectResponse($this->router->generate('fos_user_registration_register'));            

            return $response;
        } else {
            return parent::onAuthenticationFailure($request, $exception);
        }
    }

}