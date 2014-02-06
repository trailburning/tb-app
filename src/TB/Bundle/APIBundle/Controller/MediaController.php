<?php

namespace TB\Bundle\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Response;
use TB\Bundle\APIBundle\Util\ApiException;
use TB\Bundle\APIBundle\Util\JpegMedia;
use TB\Bundle\FrontendBundle\Entity\Media;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class MediaController extends Controller
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
        
        $output = array('usermsg' => 'success', "value" => $medias);
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/route/{routeId}/medias/add")
     * @Method("POST")
     */
    public function postRouteMedias($routeId)
    {
        if (!array_key_exists('medias', $_FILES)) {
            throw (new ApiException("Medias variable not set", 400));
        }
        
        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById($routeId);

        if (!$route) {
            throw $this->createNotFoundException(
                sprintf('Route with id "%s" not found', $routeId)
            );
        }

        $postgis = $this->get('postgis');

        $r = $postgis->readRoute($routeId);

        if (($tz = $postgis->getTimezone($r->getCentroid()->getLongitude(), $r->getCentroid()->getLatitude())) == NULL) {
            throw new \Exception("Error getting timezone");     
        }

        $dtz = new \DateTimeZone($tz);
        $offset = $dtz->getOffset(\DateTime::createFromFormat('U', $r->getRoutePoints()[0]->getTags()['datetime']));

        $medias = array();
        for ($i = 0; $i < count($_FILES['medias']['name']); $i++) {
            if ($_FILES['medias']['error'][$i] != 0) {
                throw (new \ApiException("An error happened uploading the medias", 400));
            }

            $media_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $_FILES["medias"]["name"][$i]);

            $extension = strtolower(pathinfo($media_filename, PATHINFO_EXTENSION));
            switch ($extension) {
                case "jpg":
                case "jpeg":
                    $media = new JpegMedia();
                    break;
                default:
                    throw (new ApiException(sprintf('Tried to upload file with non recognised extension "%s"', $extension), 400));        
                    break;
            }

            $media_tmp_path = $_FILES["medias"]["tmp_name"][$i]; 
        
            $media->fromFile($media_filename, $media_tmp_path);
            $media->setTag("datetime", intval($media->getTag('datetime')) - $offset);

            $rp = $r->getNearestPointByTime($media->tags['datetime']);
            $media->setCoords($rp->getCoords()->getLongitude(), $rp->getCoords()->getLatitude());
            if (isset($rp->getTags()['altitude'])) {
                $media->setTag('altitude', $rp->getTags()['altitude']);
            }

            $postgis->importPicture($media);
            $postgis->attachMediaToRoute($routeId, $media);

            // $s3_client = $this->get('s3_client'); service call results in an error, fix when possible
            $s3_client = \Aws\S3\S3Client::factory(array(
                'key'    => $this->container->getParameter('aws_accesskey'),
                'secret' => $this->container->getParameter('aws_secretkey')
            ));
            
            $result = $s3_client->putObject(array(
                'Bucket'      => 'trailburning-media',
                'Key'         => sha1_file($media_tmp_path).'.jpg',
                'Body'        => file_get_contents($media_tmp_path),
                'ContentType' => $media->mimetype,
                'ACL'         => 'public-read'
            ));

            $medias[] = $media;
        }

        $output = array('usermsg' => 'success', "value" => $medias);
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/media/{id}")
     * @Method("DELETE")
     */
    public function deleteMedia($id)
    {
        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Media')
            ->findOneById($id);

        if (!$route) {
            throw $this->createNotFoundException(
                sprintf('Media with id "%s" not found', $id)
            );
        }
        
        $postgis = $this->get('postgis');
        $postgis->deleteMedia($id);
        
        $output = array('usermsg' => 'success', "value" => $id);
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
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
        
        $output = array('usermsg' => 'success', "value" => $id);
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
