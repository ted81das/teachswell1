<?php

class wps_ic_perfmatters extends wps_ic_integrations
{

  public function __construct()
  {
    parent::__construct();
    $this->settings = get_option( 'perfmatters_options' );
    $this->plugin = 'Autoptimize';
  }

  public function do_checks()
  {

    if ( isset($this->wps_settings['delay-js']) && $this->wps_settings['delay-js'] == '1' ) {
      if (isset($this->settings['assets']['delay_js']) && $this->settings['assets']['delay_js'] ) {
        $this->report_conflict($this->plugin, 'Delay JS');
      }
    }


    if ( isset($this->wps_settings['js_defer']) && $this->wps_settings['js_defer'] == '1' ) {
      if ( isset($this->settings['assets']['defer_js']) && $this->settings['assets']['defer_js'] ) {
        $this->report_conflict($this->plugin, 'Defer all JS');
      }
    }


    if ( isset($this->wps_settings['lazy']) && $this->wps_settings['lazy'] == '1' ) {
      if ( isset($this->settings['lazyload']['lazy_loading']) && $this->settings['lazyload']['lazy_loading'] ) {
        $this->report_conflict($this->plugin, 'Lazy Load');
      }
    }

  }


}