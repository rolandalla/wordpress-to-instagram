<?php

namespace InstagramAPI;

class UploadJobVideoResponse extends Response {

    protected $upload_id;
    protected $video_upload_urls;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->upload_id = $response['upload_id'];
            $this->video_upload_urls = $response['video_upload_urls'];
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getUploadId() {
        return $this->upload_id;
    }

    public function getVideoUploadUrls() {
        return $this->video_upload_urls;
    }

    public function getVideoUploadUrl() {
        return $this->getVideoUploadUrls()[3]['url'];
    }

    public function getVideoUploadJob() {
        return $this->getVideoUploadUrls()[3]['job'];
    }
}
