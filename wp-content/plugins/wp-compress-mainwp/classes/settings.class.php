<?php


class wpic_mainwp_settings
{


  public function __construct()
  {
    add_filter('mainwp-getsubpages-sites', array(&$this, 'managesites_subpage'), 10, 1);
  }


  function managesites_subpage($subPage)
  {

    $subPage[] = array('plugin' => __FILE__, 'api' => $this->plugin_handle, 'mainwp' => true, 'callback' => array(&$this, 'renderPage'), 'apiManager' => true);

    return $subPage;
  }


  public static function renderPage()
  {
    global $MainWPWPCompressExtensionActivator;
    echo 'da';
  }


  public function size_format_custom($bytes, $decimals)
  {
    $quant = array('TB' => 1000 * 1000 * 1000 * 1000, 'GB' => 1000 * 1000 * 1000, 'MB' => 1000 * 1000, 'KB' => 1000, 'B' => 1,);

    if (0 === $bytes) {
      return number_format_i18n(0, $decimals) . ' B';
    }

    foreach ($quant as $unit => $mag) {
      if (doubleval($bytes) >= $mag) {
        return number_format_i18n($bytes / $mag, $decimals) . ' ' . $unit;
      }
    }

    return false;
  }


  public function renderInstallHolder()
  {
    global $MainWPWPCompressExtensionActivator;
    $websites = apply_filters('mainwp-getsites', $MainWPWPCompressExtensionActivator->getChildFile(), $MainWPWPCompressExtensionActivator->getChildKey(), null);

    if (!empty($_GET['unlink'])) {
      delete_option('ic_mainwp_connected');
      echo '<script type="text/javascript">';
      echo 'setTimeout(function(){';
      echo 'window.location = "' . admin_url('admin.php?page=Extensions-Wp-Compress-Mainwp') . '";';
      echo '}, 200);';
      echo '</script>';

      return;
    }

    $mainWP_connected = get_option('ic_mainwp_connected');
    $credits = 0;

    if (!empty($_GET['dbg_mainwp_options'])) {
      var_dump($mainWP_connected);
      var_dump($_POST);
      die();
    }


    if (!empty($mainWP_connected['token'])) {
      $call = wp_remote_get('https://keys.wpcompress.com/?action=get_mainwp_credits&token=' . $mainWP_connected['token'] . '&v=2', array('sslverify' => false, 'timeout' => 30));
      if (wp_remote_retrieve_response_code($call) == 200) {
        $cleanBody = wp_remote_retrieve_body($call);
        $body = json_decode($cleanBody);
        $body = $body->data;

        $quotaType = $body->quotaType;
        $credits = $body->availableCredits;

        if ($quotaType == 'bandwidth') {
          $multiplier = 1;
          $multiplierGB = 1000*1000*1000;
          $packageArray = array(2, 5, 10, 50, 100, 500);
          $unit = 'GB';
          $creditsFormatted = $this->size_format_custom($credits, 0);
          $plain_credits = $credits;
        } else {
          $multiplierGB = 1;
          $multiplier = 1000;
          $packageArray = array(100, 250, 500, 1000, 2500);
          $plain_credits = $credits;
          $unit = '';
          $creditsFormatted = number_format($credits, 0);
        }


      }

      echo '<div class="wps-ic-mainwp-settings">';
      echo '<input type="hidden" name="select_by" id="select_by" value="site" />';
      echo '<input type="checkbox" checked="checked" value="1" id="chk_activate_plugin" style="display:none;"/>';
      echo '<input type="checkbox" checked="checked" value="2" id="chk_overwrite" style="display:none;"/>';

      echo '<div class="wps-ic-mainwp-box" style="margin-bottom:20px !important;">';
      echo '<div class="wps-ic-box-header">';

      if (!empty($_GET['dbgCredits'])) {
        echo print_r($quotaType, true);
        echo print_r($cleanBody, true);
        echo print_r($body, true);
      }

      echo '<h3>Set a Monthly Quota - You have ' . $creditsFormatted . ' credits remaining <a href="https://wpcompress.com/pricing" class="button button-primary buy-more-credits" target="_blank" style="margin-left:10px !important;">Get More Credits</a></h3>';
      echo '</div>';
      echo '<div class="wps-ic-box-content">';
      echo '<p>Please select the desired monthly quota to apply to each linked website. You may change this later for each individual website.</p>';
      echo '<div class="inline-select">
          <input type="hidden" name="contact[plan]" class="plan-change" value="shared">';
      echo '<div class="option selected" data-plan="shared" data-credits="shared">Credits Sharing</div>';

      foreach ($packageArray as $pid => $package_credits) {

        #echo $package_credits*$multiplierGB.',';
        #echo $plain_credits.'|';

        $css = '';
        if ($package_credits*$multiplierGB > $plain_credits) {
          $css = 'disabled';
        }

       # echo '<div class="option ' . $css . '" data-plan="' . $pid . '" data-credits="' . $package_credits * $multiplier . '">' . number_format($package_credits * $multiplier, 0) . $unit . '</div>';

      }

      echo '</div>';

      echo '<div class="clear" style=""></div>';

      echo '</div>';
      echo '</div>';

      echo '<div class="wps-ic-mainwp-box" style="margin-bottom:20px !important;">';
      echo '<div class="wps-ic-box-header">';
      echo '<h3>Select sites for installation <a href="#" class="button button-primary sites_list_all" style="margin-left:10px !important;">Select All</a></h3>';
      echo '</div>';
      echo '<div class="wps-ic-box-content">';
      echo '<span style="font-weight:normal;font-size:13px;margin-bottom:10px;display:inline-block;">&nbsp;If you\'d like to further change your quota after linking, add credits or configure compression you may do so in the WP Compress portal.</span>';
      echo '<div id="selected_sites" class="selected_sites_wrapper" is-staging="0">';
      if (!empty($websites)) {
        foreach ($websites as $ID => $website) {
          echo '<div title="' . $website['url'] . '" class="mainwp_selected_sites_item mainwp-padding-5" id="ic-site-id-' . $website['id'] . '">';

          echo '<div class="main_wp_checksite mainwp_check_site_container_' . $website['id'] . '" data-site-id="' . $website['id'] . '" data-url="' . $website['url'] . '">';
          echo '<span style="color:#333;">Checking...' . $website['url'] . '</span>';
          echo '</div>';

          /*
           *
          $body     = false;
          $isActive = wp_remote_get( $website['url'] . '?check_mainwp=true&no_cache=' . time() . '&hash=' . md5( mt_rand( 999, 9999 ) ), array( 'timeout' => 30, 'sslverify' => false, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0' ) );

          if ( wp_remote_retrieve_response_code( $isActive ) == 200 ) {
            $body = wp_remote_retrieve_body( $isActive );
            $body = json_decode( $body );
            if ( $body->success == true ) {
              // Active
              echo '<input onclick="mainwp_site_select(this)" type="checkbox" name="selected_sites[]" siteid="' . $website['id'] . '" value="' . $website['id'] . '" id="selected_sites_' . $website['id'] . '" disabled="disabled"/>';
            } else {
              // Not Active
              echo '<input onclick="mainwp_site_select(this)" type="checkbox" name="selected_sites[]" siteid="' . $website['id'] . '" value="' . $website['id'] . '" id="selected_sites_' . $website['id'] . '"/>';
            }
          } else {
            echo 'Could not communicate.';
          }

          echo '<label for="selected_sites_' . $website['id'] . '" style="padding-left:10px;">';

          if ( wp_remote_retrieve_response_code( $isActive ) == 200 ) {
            if ( $body->success == true ) {
              echo '<span style="color:#7fb100;">' . $website['url'] . '</span>';
            } else {
              echo '<span style="color:#333;">' . $website['url'] . '</span>';
            }
          }

          echo '</label>';
          */

          echo '</div>';
        }
      }
      echo '</div>';
      echo '</div>';
      echo '</div>';

      echo '<div class="clear" style=""></div>';

      echo '<div class="wps-ic-mainwp-box bulk-status" style="display:none;margin-bottom:20px !important;">';
      echo '<div class="wps-ic-box-header">';
      echo '<h3>Bulk Process Status</h3>';
      echo '</div>';
      echo '<div class="wps-ic-box-content">';
      echo '<div id="wpcompress-queue" style="display:none;"><ul></ul></div>';
      echo '</div>';
      echo '</div>';

      echo '<div id="plugintheme-installation-queue" style="display:none;"></div>';
      echo '<div style="display:none;"><input type="checkbox" value="1" checked="checked" id="chk_activate_plugin" class="hidden"></div>';
      echo '<div style="display:none;"><input type="checkbox" value="2" id="chk_overwrite" class="hidden"></div>';

      echo '<a href="#" class="wp-ic-activate-all button button-primary">Install & Link Websites</a>';
      echo '<a href="' . admin_url('admin.php?page=Extensions-Wp-Compress-Mainwp&unlink=true') . '" class="button button-primary" style="float:right;">Unlink Main Account</a>';
      echo '</div>';
    }
  }


  public function renderConnectHolder()
  {
    echo '<div class="ic-connect-form-outer">';

    echo '<div class="ic-form-error" style="display:none;">';
    echo '<span><h3>Your username or password are not correct.</h3></span>';
    echo '</div>';

    echo '<form method="POST" class="ic-connect-form">';

    echo '<div class="ic-form-holder">';
    echo '<label for="wpcompress_token">Token:</label>';
    echo '<input type="text" name="wpcompress[token]" id="wpcompress_token" value="" />';
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

    echo '</div>';
  }


  public function renderConnectSettings()
  {
    global $MainWPWPCompressExtensionActivator;

    if (!empty($_GET['reset'])) {
      delete_option('ic_mainwp_connected');
      die('reset');
    }

    $mainWP_connected = get_option('ic_mainwp_connected');
    do_action('mainwp-pageheader-extensions', __FILE__);
    var_dump($mainWP_connected);

    if (empty($mainWP_connected)) {
      do_action('mainwp_do_meta_boxes', 'mainwp_wpic_connect_settings');
    } else {
      do_action('mainwp_do_meta_boxes', 'mainwp_wpic_install_settings');
    }

    do_action('mainwp-pagefooter-extensions', __FILE__);
  }


}