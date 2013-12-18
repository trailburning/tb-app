<?php

namespace TB\Bundle\FrontendBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TBFrontendBundle extends Bundle
{	
	public function boot()
	{
		$this->container->get('doctrine')->getEntityManager()->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('hstore', 'hstore');
	}
}
