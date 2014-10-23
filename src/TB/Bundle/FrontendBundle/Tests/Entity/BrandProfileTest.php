<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\brandProfile;

class BrandProfileTest extends AbstractFrontendTest
{
        
        
    public function testExport() 
    {
        $brandProfile = new BrandProfile();
        
        $expected = [
          'name' => null,
          'title' => null,
          'avatar' => null,
          'type' => 'brand',
        ];
        
        $this->assertEquals($expected, $brandProfile->export(),
            'BrandProfile::export() returns the expected data array');
    }
}