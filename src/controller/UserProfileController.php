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
        return true;
    }

    public function getWebIdLocation () {
        return empty($_POST['webId']) ? WebIdService::LOCAL_WEB_ID : $_POST['webIdLocation'];
    }

}
