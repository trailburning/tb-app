<?php

namespace TB\Bundle\APIBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TBAPIBundle extends Bundle
{
	public function boot()
	{
        // Enable the HStore Doctrine extension for PostgreSQL
		$this->container->get('doctrine')->getManager()->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('hstore', 'hstore');
        
        // Change the default Tempalate location because app folder was moved to support multiple apps
        $this->container->get('twig.loader')->addPath($this->container->get('kernel')->getRootDir() . '/Resources');
	}
}
