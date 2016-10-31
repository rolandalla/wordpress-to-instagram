<?php

namespace InstagramAPI;

class Comment {

    protected $username_id;
    protected $comment;
    protected $user;

    public function __construct($commentData)
    {
        $this->username_id = $commentData['user_id'];
        $this->comment = $commentData['text'];
        $this->user = new User($commentData['user']);
    }

    public function getUsernameId() {
        return $this->username_id;
    }

    public function getComment() {
        return $this->comment;
    }

    public function getUser() {
        return $this->user;
    }
}
