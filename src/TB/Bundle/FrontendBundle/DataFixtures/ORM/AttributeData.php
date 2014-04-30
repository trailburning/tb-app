<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use TB\Bundle\FrontendBundle\Entity\Attribute;

class AttributeData extends AbstractFixture implements FixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $attribute = new Attribute();
        $attribute->setName('run');
        $attribute->setType('activity');
        
        $manager->persist($attribute);
        $manager->flush();
        $this->addReference('Attribute-run', $attribute);
        
        $attribute = new Attribute();
        $attribute->setName('cycle');
        $attribute->setType('activity');
        
        $manager->persist($attribute);
        $manager->flush();
        $this->addReference('Attribute-cycle', $attribute);
        
        $attribute = new Attribute();
        $attribute->setName('ski');
        $attribute->setType('activity');
        
        $manager->persist($attribute);
        $manager->flush();
        $this->addReference('Attribute-ski', $attribute);
    }
    
    public function getOrder()
    {
        return 1;
    }
    
}