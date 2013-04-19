<?php

class WP_Query {

    public $post;

    public function __construct ($posts = array()) {
        $this->posts = $posts;
    }

    function have_posts () {
        return count($this->posts) > 0;
    }

    function next_post () {
        $this->post = array_shift($this->posts);
    }

}
