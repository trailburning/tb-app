<?php

namespace TB\Bundle\FrontendBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use TB\Bundle\FrontendBundle\Entity\User;

class TBFrontendBundle extends Bundle
{	
	public function boot()
	{
        $u = new User();
		$this->container->get('doctrine')->getEntityManager()->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('hstore', 'hstore');
	}
}
