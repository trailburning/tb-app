<?php 

namespace TB\Bundle\FrontendBundle\Tests\Service;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;

class FacebookConnectorTest extends AbstractFrontendTest
{
    
    public function testGetProfilePicture()
    {
        $facebookConnector = $this->getContainer()->get('tb.facebook.connector');
        $picture = $facebookConnector->getProfilePicture(863323267041867);
        $this->assertRegExp('/https:\/\/fbcdn-profile-a\.akamaihd\.net\/hprofile-ak-prn2\/v\/t1\.0-1\/p200x200/', $picture);
        
        $picture = $facebookConnector->getProfilePicture(8633232670418672323423423);
        $this->assertNull($picture, 'return value is null for invalid userId');
    }

}