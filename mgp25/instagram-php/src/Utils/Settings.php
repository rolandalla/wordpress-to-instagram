<?php

namespace InstagramAPI;

class Settings
{
    private $path;
    private $sets;

    public function __construct($path)
    {
        $this->path = $path;
        $this->sets = [];
        if (file_exists($path)) {
            $fp = fopen($path, 'rb');
            while ($line = fgets($fp, 2048)) {
                $line = trim($line, ' ');
                if ($line[0] == '#') {
                    continue;
                }
                $kv = explode('=', $line, 2);
                $this->sets[$kv[0]] = trim($kv[1], "\r\n ");
            }
            fclose($fp);
        }
    }

    public function get($key, $default = null)
    {
        if ($key == 'sets') {
            return $this->sets;
        }
        if (isset($this->sets[$key])) {
            return $this->sets[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        if ($key == 'sets') {
            return;
        }
        $this->sets[$key] = $value;
        $this->Save();
    }

    public function Save()
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
        $fp = fopen($this->path, 'wb');
        fseek($fp, 0);
        foreach ($this->sets as $key => $value) {
            fwrite($fp, $key.'='.$value."\n");
        }
        fclose($fp);
    }

    public function __set($prop, $value)
    {
        $this->set($prop, $value);
    }

    public function __get($prop)
    {
        return $this->get($prop);
    }
}
