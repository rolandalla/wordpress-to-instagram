<?php

namespace InstagramAPI;

class Utils
{
    /**
     * Length of the file in Seconds.
     *
     * @param string $file
     *                     path to the file name
     *
     * @return int
     *             length of the file in seconds
     */
    public static function getSeconds($file)
    {
        $ffmpeg = self::checkFFMPEG();
        if ($ffmpeg) {
            $time = exec("$ffmpeg -i ".$file." 2>&1 | grep 'Duration' | cut -d ' ' -f 4");
            $duration = explode(':', $time);
            $seconds = $duration[0] * 3600 + $duration[1] * 60 + round($duration[2]);

            return $seconds;
        }

        return mt_rand(15, 300);
    }

    /**
     * Check for ffmpeg/avconv dependencies.
     *
     * @return string/boolean
     *                        name of the library if present, false otherwise
     */
    public static function checkFFMPEG()
    {
        @exec('ffmpeg -version 2>&1', $output, $returnvalue);
        if ($returnvalue === 0) {
            return 'ffmpeg';
        }
        @exec('avconv -version 2>&1', $output, $returnvalue);
        if ($returnvalue === 0) {
            return 'avconv';
        }

        return false;
    }

    /**
     * Creating a video icon/thumbnail.
     *
     * @param string $file
     *                     path to the video file
     *
     * @return image
     *               icon/thumbnail for the video
     */
    public static function createVideoIcon($file)
    {
        /* should install ffmpeg for the method to work successfully  */
        $ffmpeg = self::checkFFMPEG();
        if ($ffmpeg) {
            //generate thumbnail
            $preview = sys_get_temp_dir().'/'.md5($file).'.jpg';
            @unlink($preview);

            //capture video preview
            $command = $ffmpeg.' -i "'.$file.'" -f mjpeg -ss 00:00:01 -vframes 1 "'.$preview.'" 2>&1';
            @exec($command);

            return file_get_contents($preview);
        }
    }

    /**
     * Implements the actual logic behind creating the icon/thumbnail.
     *
     * @param string $file
     *                     path to the file name
     *
     * @return image
     *               icon/thumbnail for the video
     */
    public static function createIconGD($file, $size = 100, $raw = true)
    {
        list($width, $height) = getimagesize($file);
        if ($width > $height) {
            $y = 0;
            $x = ($width - $height) / 2;
            $smallestSide = $height;
        } else {
            $x = 0;
            $y = ($height - $width) / 2;
            $smallestSide = $width;
        }

        $image_p = imagecreatetruecolor($size, $size);
        $image = imagecreatefromstring(file_get_contents($file));

        imagecopyresampled($image_p, $image, 0, 0, $x, $y, $size, $size, $smallestSide, $smallestSide);
        ob_start();
        imagejpeg($image_p, null, 95);
        $i = ob_get_contents();
        ob_end_clean();

        imagedestroy($image);
        imagedestroy($image_p);

        return $i;
    }
}
