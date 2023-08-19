<?php
/**
	* Plugin Name: Security Ninja for MainWP
	* Plugin URI: https://wpsecurityninja.com/mainwp/
	* Description: This extension for MainWP integrates with WP Security Ninja Free.
	* Author: WP Security Ninja
	* Version: 1.6
	* Text Domain: security-ninja-mainwp
	* Domain Path: /languages
	* Author URI: http://www.wpwhitesecurity.com/
	* License: GPL2
	*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
* The register_activation_hook function registers a plugin function to be run when the plugin is activated.
*/
register_activation_hook( __FILE__, 'security_ninja_mainwp_activate' );


/**
* security_ninja_mainwp_activate.
*
* @author  Lars Koudal
* @since   v0.0.1
* @version v1.0.0  Monday, April 4th, 2022.
* @global
* @return  void
*/
function security_ninja_mainwp_activate() {
	update_option( 'security_ninja_mainwp_activated', 'yes' );
}



/*
* Activator Class is used for extension activation and deactivation
*/
class security_ninja_mainwp_extension_activator {
	public static $mainwp_main_activated = false;
	public static $child_enabled         = false;
	public static $child_key             = false;
	public static $plugin_handle         = 'secnin-mainwp-extension';
	public static $child_file;
	public static $plugin_url;
	public static $plugin_slug;

	/**
	* __construct.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 10th, 2022.
	* @version v1.0.1  Monday, April 4th, 2022.
	* @access  public
	* @return  boolean
	*/
	public function __construct() {
		self::$child_file  = __FILE__;
		self::$plugin_url  = plugin_dir_url( __FILE__ );
		self::$plugin_slug = plugin_basename( __FILE__ );

		add_filter( 'mainwp_getextensions', array( __CLASS__, 'get_this_extension' ) );


		add_filter( 'mainwp_plugins_install_checks', array(__CLASS__,'wpsn_mainwp_plugins_install_checks'), 10, 1 );


		// This filter will return true if the main plugin is activated
		self::$mainwp_main_activated = apply_filters( 'mainwp_activated_check', false );
		if ( false !== self::$mainwp_main_activated ) {
			self::activate_this_plugin();
		} else {
			//Because sometimes our main plugin is activated after the extension plugin is activated we also have a second step,
			//listening to the 'mainwp-activated' action. This action is triggered by MainWP after initialisation.
			add_action( 'mainwp_activated', array( __CLASS__, 'activate_this_plugin' ) );
		}
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_notices', array( __CLASS__, 'mainwp_error_notice' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_secnin_get_test_info', array( __CLASS__, 'get_site_test_info' ) );

		add_action( 'wp_ajax_secnin_update_site_info', array( __CLASS__, 'rerun_website_tests' ) );

		add_action( 'mainwp_header_left', array( __CLASS__, 'custom_page_title' ) );

		add_filter( 'mainwp_main_menu', array( __CLASS__, 'secnin_main_menu' ), 10, 1 );

		add_filter( 'mainwp_pro_reports_custom_tokens', array( __CLASS__, 'secnin_mainwp_pro_reports_custom_tokens' ), 10, 3 );
	}

	static function wpsn_mainwp_plugins_install_checks( $plugins ) {
		$plugins[] = array(
			 'page' => 'Extensions-Security-Ninja-For-Mainwp',
			 'slug' => 'security-ninja-premium/security-ninja.php',
			 'name' => 'WP Security Ninja',
	 );
	 return $plugins;
 }



	/**
	* secnin_mainwp_pro_reports_custom_tokens.
	*
	* @author	Lars Koudal
	* @since	v0.0.1
	* @version	v1.0.0	Friday, April 8th, 2022.
	* @param	mixed	$tokensValues	
	* @param	mixed	$report      	
	* @param	mixed	$website     	
	* @return	mixed
	*/
	public static function secnin_mainwp_pro_reports_custom_tokens( $tokens_values, $report, $website ) {

		$information = apply_filters( 'mainwp_fetchurlauthed', __FILE__, self::$child_key, $website, 'extra_execution', array( 'action' => 'get_test_results' ) );
		
		if (!$information) return $tokens_values;


		if ( is_array( $tokens_values ) && isset( $tokens_values['[securityninja.score]'] ) ) {
			$tokens_values['[securityninja.score]'] = $information['score'];
		}

		if ( is_array( $tokens_values ) && isset( $tokens_values['[securityninja.vulnerabilities]'] ) ) {
			$tokens_values['[securityninja.vulnerabilities]'] = $information['vulns'];
		}


		return $tokens_values;
	}





	/**
	* Pending - asks sites to rerun tests - rework in plugin also.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 24th, 2022.
	* @version v1.0.1  Monday, April 4th, 2022.
	* @access  public static
	* @return  void
	*/
	public static function rerun_website_tests() {
		check_ajax_referer( 'secnin_get_test_info' );
		if ( isset( $_POST['website'] ) ) {
			$website     = sanitize_key( $_POST['website'] );
			$information = apply_filters( 'mainwp_fetchurlauthed', __FILE__, self::$child_key, $website, 'extra_execution', array( 'action' => 'run_tests' ) );
			if ( $information ) {
				wp_send_json_success( $information );
			} else {
				wp_send_json_error();
			}
		}
		exit();
	}



	/**
	* Get info about a site
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 24th, 2022.
	* @version v1.0.1  Monday, April 4th, 2022.
	* @access  public static
	* @return  void
	*/
	public static function get_site_test_info() {
		check_ajax_referer( 'secnin_get_test_info' );
		if ( isset( $_POST['website'] ) ) {
			$website     = sanitize_key( $_POST['website'] );
			$information = apply_filters( 'mainwp_fetchurlauthed', __FILE__, self::$child_key, $website, 'extra_execution', array( 'action' => 'get_test_results' ) );
			if ( $information ) {
				wp_send_json_success( $information );
			} else {
				wp_send_json_error();
			}
		}
		exit();
	}

	/**
	* enqueue_scripts.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 10th, 2022.
	* @access  public
	* @return  void
	*/
	public static function enqueue_scripts() {
		
		wp_register_script( 'security-ninja-mainwp-extension', self::$plugin_url . 'js/security-ninja-mainwp.js', array( 'jquery' ), '1.0', true ); // @todo -min

		$js_vars = array(
			'nonce_secnin_getinfo' => wp_create_nonce( 'secnin_get_test_info' ),
		);
		wp_localize_script( 'security-ninja-mainwp-extension', 'secninja_mainwp', $js_vars );
		wp_enqueue_script( 'security-ninja-mainwp-extension' );

		wp_enqueue_style( 'security-ninja-mainwp-extension', self::$plugin_url . 'css/security-ninja-mainwp-min.css', array(), '1.0' );
	}

	/**
	* custom_page_title.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Saturday, April 2nd, 2022.
	* @param   mixed   $title
	* @return  mixed
	*/
	public static function custom_page_title( $title ) {

		if ( isset( $_REQUEST['page'] ) && 'Extensions-Security-Ninja-For-Mainwp' === $_REQUEST['page'] ) {

			$title = '<img src="' . esc_url( self::$plugin_url . 'images/sn-logo.svg' ) . '" height="40" alt="Visit wpsecurityninja.com" class="logoleft"> ';

			$title .= '<span>' . esc_html__( 'Security Ninja for MainWP', 'security-ninja-mainwp' ) . '</span>';
		}

		return $title;
	}





	/**
	* admin_init.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 10th, 2022.
	* @return  void
	*/
	public static function admin_init() {
		if ( 'yes' === get_option( 'security_ninja_mainwp_activated' ) ) {
			delete_option( 'security_ninja_mainwp_activated' );
			wp_safe_redirect( admin_url( 'admin.php?page=Extensions' ) );
			exit();
		}
	}



	/**
	* mainwp_extension_autoload.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 10th, 2022.
	* @param   mixed   $class_name
	* @return  void
	*/
	public static function mainwp_extension_autoload( $class_name ) {
		$allowed_loading_types = array( 'class', 'page' );

		foreach ( $allowed_loading_types as $allowed_loading_type ) {
			$class_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) . $allowed_loading_type . DIRECTORY_SEPARATOR . $class_name . '.' . $allowed_loading_type . '.php';
			if ( file_exists( $class_file ) ) {
				require_once $class_file;
			}
		}
	}


	/**
	* get_this_extension.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 10th, 2022.
	* @param   mixed   $p_array
	* @return  mixed
	*/
	public static function get_this_extension( $p_array ) {
		$p_array[] = array(
			'plugin'   => __FILE__,
			'api'      => 'mainwp-example-extension',
			'mainwp'   => false,
			'callback' => array( __CLASS__, 'settings' ),
		);
		return $p_array;
	}


	/**
	* settings.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 10th, 2022.
	* @return  void
	*/
	public static function settings() {
		//The "mainwp-pageheader-extensions" action is used to render the tabs on the Extensions screen.
		//It's used together with mainwp-pagefooter-extensions and mainwp-getextensions
		do_action( 'mainwp_pageheader_extensions', __FILE__ );
		if ( self::$child_enabled ) {
			self::mainwp_extension_autoload( 'SecurityNinjaMainWPExtension' );
			SecurityNinjaMainWPExtension::render_page();
		} else {
			?><div class="mainwp_info-box-yellow"><?php esc_html_e( 'The Extension has to be enabled to change the settings.' ); ?></div>
			<?php
		}
		do_action( 'mainwp_pagefooter_extensions', __FILE__ );
	}




	/**
	* The function "activate_this_plugin" is called when the main is initialized.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 10th, 2022.
	* @return  boolean
	*/
	public static function activate_this_plugin() {
		//Checking if the MainWP plugin is enabled. This filter will return true if the main plugin is activated.
		self::$mainwp_main_activated = apply_filters( 'mainwp_activated_check', self::$mainwp_main_activated );

		// The 'mainwp_extension_enabled_check' hook. If the plugin is not enabled this will return false,
		// if the plugin is enabled, an array will be returned containing a key.
		// This key is used for some data requests to our main
		self::$child_enabled = apply_filters( 'mainwp_extension_enabled_check', __FILE__ );

		if ( ! self::$child_enabled ) {
			return;
		}
		self::$child_key = self::$child_enabled['key'];
	}

	/**
	* Extension left menu for MainWP v4 or later.
	*
	* @param array $secnin_left_menu - Left menu array.
	* @return array
	*/
	public static function secnin_main_menu( $secnin_left_menu ) {
		$sub_menu_after = array_splice( $secnin_left_menu['mainwp_tab'], 2 );

		$activity_log   = array();
		$activity_log[] = 'Security Ninja';
		$activity_log[] = 'Extensions-Security-Ninja-For-Mainwp';
		$activity_log[] = admin_url( 'admin.php?page=Extensions-Security-Ninja-For-Mainwp' );

		$secnin_left_menu['mainwp_tab'][] = $activity_log;
		$secnin_left_menu['mainwp_tab']   = array_merge( $secnin_left_menu['mainwp_tab'], $sub_menu_after );

		return $secnin_left_menu;
	}

	/**
	* mainwp_error_notice.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 10th, 2022.
	* @return  void
	*/
	public static function mainwp_error_notice() {
		global $current_screen;
		if ( 'plugins' === $current_screen->parent_base && false === self::$mainwp_main_activated ) {
			?>
			<div class="error"><p>This extension requires the <a href="https://mainwp.com/" target="_blank" rel="noopener">MainWP</a> Plugin to be activated in order to work.</p></div>
			<?php
		}
	}

	/**
	* get_child_key.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 10th, 2022.
	* @access  public
	* @return  mixed
	*/
	public static function get_child_key() {
		return self::$child_key;
	}

	/**
	* get_child_file.
	*
	* @author  Lars Koudal
	* @since   v0.0.1
	* @version v1.0.0  Thursday, March 10th, 2022.
	* @access  public
	* @return  mixed
	*/
	public static function get_child_file() {
		return self::$child_file;
	}
}
$security_ninja_mainwp_extension_activator = new security_ninja_mainwp_extension_activator();
