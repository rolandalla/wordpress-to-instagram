<?php
/*
Plugin Name: Auto Post WP to Photo Social Network
Plugin URI: https://wordpress.org/plugins/auto-post-to-instagram/
Description: Plugin for automatic posting Wordpress images 
Author: Roland & Informatica Duran
Version: 1.5.5
Author URI: https://wordpress.org/plugins/auto-post-to-instagram/
*/

define('WP2INSTAGRAM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WP2INSTAGRAM_PLUGIN_SETTINGS', 'wp2instagram');
define('WP2INSTAGRAM_PLUGIN_BASE', plugin_basename(__FILE__));
define('WP2INSTAGRAM_RETURN_URI', strtolower(site_url('/').'wp-admin/options-general.php?page='.WP2INSTAGRAM_PLUGIN_SETTINGS));
define('WP2INSTAGRAM_VERSION', '1.5.3');

include WP2INSTAGRAM_PLUGIN_PATH.'vendor/autoload.php';
require WP2INSTAGRAM_PLUGIN_PATH.'vendor/mgp25/instagram-php/src/Instagram.php';

if (!class_exists('wp2instagram')) {
    class wp2instagram
    {
        public function wp2instagram_plugin_meta_links($wp2instagram_meta_links, $wp2instagram_file)
        {
            if ($wp2instagram_file == WP2INSTAGRAM_PLUGIN_BASE) {
                $wp2instagram_meta_links[] = '<a href="https://wordpress.org/support/plugin/auto-post-to-instagram">Support forum</a>';
                $wp2instagram_meta_links[] = '<a href="http://wordpress.org/extend/plugins/auto-post-to-instagram/faq">FAQ</a>';
            }

            return $wp2instagram_meta_links;
        }


		/**
		 * wp2instagram_email_headers.
		 *
		 * @access public
		 */
		public function wp2instagram_email_headers(){
			$admin_email = get_option( 'admin_email' );
			if ( empty( $admin_email ) ) {
				$admin_email = 'support@' . $_SERVER['SERVER_NAME'];
			}

			$from_name = get_option( 'blogname' );

			$header = "From: \"{$from_name}\" <{$admin_email}>\n";
			$header.= "MIME-Version: 1.0\r\n";
			$header.= "Content-Type: text/html; charset=\"" . get_option( 'blog_charset' ) . "\"\n";
			$header.= "X-Priority: 1\r\n";

			return $header;
		}

		/**
		 * wp2instagram_send_email.
		 *
		 * @access public
		 */
		public function wp2instagram_send_email($email_subject,$message_to_send,$extra_email_addresses){

				$admin_email = get_option( 'admin_email' );
				if ( empty( $admin_email ) ) {
					$admin_email = 'postmaster@' . $_SERVER['SERVER_NAME'];
				}

				$current_user = wp_get_current_user();
				$user_email = $current_user->user_email;
				$email_recipients=$user_email.','.$admin_email;
				if ($extra_email_addresses != '') {
					$email_recipients.=','.$extra_email_addresses;
				}

				$email_message = $message_to_send . "\r\n";

				wp_mail($email_recipients, $email_subject, $email_message, $this->wp2instagram_email_headers());

		}


		/**
         * wp2instagram_IG_status.
         *
         */

		static function wp2instagram_IG_status($post_id) {

			$IG_status='';

			$green_arrow='<span class="dashicons dashicons-yes" title="Yes" style="color:green;font-size: 200%"></span>';
			$blue_sticky='<span class="dashicons dashicons-sticky" title="Yes" style="color:blue;font-size: 200%"></span>';
			$red_cross='<span class="dashicons dashicons-no-alt" title="No" style="color:#a20404;font-size: 200%"></span>';


			if (!get_post_meta($post_id, 'firstpublish', $single = true)) {
                $status = 'Never published'.' '.$blue_sticky;
                $status_post_date = 'Not yet';
                $status_upload = 'Not uploaded'.' '.$red_cross;
                $status_response = '';
            } else {
                $status = 'Already published'.' '.$green_arrow;
            }

			$instagram_post_date = get_post_meta($post_id, 'instagram_post_date', $single = true);

            if ($instagram_post_date != '') {
                $status_post_date = $instagram_post_date;
            } else {
                $status_post_date = 'Unknown';
            }

			$instagram_post_upload_status = get_post_meta($post_id, 'instagram_post_upload_status', $single = true);

            if ($instagram_post_upload_status != '') {
                $status_upload = json_decode($instagram_post_upload_status);

				if ($status_upload == 'Uploaded to Instagram') {
					$status_upload = $status_upload.' '.$green_arrow;
				} else {
					$status_upload = $status_upload.' '.$red_cross;
				}

            } else {
                $status_upload = '';
            }


			$IG_status='Instagram Status : '.$status.' <br><br>';
			$IG_status.='Posted : '.$status_post_date.' <br><br>';
			$IG_status.='Upload info : '.$status_upload.' <br><br>';

			return $IG_status;
		}



		/**
         * wp2instagram_column_head.
         *
         * @return defaults
         */

		public function wp2instagram_column_head($defaults) {

			$defaults['wp2instagram'] = 'Instagram Status';
			return $defaults;
		}

		public static function wp2instagram_handle_column_data( $column_name, $post_id ) {
			$post = get_post( $post_id );

			if ( 'wp2instagram' == $column_name ) {

				$display_IG_status='';

				$display_IG_status = self::wp2instagram_IG_status( $post_id );

				echo $display_IG_status;
			}
			return;
		}


		/**
         * wp2instagram_get_option
         *
		 * @access public
         * @return opt_mode
         */
        public static function wp2instagram_get_option($option_name,$option_default_when_not_set, $option_is_of_type_yes_or_no=true) {
		    $opt_mode = get_option($option_name, '');
            if ($opt_mode == false || $opt_mode == 'null') {
                $opt_mode = '';
            } else {
                $opt_mode = json_decode($opt_mode);
            }

            if (empty($opt_mode)) {
                $opt_mode = $option_default_when_not_set;
            }

			if ( $option_is_of_type_yes_or_no == true ) {

				if ($opt_mode != 'Y') {
					$opt_mode = 'N';
				}

			}

			return $opt_mode;
		}


        /**
         * wp2instagram_add_metabox.
         *
         * @return void
         */
        public function wp2instagram_add_metabox()
        {
            add_meta_box(
            'wp2instagram_metabox',
            'Auto Post to Instagram',
            [$this, 'wp2instagram_metabox'],
            ['post', 'page'],
            'side',
            'high'
        );
        }

        /**
         * wp2instagram_metabox.
         *
         * @param WP_Post $post the current post.
         *
         * @return void
         */
        public function wp2instagram_metabox($post)
        {



			$dontautopublish = self::wp2instagram_get_option('wp2instagram_dontautopublish','N');
			$debug_mode = self::wp2instagram_get_option('wp2instagram_debug','N');

            $username = get_option('wp2instagram_username', '');
            $instagram_account = 'https://instagram.com/'.$username;

			$display_IG_status='';
			$display_IG_status = self::wp2instagram_IG_status( $post->ID );

			$status_response = '';

            $instagram_response_status = get_post_meta($post->ID, 'instagram_response_status', $single = true);
            if ($instagram_response_status != '') {
                $status_response = $instagram_response_status;
            } else {
                $status_response = '';
            }

            $instagram_fullname_status = get_post_meta($post->ID, 'instagram_fullname_status', $single = true);
            if ($instagram_fullname_status != '') {
                $status_fullname = $instagram_fullname_status;
            } else {
                $status_fullname = '';
            }



			$instagram_post_link = get_post_meta($post->ID, 'instagram_post_link', $single = true);
            if ($instagram_post_link != '') {
                $status_instagram_post_link = str_replace(array("'", '"'), " ", $instagram_post_link);
            } else {
                $status_instagram_post_link = '';
            }



			?>
		<div style="margin: 20px 0;">
		<input type="hidden" name="wp2instagram_in_instagram_box[]" value="Y">
		<input type="checkbox" <?php if ($dontautopublish == 'N') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_post_this_article_to_instagram">Post to Instagram<br><br>
		<?

		echo $display_IG_status;

		?>

		Instagram link to your photo : <a href="<?php echo htmlentities($status_instagram_post_link, ENT_QUOTES); ?>"><?php echo htmlentities($status_instagram_post_link, ENT_QUOTES); ?></a><br>

		<?php
        if ($debug_mode == 'Y') {
            echo '<br>Instagram response : ';
            print_r($status_response);
            echo "<br><br>\n";
        } ?>

		Your Account <?php echo $status_fullname; ?> : <a href="<?php echo $instagram_account; ?>"><?php echo $instagram_account; ?></a>
		</div>


		<?php
        }





        /* Plugin loading method */
        public function load_plugin()
        {

            //metabox
            add_action('add_meta_boxes', [$this, 'wp2instagram_add_metabox']);

			//add column for post
			//add_filter('manage_posts_columns', 'wp2instagram_column_head');
			add_filter( 'manage_posts_columns',       array( __CLASS__, 'wp2instagram_column_head' ) );
			add_action( 'manage_posts_custom_column', array( __CLASS__, 'wp2instagram_handle_column_data' ), 10, 2 );



            //settings menu
            add_action('admin_menu', get_class().'::register_settings_menu');

            $wp2instagram_post_types = get_option('wp2instagram_post_types');
            if ($wp2instagram_post_types == false or $wp2instagram_post_types == 'null') {
                $wp2instagram_post_types = [];
            } else {
                $wp2instagram_post_types = json_decode($wp2instagram_post_types);
            }

            foreach ($wp2instagram_post_types as $post_type) {
                add_action('publish_'.$post_type, [$this, 'post_published_instagram'], 10, 2);
            }

            add_filter('plugin_action_links', [$this, 'register_settings_link'], 10, 2);
            add_filter('plugin_row_meta', [$this, 'wp2instagram_plugin_meta_links'], 10, 2);
            register_activation_hook(__FILE__, [$this, 'wp2instagram_activate']);
        }

        public function wp2instagram_activate()
        {
            $wp2instagram_post_types = json_encode(['post']);
            update_option('wp2instagram_post_types', $wp2instagram_post_types);
        }

        /* Add menu item for plugin to Settings Menu */
        public static function register_settings_menu()
        {
            add_options_page('wp2instagram', 'wp2instagram', 'manage_options', WP2INSTAGRAM_PLUGIN_SETTINGS, get_class().'::settings_page');
        }

        public function register_settings_link($links, $file)
        {
            static $this_plugin;
            if (!$this_plugin) {
                $this_plugin = WP2INSTAGRAM_PLUGIN_BASE;
            }

            if ($file == $this_plugin) {
                $settings_link = '<a href="options-general.php?page='.WP2INSTAGRAM_PLUGIN_SETTINGS.'">'.__('Settings', WP2INSTAGRAM_PLUGIN_SETTINGS).'</a>';
                array_unshift($links, $settings_link);
            }

            return $links;
        }

        public static function settings_page()
        {
            ?>

            <div class="wrap">
            <div class="h2_left">
                <h1 class="instagrate-icon dashicons-before dashicons-camera">WP 2 INSTAGRAM&nbsp;<?php echo WP2INSTAGRAM_VERSION; ?></h1>
            </div>

			<h2><span>Settings</span></h2>
			<hr />
			<br />
            <h3>Instagram account</h3>
            <?php

            if (isset($_REQUEST['submit_instagram_account'])) {
                if (isset($_REQUEST['wp2instagram_username']) && isset($_REQUEST['wp2instagram_password']) && $_REQUEST['wp2instagram_username'] != '' && $_REQUEST['wp2instagram_password'] != '') {
                    update_option('wp2instagram_username', $_REQUEST['wp2instagram_username']);
                    update_option('wp2instagram_password', $_REQUEST['wp2instagram_password']);
                    echo '<p><b>Save Success!</b></p>';
                } else {
                    echo '<p><b>Please fill both fields!</b></p>';
                }
            }

            $username = get_option('wp2instagram_username', '');
            $password = get_option('wp2instagram_password', '');
            echo '<b>You have to set username and password for your instagram account.</b>'; ?>
			<b>If you do not have one, you need to create one first here :
            <a href="https://www.instagram.com/" target="_new" style="font-size:12px">https://www.instagram.com</a></b>



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

            if (isset($_REQUEST['submit_post_types'])) {



                $wp2instagram_use_proxy = isset($_REQUEST['wp2instagram_use_proxy']) ? $_REQUEST['wp2instagram_use_proxy'] : NULL;
                $wp2instagram_use_proxy = json_encode($wp2instagram_use_proxy);
                update_option('wp2instagram_use_proxy', $wp2instagram_use_proxy);


				$wp2instagram_ig_proxy = isset($_REQUEST['wp2instagram_ig_proxy']) ? $_REQUEST['wp2instagram_ig_proxy'] : NULL;
                $wp2instagram_ig_proxy = json_encode($wp2instagram_ig_proxy);
                update_option('wp2instagram_ig_proxy', $wp2instagram_ig_proxy);



				$wp2instagram_additional_hashtags = isset($_REQUEST['wp2instagram_additional_hashtags']) ? $_REQUEST['wp2instagram_additional_hashtags'] : NULL;
                $wp2instagram_additional_hashtags = json_encode($wp2instagram_additional_hashtags);
                update_option('wp2instagram_additional_hashtags', $wp2instagram_additional_hashtags);

                $wp2instagram_max_number_of_hashtags = isset($_REQUEST['wp2instagram_max_number_of_hashtags']) ? $_REQUEST['wp2instagram_max_number_of_hashtags'] : NULL;
                $wp2instagram_max_number_of_hashtags = json_encode($wp2instagram_max_number_of_hashtags);
                update_option('wp2instagram_max_number_of_hashtags', $wp2instagram_max_number_of_hashtags);


                $wp2instagram_use_title_words_of_post = isset($_REQUEST['wp2instagram_use_title_words_of_post']) ? $_REQUEST['wp2instagram_use_title_words_of_post'] : NULL;
                $wp2instagram_use_title_words_of_post = json_encode($wp2instagram_use_title_words_of_post);
                update_option('wp2instagram_use_title_words_of_post', $wp2instagram_use_title_words_of_post);

                $wp2instagram_use_tags_of_post = isset($_REQUEST['wp2instagram_use_tags_of_post']) ? $_REQUEST['wp2instagram_use_tags_of_post'] : NULL;
                $wp2instagram_use_tags_of_post = json_encode($wp2instagram_use_tags_of_post);
                update_option('wp2instagram_use_tags_of_post', $wp2instagram_use_tags_of_post);

                $wp2instagram_use_categories_of_post = isset($_REQUEST['wp2instagram_use_categories_of_post']) ? $_REQUEST['wp2instagram_use_categories_of_post'] : NULL;
                $wp2instagram_use_categories_of_post = json_encode($wp2instagram_use_categories_of_post);
                update_option('wp2instagram_use_categories_of_post', $wp2instagram_use_categories_of_post);


                $wp2instagram_use_of_excerpt = isset($_REQUEST['wp2instagram_use_of_excerpt']) ? $_REQUEST['wp2instagram_use_of_excerpt'] : NULL;
                $wp2instagram_use_of_excerpt = json_encode($wp2instagram_use_of_excerpt);
                update_option('wp2instagram_use_of_excerpt', $wp2instagram_use_of_excerpt);


                $wp2instagram_max_number_of_words_in_excerpt = isset($_REQUEST['wp2instagram_max_number_of_words_in_excerpt']) ? $_REQUEST['wp2instagram_max_number_of_words_in_excerpt'] : NULL;
                $wp2instagram_max_number_of_words_in_excerpt = json_encode($wp2instagram_max_number_of_words_in_excerpt);
                update_option('wp2instagram_max_number_of_words_in_excerpt', $wp2instagram_max_number_of_words_in_excerpt);

                $wp2instagram_replace_accents = isset($_REQUEST['wp2instagram_replace_accents']) ? $_REQUEST['wp2instagram_replace_accents'] : NULL;
                $wp2instagram_replace_accents = json_encode($wp2instagram_replace_accents);
                update_option('wp2instagram_replace_accents', $wp2instagram_replace_accents);

                $wp2instagram_sanitize = isset($_REQUEST['wp2instagram_sanitize']) ? $_REQUEST['wp2instagram_sanitize'] : NULL;
                $wp2instagram_sanitize = json_encode($wp2instagram_sanitize);
                update_option('wp2instagram_sanitize', $wp2instagram_sanitize);

                $wp2instagram_author = isset($_REQUEST['wp2instagram_author']) ? $_REQUEST['wp2instagram_author'] : NULL;
                $wp2instagram_author = json_encode($wp2instagram_author);
                update_option('wp2instagram_author', $wp2instagram_author);

                $wp2instagram_author_caption = isset($_REQUEST['wp2instagram_author_caption']) ? $_REQUEST['wp2instagram_author_caption'] : NULL;
                $wp2instagram_author_caption = json_encode($wp2instagram_author_caption);
                update_option('wp2instagram_author_caption', $wp2instagram_author_caption);

                $wp2instagram_header_caption = isset($_REQUEST['wp2instagram_header_caption']) ? $_REQUEST['wp2instagram_header_caption'] : NULL;
                $wp2instagram_header_caption = json_encode($wp2instagram_header_caption);
                update_option('wp2instagram_header_caption', $wp2instagram_header_caption);

                $wp2instagram_footer_caption = isset($_REQUEST['wp2instagram_footer_caption']) ? $_REQUEST['wp2instagram_footer_caption'] : NULL;
                $wp2instagram_footer_caption = json_encode($wp2instagram_footer_caption);
                update_option('wp2instagram_footer_caption', $wp2instagram_footer_caption);

                $wp2instagram_post_types = isset($_REQUEST['wp2instagram_post_types']) ? $_REQUEST['wp2instagram_post_types'] : NULL;
                $wp2instagram_post_types = json_encode($wp2instagram_post_types);
                update_option('wp2instagram_post_types', $wp2instagram_post_types);

                $wp2instagram_dontautopublish = isset($_REQUEST['wp2instagram_dontautopublish']) ? $_REQUEST['wp2instagram_dontautopublish'] : NULL;
                $wp2instagram_dontautopublish = json_encode($wp2instagram_dontautopublish);
                update_option('wp2instagram_dontautopublish', $wp2instagram_dontautopublish);

                $wp2instagram_bypass_control = isset($_REQUEST['wp2instagram_bypass_control']) ? $_REQUEST['wp2instagram_bypass_control'] : NULL;
                $wp2instagram_bypass_control = json_encode($wp2instagram_bypass_control);
                update_option('wp2instagram_bypass_control', $wp2instagram_bypass_control);

                $wp2instagram_resize_image = isset($_REQUEST['wp2instagram_resize_image']) ? $_REQUEST['wp2instagram_resize_image'] : NULL;
                $wp2instagram_resize_image = json_encode($wp2instagram_resize_image);
                update_option('wp2instagram_resize_image', $wp2instagram_resize_image);

                $wp2instagram_remove_exif_image = isset($_REQUEST['wp2instagram_remove_exif_image']) ? $_REQUEST['wp2instagram_remove_exif_image'] : NULL;
                $wp2instagram_remove_exif_image = json_encode($wp2instagram_remove_exif_image);
                update_option('wp2instagram_remove_exif_image', $wp2instagram_remove_exif_image);

                $wp2instagram_receive_mail_if_ig_post_good = isset($_REQUEST['wp2instagram_receive_mail_if_ig_post_good']) ? $_REQUEST['wp2instagram_receive_mail_if_ig_post_good'] : NULL;
                $wp2instagram_receive_mail_if_ig_post_good = json_encode($wp2instagram_receive_mail_if_ig_post_good);
                update_option('wp2instagram_receive_mail_if_ig_post_good', $wp2instagram_receive_mail_if_ig_post_good);

                $wp2instagram_receive_mail_if_ig_post_bad = isset($_REQUEST['wp2instagram_receive_mail_if_ig_post_bad']) ? $_REQUEST['wp2instagram_receive_mail_if_ig_post_bad'] : NULL;
                $wp2instagram_receive_mail_if_ig_post_bad = json_encode($wp2instagram_receive_mail_if_ig_post_bad);
                update_option('wp2instagram_receive_mail_if_ig_post_bad', $wp2instagram_receive_mail_if_ig_post_bad);

                $wp2instagram_attach_post_link_to_caption = isset($_REQUEST['wp2instagram_attach_post_link_to_caption']) ? $_REQUEST['wp2instagram_attach_post_link_to_caption'] : NULL;
                $wp2instagram_attach_post_link_to_caption = json_encode($wp2instagram_attach_post_link_to_caption);
                update_option('wp2instagram_attach_post_link_to_caption', $wp2instagram_attach_post_link_to_caption);

                $wp2instagram_use_additional_email_addresses = isset($_REQUEST['wp2instagram_use_additional_email_addresses']) ? $_REQUEST['wp2instagram_use_additional_email_addresses'] : NULL;
                $wp2instagram_use_additional_email_addresses = json_encode($wp2instagram_use_additional_email_addresses);
                update_option('wp2instagram_use_additional_email_addresses', $wp2instagram_use_additional_email_addresses);

				$wp2instagram_additional_email_addresses = isset($_REQUEST['wp2instagram_additional_email_addresses']) ? $_REQUEST['wp2instagram_additional_email_addresses'] : NULL;
                $wp2instagram_additional_email_addresses = json_encode($wp2instagram_additional_email_addresses);
                update_option('wp2instagram_additional_email_addresses', $wp2instagram_additional_email_addresses);

                $wp2instagram_debug = isset($_REQUEST['wp2instagram_debug']) ? $_REQUEST['wp2instagram_debug'] : NULL;
                $wp2instagram_debug = json_encode($wp2instagram_debug);
                update_option('wp2instagram_debug', $wp2instagram_debug);

                echo '<p><b>Post types save!</b></p>';
            }

            $wp2instagram_ig_proxy = get_option('wp2instagram_ig_proxy');
            if ($wp2instagram_ig_proxy == false || $wp2instagram_ig_proxy == 'null') {
                $wp2instagram_ig_proxy = '';
            } else {
                $wp2instagram_ig_proxy = json_decode($wp2instagram_ig_proxy);
            }

            $wp2instagram_additional_hashtags = get_option('wp2instagram_additional_hashtags');
            if ($wp2instagram_additional_hashtags == false || $wp2instagram_additional_hashtags == 'null') {
                $wp2instagram_additional_hashtags = '';
            } else {
                $wp2instagram_additional_hashtags = json_decode($wp2instagram_additional_hashtags);
            }




            $wp2instagram_max_number_of_hashtags = get_option('wp2instagram_max_number_of_hashtags');
            if ($wp2instagram_max_number_of_hashtags == false || $wp2instagram_max_number_of_hashtags == 'null') {
                $wp2instagram_max_number_of_hashtags = '';
            } else {
                $wp2instagram_max_number_of_hashtags = json_decode($wp2instagram_max_number_of_hashtags);
            }


			$wp2instagram_use_proxy = self::wp2instagram_get_option('wp2instagram_use_proxy','N');


			$wp2instagram_use_title_words_of_post = self::wp2instagram_get_option('wp2instagram_use_title_words_of_post','N');
			$wp2instagram_use_tags_of_post = self::wp2instagram_get_option('wp2instagram_use_tags_of_post','N');
			$wp2instagram_use_categories_of_post = self::wp2instagram_get_option('wp2instagram_use_categories_of_post','N');
			$wp2instagram_use_of_excerpt = self::wp2instagram_get_option('wp2instagram_use_of_excerpt','N');

            $wp2instagram_max_number_of_words_in_excerpt = get_option('wp2instagram_max_number_of_words_in_excerpt');
            if ($wp2instagram_max_number_of_words_in_excerpt == false || $wp2instagram_max_number_of_words_in_excerpt == 'null') {
                $wp2instagram_max_number_of_words_in_excerpt = '';
            } else {
                $wp2instagram_max_number_of_words_in_excerpt = json_decode($wp2instagram_max_number_of_words_in_excerpt);
            }


			$wp2instagram_replace_accents = self::wp2instagram_get_option('wp2instagram_replace_accents','N');
			$wp2instagram_sanitize = self::wp2instagram_get_option('wp2instagram_sanitize','N');
			$wp2instagram_author = self::wp2instagram_get_option('wp2instagram_author','N');

			$wp2instagram_author_caption = self::wp2instagram_get_option('wp2instagram_author_caption','',false);
			$wp2instagram_header_caption = self::wp2instagram_get_option('wp2instagram_header_caption','',false);
			$wp2instagram_footer_caption = self::wp2instagram_get_option('wp2instagram_footer_caption','',false);


            $wp2instagram_post_types = get_option('wp2instagram_post_types');
            if ($wp2instagram_post_types == false || $wp2instagram_post_types == 'null') {
                $wp2instagram_post_types = [];
            } else {
                $wp2instagram_post_types = json_decode($wp2instagram_post_types);
            }

			$wp2instagram_bypass_control = self::wp2instagram_get_option('wp2instagram_bypass_control','N');
			$wp2instagram_resize_image = self::wp2instagram_get_option('wp2instagram_resize_image','N');

			$wp2instagram_remove_exif_image = self::wp2instagram_get_option('wp2instagram_remove_exif_image','N');

			$wp2instagram_dontautopublish = self::wp2instagram_get_option('wp2instagram_dontautopublish','N');

			$wp2instagram_receive_mail_if_ig_post_good = self::wp2instagram_get_option('wp2instagram_receive_mail_if_ig_post_good','N');
			$wp2instagram_receive_mail_if_ig_post_bad = self::wp2instagram_get_option('wp2instagram_receive_mail_if_ig_post_bad','N');
			$wp2instagram_attach_post_link_to_caption = self::wp2instagram_get_option('wp2instagram_attach_post_link_to_caption','N');
			$wp2instagram_use_additional_email_addresses = self::wp2instagram_get_option('wp2instagram_use_additional_email_addresses','N');
			$wp2instagram_additional_email_addresses = self::wp2instagram_get_option('wp2instagram_additional_email_addresses','',false);

			//Validate email, and remove extra char from email addresses

			$wp2instagram_sanitized_additional_email_addresses ='';
			$number_of_sanitized_email_address=0;

			if (empty($wp2instagram_additional_email_addresses)) {
				$wp2instagram_additional_email_addresses_list = [''];
			} else {
				$wp2instagram_additional_email_addresses_list = explode(',', $wp2instagram_additional_email_addresses);
			}

			foreach ($wp2instagram_additional_email_addresses_list as $unique_email_address) {
				$sanitized_unique_email_address = sanitize_email($unique_email_address);

				if (strlen($sanitized_unique_email_address) > 0 ) {

					if ($number_of_sanitized_email_address == 0 ) {
						$wp2instagram_sanitized_additional_email_addresses = $sanitized_unique_email_address;
					} else {
						$wp2instagram_sanitized_additional_email_addresses .= ','.$sanitized_unique_email_address;
					}

					$number_of_sanitized_email_address++;

				}

			}

			$wp2instagram_additional_email_addresses = $wp2instagram_sanitized_additional_email_addresses;



			$wp2instagram_debug = self::wp2instagram_get_option('wp2instagram_debug','N');

			$current_user = wp_get_current_user();
			$user_email = $current_user->user_email;


            $args = [
               '_builtin' => false,
            ];

            $output = 'names'; // names or objects, note names is the default
            $operator = 'and'; // 'and' or 'or'

            $post_types = get_post_types($args, $output, $operator);

            $pts = [];
            $pts[] = 'post';

            foreach ($post_types  as $post_type) {
                $pts[] = $post_type;
            } ?>
            <b>Set the post types these will auto post image to Instagram.</b>
            <form method="post">
            <?php
            foreach ($pts as $post_type) {
                ?>
                <input type="checkbox" name="wp2instagram_post_types[]"
                    <?php

                    if (in_array($post_type, $wp2instagram_post_types)) {
                        echo "checked='checked'";
                    } ?>

                value="<?php echo $post_type; ?>" /> <?php echo $post_type; ?> <br />

                <?php
            } ?>

			<br/><br/><br/>
			<h3>Proxy settings</h3>
			<hr/>
			
			<table cellpadding="2">
			
			<tr>
				<td>Use a proxy to connect to Instagram</td>
				<td><input type="checkbox" <?php if ($wp2instagram_use_proxy == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_use_proxy"></td>
				<td>Check this box if you want to use proxy to Instagram.
			</td>
			<tr>
				<td align="top">Proxy</td>
				<td align="top"><input type="text" name="wp2instagram_ig_proxy" size="100" placeholder="https://user:pass@proxyserver:port" value="<?php echo $wp2instagram_ig_proxy; ?>"></td>
				<td>Proxy to use - Used only if <i>Use a proxy to connect to Instagram</i> is checked <br/>
				<b>If you do not have one, you may find some ( Free or Premium ) below :</br>
				- Free Proxies : <a href="https://free-proxy-list.net/" target="_new" style="font-size:12px">https://free-proxy-list.net/</a></br>
				- Instagram Proxies <a href="https://hide-ip-proxy.com/what-is-instagram-proxy/" target="_new" style="font-size:12px">https://hide-ip-proxy.com/what-is-instagram-proxy/</a></b>
				</td>
			</tr>

			</table> 
			<h3>Caption & Hashtags settings</h3>
			<hr/>
			<br/><br/><br/>
			<table cellpadding="2">
			<tr>
				<td align="top">Use additional hashtags</td>
				<td align="top"><textarea name="wp2instagram_additional_hashtags" rows="5" cols="50"><?php echo $wp2instagram_additional_hashtags; ?></textarea></td>
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
				<td>Add post title words as hashtags</td>
				<td><input type="checkbox" <?php if ($wp2instagram_use_title_words_of_post == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_use_title_words_of_post"></td>
				<td>Check the box if you want to use it - For example if the post title is “The Lazy Brown Dog” then as additional hashtags it adds “#the #lazy #brown #dog”
			</td>
			</tr>
			<tr>
				<td>Also use tags of post as hashtags</td>
				<td><input type="checkbox" <?php if ($wp2instagram_use_tags_of_post == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_use_tags_of_post"></td>
				<td>Check the box if you want to use it.
			</td>
			</tr>
			<tr>
				<td>Also use categories of post as hashtags</td>
				<td><input type="checkbox" <?php if ($wp2instagram_use_categories_of_post == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_use_categories_of_post"></td>
				<td>Check the box if you want to use it.
			</td>
			</tr>
			<tr>
				<td align="top">Add this header to caption</td>
				<td align="top"><input type="text" name="wp2instagram_header_caption" size="100" value="<?php echo $wp2instagram_header_caption; ?>"></td>
				<td>This text will be placed after Title and Author and before hashgtags<br/>
				</td>
			</tr>
			<tr>
				<td align="top">Add this footer to caption</td>
				<td align="top"><input type="text" name="wp2instagram_footer_caption" size="100" value="<?php echo $wp2instagram_footer_caption; ?>"></td>
				<td>This text will be placed after the hashgtags<br/>
				</td>
			</tr>
			<tr>
				<td>Include Author's name in header caption</td>
				<td><input type="checkbox" <?php if ($wp2instagram_author == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_author"></td>
				<td>Check this box if you want to see the author's name in front of the header caption.
			</td>
			<tr>
				<td align="top">Add this author label to caption</td>
				<td align="top"><input type="text" name="wp2instagram_author_caption" size="100" value="<?php echo $wp2instagram_author_caption; ?>"></td>
				<td>This text will be placed after Title and before hashgtags - Used only if <i>Include Author's name in header caption</i> is checked <br/>
				</td>
			</tr>

			<tr>
				<td>Attach post link to caption</td>
				<td><input type="checkbox" <?php if ($wp2instagram_attach_post_link_to_caption == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_attach_post_link_to_caption"></td>
				<td>Check the box if you want to attach the post link to the caption - It will be insterted after the footer.
			</td>
			</tr>
			<tr>
				<td>Use of excerpt</td>
				<td><input type="checkbox" <?php if ($wp2instagram_use_of_excerpt == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_use_of_excerpt"></td>
				<td>Check the box if you want to use an excerpt of your post as description (or text) in Instagram.
			</td>
			</tr>
			<tr>
				<td align="top">Maximum number of words in excerpt</td>
				<td align="top"><input type="number" name="wp2instagram_max_number_of_words_in_excerpt" min="0" max="1500" value="<?php echo $wp2instagram_max_number_of_words_in_excerpt; ?>"></td>
				<td>Number of words to use from your post exceprt - choose between 0 and 1500 - This text will be placed after the hashgtags and before the footer - Used only if <i>Use of excerpt</i> is checked <br/>
				</td>
			</tr>
			<tr>
				<td>Replace accents from hashtags</td>
				<td><input type="checkbox" <?php if ($wp2instagram_replace_accents == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_replace_accents"></td>
				<td>Check the box if you want to replace accents with their equivalents without ( Eg ùÙüÜïÎÏàÀôöÔÖÈÉËÊéèê ).
			</td>
			</tr>
			<tr>
				<td>Sanitize hashtags</td>
				<td><input type="checkbox" <?php if ($wp2instagram_sanitize == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_sanitize"></td>
				<td>Check the box if you want to sanitize your hastags ( Eg remove hyphen (-), underscore (_) ).
			</td>
			</tr>
			<tr>
				<td>Do not auto publish new posts</td>
				<td><input type="checkbox" <?php if ($wp2instagram_dontautopublish == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_dontautopublish"></td>
				<td>Check the box if you do not want to have all new posts published to Instagram.
			</td>
			</tr>
			<tr>
				<td>Bypass control of width and height</td>
				<td><input type="checkbox" <?php if ($wp2instagram_bypass_control == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_bypass_control"></td>
				<td>Check the box if you do not want any control of width or height before sending your picture to Instagram (May be refused by instagram).
			</td>
			</tr>
			<tr>
				<td>Resize picture if needed</td>
				<td><input type="checkbox" <?php if ($wp2instagram_resize_image == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_resize_image"></td>
				<td>Check the box if you want that the plugin try to resize your image to a size accepted by Instagram ( max 1080px ) if your picture is too big.
			</td>
			</tr>
			<tr>
				<td>Remove Exif from picture</td>
				<td><input type="checkbox" <?php if ($wp2instagram_remove_exif_image == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_remove_exif_image"></td>
				<td>Check the box if you want that the plugin remove the Exif from your photo before sending it to Instagram.
			</td>
			</tr>
			
			
			</table> 
			<h3>Notifications settings</h3>
			<hr/>
			<br/><br/><br/>
			<table cellpadding="2">
			
			
			
			
			<tr>
				<td>Receive a notification if IG post is good</td>
				<td><input type="checkbox" <?php if ($wp2instagram_receive_mail_if_ig_post_good == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_receive_mail_if_ig_post_good"></td>
				<td>Check the box if you want to receive a mail each time a picture is sent to Instagram - Mail will be sent to <b><?php echo $user_email; ?></b>
			</td>
			</tr>
			<tr>
				<td>Receive a notification if IG post failed</td>
				<td><input type="checkbox" <?php if ($wp2instagram_receive_mail_if_ig_post_bad == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_receive_mail_if_ig_post_bad"></td>
				<td>Check the box if you want to receive a mail each time a picture is sent to Instagram and failed to upload - Mail will be sent to <b><?php echo $user_email; ?></b>
			</td>
			</tr>
			<tr>
				<td>Include additional email address to notification</td>
				<td><input type="checkbox" <?php if ($wp2instagram_use_additional_email_addresses == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_use_additional_email_addresses"></td>
				<td>Check this box if you want to add additional email recipients
			</td>
			</tr>
			<tr>
				<td align="top">Add these email addresses</td>
				<td align="top"><input type="text" name="wp2instagram_additional_email_addresses" size="90" value="<?php echo $wp2instagram_additional_email_addresses; ?>"></td>
				<td>Separate your email addresses by a comma - Used only if <i>Include additional email address to notification</i> is checked <br/>
				</td>
			</tr>
			
			
			</table> 
			<h3>Debug settings</h3>
			<hr/>
			<br/><br/><br/>
			<table cellpadding="2">
			
			
			<tr>
				<td>Debug</td>
				<td><input type="checkbox" <?php if ($wp2instagram_debug == 'Y') {
                echo 'checked="checked"';
            } ?>value="Y" name="wp2instagram_debug"></td>
				<td>Check the box if you want to enable debug mode.
			</td>

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

			<h3 class="ws-table-title">Checking required PHP extensions to be compliant with Instagram-API </h3>
			<h2><a href="https://github.com/mgp25/Instagram-API/wiki/Dependencies">https://github.com/mgp25/Instagram-API/wiki/Dependencies</a></h2>
			<br>
			<div style="font-size:18px; color:#03D">
			<?php
				include_once 'required_extensions.php';
			?>
			</div>


            <?php
        }

        public function post_published_instagram($ID, $post)
        {


			$dontautopublish = self::wp2instagram_get_option('wp2instagram_dontautopublish','N');
			$bypass_control = self::wp2instagram_get_option('wp2instagram_bypass_control','N');
			$resize_image = self::wp2instagram_get_option('wp2instagram_resize_image','N');
			$remove_exif = self::wp2instagram_get_option('wp2instagram_remove_exif_image','N');

            $post_this_article_to_instagram = 'N';
            $in_instagram_box = 'N';

            $in_instagram_box = isset($_POST['wp2instagram_in_instagram_box']);
            if (empty($in_instagram_box)) {
                $in_instagram_box = 'N';
            }

            $post_this_article_to_instagram = isset($_POST['wp2instagram_post_this_article_to_instagram']);

            if (empty($post_this_article_to_instagram) && ($in_instagram_box == 'Y')) {
                $post_this_article_to_instagram = 'N';
            } elseif (empty($post_this_article_to_instagram) && ($in_instagram_box == 'N')) {
                // We are not in the instagram box post, but for instance in an auto scheduled post case

                if ($dontautopublish == 'Y') {
                    $post_this_article_to_instagram = 'N';
                } else {
                    $post_this_article_to_instagram = 'Y';
                }
            }

            if ($post_this_article_to_instagram != 'Y') {
                $post_this_article_to_instagram = 'N';
            }

            if (has_post_thumbnail()) {
                $username = get_option('wp2instagram_username', '');
                $password = get_option('wp2instagram_password', '');

				$use_ig_proxy = self::wp2instagram_get_option('wp2instagram_use_proxy','N');
								
				//$ig_proxy='https://204.15.243.234:45078';

				$ig_proxy = self::wp2instagram_get_option('wp2instagram_ig_proxy','',false);

				if (empty($ig_proxy)) {
					$ig_proxy = '';
				}



                if ($username == '' || $password == '') {
                    return;
                } else {
                    if (($username != '') && ($post_this_article_to_instagram == 'Y')) {
                        if (!get_post_meta($ID, 'firstpublish', $single = true)) {
                            $feature_image_id = get_post_thumbnail_id($ID);
                            $feature_image_meta = wp_get_attachment_image_src($feature_image_id, '32');
                            $photo = get_attached_file($feature_image_id); // Full path

                            $author_id = get_post_field('post_author', $ID);
                            $display_name = get_the_author_meta('display_name', $author_id);
                            //echo $display_name;

                            $action_delete = false;

                            list($originalWidth, $originalHeight) = getimagesize($photo);


							if ( ( $originalWidth > 0 ) &&  ( $originalHeight > 0 ) ) {


								if ($bypass_control == 'N') {
									if ($originalWidth > 1080) {
										$instagram_post_upload_status = 'Width too big for Instagram - must be less than 1080px';
										update_post_meta($ID, 'instagram_post_upload_status', json_encode($instagram_post_upload_status));
										echo $instagram_post_upload_status."\n";
										exit(0);
									}

									if ($originalHeight > 1080) {
										$instagram_post_upload_status = 'Height too big for Instagram - must be less than 1080px';
										update_post_meta($ID, 'instagram_post_upload_status', json_encode($instagram_post_upload_status));
										echo $instagram_post_upload_status."\n";
										exit(0);
									}

									if ($originalWidth < 320) {
										$instagram_post_upload_status = 'Width too small for Instagram - must be higher than 320px';
										update_post_meta($ID, 'instagram_post_upload_status', json_encode($instagram_post_upload_status));
										echo $instagram_post_upload_status."\n";
										exit(0);
									}

									if ($originalHeight < 320) {
										$instagram_post_upload_status = 'Height too small for Instagram - must be higher than 320px';
										update_post_meta($ID, 'instagram_post_upload_status', json_encode($instagram_post_upload_status));
										echo $instagram_post_upload_status."\n";
										exit(0);
									}
								}



								if ($remove_exif == 'Y')  {

									$ratio = $originalWidth / $originalHeight;
									$resizeH = $originalHeight;
									$resizeW = $originalWidth;

									//let's go and resize picture, that will remove the exif

									$origimg = imagecreatefromjpeg($photo);
									$resizeimg = imagecreatetruecolor($resizeW, $resizeH);

									$white = imagecolorallocate($resizeimg, 255, 255, 255);
									imagefill($resizeimg, 0, 0, $white);

									// Crop

									imagecopyresized($resizeimg, $origimg, 0, 0, 0, 0, $resizeW, $resizeH, $originalWidth, $originalHeight);
									imagejpeg($resizeimg, WP2INSTAGRAM_PLUGIN_PATH.'temp.jpg');
									$photo = WP2INSTAGRAM_PLUGIN_PATH.'temp.jpg';

									$action_delete = true;

									list($originalWidth, $originalHeight) = getimagesize($photo);

								}



								if ($resize_image == 'Y')  {
									$ratio = $originalWidth / $originalHeight;

									if (($originalWidth > $originalHeight) && ($originalWidth > 1080)) { // landscape picture

										$resizeW = 1080;
										$resizeH = $resizeW / $ratio;
									} elseif (($originalWidth < $originalHeight) && ($originalHeight > 1080)) { // portrait picture

										$resizeH = 1080;
										$resizeW = $resizeH * $ratio;
									} elseif (($originalWidth == $originalHeight) && ($originalWidth > 1080)) { // square picture

										$resizeH = 1080;
										$resizeW = 1080;
									} else { // picture size is lower than 1080 but bigger than 320 , so we keep the original size

										$resizeH = $originalHeight;
										$resizeW = $originalWidth;
									}

									//let's go and resize picture

									$origimg = imagecreatefromjpeg($photo);
									$resizeimg = imagecreatetruecolor($resizeW, $resizeH);

									$white = imagecolorallocate($resizeimg, 255, 255, 255);
									imagefill($resizeimg, 0, 0, $white);

									// Crop

									imagecopyresized($resizeimg, $origimg, 0, 0, 0, 0, $resizeW, $resizeH, $originalWidth, $originalHeight);
									imagejpeg($resizeimg, WP2INSTAGRAM_PLUGIN_PATH.'temp.jpg');
									$photo = WP2INSTAGRAM_PLUGIN_PATH.'temp.jpg';

									$action_delete = true;

									list($originalWidth, $originalHeight) = getimagesize($photo);
								}

								$ratio = $originalWidth / $originalHeight;

								if ($ratio < 0.8) {
									$cropH = $originalHeight;
									$cropW = $originalHeight * 0.8 + 2;
									$X = ($cropW - $originalWidth) / 2;

									$origimg = imagecreatefromjpeg($photo);
									$cropimg = imagecreatetruecolor($cropW, $cropH);

									$white = imagecolorallocate($cropimg, 255, 255, 255);
									imagefill($cropimg, 0, 0, $white);

									// Crop

									imagecopyresized($cropimg, $origimg, $X, 0, 0, 0, $originalWidth, $originalHeight, $originalWidth, $originalHeight);
									imagejpeg($cropimg, WP2INSTAGRAM_PLUGIN_PATH.'temp.jpg');
									$photo = WP2INSTAGRAM_PLUGIN_PATH.'temp.jpg';

									$action_delete = true;
								} elseif ($ratio > 1.77) { // case of a panoramic above a 16:9 ratio

									$cropW = $originalWidth;
									$cropH = ($originalWidth - $originalHeight) + 2;
									$Y = ($cropH - $originalHeight) / 2;

									$origimg = imagecreatefromjpeg($photo);
									$cropimg = imagecreatetruecolor($cropW, $cropH);

									$white = imagecolorallocate($cropimg, 255, 255, 255);
									imagefill($cropimg, 0, 0, $white);

									// Crop

									imagecopyresized($cropimg, $origimg, 0, $Y, 0, 0, $originalWidth, $originalHeight, $originalWidth, $originalHeight);
									imagejpeg($cropimg, WP2INSTAGRAM_PLUGIN_PATH.'temp.jpg');
									$photo = WP2INSTAGRAM_PLUGIN_PATH.'temp.jpg';

									$action_delete = true;
								}

							}





                            $debug = false;
							$debug_mode = self::wp2instagram_get_option('wp2instagram_debug','Y');

                            if ($debug_mode == 'Y') {
                                $debug = true;
                            } else {
                                $debug = false;
                            }

                            $caption = get_the_title($ID);     // caption

                            //////////////////////
                            $caption = html_entity_decode($caption, ENT_QUOTES, 'UTF-8');
							$the_photo_title = $caption;

                            //////////////////////
                            // ADD HASHTAGS
                            // Beware not to use more than 30 tags
                            // ( https://www.quora.com/What-is-the-maximum-number-of-hashtags-you-can-insert-in-a-comment-on-an-Instagram-photo )
                            //////////////////////

                            $tags = wp_get_post_tags($ID);
                            $categories = wp_get_post_categories($ID);
                            $tag_list = '';
                            $number_of_hashtags = 0;

                            $replace_sanitize = [
                        '_' => '',
                        '-' => '',
                        ];

                            $replace_accents = [
                        'ù' => 'u',
                        'Ù' => 'U',
                        'ü' => 'u',
                        'Ü' => 'U',
                        'ï' => 'i',
                        'Î' => 'I',
                        'Ï' => 'I',
                        'à' => 'a',
                        'À' => 'A',
                        'ô' => 'o',
                        'ö' => 'o',
                        'Ô' => 'O',
                        'Ö' => 'O',
                        'È' => 'E',
                        'É' => 'E',
                        'Ë' => 'E',
                        'Ê' => 'E',
                        'é' => 'e',
                        'è' => 'e',
                        'ê' => 'e',
                        ];

                            //$max_number_of_hashtags=28;


                            $max_number_of_hashtags = get_option('wp2instagram_max_number_of_hashtags', '');

                            if ($max_number_of_hashtags == false || $max_number_of_hashtags == 'null') {
                                $max_number_of_hashtags = '';
                            } else {
                                $max_number_of_hashtags = json_decode($max_number_of_hashtags);
                            }

							if (empty($max_number_of_hashtags)) {
                                $max_number_of_hashtags = 29;
                            }


                            $additional_tag_list = '';
                            $bad_char = [' '];


							$use_of_excerpt = self::wp2instagram_get_option('wp2instagram_use_of_excerpt','N');


							 //$max_number_of_words_in_excerpt


                            $max_number_of_words_in_excerpt = get_option('wp2instagram_max_number_of_words_in_excerpt', '');

                            if ($max_number_of_words_in_excerpt == false || $max_number_of_words_in_excerpt == 'null') {
                                $max_number_of_words_in_excerpt = '';
                            } else {
                                $max_number_of_words_in_excerpt = json_decode($max_number_of_words_in_excerpt);
                            }

							if (empty($max_number_of_words_in_excerpt)) {
                                $max_number_of_words_in_excerpt = 0;
                            }




							$sanitize_tags_of_post = self::wp2instagram_get_option('wp2instagram_sanitize','N');
							$replace_accents_in_tags_of_post = self::wp2instagram_get_option('wp2instagram_replace_accents','Y');
							$use_title_words_of_post = self::wp2instagram_get_option('wp2instagram_use_title_words_of_post','N');
							$use_tags_of_post = self::wp2instagram_get_option('wp2instagram_use_tags_of_post','Y');
							$use_categories_of_post = self::wp2instagram_get_option('wp2instagram_use_categories_of_post','N');
							$use_author_of_post = self::wp2instagram_get_option('wp2instagram_author','Y');
							$receive_mail_if_ig_post_good = self::wp2instagram_get_option('wp2instagram_receive_mail_if_ig_post_bad','N');
							$receive_mail_if_ig_post_bad = self::wp2instagram_get_option('wp2instagram_receive_mail_if_ig_post_bad','N');
							$attach_post_link_to_caption = self::wp2instagram_get_option('wp2instagram_attach_post_link_to_caption','N');
							$use_additional_email_addresses = self::wp2instagram_get_option('wp2instagram_use_additional_email_addresses','N');


							// Get extra email addresses

							$additional_email_addresses = self::wp2instagram_get_option('wp2instagram_additional_email_addresses','',false);

							if ($use_additional_email_addresses == 'N') {
								$additional_email_addresses = '';
							}

                            // Get Author's Caption
							$author_caption = self::wp2instagram_get_option('wp2instagram_author_caption','',false);

                            // Get header & footer
							$header_caption = self::wp2instagram_get_option('wp2instagram_header_caption','',false);

                            if (empty($header_caption)) {
                                $header_caption = '';
                            }

							$footer_caption = self::wp2instagram_get_option('wp2instagram_footer_caption','',false);

                            if (empty($footer_caption)) {
                                $footer_caption = '';
                            }

                            // First process additional tags
							$additional_hashtags = self::wp2instagram_get_option('wp2instagram_additional_hashtags','',false);

                            if (empty($additional_hashtags)) {
                                $additional_hashtags_list = [''];
                            } else {
                                $additional_hashtags_list = explode(',', $additional_hashtags);
                            }

                            foreach ($additional_hashtags_list as $tag) {
                                $instagram_tag = str_replace($bad_char, '', $tag);

                                if ($replace_accents_in_tags_of_post == 'Y') {
                                    $instagram_tag = str_replace(array_keys($replace_accents), array_values($replace_accents), $instagram_tag);
                                }
                                if ($sanitize_tags_of_post == 'Y') {
                                    $instagram_tag = str_replace(array_keys($replace_sanitize), array_values($replace_sanitize), $instagram_tag);
                                }

                                if ($number_of_hashtags < $max_number_of_hashtags) {
                                    $tag_list .= '#'.$instagram_tag.' ';
                                    $number_of_hashtags++;
                                }
                            }


							// Now process tags associated to post title

							if ($use_title_words_of_post == 'Y') {

								if (empty($caption)) {
									$title_words_hashtags_list = [''];
								} else {
									$title_words_hashtags_list = explode(' ', $caption);
								}

								foreach ($title_words_hashtags_list as $tag) {
									$instagram_tag = str_replace($bad_char, '', $tag);

									if ($replace_accents_in_tags_of_post == 'Y') {
										$instagram_tag = str_replace(array_keys($replace_accents), array_values($replace_accents), $instagram_tag);
									}
									if ($sanitize_tags_of_post == 'Y') {
										$instagram_tag = str_replace(array_keys($replace_sanitize), array_values($replace_sanitize), $instagram_tag);
									}

									if ($number_of_hashtags < $max_number_of_hashtags) {
										$tag_list .= '#'.$instagram_tag.' ';
										$number_of_hashtags++;
									}
								}

							}


                            // Now process tags associated to post

                            if ($use_tags_of_post == 'Y') {
                                foreach ($tags as $tag) {
                                    $instagram_tag = str_replace($bad_char, '', $tag->name);

                                    if ($replace_accents_in_tags_of_post == 'Y') {
                                        $instagram_tag = str_replace(array_keys($replace_accents), array_values($replace_accents), $instagram_tag);
                                    }
                                    if ($sanitize_tags_of_post == 'Y') {
                                        $instagram_tag = str_replace(array_keys($replace_sanitize), array_values($replace_sanitize), $instagram_tag);
                                    }

                                    //Just in case, if the tag contains a comma
                                    $instagram_tag_list = [''];
                                    $instagram_tag_list = explode(',', $instagram_tag);

                                    foreach ($instagram_tag_list as $ig_tag) {
                                        if ($number_of_hashtags < $max_number_of_hashtags) {
                                            $tag_list .= '#'.$ig_tag.' ';
                                            $number_of_hashtags++;
                                        }
                                    }
                                }
                            }

                            // Now process categories associated to post

                            if ($use_categories_of_post == 'Y') {
                                foreach ($categories as $category_element) {
                                    $category = get_category($category_element);

                                    $instagram_tag = str_replace($bad_char, '', $category->name);

                                    if ($replace_accents_in_tags_of_post == 'Y') {
                                        $instagram_tag = str_replace(array_keys($replace_accents), array_values($replace_accents), $instagram_tag);
                                    }
                                    if ($sanitize_tags_of_post == 'Y') {
                                        $instagram_tag = str_replace(array_keys($replace_sanitize), array_values($replace_sanitize), $instagram_tag);
                                    }

                                    //Just in case, if the category name contains a comma
                                    $instagram_tag_list = [''];
                                    $instagram_tag_list = explode(',', $instagram_tag);

                                    foreach ($instagram_tag_list as $ig_tag) {
                                        if ($number_of_hashtags < $max_number_of_hashtags) {
                                            $tag_list .= '#'.$ig_tag.' ';
                                            $number_of_hashtags++;
                                        }
                                    }
                                }
                            }

                            if ($use_author_of_post == 'Y') {
                                if (strlen($author_caption) > 0) {
                                    $caption .= ' - '.$author_caption.' : ';
                                }
                                if (strlen($display_name) > 0) {
                                    $caption .= $display_name;
                                } else {
									$caption .= 'Unknown author';
								}
                            }

                            if (strlen($header_caption) > 0) {
                                $caption .= ' - '.$header_caption;
                            }

                            if (strlen($tag_list) > 0) {
                                $caption .= ' - '.strtolower($tag_list);
                            }




							if ( $use_of_excerpt == 'Y' ) {

								$post_excerpt = wp_trim_words( get_post_field( 'post_content', $ID ), $max_number_of_words_in_excerpt) ;

								if (strlen($post_excerpt) > 0) {
									$caption .= ' - '.$post_excerpt;
								}
                            }



                            if (strlen($footer_caption) > 0) {
                                $caption .= ' - '.$footer_caption;
                            }

							$post_link = get_permalink( $ID );

							if ( $attach_post_link_to_caption == 'Y' ) {
								if (strlen($post_link) > 0) {
									$caption .= ' - '.$post_link;
								}
                            }


                            //////////////////////
                            $caption = html_entity_decode($caption, ENT_QUOTES, 'UTF-8');

                            $wp2IGFullPath = null;

                            try {
								\InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
								$i = new \InstagramAPI\Instagram(false, true);
								if ( $use_ig_proxy == 'Y' ) { $i->setProxy($ig_proxy); }
								$i->login($username, $password);
                            } catch (\Exception $e) {
                                $error_message = $e->getMessage();

                                if ($error_message == 'login_required') {
                                    try {
                                        $loging_force = true;
										if ( $use_ig_proxy == 'Y' ) { $i->setProxy($ig_proxy); }
                                        $i->login($loging_force);
                                    } catch (\Exception $e2) {
                                        $instagram_post_upload_status = 'Something went wrong while logging to instagram: '.$e2->getMessage();
                                        update_post_meta($ID, 'instagram_post_upload_status', json_encode($instagram_post_upload_status));


										if ( $receive_mail_if_ig_post_bad == 'Y' ) {

											$email_subject = "Auto Post To Instagram Failed Notification - your photo «".$the_photo_title."» was not uploaded to Instagram";

											$email_msg = '<p>Dear user</p>';
											$email_msg.= '<p>Your post ID '. $ID .' / «'.$the_photo_title.'» was not posted on Instagram due to the following reason - see below</p></br>';
											$email_msg.= '</br>';
											$email_msg.= '<p>'.$instagram_post_upload_status.'</p>';

											$this->wp2instagram_send_email($email_subject,$email_msg,$additional_email_addresses);

										}

                                        echo $instagram_post_upload_status."\n";
                                        exit(0);
                                    }
                                } else {
                                    $instagram_post_upload_status = 'Something went wrong while connecting to instagram: '.$e->getMessage();
                                    update_post_meta($ID, 'instagram_post_upload_status', json_encode($instagram_post_upload_status));

									if ( $receive_mail_if_ig_post_bad == 'Y' ) {

										$email_subject = "Auto Post To Instagram Failed Notification - your photo «".$the_photo_title."» was not uploaded to Instagram";

										$email_msg = '<p>Dear user</p>';
										$email_msg.= '<p>Your post ID '. $ID .' / «'.$the_photo_title.'» was not posted on Instagram due to the following reason - see below</p></br>';
										$email_msg.= '</br>';
										$email_msg.= '<p>'.$instagram_post_upload_status.'</p>';

										$this->wp2instagram_send_email($email_subject,$email_msg,$additional_email_addresses);
									}


                                    echo $instagram_post_upload_status."\n";
                                    exit(0);
                                }
                            }

							// Retreive User Info

							try {
								$insta_user_details = $i->people->getInfoByName($username);
							} catch (\Exception $e) {
                                $instagram_post_userdetails_status = 'Something went wrong while getting user details from instagram: '.$e->getMessage();
                                update_post_meta($ID, 'instagram_post_userdetails_status', json_encode($instagram_post_userdetails_status));
                                echo $instagram_post_userdetails_status."\n";
                                exit(0);
                            }

							$insta_user_details = json_decode($insta_user_details);
							$insta_userid = $insta_user_details->user->pk;
							$insta_fullname_status = $insta_user_details->user->full_name;

							update_post_meta($ID, 'instagram_fullname_status', json_encode($insta_fullname_status));

                            try {

								$IGphoto = new \InstagramAPI\Media\Photo\InstagramPhoto($photo);
								$insta_info = $i->timeline->uploadPhoto($IGphoto->getFile(),  ['caption' => $caption]);

                            } catch (\Exception $e) {
                                $error_message = $e->getMessage();

                                if ($error_message == 'login_required') {
                                    try {
                                        $loging_force = true;
										if ( $use_ig_proxy == 'Y' ) { $i->setProxy($ig_proxy); }
                                        $i->login($loging_force);
                                    } catch (\Exception $e2) {
                                        $instagram_post_upload_status = 'Something went wrong while logging to instagram: '.$e2->getMessage();
                                        update_post_meta($ID, 'instagram_post_upload_status', json_encode($instagram_post_upload_status));


										if ( $receive_mail_if_ig_post_bad == 'Y' ) {

											$email_subject = "Auto Post To Instagram Failed Notification - your photo «".$the_photo_title."» was not uploaded to Instagram";

											$email_msg = '<p>Dear user</p>';
											$email_msg.= '<p>Your post ID '. $ID .' / «'.$the_photo_title.'» was not posted on Instagram due to the following reason - see below</p></br>';
											$email_msg.= '</br>';
											$email_msg.= '<p>'.$instagram_post_upload_status.'</p>';

											$this->wp2instagram_send_email($email_subject,$email_msg,$additional_email_addresses);

										}

										echo $instagram_post_upload_status."\n";
                                        exit(0);
                                    }
                                } else {
                                    $instagram_post_upload_status = 'Something went wrong while uploading your photo: '.$e->getMessage();
                                    update_post_meta($ID, 'instagram_post_upload_status', json_encode($instagram_post_upload_status));


									if ( $receive_mail_if_ig_post_bad == 'Y' ) {

											$email_subject = "Auto Post To Instagram Failed Notification - your photo «".$the_photo_title."» was not uploaded to Instagram";

											$email_msg = '<p>Dear user</p>';
											$email_msg.= '<p>Your post ID '. $ID .' / «'.$the_photo_title.'» was not posted on Instagram due to the following reason - see below</p></br>';
											$email_msg.= '</br>';
											$email_msg.= '<p>'.$instagram_post_upload_status.'</p>';

											$this->wp2instagram_send_email($email_subject,$email_msg,$additional_email_addresses);

										}

									echo $instagram_post_upload_status."\n";
									exit(0);
                                }
                            }

                            if ($action_delete == true) {
                                unlink($photo);
                            }

                            update_post_meta($ID, 'instagram_response_status', $insta_info);



							if (strpos($insta_info, 'cdninstagram') == false) {
                                $instagram_post_upload_status = 'Not accepted by Instagram';
                                update_post_meta($ID, 'instagram_post_upload_status', json_encode($instagram_post_upload_status));


								if ( $receive_mail_if_ig_post_bad == 'Y' ) {

									$email_subject = "Auto Post To Instagram Failed Notification - your photo «".$the_photo_title."» was not accepted by Instagram";

									$email_msg = '<p>Dear user</p>';
									$email_msg.= '<p>Your post ID '. $ID .' / «'.$the_photo_title.'» was not posted on Instagram due to following reason - see below</p></br>';
									$email_msg.= '</br>';
									$email_msg.= '<p>'.$insta_info.'</p>';

									$this->wp2instagram_send_email($email_subject,$email_msg,$additional_email_addresses);

								}

                            } else {
                                update_post_meta($ID, 'firstpublish', true);

                                $instagram_post_upload_status = 'Uploaded to Instagram';
                                update_post_meta($ID, 'instagram_post_upload_status', json_encode($instagram_post_upload_status));

								// Now retrieve Instagram link, it is the last one that we sent in our Instagram User feed

								$userId = $i->people->getUserIdForName($username);
								$maxId = null;
								$response = $i->timeline->getUserFeed($userId, $maxId);
								$IgItemList = $response->getItems();
								$InstaCode = $IgItemList[0]->getCode();
								$InstaLink = 'https://instagram.com/p/'.$InstaCode;

								update_post_meta($ID, 'instagram_post_link', json_encode($InstaLink));

								if ( $receive_mail_if_ig_post_good == 'Y' ) {

									$email_subject = "Auto Post To Instagram Notification - your photo «".$the_photo_title."» was successfully sent to Instagram";

									$email_msg = '<p>Dear user</p>';
									$email_msg.= '<p>Your post ID '. $ID .' / «'.$the_photo_title.'» - ('.$post_link.') was successfully posted on Instagram - ('.$InstaLink.') - result below</p></br>';
									$email_msg.= '</br>';
									$email_msg.= '<p>'.$insta_info.'</p>';


									$this->wp2instagram_send_email($email_subject,$email_msg,$additional_email_addresses);

								}
                            }

                            $instagram_post_date = date('Y-m-d H:i:s', time());
                            update_post_meta($ID, 'instagram_post_date', $instagram_post_date);


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
