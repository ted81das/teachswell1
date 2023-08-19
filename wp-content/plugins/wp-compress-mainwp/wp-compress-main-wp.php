<?php
/**
 * Plugin name: WP Compress | MainWP
 * Author: WP Compress
 * Description: Bulk install, activate and connect all of your MainWP Child sites to the WP Compress portal in just a few clicks.
 * Version: 6.10.07
 */

if ( ! empty($_GET['debug'])) {
  update_option('wpic_mainwp_debug', $_GET['debug']);
}


define('WPIC_URI', plugin_dir_url(__FILE__));
define('WPIC_DIR', plugin_dir_path(__FILE__));

spl_autoload_register(function($class_name) {
  $class_name = str_replace('wpic_mainwp_', '', $class_name);
  $class_name = $class_name . '.class.php';
  if (file_exists(WPIC_DIR . 'classes/' . $class_name)) {
    include 'classes/' . $class_name;
  }
});


class MainWPWPCompressExtensionActivator {

  protected $mainwpMainActivated = false;
  protected $childEnabled = false;
  protected $childKey = false;
  protected $childFile;

  private $ajax;
  private $settings;


  public function __construct() {
    $this->childFile = __FILE__;

    add_filter('mainwp-getextensions', array(&$this, 'get_this_extension'));
    $mainWPActivated = apply_filters('mainwp-activated-check', false);

    if ($mainWPActivated !== false) {
      $this->activate_this_plugin();
    } else {
      add_action('mainwp-activated', array(&$this, 'activate_this_plugin'));
    }

    add_action('admin_init', array(&$this, 'admin_init'));
    add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue'));
    add_action('admin_footer', array(&$this, 'mainwp_popups'));

    $this->ajax     = new wpic_mainwp_ajax();
    $this->settings = new wpic_mainwp_settings();

  }


  function mainwp_popups() {
    echo '<div id="wp-ic-not-enough-credits" style="display:none;">';

    echo '<div class="ic-popup">';
    echo '<div class="text-center">
      <h3>You do not have enough credits for this option!</h3>
      <h4 style="font-size: 14px; font-weight: 300;">Please purchase additional credits to link another website.</h4>
    </div>

    <div class="row" style="text-align: center;">
      <div class="col-lg-12 inline-all" style="margin-top: 20px;margin-bottom: 20px;">
        <!--<a href="https://wpcompress.com/pricing" class="button button-primary smaller-button btn_ic_maintenance_plan">Monthly</a>-->
        <a href="https://wpcompress.com/pricing" target="_blank" class="button button-primary smaller-button">Monthly</a>
        <span style="margin: 0 10px;">OR</span>
        <!--<a href="#" class="button button-primary smaller-button popup-one-time-credits">One-Time</a>-->
        <a href="https://wpcompress.com/credit-packs" target="_blank" class="button button-primary smaller-button">One-Time</a>
      </div>
    </div>';
    echo '</div>';
    echo '</div>';
  }


  function admin_enqueue() {
    wp_enqueue_style('ic-mainwp-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.1.0');
    wp_enqueue_script('ic-mainwp-scripts', plugin_dir_url(__FILE__) . 'assets/js/scripts.js', array(), '1.0.0');
    wp_enqueue_script('ic-mainwp-swal', plugin_dir_url(__FILE__) . 'assets/swal/sweetalert2.all.min.js', array(), '1.0.0');
    wp_enqueue_style('ic-mainwp-swalcss', plugin_dir_url(__FILE__) . 'assets/swal/sweetalert2.min.css', array(), '1.0.0');
  }


  function admin_init() {
    if ( ! empty($_GET['action']) && $_GET['action'] == 'disconnect') {
      delete_option('ic_mainwp_connected');
      wp_redirect(admin_url('admin.php?page=Extensions-Wp-Compress-Main-Wp'));

      return;
    }

    if (get_option('mainwp_wpcompress_extension_activated') == 'yes') {
      delete_option('mainwp_wpcompress_extension_activated');
      wp_redirect(admin_url('admin.php?page=Extensions'));

      return;
    }
  }


  function get_this_extension($pArray) {
    $pArray[] = array('plugin' => __FILE__, 'callback' => array(&$this, 'settings'), 'name' => 'WP Compress');
    return $pArray;
  }


  public function settings() {
    //The "mainwp-pageheader-extensions" action is used to render the tabs on the Extensions screen.
    //It's used together with mainwp-pagefooter-extensions and mainwp-getextensions
    do_action('mainwp-pageheader-extensions', __FILE__);
    if ($this->childEnabled) {
      #echo 'da';
    } else {

      $mainWP_connected = get_option('ic_mainwp_connected');

      if (empty($mainWP_connected)) {
        $this->settings->renderConnectHolder();
      } else {
        $this->settings->renderInstallHolder();
      }

    }
    do_action('mainwp-pagefooter-extensions', __FILE__);
  }


  public function renderConnectHolder() {
    echo '<form method="POST" class="ic-connect-form">';

    echo '<div class="ic-form-holder">';
    echo '<label for="wpcompress_username">Username:</label>';
    echo '<input type="text" name="wpcompress[username]" id="wpcompress_username" value="" />';
    echo '</div>';

    echo '<div class="ic-form-holder">';
    echo '<label for="wpcompress_password">Password:</label>';
    echo '<input type="password" name="wpcompress[password]" id="wpcompress_password" value="" />';
    echo '</div>';

    echo '<div class="ic-form-holder center">';
    echo '<a href="https://app.wpcompress.com/register" target="_blank">Don\'t have an account yet?</a>';
    echo '<span>OR</span>';
    echo '<a href="https://app.wpcompress.com/forgot-password" target="_blank">Forgot your password?</a>';
    echo '</div>';

    echo '<div class="ic-form-holder center submit-holder">';
    echo '<input type="submit" name="submit" value="Connect" class="button button-primary" />';
    echo '</div>';

    echo '</form>';

    echo '<div class="ic-form-loading" style="display:none;">';
    echo '<span><img src="' . WPIC_URI . 'assets/spinner.gif" /> <h3>Connecting...</h3></span>';
    echo '</div>';
  }


  function on_load_page() {
    add_meta_box(
      'wpic-connect',
      '<i class="fa fa-cog"></i> ' . __('Connect your WP Compress account with MainWP', 'wp-image-compress-extension'),
      array(__CLASS__, 'renderConnectHolder'),
      'mainwp_wpic_connect_settings',
      'normal',
      'core'
    );

    add_meta_box(
      'wpic-install',
      '<i class="fa fa-cog"></i> ' . __('Install WP Compress on the selected sites',
                                        'wp-image-compress-extension') . ' <a href="' . admin_url('admin.php?page=Extensions-Wp-Compress-Main-Wp&action=disconnect') . '" class="button button-primary wps-i-disconnect">Disconnect</a>',
      array('wpic_mainwp_settings', 'renderInstallHolder'),
      'mainwp_wpic_install_settings',
      'normal',
      'core'
    );
  }


  #function settings() {
    #$this->settings->renderConnectSettings();
  #}


  //The function "activate_this_plugin" is called when the main is initialized.
  function activate_this_plugin() {
    global $childEnabled;

    $childEnabled = apply_filters('mainwp-extension-enabled-check', __FILE__);
    if ( ! $childEnabled) {
      return;
    }

    $this->childKey = $childEnabled['key'];
    //Code to initialize your plugin
  }


  public function getChildKey() {
    return $this->childKey;
  }


  public function getChildFile() {
    return $this->childFile;
  }

}


global $MainWPWPCompressExtensionActivator;
$MainWPWPCompressExtensionActivator = new MainWPWPCompressExtensionActivator();