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
     * @Route("/route/{routeId}/medias")
     * @Method("GET")
     */
    public function getRouteMedias($routeId)
    {
        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById($routeId);

        if (!$route) {
            throw $this->createNotFoundException(
                sprintf('Route with id "%s" not found', $routeId)
            );
        }
        
        $postgis = $this->get('postgis');
        $medias = $postgis->getRouteMedia($routeId);
        
        $jsonMedias = [];
        foreach ($medias as $media) {
            $jsonMedias[] = $media->jsonSerialize();
        }
        
        $output = ['usermsg' => 'success', "value" => json_decode('['. implode(',', $jsonMedias).']')];

       return $this->getRestResponse($output);
    }
    
    /**
     * @Route("/route/{routeId}/medias/add")
     * @Method("POST")
     */
    public function postRouteMedias($routeId)
    {
        $request = $this->getRequest();
        if (!$request->files->has('medias')) {
            throw (new ApiException('medias variable not set', 400));
        }
        
        $mediaFiles = $request->files->get('medias');
        if (!is_array($mediaFiles)) {
            $mediaFiles = [$mediaFiles];
        }
        
        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById($routeId);

        if (!$route) {
            throw $this->createNotFoundException(
                sprintf('Route with id "%s" not found', $routeId)
            );
        }
        
        $mediaImporter = $this->get('media_importer');
        $filesystem = $this->get('trail_media_files_filesystem');
        $medias = [];
        
        foreach ($mediaFiles as $mediaFile) {
            $media = new Media();    
            $media->setRoute($route);
            $media->setFile($mediaFile);
            $media->readMetadata($mediaImporter);
            $media->upload($filesystem);
        
            $em = $this->getDoctrine()->getManager();
            $em->persist($media);
            $em->flush();    
            
            $medias[] = $media->jsonSerialize();
        }
        
        $output = ['usermsg' => 'success', "value" => json_decode('['. implode(',', $medias).']')];

       return $this->getRestResponse($output);
    }
    
    /**
     * @Route("/media/{id}")
     * @Method("DELETE")
     */
    public function deleteMedia($id)
    {
        $media = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Media')
            ->findOneById($id);

        if (!$media) {
            throw $this->createNotFoundException(
                sprintf('Media with id "%s" not found', $id)
            );
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($media);
        $em->flush();
                
        $output = ['usermsg' => 'success', "value" => $id];

        return $this->getRestResponse($output);
    }
    
    /**
     * @Route("/media/{id}")
     * @Method("PUT")
     */
    public function putMedia($id)
    {
        $request = $this->getRequest();
        if (!$request->request->has('json')) {
            throw new ApiException('Missing JSON object in request data', 400);
        }
        
        $mediaObj = json_decode($request->request->get('json'));
        if ($mediaObj === null) {
            throw new ApiException('Invalid JSON data', 400);
        }
        
        $media = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Media')
            ->findOneById($id);

        if (!$media) {
            throw new ApiException(sprintf('Media with id "%s" not found', $id), 400);
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
