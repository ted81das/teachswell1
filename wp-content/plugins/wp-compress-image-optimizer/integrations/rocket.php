<?php

class wps_ic_rocket extends wps_ic_integrations
{

  public function __construct()
  {
    parent::__construct();
    $this->settings = get_option('wp_rocket_settings');
    $this->plugin = 'WP Rocket';
  }

  public function do_checks()
  {

    if ( isset($this->wps_settings['delay-js']) && $this->wps_settings['delay-js'] == '1' ) {
      if (isset($this->settings['delay_js']) && $this->settings['delay_js'] ) {
        $this->report_conflict($this->plugin, 'Delay JS');
      }
    }


    if ( isset($this->wps_settings['js_defer']) && $this->wps_settings['js_defer'] == '1' ) {
      if ( isset($this->settings['defer_all_js']) && $this->settings['defer_all_js'] ) {
        $this->report_conflict($this->plugin, 'Defer all JS');
      }
    }


    if ( isset($this->wps_settings['lazy']) && $this->wps_settings['lazy'] == '1' ) {
      if ( isset($this->settings['lazyload']) && $this->settings['lazyload'] ) {
        $this->report_conflict($this->plugin, 'Lazy Load');
      }
    }

    if ( isset($this->wps_settings['remove-render-blocking']) && $this->wps_settings['remove-render-blocking'] == '1' ) {
      if ( isset($this->settings['optimize_css_delivery']) && $this->settings['optimize_css_delivery'] ) {
        $this->report_conflict($this->plugin, 'Defer CSS');
      }
    }

  }

}