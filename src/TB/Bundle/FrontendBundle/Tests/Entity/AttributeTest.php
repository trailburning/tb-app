<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\Attribute;

class AttributeTest extends AbstractFrontendTest
{
    
    /**
     * Test export entity
     */
    public function testExport()
    {
        $this->loadFixtures(
            ['TB\Bundle\FrontendBundle\DataFixtures\ORM\AttributeData']
        );        
        
        // Get RouteCategory from DB with the name "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $attribute = $em
            ->getRepository('TBFrontendBundle:Attribute')
            ->findOneByName('run');
        if (!$attribute) {
            $this->fail('Missing Attribute with name "run" in test DB');
        }
        
        $expectedJson = '{
            "id":' . $attribute->getId() . ',
            "name":"run",
            "type":"activity"
        }';
        
        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($attribute->export()),
            'Attribute::export() returns the expected data array');
    }
    
    public function testIsValidType($value='')
    {
        $this->assertTrue(Attribute::isValidType('activity'));
        $this->assertFalse(Attribute::isValidType('invalidtype'));
    }
}