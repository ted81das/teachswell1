<?php

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

spl_autoload_register(function ($class_name) {
  if (strpos($class_name, 'wps_ic_') !== false) {
    $class_name = str_replace('wps_ic_', '', $class_name);
    $class_name = $class_name.'.php';
    $class_name_underscore = str_replace('_', '-', $class_name);
    if (file_exists(WPS_IC_DIR.'integrations/'.$class_name)) {
      include WPS_IC_DIR.'integrations/'.$class_name;
    }
    elseif (file_exists(WPS_IC_DIR.'integrations/'.$class_name_underscore)) {
      include WPS_IC_DIR.'integrations/'.$class_name_underscore;
    }
  }
});


class wps_ic_integrations
{

  public $wps_settings;

  public function __construct()
  {
    $this->active_plugins = array();
    $this->wps_settings = get_option(WPS_IC_SETTINGS);
    $this->notices = new wps_ic_notices;
  }

  public function init()
  {
    //$this->get_active_plugins();
    //$this->do_checks();
  }


  public function get_active_plugins()
  {
    /*
    $classes = get_declared_classes();
    foreach ($classes as $class){
      echo $class.'<br>';
    }
    die();
*/

    //WP Rocket
    if (function_exists('get_rocket_option')) {
      $this->active_plugins = array_merge($this->active_plugins, ['rocket' => new wps_ic_rocket]);
    }

    //Smush
    if (class_exists('\Smush\Core\Settings')) {
      $this->active_plugins = array_merge($this->active_plugins, ['smush' => new wps_ic_smush]);
    }

    //WP Optimize
    if (class_exists('WP_Optimize')) {
      $this->active_plugins = array_merge($this->active_plugins, ['wpoptimize' => new wps_ic_wpoptimize()]);
    }

    //Autoptimize
    if (is_plugin_active('autoptimize/autoptimize.php')) {
      $this->active_plugins = array_merge($this->active_plugins, ['autoptimize' => new wps_ic_autoptimize()]);
    }

    //Perfmatters
    if (function_exists('perfmatters_version_check')) {
      $this->active_plugins = array_merge($this->active_plugins, ['perfmatters' => new wps_ic_perfmatters()]);
    }
  }


  public function do_checks()
  {
    foreach ($this->active_plugins as $name => $plugin) {
      $plugin->do_checks();
    }
  }


  public function report_conflict($plugin, $setting)
  {
    $msg = $setting . ' setting in ' . $plugin . ' is in conflict with ours. Please disable.';
    $this->notices->show_notice('Settings conflict detected', $msg);
  }

}