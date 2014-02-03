<?php

namespace TB\Bundle\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use TB\Bundle\FrontendBundle\Entity\GpxFile;
use TB\Bundle\APIBundle\Util\GpxImporter;

class GpxFileController extends Controller
{
    /**
     * @Route("/import/gpx", name="gpx_import")
     * @Method("GET")
     * @Template()
     */
    public function importAction()
    {
        $gpxFile = new GpxFile();

        $form = $this->createFormBuilder($gpxFile)
            ->setAction($this->generateUrl('gpx_post_import'))
            ->add('path', 'file')
            ->add('Senden', 'submit')
            ->getForm();

        return $this->render('TBAPIBundle:Gpx:import.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    /**
     * @Route("/import/gpx", name="gpx_post_import")
     * @Method("POST")
     * @Template()
     */
    public function postImportAction()
    {
        if (!array_key_exists("gpxfile", $_FILES)) {
            throw (new ApiException("Gpxfile variable not set", 400));
        }
        if ($_FILES['gpxfile']['error'] != 0) {
            throw (new ApiException("An error happened uploading the GPX file", 400));
        }
        $gpx_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $_FILES["gpxfile"]["name"]);
        $gpx_tmp_path = $_FILES["gpxfile"]["tmp_name"];

        $importer = new GPXImporter();
        try {
            $routes = $importer->parse(file_get_contents($gpx_tmp_path));
        } catch (\Exception $e) {
            throw (new ApiException("Problem parsing GPX file - not a valid GPX file?", 400));
        }

        $db = getDB();

        $gpx_file_id = $db->importGpxFile('/trailburning-gpx/'.$gpx_tmp_path);
        $importedRoutesIds = array();
        foreach ($routes as $route) {
            $route->setGpxFileId($gpx_file_id);
            $importedRoutesIds[] = $db->writeRoute($route);
        }

        $aws_client = S3Client::factory(array(
            'key'    => $_SERVER['AWS_ACCESSKEY'],
            'secret' => $_SERVER['AWS_SECRETKEY']
        ));

        $result = $aws_client->putObject(array(
            'Bucket'    => 'trailburning-gpx',
            'Key'       => sha1_file($gpx_tmp_path).'.gpx',
            'Body'      => file_get_contents($gpx_tmp_path)
        ));

        $res = $slim->response();
        $res['Content-Type'] = 'application/json';
        $slim->render(
            'ApiReplyView.php', 
            array("value" => '{"route_ids": '.json_encode($importedRoutesIds).'}', 'usermsg' => 'GPX successfully imports'), 
            200
        );
    }
    
}
