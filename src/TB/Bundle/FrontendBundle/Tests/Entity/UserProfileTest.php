<?php 

namespace TB\Bundle\APIBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use TB\Bundle\FrontendBundle\Entity\UserProfile;

class UserProfileTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    /**
     * Test that setEmail sets the username field
     */
    public function testSluggableName()
    {
        $this->loadFixtures([]);        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $user = new UserProfile();
        $user->setEmail('test@mail');
        $user->setPassword('password');
        $user->setFirstName('first');
        $user->setlastName('last name');

        $em->persist($user);
        $em->flush();
        
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($user->getId());
        if (!$user) {
            $this->fail('Missing new created user in test DB');
        }    
        
        $this->assertEquals('firstlastname', $user->getName());
        
        // test that name gets not updated on firstName nad lastName changes
        
        $user->setFirstName('new first');
        $user->setLastName('new last name');

        $em->persist($user);
        $em->flush();
        
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($user->getId());
        if (!$user) {
            $this->fail('Missing updated user in test DB');
        }    
        
        $this->assertEquals('firstlastname', $user->getName());
        
        
        // test another user with the same firstName and lastname
        
        $user = new UserProfile();
        $user->setEmail('test2@mail');
        $user->setPassword('password');
        $user->setFirstName('first');
        $user->setlastName('last name');

        $em->persist($user);
        $em->flush();
        
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($user->getId());
        if (!$user) {
            $this->fail('Missing second new created user in test DB');
        }    
        
        $this->assertEquals('firstlastname1', $user->getName());
        
    }
    
    
}