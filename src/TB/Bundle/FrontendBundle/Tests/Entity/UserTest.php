<?php 

namespace TB\Bundle\APIBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

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
    
    /**
     * Test setLocation() with Point Object
     */
    public function testSetLocationPoint()
    {   
        $point = new Point(52.5234051, 13.4113999, 4326);
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        $user->setlocation($point);
        $this->assertEquals($point, $user->getLocation(), 
            'The set Point object is returned');
    }
    
    /**
     * Test setLocation() with a string representation of a Point
     */
    public function testSetLocationString()
    {   
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        $user->setlocation('(52.5234051, 13.4113999)');
        $this->assertInstanceOf('CrEOF\Spatial\PHP\Types\Geometry\Point', $user->getLocation(),
            'A Point Object was created');
        $user->setlocation('(-34.92862119999999, 138.5999594)');
        $this->assertInstanceOf('CrEOF\Spatial\PHP\Types\Geometry\Point', $user->getLocation(),
            'A Point Object was created');
    }
    
    
    /**
     * Test that an Exception is throsn when passing an invalid Point string to setLocation()
     *
     * @expectedException Exception
     * @@expectedExceptionMessage Invalid location string format: invalid format
     */
    public function testSetLocationStringThrowsLocation()
    {   
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        $user->setlocation('invalid format');
    }
    
    public function testUpdateAvatarGravatar()
    {
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        $user->setEmail('info@cynova.net'); // email that has a gravatar profile
        $user->updateAvatarGravatar();
        $this->assertEquals('http://www.gravatar.com/avatar/0410852438283d8bec95c3eef1fe0814', $user->getAvatarGravatar());
    }
    
    public function testUpdateAvatarGravatarUnavailable()
    {
        $user = $this->getMockForAbstractClass('TB\Bundle\FrontendBundle\Entity\User');
        $user->setEmail('email@withoutgravatar'); // email that has no gravatar profile
        $user->updateAvatarGravatar();
        $this->assertEquals('', $user->getAvatarGravatar());
    }
    
    
}