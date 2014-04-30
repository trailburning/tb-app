<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TB\Bundle\APIBundle\Util\ApiException;
use TB\Bundle\FrontendBundle\Entity\Attribute;

class AttributeController extends AbstractRestController
{
    
    /**
     * @Route("/attribute/{type}/list")
     * @Method("GET")
     */
    public function getTypeListAction($type)
    {
        $this->checkType($type);
        
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT a FROM TBFrontendBundle:Attribute a
                WHERE a.type=:type
                ORDER BY a.name')
            ->setParameter('type', $type);

        $attributes = $query->getResult();
        
        $jsonAttributes = [];
        
        foreach ($attributes as $attribute) {
            $jsonAttributes[] = $attribute->export();
        }
        
        $output = ['usermsg' => 'success', 'value' => ['attributes' => $jsonAttributes]];

        return $this->getRestResponse($output);
    }
    
    protected function checkType($type)
    {
        if (!Attribute::isValidType($type)) {
            throw (new ApiException(sprintf('Invalid type "%s"', $type), 400));
        }
    }
}
