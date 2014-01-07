<?php

namespace TB\Bundle\FrontendBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    public function testEvent()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/event');
    }

}
