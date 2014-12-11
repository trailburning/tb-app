<?php 

namespace TB\Bundle\FrontendBundle\Tests\Service;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;

class GPXGeneratorTest extends AbstractFrontendTest
{

    public function testGenerate()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $gpxGenerator = $this->getContainer()->get('debug_filesystem'); 
        $route = $this->getRoute('grunewald');
        

    }

}    