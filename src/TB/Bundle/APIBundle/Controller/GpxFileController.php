<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use TB\Bundle\FrontendBundle\Entity\GpxFile;
use TB\Bundle\APIBundle\Util\GpxFileImporter;
use TB\Bundle\APIBundle\Util\ApiException;

class GpxFileController extends AbstractRestController
{

    /**
     * @Route("/import/gpx")
     * @Method("GET")
     * @Template()
     */    
    public function importAction()
    {
        $form = $this->get('form.factory')->createNamedBuilder(null, 'form')
            ->add('gpxfile', 'file')
            ->getForm();

        return array(
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/import/gpx")
     * @Method("POST")
     */
    public function postImport(Request $request)
    {   
        if (!$request->headers->has('Trailburning-User-ID')) {
            throw new ApiException('Header Trailburning-User-ID is not set', 400);
        }
        
        $userId = $request->headers->get('Trailburning-User-ID');
        $user = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($userId);

        if (!$user) {
            throw $this->createNotFoundException(
                sprintf('User with id "%s" not found', $userId)
            );
        }

        if (!$request->files->has('gpxfile')) {
            throw (new ApiException('gpxfile variable not set', 400));
        }
        $file = $request->files->get('gpxfile');
        
        $importer = $this->get('tb.gpxfile.importer');
        try {
            $routes = $importer->parse(file_get_contents($file->getPathname()));
        } catch (\Exception $e) {
            throw (new ApiException('Problem parsing GPX file - not a valid GPX file?', 400));
        }
    
        $filesystem = $this->get('gpx_files_filesystem');
        $postgis = $this->get('postgis');
        
        $gpxFile = new GpxFile();    
        $gpxFile->setFile($file);
        $filename = $gpxFile->upload($filesystem);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($gpxFile);
        $em->flush();
        
        $importedRoutesIds = array();
        foreach ($routes as $route) {
            $route->setGpxFileId($gpxFile->getId());
            $route->setUserId($user->getId());
            $importedRoutesIds[] = $postgis->writeRoute($route);
        }

        $output = array('usermsg' => 'GPX successfully imports', "value" => json_decode('{"route_ids": '.json_encode($importedRoutesIds).'}'));

        return $this->getRestResponse($output);
    }
    
}
