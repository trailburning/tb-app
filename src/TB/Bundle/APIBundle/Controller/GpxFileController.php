<?php

namespace TB\Bundle\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use TB\Bundle\APIBundle\Entity\GpxFile;
use TB\Bundle\APIBundle\Util\GpxFileImporter;
use TB\Bundle\APIBundle\Util\ApiException;

class GpxFileController extends Controller
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
        
        if (!array_key_exists('gpxfile', $_FILES)) {
            throw (new ApiException('Gpxfile variable not set', 400));
        }
        if ($_FILES['gpxfile']['error'] != 0) {
            throw (new ApiException('An error happened uploading the GPX file', 400));
        }
        $gpx_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $_FILES['gpxfile']['name']);
        $gpx_tmp_path = $_FILES['gpxfile']['tmp_name'];

        $importer = new GpxFileImporter();
        try {
            $routes = $importer->parse(file_get_contents($gpx_tmp_path));
        } catch (\Exception $e) {
            throw (new ApiException('Problem parsing GPX file - not a valid GPX file?', 400));
        }

        $postgis = $this->get('postgis');
        
        $gpx_file_id = $postgis->importGpxFile('/trailburning-gpx/'.$gpx_tmp_path);
        $importedRoutesIds = array();
        foreach ($routes as $route) {
            $route->setGpxFileId($gpx_file_id);
            $importedRoutesIds[] = $postgis->writeRoute($route);
        }

        // $s3_client = $this->get('s3_client'); service call results in an error, fix when possible
        $s3_client = \Aws\S3\S3Client::factory(array(
            'key'    => $this->container->getParameter('aws_accesskey'),
            'secret' => $this->container->getParameter('aws_secretkey')
        ));

        $result = $s3_client->putObject(array(
            'Bucket'    => 'trailburning-gpx',
            'Key'       => sha1_file($gpx_tmp_path).'.gpx',
            'Body'      => file_get_contents($gpx_tmp_path)
        ));

        $output = array('usermsg' => 'GPX successfully imports', "value" => json_decode('{"route_ids": '.json_encode($importedRoutesIds).'}'));
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
}
