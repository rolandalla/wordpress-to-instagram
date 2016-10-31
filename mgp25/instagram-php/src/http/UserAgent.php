<?php

namespace InstagramAPI;

class UserAgent
{
    protected $parent;

    public function __construct($parent) {
        $this->parent = $parent;
    }
    protected function getDeviceData()
    {
        $csvfile = __DIR__.'/devices.csv';
        $file_handle = fopen($csvfile, 'r');
        $line_of_text = [];
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 1024);
        }
        $deviceData = explode(';', $line_of_text[mt_rand(0, 11867)][0]);
        fclose($file_handle);

        return $deviceData;
    }

    public function buildUserAgent()
    {
        $deviceData = $this->getDeviceData();
        $this->parent->settings->set('manufacturer', $deviceData[0]);
        $this->parent->settings->set('device', $deviceData[1]);
        $this->parent->settings->set('model', $deviceData[2]);

        return sprintf('Instagram %s Android (18/4.3; 320dpi; 720x1280; %s; %s; %s; qcom; en_US)', Constants::VERSION, $deviceData[0], $deviceData[1], $deviceData[2]);
    }
}
