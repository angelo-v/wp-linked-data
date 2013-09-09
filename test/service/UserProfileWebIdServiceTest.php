<?php

namespace org\desone\wordpress\wpLinkedData;

require_once 'test/mock/mock_plugin_dir_path.php';
require_once 'test/mock/WP_User.php';
require_once 'src/service/UserProfileWebIdService.php';

function untrailingslashit ($string) {
    return $string;
}

function esc_attr ($string) {
    return $string;
}

function get_author_posts_url ($id) {
    return 'http://example.com/author/' . $id;
}

$mockedMetaData = array(
    1 => array(
        'webIdLocation' => WebIdService::CUSTOM_WEB_ID,
        'webId' => 'http://custom.webid.example#me'
    ),
    2 => array(
        'webIdLocation' => WebIdService::LOCAL_WEB_ID,
        'webId' => '',
        'additionalRdf' => ''
    )
);

function get_the_author_meta ($field, $userId) {
    global $mockedMetaData;
    return $mockedMetaData[$userId][$field];
}

function setMockedMetaData ($userId, $dataArray) {
    global $mockedMetaData;
    $mockedMetaData[$userId] = $dataArray;
}


class UserProfileWebIdServiceTest extends \PHPUnit_Framework_TestCase {

    public function testHasUserCustomWebIdForUserWithCustomWebId () {
        $service = new UserProfileWebIdService();
        $user = new \WP_User(
            1, 'Mario Mustermann'
        );
        $this->assertTrue($service->hasUserCustomWebId($user));
    }

    public function testHasUserCustomWebIdForUserWithLocalWebId () {
        $service = new UserProfileWebIdService();
        $user = new \WP_User(
            2, 'Maria Musterfrau'
        );
        $this->assertFalse($service->hasUserCustomWebId($user));
    }

    public function testGetCustomWebId () {
        $service = new UserProfileWebIdService();
        $user = new \WP_User(
            1, 'Mario Mustermann'
        );
        $this->assertEquals('http://custom.webid.example#me', $service->getWebIdOf($user));
    }

    public function testGetLocalWebId () {
        $service = new UserProfileWebIdService();
        $user = new \WP_User(
            2, 'Maria Musterfrau'
        );
        $this->assertEquals('http://example.com/author/2#me', $service->getWebIdOf($user));
    }

    public function testGetRsaPublicKey () {
        setMockedMetaData (3, array(
            'publicKeyExponent' => 1234,
            'publicKeyModulus' => 'abc123'
        ));
        $service = new UserProfileWebIdService();
        $user = new \WP_User(
            3, 'Walter Whatever'
        );
        $key = $service->getRsaPublicKey ($user);
        $this->assertNotNull ($key);
        $this->assertEquals(1234, $key->getExponent());
        $this->assertEquals('abc123', $key->getModulus());
    }

    public function testGetNoRsaPublicKeyIfExponentIsMissing () {
        setMockedMetaData (3, array(
            'publicKeyExponent' => '',
            'publicKeyModulus' => 'abc123'
        ));
        $service = new UserProfileWebIdService();
        $user = new \WP_User(
            3, 'Walter Whatever'
        );
        $key = $service->getRsaPublicKey ($user);
        $this->assertNull ($key);
    }

    public function testGetNoRsaPublicKeyIfModulusIsMissing () {
        setMockedMetaData (3, array(
            'publicKeyExponent' => 1234,
            'publicKeyModulus' => ''
        ));
        $service = new UserProfileWebIdService();
        $user = new \WP_User(
            3, 'Walter Whatever'
        );
        $key = $service->getRsaPublicKey ($user);
        $this->assertNull ($key);
    }
}
