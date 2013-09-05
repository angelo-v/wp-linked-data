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


class UserProfileControllerTest extends \PHPUnit_Framework_TestCase {

    public function testSaveWebIdData () {
        global $saved_meta_data;
        $_POST['webIdLocation'] = WebIdService::CUSTOM_WEB_ID;
        $_POST['webId'] = 'http://example.com';
        $_POST['publicKeyModulus'] = '';
        $_POST['publicKeyExponent'] = '';
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
        $_POST['publicKeyModulus'] = '';
        $_POST['publicKeyExponent'] = '';
        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (99);
        $this->assertFalse ($result);
        $this->assertTrue (empty($saved_meta_data));
    }

    public function testSaveLocalWebIdIfCustomIsEmpty () {
        global $saved_meta_data;
        $_POST['webIdLocation'] = WebIdService::CUSTOM_WEB_ID;
        $_POST['webId'] = '';
        $_POST['publicKeyModulus'] = '';
        $_POST['publicKeyExponent'] = '';
        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (42);
        $this->assertTrue ($result);
        $this->assertEquals (WebIdService::LOCAL_WEB_ID, $saved_meta_data['webIdLocation']);
        $this->assertEquals ('', $saved_meta_data['webId']);
    }

    public function testSaveRsaPublicKey () {
        global $saved_meta_data;
        $_POST['webId'] = '';
        $_POST['webIdLocation'] = '';
        $_POST['publicKeyModulus'] = 'abc123';
        $_POST['publicKeyExponent'] = '1234';
        $controller = new UserProfileController(null);
        $result = $controller->saveWebIdData (42);
        $this->assertTrue ($result);
        $this->assertEquals ('abc123', $saved_meta_data['publicKeyModulus']);
        $this->assertEquals (1234, $saved_meta_data['publicKeyExponent']);
    }

}
