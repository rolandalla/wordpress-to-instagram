<?php

/*  

Plugin Name: Auto-Post To Instagram

Plugin URI: http://h-tech.al

Description: Plugin for automatic posting Wordpress image to Instagram

Author: Roland Alla

Version: 1.4.1

Author URI: http://h-tech.al

*/

define( 'WP2INSTAGRAM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

define( 'WP2INSTAGRAM_PLUGIN_SETTINGS', 'wp2instagram' );

define( 'WP2INSTAGRAM_PLUGIN_BASE', plugin_basename( __FILE__ ) );

define( 'WP2INSTAGRAM_RETURN_URI', strtolower( site_url( '/' ) . 'wp-admin/options-general.php?page=' . WP2INSTAGRAM_PLUGIN_SETTINGS ) );





include WP2INSTAGRAM_PLUGIN_PATH .'autoload.php';

require WP2INSTAGRAM_PLUGIN_PATH .'mgp25/instagram-php/src/Instagram.php';



if ( ! class_exists( "wp2instagram" ) ) {





    class wp2instagram {



        /* Plugin loading method */

        function load_plugin() {

            //settings menu

            add_action( 'admin_menu', get_class() . '::register_settings_menu' );

            

            $wp2instagram_post_types = get_option('wp2instagram_post_types');

            if($wp2instagram_post_types == false or $wp2instagram_post_types == 'null') {

                $wp2instagram_post_types = array();

            } else

                $wp2instagram_post_types = json_decode($wp2instagram_post_types);

            foreach($wp2instagram_post_types as $post_type) {

                add_action( 'publish_'.$post_type, array( $this, 'post_published_instagram' ), 10, 2 );

            }

            add_filter( 'plugin_action_links', array( $this, 'register_settings_link'), 10, 2 );

            register_activation_hook( __FILE__, array( $this, 'wp2instagram_activate' ) );

        

        }

        

        function wp2instagram_activate() {

            $wp2instagram_post_types = json_encode(array('post'));

            update_option('wp2instagram_post_types', $wp2instagram_post_types);  

        }



        /* Add menu item for plugin to Settings Menu */

        public static function register_settings_menu() {

            add_options_page( 'wp2instagram', 'wp2instagram', 'manage_options', WP2INSTAGRAM_PLUGIN_SETTINGS, get_class() . '::settings_page' );

        }

        

        public function register_settings_link( $links, $file ) {



            static $this_plugin;

            if ( ! $this_plugin ) {

                $this_plugin = WP2INSTAGRAM_PLUGIN_BASE;

            }



            if ( $file == $this_plugin ) {

                $settings_link = '<a href="options-general.php?page=' . WP2INSTAGRAM_PLUGIN_SETTINGS . '">' . __( 'Settings', WP2INSTAGRAM_PLUGIN_SETTINGS ) . '</a>';

                array_unshift( $links, $settings_link );

            }



            return $links;



        }

        

        public static function settings_page(){

            ?>

            <div class="wrap">

            <div class="h2_left">

                <h1 class="instagrate-icon dashicons-before dashicons-camera">WP 2 INSTAGRAM</h1>

            </div>

			<h2><span>Settings</span></h2>

			<hr />

			<br />

            <h3>Instagram account</h3>

            <?php

            if(isset($_REQUEST['submit_instagram_account'])) {

                if(isset($_REQUEST['wp2instagram_username']) && isset($_REQUEST['wp2instagram_password']) && $_REQUEST['wp2instagram_username'] != "" && $_REQUEST['wp2instagram_password'] != "") {

                    update_option("wp2instagram_username", $_REQUEST['wp2instagram_username']);

                    update_option("wp2instagram_password", $_REQUEST['wp2instagram_password']);

                    echo "<p><b>Save Success!</b></p>";

                }

                else {

                    echo "<p><b>Please fill both fields!</b></p>";

                }

            }

                

				

            $username = get_option("wp2instagram_username", "");

            $password = get_option("wp2instagram_password", "");

            echo "<b>You have to set username and password for your instagram account.</b>";

            ?>

			

            <form method="post">

                Username: <input type="text" name="wp2instagram_username" value="<?php echo $username; ?>" /> <br />

                Password: <input type="password" name="wp2instagram_password" value="<?php echo $password; ?>" /> <br />

                <input type="submit" name="submit_instagram_account" value="Submit" />

            </form>

            </br>

			<h3>Advanced settings</h3>

            <hr />

            

             v

            <?php

            if(isset($_REQUEST['submit_post_types'])) {





				$wp2instagram_additional_hashtags = $_REQUEST['wp2instagram_additional_hashtags'];	

				$wp2instagram_additional_hashtags = json_encode($wp2instagram_additional_hashtags);

				update_option('wp2instagram_additional_hashtags', $wp2instagram_additional_hashtags);  





				$wp2instagram_max_number_of_hashtags = $_REQUEST['wp2instagram_max_number_of_hashtags'];	

				$wp2instagram_max_number_of_hashtags = json_encode($wp2instagram_max_number_of_hashtags);

				update_option('wp2instagram_max_number_of_hashtags', $wp2instagram_max_number_of_hashtags);  





				$wp2instagram_use_tags_of_post = $_REQUEST['wp2instagram_use_tags_of_post'];	

				$wp2instagram_use_tags_of_post = json_encode($wp2instagram_use_tags_of_post);

				update_option('wp2instagram_use_tags_of_post', $wp2instagram_use_tags_of_post);  

				

				$wp2instagram_header_caption = $_REQUEST['wp2instagram_header_caption'];	

				$wp2instagram_header_caption = json_encode($wp2instagram_header_caption);

				update_option('wp2instagram_header_caption', $wp2instagram_header_caption);

				

				

				

				$wp2instagram_footer_caption = $_REQUEST['wp2instagram_footer_caption'];	

				$wp2instagram_footer_caption = json_encode($wp2instagram_footer_caption);

				update_option('wp2instagram_footer_caption', $wp2instagram_footer_caption);

				

				

				

				

				

				

                $wp2instagram_post_types = $_REQUEST['wp2instagram_post_types'];

                $wp2instagram_post_types = json_encode($wp2instagram_post_types);

                update_option('wp2instagram_post_types', $wp2instagram_post_types);  

				

                echo "<p><b>Post types save!</b></p>";  

            }

            

            $wp2instagram_additional_hashtags = get_option('wp2instagram_additional_hashtags');

			if($wp2instagram_additional_hashtags == false || $wp2instagram_additional_hashtags == 'null') {

                $wp2instagram_additional_hashtags = '';

            } else

                $wp2instagram_additional_hashtags = json_decode($wp2instagram_additional_hashtags);

			

			

			$wp2instagram_max_number_of_hashtags = get_option('wp2instagram_max_number_of_hashtags');

			if($wp2instagram_max_number_of_hashtags == false || $wp2instagram_max_number_of_hashtags == 'null') {

                $wp2instagram_max_number_of_hashtags = '';

            } else

                $wp2instagram_max_number_of_hashtags = json_decode($wp2instagram_max_number_of_hashtags);

			

			

			$wp2instagram_use_tags_of_post = get_option('wp2instagram_use_tags_of_post');

			if($wp2instagram_use_tags_of_post == false || $wp2instagram_use_tags_of_post == 'null') {

                $wp2instagram_use_tags_of_post = '';

            } else

                $wp2instagram_use_tags_of_post = json_decode($wp2instagram_use_tags_of_post);

			

			

			

			$wp2instagram_header_caption = get_option('wp2instagram_header_caption');

			if($wp2instagram_header_caption == false || $wp2instagram_header_caption == 'null') {

                $wp2instagram_header_caption = '';

            } else

                $wp2instagram_header_caption = json_decode($wp2instagram_header_caption);

			

			

			$wp2instagram_footer_caption = get_option('wp2instagram_footer_caption');

			if($wp2instagram_footer_caption == false || $wp2instagram_footer_caption == 'null') {

                $wp2instagram_footer_caption = '';

            } else

                $wp2instagram_footer_caption = json_decode($wp2instagram_footer_caption);

			

			

			$wp2instagram_post_types = get_option('wp2instagram_post_types');

            if($wp2instagram_post_types == false || $wp2instagram_post_types == 'null') {

                $wp2instagram_post_types = array();

            } else

                $wp2instagram_post_types = json_decode($wp2instagram_post_types);

            

            $args = array(

               '_builtin' => false

            );

            

            $output = 'names'; // names or objects, note names is the default

            $operator = 'and'; // 'and' or 'or'

            

            $post_types = get_post_types( $args, $output, $operator );

            

            $pts = array();

            $pts[] = 'post';

            foreach ( $post_types  as $post_type ) {

                $pts[] = $post_type;

            }

            ?>

            <b>Set the post types these will auto post image to Instagram.</b>

            <form method="post">

            <?php

            foreach($pts as $post_type) {

                ?>

                <input type="checkbox" name="wp2instagram_post_types[]" 

                    <?php

                    if(in_array($post_type, $wp2instagram_post_types)) {

                        echo "checked='checked'";

                    }

                    ?>

                value="<?php echo $post_type; ?>" /> <?php echo $post_type; ?> <br />

                <?php

            }

            ?>

			

			<br/><br/><br/>

			<table cellpadding="2">

			<tr>

				<td align="top">Use additional hashtags</td>

				<td align="top"><textarea name="wp2instagram_additional_hashtags" rows="5" cols="50"><?php echo $wp2instagram_additional_hashtags;?></textarea></td>

				<td>If you want to add some additional hashtags then put them here, separated by a comma. Do not add #, it will be converted automatically<br/>

				</td>

			</tr>

			

			<tr>

				<td align="top">Maximum number of hashtags</td>

				<td align="top"><input type="text" name="wp2instagram_max_number_of_hashtags" size="2" value="<?php echo $wp2instagram_max_number_of_hashtags; ?>"></td>

				<td>Beware not to use more than 30 tags.<br/><a href='https://www.quora.com/What-is-the-maximum-number-of-hashtags-you-can-insert-in-a-comment-on-an-Instagram-photo' target="_blank">https://www.quora.com/What-is-the-maximum-number-of-hashtags-you-can-insert-in-a-comment-on-an-Instagram-photo</a><br/>

				</td>

			</tr>

			

			<tr>

				<td>Also use tags of post as hashtags</td>

				<td><input type="checkbox" <?php if ($wp2instagram_use_tags_of_post=='Y') {echo 'checked="checked"';} ?>value="Y" name="wp2instagram_use_tags_of_post"></td>

				<td>Check the box if you want to use it.

			</td>

			

			<tr>

				<td align="top">Add this header to caption</td>

				<td align="top"><input type="text" name="wp2instagram_header_caption" size="100" value="<?php echo $wp2instagram_header_caption; ?>"></td>

				<td>This text will be placed after Title and before hashgtags<br/>

				</td>

			</tr>

			

			<tr>

				<td align="top">Add this footer to caption</td>

				<td align="top"><input type="text" name="wp2instagram_footer_caption" size="100" value="<?php echo $wp2instagram_footer_caption; ?>"></td>

				<td>This text will be placed after the hashgtags<br/>

				</td>

			</tr>

	</tr>

			

			</table>

			

			

			

			

            <input type="submit" name="submit_post_types" value="Save" />

            </form>

			<br/>

			<hr />

             <h3> Developer Section</h3>



             <a href="https://www.paypal.me/ROLANDALLA/"> <img src="https://camo.githubusercontent.com/ea8c6da768f69e6edfef4f75600e61a32282cf65/687474703a2f2f696d6775722e636f6d2f5753565a5354572e706e67" alt="send a donation"> </a>

            <h2>or </h2>

             <a href="http://www.rolandalla.com/contact/"> <img src="https://www.seoclerk.com/files/user/images/hire-me2(5).png" alt="hire me"></a>

            </div>

            <?php

        }

        

        function post_published_instagram( $ID, $post ) {

            if ( has_post_thumbnail() ) {

            $username = get_option("wp2instagram_username", "");

            $password = get_option("wp2instagram_password", "");

            if($username == "" || $password == "") {

                return;

            } else {

                if ($username!="") {

                    

                     if ( !get_post_meta( $ID, 'firstpublish', $single = true ) ) {

                        $feature_image_id = get_post_thumbnail_id($ID);

                        $feature_image_meta = wp_get_attachment_image_src($feature_image_id, '32');

                        $photo = get_attached_file( $feature_image_id ); // Full path

                        

                        $action_delete = FALSE;

                        list($originalWidth, $originalHeight) = getimagesize($photo);

                        $ratio = $originalWidth / $originalHeight;

                        if($ratio < 0.8) {

                            $cropH = $originalHeight;

                            $cropW = $originalHeight * 0.8 + 2;

                            $X = ($cropW - $originalWidth) / 2;

                        

                            $origimg = imagecreatefromjpeg($photo);

                            $cropimg = imagecreatetruecolor($cropW,$cropH);

                            $white = imagecolorallocate($cropimg, 255, 255, 255);

                            imagefill($cropimg, 0, 0, $white);

                        

                            // Crop

                            imagecopyresized($cropimg, $origimg, $X, 0, 0, 0, $originalWidth, $originalHeight, $originalWidth, $originalHeight);

                            imagejpeg($cropimg, WP2INSTAGRAM_PLUGIN_PATH . 'temp.jpg');

                            $photo = WP2INSTAGRAM_PLUGIN_PATH . 'temp.jpg';

                            $action_delete = TRUE;

                        }

                        $debug = false;

                        

                         $caption = get_the_title($ID);     // caption

                         //////////////////////

                         $caption =html_entity_decode($caption, ENT_QUOTES, "UTF-8");

						 

						//////////////////////

						// ADD HASHTAGS

						// Beware not to use more than 30 tags

						// ( https://www.quora.com/What-is-the-maximum-number-of-hashtags-you-can-insert-in-a-comment-on-an-Instagram-photo )

						//////////////////////



						$tags = wp_get_post_tags($ID);

						$tag_list = "";

						$number_of_hashtags=0;

						//$max_number_of_hashtags=28;

						$max_number_of_hashtags = get_option("wp2instagram_max_number_of_hashtags", "");

						if($max_number_of_hashtags == false || $max_number_of_hashtags == 'null') {

							$max_number_of_hashtags = '';

						} else

							$max_number_of_hashtags = json_decode($max_number_of_hashtags);

						

						

						if (empty($max_number_of_hashtags)) $max_number_of_hashtags=28;

						

						$additional_tag_list="";

						$bad_char   = array(" ");

						

						

						

						



						$use_tags_of_post = get_option("wp2instagram_use_tags_of_post", "");

						if($use_tags_of_post == false || $use_tags_of_post == 'null') {

							$use_tags_of_post = '';

						} else

							$use_tags_of_post = json_decode($use_tags_of_post);

						

						

						

						if (empty($use_tags_of_post)) $use_tags_of_post='Y';

						if ($use_tags_of_post!='Y') $use_tags_of_post='N';

						

						

						

						// Get header & footer

						

						

						$header_caption = get_option("wp2instagram_header_caption", "");

						if($header_caption == false || $header_caption == 'null') {

							$header_caption = '';

						} else

							$header_caption = json_decode($header_caption);

						

						

						if (empty($header_caption)) $header_caption ='';

						

						$footer_caption = get_option("wp2instagram_footer_caption", "");

						if($footer_caption == false || $footer_caption == 'null') {

							$footer_caption = '';

						} else

							$footer_caption = json_decode($footer_caption);

						

						if (empty($footer_caption)) $footer_caption ='';

						

						

						

						

						

						// First process additional tags

						

						$additional_hashtags = get_option("wp2instagram_additional_hashtags", "");

						if($additional_hashtags == false || $additional_hashtags == 'null') {

							$additional_hashtags = '';

						} else

							$additional_hashtags = json_decode($additional_hashtags);

						

						

						if (empty($additional_hashtags)) 

							$additional_hashtags_list=array('');

						else

							$additional_hashtags_list=explode(',',$additional_hashtags);

						

						

						foreach($additional_hashtags_list as $tag){

							$instagram_tag = str_replace($bad_char, '', $tag);



							if ($number_of_hashtags <= $max_number_of_hashtags ) {

								$tag_list .= "#" . $instagram_tag . " ";

								$number_of_hashtags++;

							}

						}

						

						

						// Now process tags associated to post

						

						

						if ($use_tags_of_post=='Y') {

						

							foreach($tags as $tag){

								$instagram_tag = str_replace($bad_char, '', $tag->name);



								if ($number_of_hashtags <= $max_number_of_hashtags ) {

									$tag_list .= "#" . $instagram_tag . " ";

									$number_of_hashtags++;

								}

							}



						}

						

						

						if (strlen($header_caption) > 0) {

							$caption .= " - ".$header_caption;

						}

						

						if (strlen($tag_list) > 0) {

							$caption .= " - ".strtolower($tag_list);

						}



						if (strlen($footer_caption) > 0) {

							$caption .= " - ".$footer_caption;

						}



						

						

						//if (strlen($additional_tag_list) > 0) {

						//	$caption .= " ".$additional_tag_list;

						//}



						//////////////////////

                        $caption =html_entity_decode($caption, ENT_QUOTES, "UTF-8");

						

                        $i = new \InstagramAPI\Instagram($username, $password, $debug);

                        

                        try {

                            $i->login();

                        } catch (Exception $e) {

                            $e->getMessage();

                            exit();

                        }

                        

                        try {

                            $i->uploadPhoto($photo, $caption);

                        } catch (Exception $e) {

                            echo $e->getMessage();

                        }

                        if($action_delete == TRUE) {

                            unlink($photo);

                        } 

                        update_post_meta( $ID, 'firstpublish', true );

                    }

                } else {

                    return;                    

                }  

            }

        }

    }



}

$a = new wp2instagram();

$a->load_plugin();

}