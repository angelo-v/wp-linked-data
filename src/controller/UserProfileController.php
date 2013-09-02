<?php

namespace org\desone\wordpress\wpLinkedData;

/**
 * Adds WebID specific fields to the user profile screen and saves them
 */
class UserProfileController {

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
        update_user_meta ($userId, 'webIdLocation', $_POST['webIdLocation']);
        update_user_meta ($userId, 'webId', $_POST['webId']);
        return true;
    }

    private function isWebIdHostedLocally ($user) {
        return esc_attr (get_the_author_meta ('webIdLocation', $user->ID)) != 'customWebId';
    }


}
