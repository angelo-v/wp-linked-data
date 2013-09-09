<?php

namespace org\desone\wordpress\wpLinkedData;

require_once 'test/mock/mock_plugin_dir_path.php';
require_once 'src/controller/UserProfileController.php';

$saved_meta_data = array();

function current_user_can ($action, $userId) {
    return $action == 'edit_user' && $userId == 42;
}

function update_user_meta ($userId, $field, $value) {
    global $saved_meta_data;
    $saved_meta_data[$field] = $value;
}

function wp_die ($message) {
}

class UserProfileControllerTest extends \PHPUnit_Framework_TestCase {

    protected function setUp () {
        $_POST['webIdLocation'] = '';
        $_POST['webId'] = '';
        $_POST['publicKeyModulus'] = '';
        $_POST['publicKeyExponent'] = '';
        $_POST['additionalRdf'] = '';
    }

    public function testSaveWebIdData () {
        global $saved_meta_data;
        $_POST['webIdLocation'] = WebIdService::CUSTOM_WEB_ID;
        $_POST['webId'] = 'http://example.com';
        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (42);
        $this->assertTrue ($result);
        $this->assertEquals (WebIdService::CUSTOM_WEB_ID, $saved_meta_data['webIdLocation']);
        $this->assertEquals ('http://example.com', $saved_meta_data['webId']);
    }

    public function testDoNotSaveWebIdDataIfUserIsNotAllowedToEdit () {
        global $saved_meta_data;
        $_POST['webIdLocation'] = WebIdService::CUSTOM_WEB_ID;
        $_POST['webId'] = 'http://example.com';
        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (99);
        $this->assertFalse ($result);
        $this->assertTrue (empty($saved_meta_data));
    }

    public function testSaveLocalWebIdIfCustomIsEmpty () {
        global $saved_meta_data;
        $_POST['webIdLocation'] = WebIdService::CUSTOM_WEB_ID;
        $_POST['webId'] = '';
        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (42);
        $this->assertTrue ($result);
        $this->assertEquals (WebIdService::LOCAL_WEB_ID, $saved_meta_data['webIdLocation']);
        $this->assertEquals ('', $saved_meta_data['webId']);
    }

    public function testSaveRsaPublicKey () {
        global $saved_meta_data;
        $_POST['publicKeyModulus'] = 'abc123';
        $_POST['publicKeyExponent'] = '1234';
        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (42);
        $this->assertTrue ($result);
        $this->assertEquals ('abc123', $saved_meta_data['publicKeyModulus']);
        $this->assertEquals (1234, $saved_meta_data['publicKeyExponent']);
    }

    public function testSaveAdditionalRdfXml () {
        global $saved_meta_data;
        $rdfxml = '<rdf:RDF xmlns:foaf="http://xmlns.com/foaf/0.1/"' .
            ' xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
            '<foaf:Person rdf:about="http://example.org/person/mmustermann#me">' .
            '<foaf:familyName>Mustermann</foaf:familyName>' .
            '<foaf:givenName>Mario</foaf:givenName>' .
            '<foaf:name>Mario Mustermann</foaf:name>' .
            '</foaf:Person>' .
            '</rdf:RDF>';
        $_POST['additionalRdf'] = $rdfxml;

        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (42);
        $this->assertTrue ($result);
        $this->assertEquals ($rdfxml, $saved_meta_data['additionalRdf']);
    }

    public function testSaveEmptyAdditionalRdf () {
        global $saved_meta_data;
        $_POST['additionalRdf'] = '   ';
        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (42);
        $this->assertTrue ($result);
        $this->assertEquals ('', $saved_meta_data['additionalRdf']);
    }

    public function testSaveAdditionalTurtle () {
        global $saved_meta_data;
        $turtle = '@prefix dc: <http://purl.org/dc/elements/1.1/>.' .
            '@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>.' .
            '<http://www.rdfabout.com/> dc:title "rdf:about: About Resource Description Framework".';
        $_POST['additionalRdf'] = $turtle;
        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (42);
        $this->assertTrue ($result);
        $this->assertEquals ($turtle, $saved_meta_data['additionalRdf']);
    }

    public function testDoNotSaveInvalidRdf () {
        global $saved_meta_data;
        $_POST['additionalRdf'] = 'invalid input';
        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (42);
        $this->assertTrue ($result);
        $this->assertFalse (isset($saved_meta_data['additionalRdf']));
    }

}
