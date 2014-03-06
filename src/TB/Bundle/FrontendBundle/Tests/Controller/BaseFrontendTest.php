<?php
 
namespace TB\Bundle\FrontendBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase; 
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
 
 
class BaseFrontendTest extends WebTestCase 
{
 
    protected function logIn($client, $username)
    {
        $response = new Response();
        $storage = new MockFileSessionStorage(__dir__.'/../../../../../../app/frontend/cache/test/sessions/');
        $session = new Session($storage);
        $session->start();
        
        $cookie = new Cookie('MOCKSESSID', $storage->getId());
        $cookieJar = new CookieJar();
        $cookieJar->set($cookie);
        
        $em = $client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('TBFrontendBundle:User')->findOneByUsername($username);
        
        if (!$user) {
            $this->fail(sprintf('Missing user "%s" in Test DB', $username));
        }
        
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        
        $client->getContainer()->get('fos_user.security.login_manager')->loginUser(
            $client->getContainer()->getParameter('fos_user.firewall_name'),
            $user,
            $response
        );
        
        $session->save();
        $client->getCookieJar()->set($cookie);
    }
}