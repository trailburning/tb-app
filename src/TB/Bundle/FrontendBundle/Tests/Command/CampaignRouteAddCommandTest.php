<?php

namespace TB\Bundle\FrontendBundle\Tests\Command;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use TB\Bundle\FrontendBundle\Command\CampaignRouteAddCommand;

class CampaignRouteAddCommandTest extends AbstractFrontendTest
{
    
    public function testExecute()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]); 
        
        $campaign = $this->getCampaign('urbantrails-london');
        $route = $this->getRoute('grunewald');
        
        $kernel = $this->createKernel();
        $kernel->boot();
        
        $application = new Application($kernel);
        $application->add(new CampaignRouteAddCommand());

        $command = $application->find('tb:campaign:route:add');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'campaignId' => $campaign->getId(),
                'routeId' => $route->getId(),
            )
        );

        $this->assertRegExp('/' . sprintf('Route %s was added to campaign %s', $route->getSlug(), $campaign->getSlug()) . '/', $commandTester->getDisplay());
    }

}
