<?php
/*
Plugin Name: WPShapere Lite
Plugin URI: https://wpshapere.com
Description: WPShapere is a wordpress plugin to customize the WordPress Admin theme and elements as your wish. Make WordPress a complete CMS with WPShapere.
Version: 1.3
Author: AcmeeDesign Softwares and Solutions
Author URI: https://acmeedesign.com
Text-Domain: wps
Domain Path: /languages
 *
*/

/*
*   WPSHAPERE LITE Version
*/

define( 'WPSHAPERE_LITE_VERSION' , '1.3' );

/*
*   WPSHAPERE Path Constant
*/
define( 'WPSHAPERE_LITE_PATH' , dirname(__FILE__) . "/");

/*
*   WPSHAPERE URI Constant
*/
define( 'WPSHAPERE_LITE_DIR_URI' , plugin_dir_url(__FILE__) );

/*
*   WPSHAPERE Options slug Constant
*/
define( 'WPSHAPERE_LITE_OPTIONS_SLUG' , 'wpshapere_options' );

require_once( WPSHAPERE_LITE_PATH . 'includes/wps-options.php' );

/*
 * Main configuration for AOF class
 */

if(!function_exists('wps_config')) {
  function wps_config() {
    if(!is_multisite()) {
        $multi_option = false;
    }
     elseif(is_multisite() && !defined('NETWORK_ADMIN_CONTROL')) {
         $multi_option = false;
     }
     else {
         $multi_option = true;
     }

     /* Stop editing after this */
     $wps_fields = get_wps_options();
     $config = array(
         'multi' => $multi_option, //default = false
         'wps_fields' => $wps_fields,
       );

       return $config;
  }
}

//Implement main settings
require_once( WPSHAPERE_LITE_PATH . 'main-settings.php' );

function wps_load_textdomain()
{
   load_plugin_textdomain('wps', false, dirname( plugin_basename( __FILE__ ) )  . '/languages' );
}
add_action('plugins_loaded', 'wps_load_textdomain');

include_once WPSHAPERE_LITE_PATH . 'includes/fa-icons.class.php';
include_once WPSHAPERE_LITE_PATH . 'includes/wpshapere.class.php';
include_once WPSHAPERE_LITE_PATH . 'includes/wps-impexp.class.php';
include_once WPSHAPERE_LITE_PATH . 'includes/premium-version.class.php';
