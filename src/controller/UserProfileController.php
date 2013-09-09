<?php

namespace org\desone\wordpress\wpLinkedData;

/**
 * Adds WebID specific fields to the user profile screen and saves them
 */
class UserProfileController {

    private $webIdService;

    public function __construct ($webIdService) {
        $this->webIdService = $webIdService;
    }

    /**
     * Renders html fields that allow to input WebID specific information
     * @param $user The user who's profile is shown
     */
    public function renderWebIdSection ($user) {
        include (WP_LINKED_DATA_PLUGIN_DIR_PATH . 'view/webId.html');
    }

    /**
     * Stores the WebID specific information in the user's meta data
     * @param $userId The ID of the user that is saved
     * @return bool Whether the save succeeded
     */
    public function saveWebIdData ($userId) {
        if (!current_user_can ('edit_user', $userId)) {
            return false;
        }
        update_user_meta ($userId, 'webIdLocation', $this->getWebIdLocation ());
        update_user_meta ($userId, 'webId', $_POST['webId']);
        update_user_meta ($userId, 'publicKeyModulus', $_POST['publicKeyModulus']);
        update_user_meta ($userId, 'publicKeyExponent', $_POST['publicKeyExponent']);
        $this->saveAdditionalRdf ($userId);
        return true;
    }

    public function saveAdditionalRdf ($userId) {
        try {
            $serializedRdf = trim (stripslashes ($_POST['additionalRdf']));
            if (!empty($serializedRdf)) {
                $graph = new \EasyRdf_Graph();
                $graph->parse ($serializedRdf); // parsing if done to check if syntax is valid
                update_user_meta ($userId, 'additionalRdf', $serializedRdf);
            } else {
                update_user_meta ($userId, 'additionalRdf', '');
            }
        } catch (\Exception $ex) {
            wp_die ('Addtional RDF Triples could not be saved. Cause: ' . $ex->getMessage ());
        }
    }

    public function getWebIdLocation () {
        return empty($_POST['webId']) ? WebIdService::LOCAL_WEB_ID : $_POST['webIdLocation'];
    }

}
