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
        if (!$request->files->has('gpxfile')) {
            throw (new ApiException('gpxfile variable not set', 400));
        }

        $file = $request->files->get('gpxfile');
        
        if (!$file->isValid()) {
            throw (new ApiException('An error happened uploading the GPX file', 400));
        }
        
        $importer = new GpxFileImporter();
        try {
            $routes = $importer->parse(file_get_contents($file->getPathname()));
        } catch (\Exception $e) {
            throw (new ApiException('Problem parsing GPX file - not a valid GPX file?', 400));
        }
        
        $postgis = $this->get('postgis');
        $filesystem = $this->get('gpx_files_filesystem');
        
        $gpxFile = new GpxFile();    
        $gpxFile->setFile($file);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($gpxFile);
        $em->flush();
            
        $filename = $gpxFile->upload($filesystem);
        
        echo $gpxFile->getId();
        exit;
        
        $importedRoutesIds = array();
        foreach ($routes as $route) {
            $route->setGpxFileId($gpxFile->getId());
            $importedRoutesIds[] = $postgis->writeRoute($route);
        }

        $output = array('usermsg' => 'GPX successfully imports', "value" => json_decode('{"route_ids": '.json_encode($importedRoutesIds).'}'));

        return $this->getRestResponse($output);
    }
    
}
