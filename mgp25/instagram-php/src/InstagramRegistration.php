<?php

namespace InstagramAPI;

class InstagramRegistration
{
    protected $debug;
    protected $IGDataPath;
    protected $username;
    protected $uuid;
    protected $userAgent;

    public function __construct($debug = false, $IGDataPath = null)
    {
        $this->debug = $debug;
        $this->uuid = $this->generateUUID(true);
        if (!is_null($IGDataPath)) {
            $this->IGDataPath = $IGDataPath;
        } else {
            $this->IGDataPath = __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR;
        }

        $this->userAgent = 'Instagram '.Constants::VERSION.' Android (18/4.3; 320dpi; 720x1280; Xiaomi; HM 1SW; armani; qcom; en_US)';
    }

  /**
   * Checks if the username is already taken (exists).
   *
   * @param string $username
   *
   * @return array
   *   Username availability data
   */
  public function checkUsername($username)
  {
      $data = json_encode([
          '_uuid'      => $this->uuid,
          'username'   => $username,
          '_csrftoken' => 'missing',
      ]);

      return $this->request('users/check_username/', $this->generateSignature($data))[1];
  }

  /**
   * Register account.
   *
   * @param string $username
   * @param string $password
   * @param string $email
   *
   * @return array
   *   Registration data
   */
  public function createAccount($username, $password, $email)
  {
      $data = json_encode([
          'phone_id'           => $this->uuid,
          '_csrftoken'         => 'missing',
          'username'           => $username,
          'first_name'         => '',
          'guid'               => $this->uuid,
          'device_id'          => 'android-'.str_split(md5(mt_rand(1000, 9999)), 17)[mt_rand(0, 1)],
          'email'              => $email,
          'force_sign_up_code' => '',
          'qs_stamp'           => '',
          'password'           => $password,
      ]);

      $result = $this->request('accounts/create/', $this->generateSignature($data));
      if (isset($result[1]['account_created']) && ($result[1]['account_created'] == true)) {
          $this->username_id = $result[1]['created_user']['pk'];
          file_put_contents($this->IGDataPath."$username-userId.dat", $this->username_id);
          preg_match('#Set-Cookie: csrftoken=([^;]+)#', $result[0], $match);
          $token = $match[1];
          $this->username = $username;
          file_put_contents($this->IGDataPath."$username-token.dat", $token);
          rename($this->IGDataPath.'cookies.dat', $this->IGDataPath."$username-cookies.dat");
      }

      return $result;
  }

    public function generateSignature($data)
    {
        $hash = hash_hmac('sha256', $data, Constants::IG_SIG_KEY);

        return 'ig_sig_key_version='.Constants::SIG_KEY_VERSION.'&signed_body='.$hash.'.'.urlencode($data);
    }

    public function generateUUID($type)
    {
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

        return $type ? $uuid : str_replace('-', '', $uuid);
    }

    public function request($endpoint, $post = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Constants::API_URL.$endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        if (file_exists($this->IGDataPath."$this->username-cookies.dat")) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath."$this->username-cookies.dat");
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath."$this->username-cookies.dat");
        } else {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath.'cookies.dat');
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath.'cookies.dat');
        }

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $body = substr($resp, $header_len);

        curl_close($ch);

        if ($this->debug) {
            echo "REQUEST: $endpoint\n";
            if (!is_null($post)) {
                if (!is_array($post)) {
                    echo "DATA: $post\n";
                }
            }
            echo "RESPONSE: $body\n\n";
        }

        return [$header, json_decode($body, true)];
    }
}
