<?php

namespace InstagramAPI;

class Response {

    const STATUS_OK = "ok";
    const STATUS_FAIL = "fail";

    protected $status;
    protected $message;

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getMessage() {
        return $this->message;
    }

    public function isOk() {
        return $this->getStatus() == self::STATUS_OK;
    }
}
