<?php

namespace org\desone\wordpress\wpLinkedData;

require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'service/WebIdService.php');
require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'model/RsaPublicKey.php');

/**
 * Helps to retrieve the correct WebID and account uri of a user
 */
class UserProfileWebIdService implements WebIdService {

    public function hasUserCustomWebId ($user) {
        return get_the_author_meta ('webIdLocation', $user->ID) == WebIdService::CUSTOM_WEB_ID;
    }

    public function getWebIdOf ($user) {
        if ($this->hasUserCustomWebId($user)) {
            return esc_attr (get_the_author_meta ('webId', $user->ID));
        } else {
            return $this->getLocalWebId ($user);
        }
    }

    public function getLocalWebId ($user) {
        return $this->getUserDocumentUri ($user) . '#me';
    }

    public function getAccountUri ($user) {
        return $this->getUserDocumentUri ($user) . '#account';
    }

    public function getUserDocumentUri ($user) {
        return untrailingslashit (get_author_posts_url ($user->ID));
    }

    public function getRsaPublicKey ($user) {
        $exponent = esc_attr (get_the_author_meta ('publicKeyExponent', $user->ID));
        $modulus = esc_attr (get_the_author_meta ('publicKeyModulus', $user->ID));
        if (empty($exponent) || empty($modulus)) {
            return null;
        }
        return new RsaPublicKey($exponent, $modulus);
    }
}

?>