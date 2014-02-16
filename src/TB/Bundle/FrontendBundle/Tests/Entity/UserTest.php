<?php 

namespace TB\Bundle\APIBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    /**
     * Test that setEmail sets the username field
     */
    public function testSetEmail()
    {
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        
        $this->assertEmpty('', $user->getUsername());
        $user->setEmail('e@mail');
        $this->assertEquals('e@mail', $user->getUsername());
        $this->assertEquals('e@mail', $user->getEmail());
    }
    
    /**
     * Test that setEmailCanonical sets the usernameCanonical field
     */
    public function testSetEmailCanonical()
    {
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        
        $this->assertEmpty('', $user->getUsernameCanonical());
        $user->setEmailCanonical('e@mail');
        $this->assertEquals('e@mail', $user->getUsernameCanonical());
        $this->assertEquals('e@mail', $user->getEmailCanonical());
    }
    
}