<?php

namespace InstagramAPI;

class MediaInfoResponse extends Response {

    protected $taken_at;
    protected $image_url;
    protected $like_count;
    protected $likers;
    protected $comments;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->taken_at = $response['items'][0]['taken_at'];
            $this->image_url = $response['items'][0]['image_versions2']['candidates']['0']['url'];
            $this->like_count = $response['items'][0]['like_count'];
            $likers = [];
            foreach ($response['items'][0]['likers'] as $liker)
            {
                $likers[] = new User($liker);
            }
            $this->likers = $likers;
            $comments = [];
            foreach ($response['items'][0]['comments'] as $comment)
            {
                $comments[] = new Comment($comment);
            }
            $this->comments = $comments;
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }
}
