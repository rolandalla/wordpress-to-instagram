<?php

namespace InstagramAPI;

class UploadPhotoResponse extends Response {

    protected $upload_id;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->upload_id = $response['upload_id'];
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getUploadId() {
        return $this->upload_id;
    }
}
