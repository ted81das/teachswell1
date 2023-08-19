<?php


class wpic_mainwp_ajax
{


  public function __construct()
  {
    add_action('wp_ajax_connect_wpcompress', array(&$this, 'connect_wpcompress'));
    add_action('wp_ajax_wpc_checkSiteConnection', array(&$this, 'checkSiteConnection'));
    add_action('wp_ajax_create_apikey_wpcompress', array(&$this, 'create_apikey_wpcompress'));
    add_action('wp_ajax_connect_apikey_wpcompress', array(&$this, 'connect_apikey_wpcompress'));
  }


  function debug()
  {
    $debug = get_option('wpic_mainwp_debug');
    if ($debug == 'enabled') {
      return true;
    } else {
      return false;
    }
  }


  function checkSiteConnection()
  {
    $output = '';
    $website['url'] = $_POST['url'];
    $website['id'] = $_POST['siteID'];

    $body = false;
    $isActive = wp_remote_get($website['url'] . '?check_mainwp=true&no_cache=' . time() . '&hash=' . md5(mt_rand(999, 9999)), array('timeout' => 30, 'sslverify' => false, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0'));

    if (wp_remote_retrieve_response_code($isActive) == 200) {
      $body = wp_remote_retrieve_body($isActive);
      $body = json_decode($body);
      if ($body->success == true) {
        // Active
        $output .= '<input onclick="mainwp_site_select(this)" type="checkbox" name="selected_sites[]" siteid="' . $website['id'] . '" value="' . $website['id'] . '" id="selected_sites_' . $website['id'] . '" disabled="disabled"/>';
      } else {
        // Not Active
        $output .= '<input onclick="mainwp_site_select(this)" type="checkbox" name="selected_sites[]" siteid="' . $website['id'] . '" value="' . $website['id'] . '" id="selected_sites_' . $website['id'] . '"/>';
      }
    } else {
      $output .= '<input onclick="mainwp_site_select(this)" type="checkbox" name="selected_sites[]" siteid="' . $website['id'] . '" value="' . $website['id'] . '" id="selected_sites_' . $website['id'] . '" disabled="disabled"/>';
      $output .= '<span style="color:#ff0000;margin-left:10px;">Could not communicate with <a href="' . $website['url'] . '?check_mainwp=true&no_cache=' . time() . '">' . $website['url'] . '</a></span>';
    }

    $output .= '<label for="selected_sites_' . $website['id'] . '" style="padding-left:10px;">';

    if (wp_remote_retrieve_response_code($isActive) == 200) {
      if ($body->success == true) {
        $output .= '<span style="color:#7fb100;">' . $website['url'] . '</span>';
      } else {
        $output .= '<span style="color:#333;">' . $website['url'] . '</span>';
      }
    } else {
      $output .= '';
    }

    $output .= '</label>';
    wp_send_json_success($output);
  }


  function connect_apikey_wpcompress()
  {
    global $MainWPWPCompressExtensionActivator;

    $data = array();
    $data['api_connect_key'] = 'true';
    $data['force_ic_connect'] = 'true';
    $data['override_check'] = 'true';
    $data['siteID'] = sanitize_text_field($_POST['siteID']);
    $data['apikey'] = sanitize_text_field($_POST['apikey']);

    $websites = apply_filters('mainwp-getsites', $MainWPWPCompressExtensionActivator->getChildFile(), $MainWPWPCompressExtensionActivator->getChildKey(), null);

    foreach ($websites as $index => $site) {
      if ($site['id'] == $data['siteID']) {
        $selectedSite = $site;
      }
    }

    $call = wp_remote_post($selectedSite['url'] . '?api_connect_key=true&force_ic_connect=true&override_check=true&siteID=' . $data['siteID'] . '&apikey=' . $data['apikey'], array('timeout' => 45, 'redirection' => 5, 'blocking' => true, 'sslverify' => false, 'headers' => array('user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0')));
    
    if (wp_remote_retrieve_response_code($call) == 200) {
      $body = wp_remote_retrieve_body($call);
      $body = json_decode($body);

      wp_send_json_success(array('apikey' => $body->data->apikey, 'siteID' => $body->data->contactID));
    } else {
      wp_send_json_error();
    }
  }


  function create_apikey_wpcompress()
  {
    global $MainWPWPCompressExtensionActivator;

    $options = get_option('ic_mainwp_connected');

    if (!empty($_GET['dbg_mainwp_options'])) {
      var_dump($options);
      var_dump($_POST);
      die();
    }

    $data = array();
    $data['api_create_key'] = 'true';
    $data['uID'] = $options['uID'];
    $data['token'] = $options['token'];
    $data['siteID'] = sanitize_text_field($_POST['siteID']);
    $data['contactPlan'] = sanitize_text_field($_POST['contactPlan']);

    $websites = apply_filters('mainwp-getsites', $MainWPWPCompressExtensionActivator->getChildFile(), $MainWPWPCompressExtensionActivator->getChildKey(), null);

    foreach ($websites as $index => $site) {
      if ($site['id'] == $data['siteID']) {
        $selectedSite = $site;
      }
    }

    $data['siteURL'] = $selectedSite['url'];

    $siteurl = urlencode($data['siteURL']);
    $token = sanitize_text_field($options['token']);
    $plan = $data['contactPlan'];

    if (empty($plan)) {
      $plan = 'shared';
    }

    // Setup URI
    $uri = 'https://keys.wpcompress.com/?action=connect_mu_single&multisite_type=mainwp&token=' . $token . '&plan=' . $plan . '&domain=' . $siteurl . '&hash=' . md5(time()) . '&time_hash=' . time();

    // Verify API Key is our database and user has is confirmed getresponse
    $call = wp_remote_get($uri, array('timeout' => 60, 'sslverify' => false, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0'));


    if (wp_remote_retrieve_response_code($call) == 200) {
      $body = wp_remote_retrieve_body($call);
      $body = json_decode($body);

      if (!empty($body->data->code) && $body->data->code == 'site-user-different') {
        // Popup Site Already Connected
        wp_send_json_error('invalid-api-key');
      }

      if ($body->success == true && $body->data->apikey != '' && $body->data->response_key != '') {
        wp_send_json_success(array('apikey' => $body->data->apikey, 'response_key' => $body->data->response_key, 'siteID' => $body->data->contactID));
      } else {
        wp_send_json_error();
      }
    } else {
      wp_send_json_error();
    }
  }


  function connect_wpcompress()
  {
    $data = array();
    $data['token'] = sanitize_text_field($_POST['token']);

    $call = wp_remote_get('https://app.wpcompress.com?action=api_login&token=' . $data['token'], array('timeout' => 45, 'redirection' => 5, 'sslverify' => false, 'blocking' => true, 'headers' => array(), 'cookies' => array()));

    if (wp_remote_retrieve_response_code($call) == 200) {
      $body = wp_remote_retrieve_body($call);
      $body = json_decode($body);

      if (empty($body->data->token) || $body->success == false) {
        wp_send_json_error();
      }

      update_option('ic_mainwp_connected', array('token' => $body->data->token, 'uID' => $body->data->uID));
      wp_send_json_success(array('token' => $body->data->token, 'uID' => $body->data->uID));
    } else {
      wp_send_json_error();
    }
  }

}