<?php

/***
*
* https://github.com/mgp25/Instagram-API/wiki/Dependencies
*
*/



$_server_extensions = get_loaded_extensions();

echo '<table class="wp-list-table widefat fixed ws-table-css">';
echo '<tbody>';

if (version_compare(phpversion(), '5.4.0', '<')) {
    // php version isn't high enough to be used with Instagram API
}
/*======= php ver =======*/
echo '<tr>';
echo '<td>';
echo 'PHP 5.6 and upper';
echo '&nbsp; ( <a href="https://php.net/releases/" target="_blank">https://php.net/releases/</a>&nbsp; )';
echo '</td>';
echo '<td>';
if (version_compare(phpversion(), '5.6', '<')) {
  echo '<span class="dashicons dashicons-no-alt" title="No" style="color:#a20404;font-size: 200%"></span>';
  echo "<span style=\"color:red\">    ".phpversion()."</span>";
} else {
  echo '<span class="dashicons dashicons-yes" title="Yes" style="color:green;font-size: 200%"></span>';
  echo "<span style=\"color:blue\">   ".phpversion()."</span>";
}
echo '</td>';
echo '</tr>';

/*======= cURL =======*/
echo '<tr>';
echo '<td>';
echo 'cURL';
echo '</td>';
echo '<td>';
if (in_array  ('curl', $_server_extensions )) {
  echo '<span class="dashicons dashicons-yes" title="Yes" style="color:green;font-size: 200%"></span>';
  echo "<span style=\"color:blue\">   installed</span> on your server";
} else {
  echo '<span class="dashicons dashicons-no-alt" title="No" style="color:#a20404;font-size: 200%"></span>';
  echo "&nbsp;&nbsp;  NOT <span style=\"color:red\">installed</span> on your server";
}
echo '</td>';
echo '</tr>';

/*======= gd =======*/
echo '<tr>';
echo '<td>';
echo 'gd';
echo '</td>';
echo '<td>';
if (in_array  ('gd', $_server_extensions )) {
  echo '<span class="dashicons dashicons-yes" title="Yes" style="color:green;font-size: 200%"></span>';
  echo "<span style=\"color:blue\">   installed</span> on your server";
} else {
  echo '<span class="dashicons dashicons-no-alt" title="No" style="color:#a20404;font-size: 200%"></span>';
  echo "&nbsp;&nbsp;  NOT <span style=\"color:red\">installed</span> on your server";
}
echo '</td>';
echo '</tr>';

/*======= mbstring =======*/
echo '<tr>';
echo '<td>';
echo 'mbstring';
echo '</td>';
echo '<td>';
if (in_array  ('mbstring', $_server_extensions )) {
  echo '<span class="dashicons dashicons-yes" title="Yes" style="color:green;font-size: 200%"></span>';
  echo "<span style=\"color:blue\">   installed</span> on your server";
} else {
  echo '<span class="dashicons dashicons-no-alt" title="No" style="color:#a20404;font-size: 200%"></span>';
  echo "&nbsp;&nbsp;  NOT <span style=\"color:red\">installed</span> on your server";
}
echo '</td>';
echo '</tr>';

/*======= exif =======*/
echo '<tr>';
echo '<td>';
echo 'exif';
echo '</td>';
echo '<td>';
if (in_array  ('exif', $_server_extensions )) {
  echo '<span class="dashicons dashicons-yes" title="Yes" style="color:green;font-size: 200%"></span>';
  echo "<span style=\"color:blue\">   installed</span> on your server";
} else {
  echo '<span class="dashicons dashicons-no-alt" title="No" style="color:#a20404;font-size: 200%"></span>';
  echo "&nbsp;&nbsp;  NOT <span style=\"color:red\">installed</span> on your server";
}
echo '</td>';
echo '</tr>';

/*======= ffmpeg =======*/
echo '<tr>';
echo '<td>';
echo 'ffmpeg';
echo '</td>';
echo '<td>';
if (in_array  ('ffmpeg', $_server_extensions )) {
  echo '<span class="dashicons dashicons-yes" title="Yes" style="color:green;font-size: 200%"></span>';
  echo "<span style=\"color:blue\">   installed</span> on your server";
} else {
  echo '<span class="dashicons dashicons-format-video" title="No" style="color:#a20404;font-size: 200%"></span>';
  echo "&nbsp;&nbsp;  NOT <span style=\"color:red\">installed</span> on your server - But that will not prevent from uploading photo to Instagram, it is necessary only to post video";
}
echo '</td>';
echo '</tr>';


/*======= ffprobe =======*/
echo '<tr>';
echo '<td>';
echo 'ffprobe';
echo '</td>';
echo '<td>';
if (in_array  ('ffprobe', $_server_extensions )) {
  echo '<span class="dashicons dashicons-yes" title="Yes" style="color:green;font-size: 200%"></span>';
  echo "<span style=\"color:blue\">   installed</span> on your server";
} else {
  echo '<span class="dashicons dashicons-format-video" title="No" style="color:#a20404;font-size: 200%"></span>';
  echo "&nbsp;&nbsp;  NOT <span style=\"color:red\">installed</span> on your server - But that will not prevent from uploading photo to Instagram, it is necessary only to post video";
}
echo '</td>';
echo '</tr>';


/*=========*/
echo '</tbody>';
echo '</table>';

?>