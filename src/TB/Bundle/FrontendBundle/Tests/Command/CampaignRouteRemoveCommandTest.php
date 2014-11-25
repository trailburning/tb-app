<?php

namespace TB\Bundle\FrontendBundle\Tests\Command;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use TB\Bundle\FrontendBundle\Command\CampaignRouteRemoveCommand;

class CampaignRouteRemoveCommandTest extends AbstractFrontendTest
{
    
    public function testExecute()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]); 
        
        $campaign = $this->getCampaign('urbantrails-london');
        $route = $this->getRoute('london');
        
        $kernel = $this->createKernel();
        $kernel->boot();
        
        $application = new Application($kernel);
        $application->add(new CampaignRouteRemoveCommand());

        $command = $application->find('tb:campaign:route:remove');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'campaignId' => $campaign->getId(),
                'routeId' => $route->getId(),
            )
        );

        $this->assertRegExp('/' . sprintf('Route %s was removed from campaign %s', $route->getSlug(), $campaign->getSlug()) . '/', $commandTester->getDisplay());
    }

}
