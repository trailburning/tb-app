<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use TB\Bundle\APIBundle\Service\ApiException;
use TB\Bundle\APIBundle\Service\JpegMedia;
use TB\Bundle\FrontendBundle\Entity\Media;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class RegionController extends AbstractRestController
{
    
    /**
     * @Route("/region/{id}/area")
     * @Method("POST")
     */
    public function postRegionArea($id)
    {
        
        $postgis = $postgis = $this->get('postgis');
        
        $request = $this->getRequest();
        if (!$request->files->has('gmlfile')) {
            throw (new ApiException('gmlfile variable not set', 400));
        }
        $file = $request->files->get('gmlfile');
        
        
        $region = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Region')
            ->findOneById($id);

        if (!$region) {
            throw new ApiException(sprintf('Region with id "%s" not found', $id), 400);
        }
        
        $gml = file_get_contents($file->getPathname()); 
        try {
            $postgis->updateRegionArea($region->getId(), $gml);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), 400);
        }       
        
        $output = ['usermsg' => 'success'];

        return $this->getRestResponse($output);
    }
}
