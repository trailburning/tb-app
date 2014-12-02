<?php 

namespace TB\Bundle\FrontendBundle\Util;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Exception;

/**
 * 
 */
class FacebookConnector
{

    protected $appId;
    
    protected $appSecret;
    
    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }
    
    /**
     * 
     */
    public function getProfilePicture($userId)
    {
        $picture = null;
        $session = $this->getSession();
        if ($session) {
            $request = new FacebookRequest(
                $session, 
                'GET', 
                sprintf('/%s/picture', $userId),
                [
                    'redirect' => false,
                    'height' => '200',
                    'type' => 'normal',
                    'width' => '200',
                ]
            );
            try {
                $response = $request->execute();
                $graphObject = $response->getGraphObject();
                if ($graphObject->getProperty('is_silhouette') == false) {
                    $picture = $graphObject->getProperty('url');
                }
            } catch (Exception $e) {

            }
        }
        
        return $picture;
    }
    
    protected function getSession() 
    {
        FacebookSession::setDefaultApplication($this->appId, $this->appSecret);
        $session = FacebookSession::newAppSession();
        
        // To validate the session:
        try {
            $session->validate();
        } catch (FacebookRequestException $ex) {
            // Session not valid, Graph API returned an exception with the reason.
            $session = null;
        } catch (\Exception $ex) {
            // Graph API returned info, but it may mismatch the current app or have expired.
            $session = null;
        }
               
        return $session;
    }
    
}


