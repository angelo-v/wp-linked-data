<?php

namespace org\desone\wordpress\wpLinkedData;


interface WebIdService {

    const LOCAL_WEB_ID = 'localWebId';
    const CUSTOM_WEB_ID = 'customWebId';

    /**
     * @abstract
     * @param $user
     * @return boolean Whether or not the given user has chosen to use a custom WebID
     */
    public function hasUserCustomWebId ($user);

    /**
     * @abstract
     * @param $user
     * @return String The actual WebID of the given user, i.e. local or custom WebID depending on his/her choice
     */
    public function getWebIdOf ($user);

    /**
     * @abstract
     * @param $user
     * @return String The URI of the given user's account
     */
    public function getAccountUri ($user);

    /**
     * @abstract
     * @param $user
     * @return String The WebID URI that is used, if the given user chooses a local WebID
     */
    public function getLocalWebId ($user);

}

?>