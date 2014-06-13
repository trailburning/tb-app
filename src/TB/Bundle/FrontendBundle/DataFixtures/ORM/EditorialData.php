<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use TB\Bundle\FrontendBundle\Entity\Editorial;
use TB\Bundle\FrontendBundle\Entity\EditorialRoute;

class EditorialData extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $editorial = new Editorial();
        $editorial->setSlug('alps');
        $editorial->setUser($this->getReference('UserProfile-matt'));
        $editorial->setTitle('Discover how to \'get lost\' in the Alps');
        $editorial->setSynopsis('Trailburning Inspire is our new section that pulls together a collection of trails into a single story. By featuring multiple Trail Cards in an Inspire article Trailburning becomes greater than the sum of its parts.');
        $editorial->setText('<p>
It\'s a simple idea, we just want an easy way to present trails. If Inspire starts to function as a trail runner\'s to-do list or a hiker\'s weekend guide –  job done!
</p>
<p>
Each article in Inspire will have a common theme to stir your wanderlust, which brings me to the first Inspire – the Alps. When the team decided to feature the Alps all I could think about was getting lost, getting off the beaten track in the foothills of the Alps on a hike.  
</p>
<p>
But by getting lost, I really mean getting found. I want to find the lake halfway up the mountainside captured by a fellow trailburner or take the trail in Reuban\'s beautiful photo so I can pat the cow (see below). 
</p>
<p>
So as a dedication to all those who would also like to pat the cow and ‘get lost’, the Trailburning team presents Inspire. Enjoy!
</p>
');
        $editorial->setDate(new \DateTime('2014-01-31'));
        $editorial->setImage('Reuben_Tabner-0208_edited.jpg');
        $editorial->setImageCredit('Reuben Tabner');
        $editorial->setImageCreditUrl('http://reubentabner.co.uk');
        
        $manager->persist($editorial);
        $manager->flush();
        $this->addReference('Editorial-alps', $editorial);
        
        $editorialRoute = new EditorialRoute();
        $editorialRoute->setEditorial($editorial);
        $editorialRoute->setRoute($this->getReference('Route-grunewald'));
        $editorialRoute->setEditorialId($editorial->getId());
        $editorialRoute->setRouteId($this->getReference('Route-grunewald')->getId());
        $editorialRoute->setOrder(1);
        
        $manager->persist($editorialRoute);
        $manager->flush();
        
        $editorial->addEditorialRoute($editorialRoute);
        
        $manager->persist($editorialRoute);
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 1;
    }
        
    public function getDependencies()
    {
        return [
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ];
    }
}