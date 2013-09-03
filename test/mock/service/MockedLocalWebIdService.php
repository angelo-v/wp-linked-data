<?php

namespace org\desone\wordpress\wpLinkedData;

require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'service/WebIdService.php');

class MockedLocalWebIdService implements WebIdService {

    public function hasUserCustomWebId ($user) {
        return false;
    }

    public function getWebIdOf ($user) {
        return 'http://example.com/author/' . $user->ID . '#me';
    }

    public function getAccountUri ($user) {
        return 'http://example.com/author/' . $user->ID . '#account';
    }
}

?>