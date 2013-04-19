<?php

class WP_User {

    public $ID;
    public $display_name;
    public $user_firstname;
    public $user_lastname;
    public $nickname;
    public $user_description;

    public function __construct ($id, $name) {
        $this->ID = $id;
        $this->display_name = $name;
    }

}
