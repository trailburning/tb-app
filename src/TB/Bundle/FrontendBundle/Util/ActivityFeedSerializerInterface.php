<?php 

namespace TB\Bundle\FrontendBundle\Util;

use TB\Bundle\FrontendBundle\Entity\AbstractActivity;

/**
* 
*/
interface ActivityFeedSerializerInterface
{
    
    public function serialize(array $activityItems);

}
  