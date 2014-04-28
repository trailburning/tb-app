<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\RouteType;

class EventTest extends AbstractFrontendTest
{
    
    /**
     * Test export entity
     */
    public function testExport()
    {
        $this->loadFixtures(
            ['TB\Bundle\FrontendBundle\DataFixtures\ORM\EventData']
        );        
        
        // Get RouteCategory from DB with the name "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $event = $em
            ->getRepository('TBFrontendBundle:Event')
            ->findOneBySlug('eiger');
        if (!$event) {
            $this->fail('Missing Event with slug "eiger" in test DB');
        }
        
        $region = $em
            ->getRepository('TBFrontendBundle:Region')
            ->findOneBySlug('grindelwald');
        if (!$region) {
            $this->fail('Missing Region with slug "grindelwald" in test DB');
        }
        
        $expectedJson = '{
            "about": "The Eiger 3970m and its North Face fascinate climbers and trail runners likewise. In 1858 the local mountain guides Christian Almer and Peter Bohren together with their client Charles Barrington were the first to reach the summit. The Eiger Northface, the last of the big walls in the Alps, was conquered in 1938 by Anderl Heckmair and Ludwig V\u00f6rg as well as Heinrich Harrer und Fritz Kasparekt in a four day ascent.", 
            "date": "2014-07-19", 
            "date_to": null, 
            "id": ' . $event->getId() . ', 
            "image": "event_header.jpg", 
            "image_credit": "Thomas Senf / visualimpact.ch", 
            "link": "http://www.eigerultratrail.ch", 
            "location": [
                130.997576, 
                -25.25605
            ], 
            "logo": "logo_eiger.png", 
            "logo_small": "card_logo_eiger.png", 
            "region": {
                "about": "The lively holiday resort at the foot of the Eiger is closely linked with mountaineering. Time and time again top alpinists have continued to cause a sensation in the Eiger north face, but Grindelwald also offers a variety of possibilities for those seeking active recovery. \n\nGrindelwald has something to offer everyone. The village is lively, sporty and active, but as a contrast also offers secluded, idyllic corners to savour and linger.", 
                "id": ' . $region->getId() . ', 
                "image": "grindelwald.jpg", 
                "link": "http://www.grindelwald.ch", 
                "logo": "logo_grindelwald.png", 
                "name": "Grindelwald | Switzerland", 
                "slug": "grindelwald"
            }, 
            "slug": "eiger", 
            "subtitle": "Eiger Ultra Trail - harder than the North Face solo", 
            "synopsis": "Grindelwald warmly welcomes you to the foot hills of the Eiger, M\u00f6nch and Jungfrau and to the Jungfrau-Aletsch UNESCO World Nature Heritage.", 
            "title": "Eiger Ultra Trail", 
            "title2": "Switzerland"
        }';
        
        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($event->export()),
            'Event::export() returns the expected data array');
    }
}