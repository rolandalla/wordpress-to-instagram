<?php
/*  
Plugin Name: Auto-Post To Instagram
Plugin URI: http://h-tech.al
Description: Plugin for automatic posting Wordpress image to Instagram
Author: Roland
Version: 1.1
Author URI: /h-tech.al
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
            
            <hr />
            
            <h3>Post types config</h3>
			<?php
            if(isset($_REQUEST['submit_post_types'])) {
                $wp2instagram_post_types = $_REQUEST['wp2instagram_post_types'];
                $wp2instagram_post_types = json_encode($wp2instagram_post_types);
                update_option('wp2instagram_post_types', $wp2instagram_post_types);  
                echo "<p><b>Post types save!</b></p>";  
            }
            
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
            <input type="submit" name="submit_post_types" value="Save" />
            </form>
            </div>
            <?php
        }
        
        function post_published_instagram( $ID, $post ) {
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