<?php

namespace org\desone\wordpress\wpLinkedData;


interface WebIdService {

    const LOCAL_WEB_ID = 'localWebId';
    const CUSTOM_WEB_ID = 'customWebId';

    public function hasUserCustomWebId ($user);
    public function getWebIdOf ($user);
    public function getAccountUri ($user);

}

?>