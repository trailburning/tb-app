<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use TB\Bundle\APIBundle\Util\ApiException;
use TB\Bundle\APIBundle\Util\JpegMedia;
use TB\Bundle\FrontendBundle\Entity\Media;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class MediaController extends AbstractRestController
{
    
    /**
     * @Route("/region/{id}/area")
     * @Method("PUT")
     */
    public function putRegionArea($id)
    {
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
        
        $media->setCoords(new Point($mediaObj->coords->long, $mediaObj->coords->lat, 4326));
        $media->setTags($mediaObj->tags);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($media);
        $em->flush();
        
        $output = ['usermsg' => 'success', "value" => $id];

       return $this->getRestResponse($output);
    }
}
