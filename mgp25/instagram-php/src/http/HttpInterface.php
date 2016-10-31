<?php

namespace InstagramAPI;

class HttpInterface
{
    protected $parent;
    protected $userAgent;

    public function __construct($parent)
    {
        $this->parent = $parent;
        $this->userAgent = $this->parent->settings->get('user_agent');
    }

    public function request($endpoint, $post = null, $login = false)
    {
        if (!$this->parent->isLoggedIn && !$login) {
            throw new InstagramException("Not logged in\n");

            return;
        }

        $headers = [
        'Connection: close',
        'Accept: */*',
        'Content-type: application/x-www-form-urlencoded; charset=UTF-8',
        'Cookie2: $Version=1',
        'Accept-Language: en-US',
    ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Constants::API_URL.$endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $body = substr($resp, $header_len);

        curl_close($ch);

        if ($this->parent->debug) {
            echo "REQUEST: $endpoint\n";
            if (!is_null($post)) {
                if (!is_array($post)) {
                    echo 'DATA: '.urldecode($post)."\n";
                }
            }
            echo "RESPONSE: $body\n\n";
        }

        return [$header, json_decode($body, true)];
    }

    public function uploadPhoto($photo, $caption = null, $upload_id = null)
    {
        $endpoint = Constants::API_URL.'upload/photo/';
        $boundary = $this->parent->uuid;

        if (!is_null($upload_id)) {
            $fileToUpload = Utils::createVideoIcon($photo);
        } else {
            $upload_id = number_format(round(microtime(true) * 1000), 0, '', '');
            $fileToUpload = file_get_contents($photo);
        }

        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'upload_id',
                'data' => $upload_id,
            ],
            [
                'type' => 'form-data',
                'name' => '_uuid',
                'data' => $this->parent->uuid,
            ],
            [
                'type' => 'form-data',
                'name' => '_csrftoken',
                'data' => $this->parent->token,
            ],
            [
                'type' => 'form-data',
                'name' => 'image_compression',
              'data'   => '{"lib_name":"jt","lib_version":"1.3.0","quality":"70"}',
            ],
            [
                'type'     => 'form-data',
                'name'     => 'photo',
                'data'     => $fileToUpload,
                'filename' => 'pending_media_'.number_format(round(microtime(true) * 1000), 0, '', '').'.jpg',
                'headers'  => [
          'Content-Transfer-Encoding: binary',
                    'Content-type: application/octet-stream',
                ],
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
                'Connection: close',
                'Accept: */*',
                'Content-type: multipart/form-data; boundary='.$boundary,
        'Content-Length: '.strlen($data),
        'Cookie2: $Version=1',
        'Accept-Language: en-US',
        'Accept-Encoding: gzip',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->parent->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = new UploadPhotoResponse(json_decode(substr($resp, $header_len), true));

        curl_close($ch);

        if (!$upload->isOk()) {
            throw new InstagramException($upload->getMessage());

            return;
        }

        if ($this->parent->debug) {
            echo 'RESPONSE: '.substr($resp, $header_len)."\n\n";
        }

        $configure = $this->parent->configure($upload->getUploadId(), $photo, $caption);
        $this->parent->expose();

        return $configure;
    }

    public function uploadVideo($video, $caption = null)
    {
        $videoData = file_get_contents($video);

        $endpoint = Constants::API_URL.'upload/video/';
        $boundary = $this->parent->uuid;
        $upload_id = round(microtime(true) * 1000);
        $bodies = [
          [
              'type' => 'form-data',
              'name' => 'upload_id',
              'data' => $upload_id,
          ],
          [
              'type' => 'form-data',
              'name' => '_csrftoken',
              'data' => $this->parent->token,
          ],
          [
              'type'   => 'form-data',
              'name'   => 'media_type',
              'data'   => '2',
          ],
          [
              'type' => 'form-data',
              'name' => '_uuid',
              'data' => $this->parent->uuid,
          ],
      ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
          'Connection: keep-alive',
          'Accept: */*',
          'Host: i.instagram.com',
          'Content-type: multipart/form-data; boundary='.$boundary,
          'Accept-Language: en-en',
      ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $body = new UploadJobVideoResponse(json_decode(substr($resp, $header_len), true));

        $uploadUrl = $body->getVideoUploadUrl();
        $job = $body->getVideoUploadJob();

        $request_size = floor(strlen($videoData) / 4);

        $lastRequestExtra = (strlen($videoData) - ($request_size * 4));

        for ($a = 0; $a <= 3; $a++) {
            $start = ($a * $request_size);
            $end = ($a + 1) * $request_size + ($a == 3 ? $lastRequestExtra : 0);

            $headers = [
              'Connection: keep-alive',
              'Accept: */*',
              'Host: upload.instagram.com',
              'Cookie2: $Version=1',
              'Accept-Encoding: gzip, deflate',
              'Content-Type: application/octet-stream',
              'Session-ID: '.$upload_id,
              'Accept-Language: en-en',
              'Content-Disposition: attachment; filename="video.mov"',
              'Content-Length: '.($end - $start),
              'Content-Range: '.'bytes '.$start.'-'.($end - 1).'/'.strlen($videoData),
              'job: '.$job,
          ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $uploadUrl);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($videoData, $start, $end));

            $result = curl_exec($ch);
            $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($result, $header_len);
            $array[] = [$body];
        }
        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = new UploadVideoResponse(json_decode(substr($resp, $header_len), true));

        curl_close($ch);

        if ($this->parent->debug) {
            echo 'RESPONSE: '.substr($resp, $header_len)."\n\n";
        }

        $configure = $this->parent->configureVideo($upload_id, $video, $caption);
        $this->parent->expose();

        return $configure;
    }

    public function changeProfilePicture($photo)
    {
        if (is_null($photo)) {
            echo "Photo not valid\n\n";

            return;
        }

        $uData = json_encode([
        '_csrftoken' => $this->parent->token,
        '_uuid'      => $this->parent->uuid,
        '_uid'       => $this->parent->username_id,
      ]);

        $endpoint = Constants::API_URL.'accounts/change_profile_picture/';
        $boundary = $this->parent->uuid;
        $bodies = [
        [
          'type' => 'form-data',
          'name' => 'ig_sig_key_version',
          'data' => Constants::SIG_KEY_VERSION,
        ],
        [
          'type' => 'form-data',
          'name' => 'signed_body',
          'data' => hash_hmac('sha256', $uData, Constants::IG_SIG_KEY).$uData,
        ],
        [
          'type'     => 'form-data',
          'name'     => 'profile_pic',
          'data'     => file_get_contents($photo),
          'filename' => 'profile_pic',
          'headers'  => [
            'Content-type: application/octet-stream',
            'Content-Transfer-Encoding: binary',
          ],
        ],
      ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
          'Proxy-Connection: keep-alive',
          'Connection: keep-alive',
          'Accept: */*',
          'Content-type: multipart/form-data; boundary='.$boundary,
          'Accept-Language: en-en',
          'Accept-Encoding: gzip, deflate',
      ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->parent->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        curl_close($ch);
    }

    public function direct_share($media_id, $recipients, $text = null)
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $string = [];
        foreach ($recipients as $recipient) {
            $string[] = "\"$recipient\"";
        }

        $recipient_users = implode(',', $string);

        $endpoint = Constants::API_URL.'direct_v2/threads/broadcast/media_share/?media_type=photo';
        $boundary = $this->parent->uuid;
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'media_id',
                'data' => $media_id,
            ],
            [
                'type' => 'form-data',
                'name' => 'recipient_users',
                'data' => "[[$recipient_users]]",
            ],
            [
                'type' => 'form-data',
                'name' => 'client_context',
                'data' => $this->parent->uuid,
            ],
            [
                'type' => 'form-data',
                'name' => 'thread_ids',
                'data' => '["0"]',
            ],
            [
                'type' => 'form-data',
                'name' => 'text',
                'data' => is_null($text) ? '' : $text,
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
                'Proxy-Connection: keep-alive',
                'Connection: keep-alive',
                'Accept: */*',
                'Content-type: multipart/form-data; boundary='.$boundary,
                'Accept-Language: en-en',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->parent->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        curl_close($ch);
    }

    protected function buildBody($bodies, $boundary)
    {
        $body = '';
        foreach ($bodies as $b) {
            $body .= '--'.$boundary."\r\n";
            $body .= 'Content-Disposition: '.$b['type'].'; name="'.$b['name'].'"';
            if (isset($b['filename'])) {
                $ext = pathinfo($b['filename'], PATHINFO_EXTENSION);
                $body .= '; filename="'.'pending_media_'.number_format(round(microtime(true) * 1000), 0, '', '').'.'.$ext.'"';
            }
            if (isset($b['headers']) && is_array($b['headers'])) {
                foreach ($b['headers'] as $header) {
                    $body .= "\r\n".$header;
                }
            }

            $body .= "\r\n\r\n".$b['data']."\r\n";
        }
        $body .= '--'.$boundary.'--';

        return $body;
    }
}
