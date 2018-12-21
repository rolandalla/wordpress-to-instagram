=== Auto-Post Wordpress To Instagram ===
Contributors: rolandalla91, dufour_l
Donate link: https://www.paypal.me/ROLANDALLA/
Tags: autopost, wordpress, instagram
Requires at least: 3.5
Tested up to: 5.0.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Post your images to Instagram by wordpress pannel

== Description ==

Auto-Post To Instagram will send automatic featured image + post title as a post into your instagram account .
This plugin is totally free , and will allow you to choose the type of post you want to auto send  into your instagram account.

***Please do not leave bad rating**, if you do not understand the code, This plugin is using an external API, not the offical Instagram API ,
 that is why some of you get a warning from instagram.
 
 ----Instagram API do not allow to send images using the API---
 You are free to use this plugin as you want. :) for FREE



== Installation ==

1. Copy the `plugin` folder into your `wp-content/plugins` folder
2. Activate the Auto-Post To Instagram plugin via the plugins admin page
3. Add your username +password into, wp2instagram page , located under Settings
4. Instagram can notice as a note secure login in you account , this happend only because the plugin is unisng an external API,not the official


== Screenshots ==

1. This will be the dashboard view, When you can add your instgram username,and password :
2. Adding Debug Option:


== Changelog ==

= 1.0 =
* Initial Release.

= 1.1 =
* Fix some bugs.

= 1.2 =
* Fix some bugs.
* Add Notice 

= 1.3 =
* Fix Html Tags.

= 1.4.1 =

New features:

* New plugin advandced settings page
* You can now customize the usage of hashtags in your caption 
* Set the maximum bumber of hashtags ( Recommanded below 30 , please check at https://www.quora.com/What-is-the-maximum-number-of-hashtags-you-can-insert-in-a-comment-on-an-Instagram-photo )
* Use of additional hashtags
* Use or not the original tags of you post as hashtags
* Use a header in caption ( This text will be placed after Title and before hashgtags )
* Use a footer in caption ( This text will be placed after the hashgtags )

* Tested in Wordpress 4.9.5

= 1.4.2 =

Bug corrected :

* Now properly handle the number of maximum hashtags

New features:

* Use or not the original categories name of your post as hashtags 
* Convert or not accents in your hashtags
* Remove or not underscore (_) or hyphen (-) in your hashtags

Please note that tags are processed before category, so if you have reach the maximumn number of hashtags before the processing of categories, you will not see them on Instagram

* Tested in Wordpress 4.9.5

= 1.4.3 =

Bug corrected :

* None

New features:

* Include Author's name in header caption	
* Add this author label to caption

* Tested in Wordpress 4.9.8

= 1.4.4 =

Bug corrected :

* Modified usage of mpg25/login function, enabling again the post to Instagram

New features:

* Debug mode
* New feature to disable post to Instagram if you want
* Include a new box on post, giving the possibility to post to Instagram or not per post

* Tested in Wordpress 4.9.8

= 1.4.5 =

Bug corrected :

* Minor correction to new box on post to Instagram / Also take care about possible auto scheduled post by another plugin 

New features:

* None

* Tested in Wordpress 4.9.8

= 1.4.6 =

Bug corrected :

* None 

New features:

* Better handling of width and height limit of Instagram, either if your picture is too big ( above 1080px )  or too small ( below 320px )
* Resize a photo in panorama mode if ratio above 16:9, in this case is reduce the size of the picture, and add a white band above and below the picture
* New fields ( date of post to instagram, upload info, link to your instagram page, in the instagram info box on post page
* if debug mode activated, in the info box, you will have the full response of instagram, with the message why it was not accepted by instagram.

* Tested in Wordpress 5.0.1