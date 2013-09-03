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

function get_the_author_meta ($field, $userId) {
    $metadata = array(
        1 => array(
            'webIdLocation' => WebIdService::CUSTOM_WEB_ID,
            'webId' => 'http://custom.webid.example#me'
        ),
        2 => array(
            'webIdLocation' => WebIdService::LOCAL_WEB_ID,
            'webId' => ''
        ),
    );
    return $metadata[$userId][$field];
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


}
