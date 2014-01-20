<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use TB\Bundle\FrontendBundle\Entity\GpxFile;

class GpxFileData extends AbstractFixture implements FixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $gpxFile = new GpxFile();
        $gpxFile->setPath('path');
        
        $manager->persist($gpxFile);
        $manager->flush();
        $this->addReference('GpxFile-grunewald', $gpxFile);
        
        $gpxFile = new GpxFile();
        $gpxFile->setPath('path');
        
        $manager->persist($gpxFile);
        $manager->flush();
        $this->addReference('GpxFile-ttm', $gpxFile);
    }
    
    public function getOrder()
    {
        return 1;
    }
    
}