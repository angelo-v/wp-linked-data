<?php

namespace org\desone\wordpress\wpLinkedData;

require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'service/WebIdService.php');

class MockedCustomWebIdService implements WebIdService {

    public function hasUserCustomWebId ($user) {
        return true;
    }

    public function getWebIdOf ($user) {
        return 'http://custom.webid.example#me';
    }

    public function getAccountUri ($user) {
        return 'http://example.com/author/' . $user->ID . '#account';
    }

    public function getLocalWebId ($user) {
        return 'http://example.com/author/' . $user->ID . '#me';
    }
}

?>