<?php

global $ic_running;
include 'debug.php';
include 'defines.php';

include 'addons/cdn/cdn-rewrite.php';
include 'addons/legacy/compress.php';

//TRAITS
include 'traits/excludes.php';

//CUSTOM_INCLUDE_HERE

spl_autoload_register(function ($class_name) {
  if (strpos($class_name, 'wps_ic_') !== false) {
    $class_nameBase = str_replace('wps_ic_', '', $class_name);
    $class_name = $class_nameBase . '.class.php';
    $class_name_underscore = str_replace('_', '-', $class_name);
    if (file_exists(WPS_IC_DIR . 'classes/' . $class_name)) {
      include 'classes/' . $class_name;
    } else if (file_exists(WPS_IC_DIR . 'classes/' . $class_name_underscore)) {
      include 'classes/' . $class_name_underscore;
    } else {
      if (file_exists(WPS_IC_DIR . 'addons/' . $class_nameBase . '/' . $class_name)) {
        include 'addons/' . $class_nameBase . '/' . $class_name;
      }
    }
  }
});


class wps_ic
{

  public static $slug;
  public static $version;

  public static $api_key;
  public static $response_key;

  public static $settings;
  public static $zone_name;
  public static $quality;
  public static $options;

  public static $js_debug;
  public static $debug;
  public static $local;
  public static $media_lib_ajax;
  private static $accountStatus;
  public $notices;
  public $enqueues;
  public $templates;
  public $menu;
  public $ajax;
  public $media_library;
  public $compress;
  public $controller;
  public $log;
  public $bulk;
  public $queue;
  public $stats;
  public $cdn;
  public $mu;
  public $mainwp;
  protected $excludes_class;

  /**
   * Our main class constructor
   */
  public function __construct()
  {
    global $wps_ic;
    self::debug_log('Constructor');

    // Basic plugin info
    self::$slug = 'wpcompress';
    self::$version = '6.10.24';
    $wps_ic = $this;

    if ((!empty($_GET['wpc_visitor_mode']) && $_GET['wpc_visitor_mode'] == true)) {
      //It has to be here, init() is too late
      new wps_ic_visitor_mode();
    }

    $cache = new wps_ic_cache();
    $cache->purgeHooks();
  }

  /**
   * Write Debug Log
   *
   * @param $message
   *
   * @return void
   */
  public static function debug_log($message)
  {
    if (get_option('ic_debug') == 'log') {
      $log_file = WPS_IC_DIR . 'debug-log-' . date('d-m-Y') . '.txt';
      $time = current_time('mysql');

      if (!file_exists($log_file)) {
        fopen($log_file, 'a');
      }

      $log = file_get_contents($log_file);
      $log .= '[' . $time . '] - ' . $message . "\r\n";
      file_put_contents($log_file, $log);
      fclose($log_file);
    }
  }

  static function generate_critical_cron()
  {
    $criticalCSS = new wps_criticalCss;
    $criticalCSS->generate_critical_cron();
  }

  public static function onUpgrade_force_regen()
  {
    delete_option('wps_ic_gen_hp_url');
  }

  /***
   * Get file size from WP filesystem
   *
   * @param $imageID
   *
   * @return string
   */
  public static function get_wp_filesize($imageID)
  {
    $filepath = get_attached_file($imageID);
    $filesize = filesize($filepath);
    $filesize = wps_ic_format_bytes($filesize, null, null, false);

    return $filesize;
  }

  public static function getAccountQuota($data, $quotaType)
  {
    $proSite = get_option('wps_ic_prosite');
    $options = get_option(WPS_IC_OPTIONS);

    if (empty($data) || empty($options['response_key'])) {
      return ['local' => 0, 'live' => 0, 'liveQuota' => 0, 'localQuota' => 0, 'liveShared' => 0, 'localShared' => 0];
    }

    $liveShared = $data->account->liveShared;
    $localShared = $data->account->localShared;

    if ($data->account->quotaType == 'requests' || $data->account->quotaType == 'requests-combined') {
      // Requests
      $liveCredits = $data->liveCredits->formatted . ' Requests Left';
      $liveQuota = $data->liveCredits->value;

      if (!empty($proSite) && $proSite == true) {
        $localCredits = 'Unlimited';
        $localQuota = 'Unlimited';
      } else {
        $localCredits = $data->liveCredits->formatted . ' Images Left';
        $localQuota = $data->liveCredits->value;
      }
    } else {
      // Bandwidth
      $liveCredits = $data->liveCredits->formatted->number . ' ' . $data->liveCredits->formatted->unit . ' Left';
      $liveQuota = $data->liveCredits->value;

      if (!empty($proSite) && $proSite == true) {
        $localCredits = 'Unlimited';
        $localQuota = 'Unlimited';
      } else {
        $localCredits = $data->localCredits->formatted->number . ' ' . $data->localCredits->formatted->unit . ' Left';
        $localQuota = $data->localCredits->value;
      }
    }

    if (empty($proSite)) {
      if ($localShared) {
        $localCredits = 'Shared Credits';
        $localCredits = 'Shared';
      }

      if ($liveShared) {
        $liveShared = 'Shared Credits';
        $liveCredits = 'Shared';
      }
    } else {
      $localCredits = 'Unlimited &infin;';
      $localCredits = 'Unlimited &infin;';
      $liveShared = 'Unlimited &infin;';
      $liveCredits = 'Unlimited &infin;';
    }

    return ['local' => $localCredits, 'live' => $liveCredits, 'liveQuota' => $liveQuota, 'localQuota' => $localQuota, 'liveShared' => $liveShared, 'localShared' => $localShared];
  }

  /**
   * Retrieve account information from memory IF it's in memory
   *
   * @param $force
   *
   * @return false|mixed|object
   */
  public static function getAccountStatusMemory($force = false)
  {
    if (!empty($_GET['refresh']) || $force) {
      delete_transient('wps_ic_account_status');
    }

    $transient_data = get_transient('wps_ic_account_status');

    if (!empty($_GET['debugAccountStatus'])) {
      var_dump($transient_data);
    }

    if (!$transient_data || empty($transient_data)) {
      self::debug_log('Not In Memory');
      self::$accountStatus = self::check_account_status();

      if (!empty($_GET['debugAccountStatus'])) {
        var_dump('----');
        var_dump(self::$accountStatus);
      }

      return self::$accountStatus;
    } else {
      self::debug_log('In Memory');
      self::debug_log(print_r($transient_data, true));

      return $transient_data;
    }
  }

  public static function check_account_status($ignore_transient = false)
  {
    self::debug_log('Check Account Status');

    if (!empty($_GET['refresh']) || $ignore_transient) {
      delete_transient('wps_ic_account_status');
    }

    $transient_data = get_transient('wps_ic_account_status');
    if (!empty($transient_data) && $transient_data !== 'no-site-found') {
      self::debug_log('Check Account Status - In Transient');

      return $transient_data;
    }

    self::debug_log('Check Account Status - Call API');

    $options = get_option(WPS_IC_OPTIONS);
    $settings = get_option(WPS_IC_SETTINGS);

    /**
     * Site is not connected
     */
    if (!$options || empty($options['api_key'])) {
      $data = array();
      $data['account']['allow_local'] = false;
      $data['account']['allow_live'] = false;
      $data['account']['allow_cname'] = false;
      $data['account']['type'] = 'shared';
      $data['account']['projected_flag'] = 1;

      $data['account'] = (object)$data['account'];

      $data['bytes']['leftover'] = '0';
      $data['bytes']['cdn_bandwidth'] = '0';
      $data['bytes']['cdn_requests'] = '0';
      $data['bytes']['bandwidth_savings'] = '0';
      $data['bytes']['bandwidth_savings_bytes'] = '0';
      $data['bytes']['original_bandwidth'] = '0';
      $data['bytes']['projected'] = '0';
      // Local
      $data['bytes']['local_requests'] = '0';
      $data['bytes']['local_savings'] = '0';
      $data['bytes']['local_original'] = '0';
      $data['bytes']['local_optimized'] = '0';

      $data['bytes'] = (object)$data['bytes'];

      $data['formatted']['leftover'] = '0 MB';
      $data['formatted']['cdn_bandwidth'] = '0 MB';
      $data['formatted']['cdn_requests'] = '0';
      $data['formatted']['bandwidth_savings'] = '0 MB';
      $data['formatted']['bandwidth_savings_bytes'] = '0 MB';
      $data['formatted']['package_without_extra'] = '0';
      $data['formatted']['original_bandwidth'] = '0 MB';
      $data['formatted']['projected'] = '0 MB';

      // Local
      $data['formatted']['local_requests'] = '0';
      $data['formatted']['local_savings'] = '0 MB';
      $data['formatted']['local_original'] = '0 MB';
      $data['formatted']['local_optimized'] = '0 MB';

      $data['formatted'] = (object)$data['formatted'];

      $data = (object)$data;

      $body = ['success' => true, 'data' => $data];
      $body = (object)$body;

      return $data;
    }


    // Check privileges
    $call = wp_remote_get(WPS_IC_KEYSURL . '?action=get_account_status_v6&apikey=' . $options['api_key'] . '&range=month&hash=' . md5(mt_rand(999, 9999)), ['timeout' => 30, 'sslverify' => false, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0']);

    if (wp_remote_retrieve_response_code($call) == 200) {
      $body = wp_remote_retrieve_body($call);
      $body = json_decode($body);
      $body = $body->data;

      if (!empty($body) && $body !== 'no-site-found') {
        // Vars
        $account_status = $body->account->status;
        $allow_local = $body->account->allowLocal;
        $allow_live = $body->account->allowLive;
        $quota_type = $body->account->quotaType;
        $proSite = $body->account->proSite;

        // If pro site,raise flag
        if (!empty($proSite) && $proSite == '1') {
          update_option('wps_ic_prosite', true);
        } else {
          update_option('wps_ic_prosite', false);
        }

        // Account Status Transient
        set_transient('wps_ic_account_status', $body, 120);

        // Allow Local
        update_option('wps_ic_allow_local', $allow_local);
        update_option('wps_ic_allow_live', $allow_live);

        // Is account active?
        if ($account_status != 'active') {
          $settings['live-cdn'] = '0'; // TODO: Fix
          update_option(WPS_IC_SETTINGS, $settings);
        }

        return $body;
      } else {
        $options = get_option(WPS_IC_OPTIONS);
        $options['api_key'] = '';
        $options['response_key'] = '';
        $options['orp'] = '';
        $options['regExUrl'] = '';
        $options['regexpDirectories'] = '';
        update_option('WPS_IC_OPTIONS', $options);
        return false;
      }
    } else {
      $data = array();
      $data['account']['allow_local'] = false;
      $data['account']['allow_live'] = false;
      $data['account']['allow_cname'] = false;
      $data['account']['type'] = 'shared';
      $data['account']['projected_flag'] = 1;

      $data['account'] = (object)$data['account'];

      $data['bytes']['leftover'] = '0';
      $data['bytes']['cdn_bandwidth'] = '0';
      $data['bytes']['cdn_requests'] = '0';
      $data['bytes']['bandwidth_savings'] = '0';
      $data['bytes']['bandwidth_savings_bytes'] = '0';
      $data['bytes']['original_bandwidth'] = '0';
      $data['bytes']['projected'] = '0';

      // Local
      $data['bytes']['local_requests'] = '0';
      $data['bytes']['local_savings'] = '0';
      $data['bytes']['local_original'] = '0';
      $data['bytes']['local_optimized'] = '0';

      $data['bytes'] = (object)$data['bytes'];

      $data['formatted']['leftover'] = '0';
      $data['formatted']['cdn_bandwidth'] = '0';
      $data['formatted']['cdn_requests'] = '0';
      $data['formatted']['bandwidth_savings'] = '0';
      $data['formatted']['bandwidth_savings_bytes'] = '0';
      $data['formatted']['package_without_extra'] = '0';
      $data['formatted']['original_bandwidth'] = '0';
      $data['formatted']['projected'] = '0';

      // Local
      $data['formatted']['local_requests'] = '0';
      $data['formatted']['local_savings'] = '0 MB';
      $data['formatted']['local_original'] = '0 MB';
      $data['formatted']['local_optimized'] = '0 MB';

      $data['formatted'] = (object)$data['formatted'];
      $data = (object)$data;

      $body = ['success' => true, 'data' => $data];
      $body = (object)$body;

      // Account Status Transient
      set_transient('wps_ic_account_status', $body->data, 30);

      update_option('wps_ic_allow_local', false);

      return $body->data;
    }
  }

  public static function mu_activation($plugin, $network_wide)
  {
    if (is_multisite() && $network_wide) {
      // It's a multisite and network install
      #wp_safe_redirect(admin_url('options-general.php?page=' . $wps_ic::$slug . '-mu'));
    }
  }

  /**
   * Activation of the plugin
   */
  public static function activation($networkwide)
  {
    // Setup config file
    $config = new wps_ic_config();
    $config->generateCacheConfig();

    // Add WP_CACHE to wp-config.php
    $htaccess = new wps_ic_htaccess();
    $htaccess->setWPCache(true);
    $htaccess->setAdvancedCache();

    // Setup inline JS Defaults
    $wpc_excludes = get_option('wpc-inline');
    $wpc_excludes['inline_js'] = explode(',', "jquery.min,adaptive,jquery-migrate");
    update_option('wpc-inline', $wpc_excludes);

    // Load Cache Class
    $cacheLogic = new wps_ic_cache();

    // Remove generateCriticalCSS Options
    delete_option('wps_ic_gen_hp_url');

    // Remove & Purge Cache Files for home directory (that's all pages)
    $cacheLogic::removeHtmlCacheFiles(0);

    // Preload the home page only
    $cacheLogic::preloadPage(0);

    if (is_multisite()) {
      //wp_safe_redirect(admin_url('options-general.php?page=wpcompress-mu'));
      /*
          foreach (get_sites(['fields' => 'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            //do your specific thing here...
            restore_current_blog();
          }*/
    } else {
      $options = get_option(WPS_IC_OPTIONS);
      $site = site_url();

      if (!$options || empty($options['api_key'])) {
        return;
      } else {

        // Setup Default Options
        $options = new wps_ic_options();
        $settings = get_option(WPS_IC_SETTINGS);
        if (!$settings || empty($settings)) {
          $options->set_defaults();
        }

        if (!file_exists(WPS_IC_DIR . 'cache')) {
          // Folder does not exist
          mkdir(WPS_IC_DIR . 'cache', 0755);
        } else {
          // Folder exists
          if (!is_writable(WPS_IC_DIR . 'cache')) {
            chmod(WPS_IC_DIR . 'cache', 0755);
          }
        }
      }
    }
  }

  /**
   * Deactivation of the plugin
   * Notify our API the plugin is disconnected
   */
  public static function deactivation()
  {
    // Remove HtAccess Rules
    $htaccess = new wps_ic_htaccess();
    $htaccess->removeHtaccessRules();

    // Add WP_CACHE to wp-config.php
    $htaccess->setWPCache(false);
    $htaccess->removeAdvancedCache();

    // Remove Stats Transients
    delete_transient('wps_ic_live_stats');
    delete_transient('wps_ic_local_stats');

    // Remove generateCriticalCSS Options
    delete_option('wps_ic_gen_hp_url');


    // Multisite Settings
    $settings = get_option(WPS_IC_MU_SETTINGS);
    $settings['hide_compress'] = 0;
    update_option(WPS_IC_MU_SETTINGS, $settings);

    // Remove from active on API
    $options = get_option(WPS_IC_OPTIONS);
    $site = site_url();
    $apikey = $options['api_key'];

    $newOptions = $options;
    $newOptions['regExUrl'] = '';
    $newOptions['regexpDirectories'] = '';
    update_option(WPS_IC_OPTIONS, $newOptions);


    // Setup URI
    $uri = WPS_IC_KEYSURL . '?action=disconnect&apikey=' . $apikey . '&site=' . urlencode($site);

    // Verify API Key is our database and user has is confirmed getresponse
    $get = wp_remote_get($uri, ['timeout' => 60, 'sslverify' => false, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0']);
  }

  /**
   * Popup on plugin deactivation button
   * @return void
   */
  public static function deactivate_script()
  {
    wp_enqueue_style('wp-pointer');
    wp_enqueue_script('wp-pointer');
    wp_enqueue_script('utils'); // for user settings
    ?>
      <script type="text/javascript">
          function deactivateButton() {
              var row = jQuery('tr[data-slug="wp-compress-image-optimizer"]');
              var span_deactivate = jQuery('span.deactivate', row);
              var link = jQuery('a', span_deactivate);
              var pointer = '';

              jQuery(link).on('click', function (e) {
                  e.preventDefault();
                  jQuery('.wp-pointer').hide();

                  pointer = jQuery(this).pointer({
                      content: '<h3>Deactivating may cause...</h3><p><ul style="padding:0px 15px;margin:0px 10px;list-style:disc;">' + '<li>Significantly higher bounce rates</li>' + '<li>Slow loading images for incoming visitors</li>' + '<li>Backups removed from our cloud</li>' + '<li>Our team crying that you’ve left... <?php echo '<img src="' . WPS_IC_URI . '/assets/crying.png" style="width:19px;" />';?></li>' + '</ul><p>If you’ve locally optimized images they’ll stay in the current state upon deactivating. Live optimization will stop immediately.</p><p>If you have any questions or issues, please visit our <a href="https://help' + '.wpcompress.com/en-us/" target="_blank">helpdesk</a>.</p><div' + ' style="padding:15px;"><a id="wps-ic-leave-active" class="button ' + 'button-primary" href="#">Leave active</a> <a id="everything" class="button ' + 'button-secondary" ' + 'href="' + jQuery(
                          link).attr('href') + '">Deactivate Anyway</a></div></p>',
                      position: {
                          my: 'left top',
                          at: 'left top',
                          offset: '0 0'
                      },
                      close: function () {
                          //
                      }
                  }).pointer('open');

                  jQuery('#wps-ic-leave-active', '.wp-pointer-content').on('click', function (e) {
                      e.preventDefault();
                      jQuery(pointer).pointer('close');
                      return false;
                  });

                  jQuery('.wp-pointer-buttons').hide();

                  return false;
              });
          }

          function reconnectButton() {
              var row = jQuery('tr[data-slug="wp-compress-image-optimizer"]');
              var span_deactivate = jQuery('span.wps-ic-reconnect', row);
              var link = jQuery('a', span_deactivate);
              var pointer = '';

              jQuery(link).on('click', function (e) {
                  e.preventDefault();
                  jQuery('.wp-pointer').hide();

                  pointer = jQuery(this).pointer({
                      content: '<h3>Are You Sure...</h3><p>If you continue, you will need your API Key in order to Reconnect the plugin.</><p>If you have any questions or issues, please visit our <a href="https://help' + '.wpcompress.com/en-us/" target="_blank">helpdesk</a>.</p><div' + ' ' + 'style="padding:15px;"><a id="wps-ic-leave-active" class="button ' + 'button-primary" href="#">Leave Connected</a> <a id="wps-ic-reconnect-confirm" class="button ' + 'button-secondary wps-ic-reconnect-confirm" ' + 'href="' + jQuery(
                          link).attr('href') + '">Reconnect Anyway</a></div></p>',
                      position: {
                          my: 'left top',
                          at: 'left top',
                          offset: '0 0'
                      },
                      close: function () {
                          //
                      }
                  }).pointer('open');

                  jQuery('#wps-ic-reconnect-confirm', '.wp-pointer-content').on('click', function (e) {
                      e.preventDefault();
                      jQuery.post(ajaxurl, {action: 'wps_ic_remove_key'}, function (response) {
                          if (response.success) {
                              window.location.reload();
                          }
                      });
                      return false;
                  });

                  jQuery('#wps-ic-leave-active', '.wp-pointer-content').on('click', function (e) {
                      e.preventDefault();
                      jQuery(pointer).pointer('close');
                      return false;
                  });

                  jQuery('.wp-pointer-buttons').hide();

                  return false;
              });
          }

          jQuery(document).ready(function ($) {
              deactivateButton();
              reconnectButton();
          });
      </script><?php
  }

  /**
   * WP Init helper
   */
  public function init()
  {

    // Raise memory limit
    ini_set('memory_limit', '1024M');

    /**
     * Force Show WP Compress
     */
    if (!empty($_GET['show_optimizer'])) {
      $settings = get_option(WPS_IC_SETTINGS);
      $settings['hide_compress'] = '0';
      update_option(WPS_IC_SETTINGS, $settings);
    }

    if (!empty($_GET['override_version'])) {
      self::$version = mt_rand(100, 999);
    }

    if (is_admin()) {
      if (!empty($_GET['remove_key'])) {
        $options = get_option(WPS_IC_OPTIONS);
        $options['api_key'] = '';
        $options['response_key'] = '';
        $options['orp'] = '';
        $options['regExUrl'] = '';
        $options['regexpDirectories'] = '';
        update_option(WPS_IC_OPTIONS, $options);
      }
    }


    // Get Options
    $this::$js_debug = get_option('wps_ic_js_debug');
    $this::$settings = get_option(WPS_IC_SETTINGS);
    $this::$options = get_option(WPS_IC_OPTIONS);

    if (empty($this::$settings)) {
      $this::$settings = [];
    }

    if (empty($this::$options)) {
      $this::$options = [];
    }

    // Todo: make it pretty
    /**
     * Runs only once on plugin first activation
     */
    if (!empty($this::$options)) {
      if (!get_option('wps_ic_gen_hp_url') || !empty($_GET['forceCriticalHP'])) {
        $this->generateHomePageURL();
        update_option('wps_ic_gen_hp_url', 'true');
      }
    }


    //CUSTOM_CONSTRUCT_HERE


    if (!empty($_GET['ignore_ic'])) {
      return;
    }


    /***
     * Local Remote Hooks
     * TODO: Make Pretty
     */
    if (!empty($_GET['getAllImages'])) {
      include_once 'addons/local/delivery.php';
      $delivery = new wpc_ic_delivery();
      wp_send_json_success($delivery->getImageList());
      die();
    }

    if (!empty($_POST['getImageByID']) || !empty($_GET['getImageByID'])) {
      include_once 'addons/local/delivery.php';
      $delivery = new wpc_ic_delivery();
      $delivery->getImageByID();
      die();
    }


    if (!empty($_POST['deliverSingleImage']) || !empty($_GET['deliverSingleImage'])) {
      include_once 'addons/local/delivery.php';
      $delivery = new wpc_ic_delivery('single');
      $delivery->compress();
      die();
    }

    if (!empty($_GET['deliverBulk']) && $_GET['deliverBulk'] == 'true') {
      include_once 'addons/local/delivery.php';
      $delivery = new wpc_ic_delivery('multi');
      $delivery->compress();
      die();
    }

    if (!empty($_GET['deliverBulk']) && $_GET['deliverBulk'] == 'false') {
      include_once 'addons/local/delivery.php';
      $delivery = new wpc_ic_delivery('single');
      $delivery->compress();
      die();
    }

    if (!empty($_GET['restoreImage'])) {
      include_once 'addons/local/delivery.php';
      $delivery = new wpc_ic_delivery();
      $delivery->restoreImage();
      die();
    }

    if (!empty($_GET['endBulk'])) {
      set_transient('wps_ic_bulk_done', true, 60);
      delete_option('wps_ic_bulk_process');
      delete_transient('wps_ic_stuck_check');
      die();
    }

    if (!empty($_POST['deliverImages']) || !empty($_GET['deliverImages'])) {
      if (!empty($_POST['debug']) || !empty($_GET['debug'])) {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
      }

      include_once 'addons/local/delivery.php';
      $delivery = new wpc_ic_delivery();
      $delivery->compress();
      die();
    }

    if (!empty($_POST['restoreImages']) || !empty($_GET['restoreImages'])) {
      include_once 'addons/local/delivery.php';
      $delivery = new wpc_ic_delivery();
      $delivery->restore();
      die();
    }
    /***
     * End Local Remote Hooks
     */


    if (!empty($_GET['deliver_css'])) {
      /**
       * Check API Key in Site is Matching ApiKey on Critical API
       */
      $apikey = sanitize_text_field($_GET['apikey']);

      if (is_multisite()) {
        $current_blog_id = get_current_blog_id();
        switch_to_blog($current_blog_id);
        $storedApiKey = get_option(WPS_IC_OPTIONS)['api_key'];
      } else {
        $storedApiKey = get_option(WPS_IC_OPTIONS)['api_key'];
      }

      if (empty($apikey) || $apikey != $storedApiKey) {
        die('Bad Api Key');
      }


      $criticalCSS = new wps_criticalCss();
      if (!empty($_GET['url']) && !empty($_GET['desktop'])) {
        $criticalCSS->saveCriticalCss_fromBackground($criticalCSS->url_key_class->setup($_GET['url']), $_GET['desktop'], $_GET['mobile']);
        die('Done');
      }

      die('error');
    }

    if (!empty($_GET['remote_generate_critical'])) {
      $criticalCSS = new wps_criticalCss();
      $criticalCSS->sendCriticalUrl('', get_the_ID());
      die('Generating Critical');
    }

    // Function that deletes cache?
    // TODO: Why is it like this?
    if (!empty($_GET['delete_wpc_cache'])) {
      array_map('unlink', array_filter((array)glob(WPS_IC_CACHE . '*')));
    }

    if (self::dontRunif()) {
      return;
    }

    if ((!empty($_GET['wps_ic_action']) || !empty($_GET['run_restore']) || !empty($_GET['run_compress'])) && !empty($_GET['apikey'])) {
      $options = get_option(WPS_IC_OPTIONS);
      $apikey = sanitize_text_field($_GET['apikey']);
      if ($apikey !== $options['api_key']) {
        die('Hacking?');
      }
    }

    $this::$settings = $this->fillMissingSettings($this::$settings);

    /**
     * Figure out ZoneName
     */
    if (empty($this::$settings['cname']) || !$this::$settings['cname']) {
      $this::$zone_name = get_option('ic_cdn_zone_name');
    } else {
      $custom_cname = get_option('ic_custom_cname');
      $this::$zone_name = $custom_cname;
    }

    /**
     * Figure out Quality
     */
    if (empty($this::$settings['optimization']) || $this::$settings['optimization'] == '' || $this::$settings['optimization'] == '0') {
      $this::$quality = 'intelligent';
    } else {
      $this::$quality = $this::$settings['optimization'];
    }

    if (empty($this::$options['css_hash'])) {
      $this::$options['css_hash'] = 5021;
    }

    if (!empty($_GET['random_css_hash'])) {
      define('WPS_IC_HASH', substr(md5(microtime(true)), 0, 6));
    } else {
      if (!defined('WPS_IC_HASH')) {
        define('WPS_IC_HASH', $this::$options['css_hash']);
      }
    }

    if (empty($this::$options['js_hash'])) {
      $this::$options['js_hash'] = 5021;
    }

    if (!empty($_GET['random_js_hash'])) {
      define('WPS_IC_JS_HASH', substr(md5(microtime(true)), 0, 6));
    } else {
      if (!defined('WPS_IC_JS_HASH')) {
        define('WPS_IC_JS_HASH', $this::$options['js_hash']);
      }
    }

    // Plugin Settings
    if (empty($this::$options['api_key'])) {
      self::$api_key = '';
    } else {
      self::$api_key = $this::$options['api_key'];
    }

    if (empty($this::$options['response_key'])) {
      self::$response_key = '';
    } else {
      self::$response_key = $this::$options['response_key'];
    }

    // Usual Checks
    #$this->checkFavicon();

    $this->upgrader = new wps_ic_upgrader();
    $this->mainwp = new wps_ic_mainwp();
    $this->enqueues = new wps_ic_enqueues();

    if (is_admin()) {
      $this->inAdmin();
    } else {
      $bgLazy = new wps_ic_bgLazy();
      $this->inFrontEnd();
    }

    // Change PHP Limits
    $wps_ic = $this;
  }

  public function generateHomePageURL()
  {
    return;
    // TODO: Bad API Url, that's critical CSS
    $call = wp_remote_post(WPS_IC_CRITICAL_API_URL, ['method' => 'POST', 'sslverify' => false, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0', 'body' => ['url' => site_url()]]);

    if (wp_remote_retrieve_response_code($call) == 200) {
      // ALL OK, run preloader
      $url = WPS_IC_PRELOADER_API_URL;

      $call = wp_remote_post($url, ['body' => ['single_url' => site_url()], 'timeout' => 30, 'sslverify' => 'false', 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0']);

      if (wp_remote_retrieve_response_code($call) == 200) {
        // TODO: Notice, we were unable to preload
      }
    } else {
      // Some ERROR Occured
      // TODO: Notice, we were unable to generate critical
    }

  }


  // TODO: Bad API Url, that's critical CSS

  /**
   * Various checks if the plugin should not be running
   * @return bool
   */
  public static function dontRunif()
  {
    if (self::hiddenAdminArea()) {
      return true;
    }

    if (self::isCriticalCSS()) {
      return true;
    }

    if (self::isPageBuilder()) {
      return true;
    }

    if (self::isPageBuilderFE()) {
      return true;
    }

    // Fix for Feedzy RSS Feed
    if (!empty($_POST['action']) && ($_POST['action'] == 'feedzy' || $_POST['action'] == 'action' || $_POST['action'] == 'elementor')) {
      return true;
    }

    if (!empty($_GET['wps_ic_action'])) {
      return true;
    }

    if (strpos($_SERVER['REQUEST_URI'], 'xmlrpc') !== false || strpos($_SERVER['REQUEST_URI'], 'wp-json') !== false) {
      return true;
    }

    if (!empty($_SERVER['SCRIPT_URL']) && $_SERVER['SCRIPT_URL'] == "/wp-admin/customize.php") {
      return true;
    }

    if (!empty($_GET['tatsu']) || !empty($_GET['tatsu-header']) || !empty($_GET['tatsu-footer'])) {
      return true;
    }

    if ((!empty($_GET['page']) && $_GET['page'] == 'livecomposer_editor')) {
      return true;
    }

    if (!empty($_GET['PageSpeed'])) {
      return true;
    }

    return false;
  }

  public static function hiddenAdminArea()
  {

    // AIOS
    if (class_exists('AIO_WP_Security')) {
      // Hide Login Exists
      $configs = get_option('aio_wp_security_configs');
      if (!empty($configs['aiowps_login_page_slug'])) {
        if (strpos($_SERVER['REQUEST_URI'], $configs['aiowps_login_page_slug']) !== false) {
          return true;
        }
      }
    }

    // WPS Hide Login
    if (class_exists('WPS\WPS_Hide_Login\Plugin')) {
      // Hide Login Exists
      $loginPage = get_option('whl_page');
      if (!empty($loginPage)) {
        if (strpos($_SERVER['REQUEST_URI'], '/' . $loginPage) !== false) {
          return true;
        }
      }
    }

    // Hide My WP - Ghost
    if (class_exists('HMWP_Classes_ObjController')) {
      $option = get_option('hmwp_options');

      if (!empty($option)) {
        $option = json_decode($option, true);
        $loginPage = $option['hmwp_login_url'];
        if (!empty($loginPage)) {
          if (strpos($_SERVER['REQUEST_URI'], $loginPage) !== false) {
            return true;
          }
        }
      }
    }

  }

  /**
   * Check if it's crtical CSS
   * TODO: Currently it's disabled
   * TODO: Maybe not required anymore?
   * @return false
   */
  public static function isCriticalCSS()
  {
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (strpos($useragent, 'headless') !== false || strpos($useragent, 'crittr') !== false) {
      #return true;
    }

    return false;
  }

  /**
   * FrontEnd Editors Detection for various page builders
   * @return bool
   */
  public static function isPageBuilder()
  {
    $page_builders = ['run_compress', //wpc
      'run_restore', //wpc
      'bwc', //bwc
      'elementor-preview', //elementor
      'fl_builder', //beaver builder
      'et_fb', //divi
      'preview', //WP Preview
      'builder', //builder
      'brizy', //brizy
      'fb-edit', //avada
      'bricks', //bricks
      'ct_template', //ct_template
      'ct_builder', //ct_builder
      'cs-render', //cs-render
      'tatsu', //tatsu
      'trp-edit-translation', //thrive
      'brizy-edit-iframe', //brizy
      'ct_builder', //oxygen
      'livecomposer_editor', //livecomposer
      'tatsu', //tatsu
      'tatsu-header', //tatsu-header
      'tatsu-footer', //tatsu-footer
      'tve'//thrive
    ];

    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'cornerstone') !== false) {
      return true;
    }

    if (!empty($_POST['_cs_nonce'])) { //cornerstone
      return false;
    }

    if (!empty($_GET['page']) && $_GET['page'] == 'bwc') {
      return false;
    }

    if (!empty($_SERVER['REQUEST_URI'])) {
      if (strpos($_SERVER['REQUEST_URI'], 'wp-json') || strpos($_SERVER['REQUEST_URI'], 'rest_route')) {
        return false;
      }
    }

    if (!empty($_GET['dbg_pagebuilder'])) {
      var_dump($_GET);
      var_dump($_POST);
      var_dump($_SERVER);
      die();
    }

    foreach ($page_builders as $page_builder) {
      if (isset($_GET[$page_builder])) {
        return true;
      }
    }

    return false;
  }

  /**
   * FrontEnd Editors Detection for various page builders
   * @return bool
   */
  public static function isPageBuilderFE()
  {
    if (class_exists('BT_BB_Root')) {
      if (is_user_logged_in() && !is_admin()) {
        return true;
      }
    }

    return false;
  }

  public function fillMissingSettings($settings)
  {
    $options = new wps_ic_options();
    $defaultSettings = $options->getDefault();

    if (empty($settings) || !is_array($settings)) {
      $settings = [];
    }

    foreach ($defaultSettings as $option_key => $option_value) {
      if (is_array($option_value)) {
        foreach ($option_value as $option_value_k => $option_value_v) {
          if (!isset($settings[$option_key][$option_value_k])) {
            $settings[$option_key] = [];
            $settings[$option_key][$option_value_k] = '0';
          }
        }
      } else {
        if (!isset($settings[$option_key])) {
          $settings[$option_key] = '0';
        }
      }
    }

    update_option(WPS_IC_SETTINGS, $settings);
    return $settings;
  }

  /***
   * In Admin Area
   */
  public function inAdmin()
  {
    if (current_user_can('manage_options') && !empty($this::$options['api_key'])) {
      // Htaccess
      $htaccess = new wps_ic_htaccess();
      // Integrations
      $integrations = new wps_ic_integrations();
      $integrations->init();
    }

    // Run Multisite
    if (is_multisite()) {
      $this->mu = new wps_ic_mu();
    }

    if (!$this::$settings) {
      $options = new wps_ic_options();
      $options->set_recommended_options();
    }

    // Deactivate Notification
    add_action('admin_footer', ['wps_ic', 'deactivate_script']);

    self::$local = new wps_local_compress();
    $this->cache = new wps_ic_cache_integrations();
    $this->cacheLogic = new wps_ic_cache();
    $this->ajax = new wps_ic_ajax();
    $this->menu = new wps_ic_menu();
    $this->log = new wps_ic_log();
    $this->templates = new wps_ic_templates();
    $this->notices = new wps_ic_notices();

    // Select Modes
    $modes = new wps_ic_modes();
    add_action('admin_footer', [$modes, 'showPopup']);

    // Purge Hooks
    $this->cacheLogic->purgeHooks();

    add_filter('big_image_size_threshold', array($this, 'max_image_width'), 999, 1);

    // Connect to API Notice
    $this->notices->connect_api_notice();

    // Ajax
    if (empty(self::$settings['css']) && empty(self::$settings['js']) && empty(self::$settings['serve']['jpg']) && empty(self::$settings['serve']['png']) && empty(self::$settings['serve']['gif']) && empty(self::$settings['serve']['svg'])) {
      $this->localMode();
    } else {
      if (!empty(self::$response_key)) {
        $this->media_library = new wps_ic_media_library_live();
        $this->stats = new wps_ic_stats();
        $this->comms = new wps_ic_comms();
      }
    }

    if (!empty($_GET['reset_compress'])) {
      $this->reset_local_compress();
      die('Reset Done');
    }

    if (!empty($_GET['ic_stats'])) {
      $this->stats->fetch_live_stats();
      die();
    }

    $this::$settings = $this->fillMissingSettings($this::$settings);

    if (empty($this::$settings['live-cdn']) || $this::$settings['live-cdn'] == '0') {
      // Is it some remote call?
      if (!empty($_GET['apikey'])) {
        if (self::$api_key !== sanitize_text_field($_GET['apikey'])) {
          die('Bad Call');
        }
      }

      if (is_admin()) {
        if (!empty($_GET['deauth'])) {
          $this->ajax->wps_ic_deauthorize_api();
          wp_safe_redirect(admin_url('admin.php?page=' . self::$slug . ''));
          die();
        }
      }
    }
  }

  public function localMode()
  {
    $this->queue = new wps_ic_queue();
    $this->compress = new wps_ic_compress();
    $this->controller = new wps_ic_controller();
    $this->remote_restore = new wps_ic_remote_restore();
    $this->comms = new wps_ic_comms();
    $this::$media_lib_ajax = $this->media_library = new wps_ic_media_library_live();
    $this->mu = new wps_ic_mu();
  }

  /**
   * Reset local image status
   */
  public function reset_local_compress()
  {
    $queue = $this->media_library->find_compressed_images();

    $compressed_images_queue = get_transient('wps_ic_restore_queue');

    if ($compressed_images_queue['queue']) {
      foreach ($compressed_images_queue['queue'] as $i => $image) {
        $attID = $image;
        delete_post_meta($attID, 'ic_status');
        delete_post_meta($attID, 'ic_stats');
        delete_post_meta($attID, 'ic_compressed_images');
      }
    }
  }

  /**
   * In Frontend Area
   */
  public function inFrontEnd()
  {
    /**
     * Preload Status
     */
    add_action('template_redirect', [$this, 'preloadPageStatusUpdate']);

    /**
     * Various integrations for 3rd party plugins
     */ #$this->integration_wp_rocket();
    #$this->integration_autoptimize();
    #$this->integration_jet_smart_filters();

    /**
     * Disable oEmbed if Enabled
     */
    if (!empty($this::$settings['disable-oembeds']) && $this::$settings['disable-oembeds'] == '1') {
      $oEmbed = new wps_ic_oEmbed();
      $oEmbed->run();
    }

    /**
     * Disable Dashicons if Enabled
     */
    if (!empty($this::$settings['disable-dashicons']) && $this::$settings['disable-dashicons'] == '1') {
      add_action('wp_enqueue_scripts', [$this->enqueues, 'disableDashicons'], 999);
    }

    /**
     * Disable Gutenberg if Enabled
     */
    if (!empty($this::$settings['disable-gutenberg']) && $this::$settings['disable-gutenberg'] == '1') {
      add_action('wp_enqueue_scripts', [$this->enqueues, 'disableGutenberg'], 1);
    }

    /**
     * Test if Critical API Generating Works Well
     */
    if (!empty($_GET['testApiGenerateCritical'])) {
      $this->generateHomePageURL();
      die('Running API Critical');
    }

    /**
     * Run API Critical CSS Generating
     * - Our API calls url with this GET parameter so that it runs critical generating
     */
    if (!empty($_GET['apiGenerateCritical'])) {
      $criticalCSS = new wps_criticalCss();
      $criticalCSS->sendCriticalUrl('', 0);
      wp_send_json_success();
    }

    /**
     * Run Preloader API
     * - Our API calls url with this GET parameter so that it runs critical generating
     */
    if (!empty($_GET['apiPreload'])) {
      $criticalCSS = new wps_criticalCss();
      $criticalCSS->sendCriticalUrl('', 0);
      wp_send_json_success();
    }

    $this->ajax = new wps_ic_ajax();

    /**
     * Run only if Current URL is not login or register
     * TODO: Maybe add some way to recognize custom login/register urls?
     */
    if (!in_array($_SERVER['PHP_SELF'], ['/wp-login.php', '/wp-register.php'])) {
      $this->menu = new wps_ic_menu();

      /**
       * Live CDN is Disabled
       */
      #if (empty($this::$settings['live-cdn']) || $this::$settings['live-cdn'] == '0') {
      if (self::$settings['css'] == 0 && self::$settings['js'] == 0 && self::$settings['serve']['jpg'] == 0 && self::$settings['serve']['png'] == 0 && self::$settings['serve']['gif'] == 0 && self::$settings['serve']['svg'] == 0) {
        /**
         * Live Not Active
         */

        $this->cdn = new wps_cdn_rewrite();
        add_action('template_redirect', [$this->cdn, 'buffer_local_go']);
        $this->comms = new wps_ic_comms();
      } else {
        /***
         * Live Active
         */
        if (!empty(self::$response_key)) {
          $this->comms = new wps_ic_comms();
        }
      }
    }
  }

  public function max_image_width()
  {
    if (empty(self::$settings['max-original-width'])) {
      return 2560;
    }

    return self::$settings['max-original-width']; // new threshold
  }

  public function integration_wp_rocket()
  {
    if (function_exists('rocket_clean_domain')) {
      add_filter('rocket_exclude_defer_js', [$this, 'exclude_wpc'], 999, 1);
      add_filter('rocket_delay_js_exclusions', [$this, 'exclude_wpc'], 999, 1);
      add_filter('rocket_exclude_js', [$this, 'exclude_wpc'], 999, 1);
    }
  }

  public function integration_autoptimize()
  {
    if (function_exists('autoptimize')) {
      add_filter('autoptimize_filter_get_config', [$this, 'exclude_from_autoptimize'], 999, 1);
    }
  }

  public function integration_jet_smart_filters()
  {
    if (class_exists('Jet_Smart_Filters')) {
      $cdn = new wps_cdn_rewrite();
      add_filter('jet-smart-filters/render/ajax/data', [$cdn, 'jetsmart_ajax_rewrite'], PHP_INT_MAX, 1);
    }
  }

  // TODO: Finish

  public function replaceImageSources($sources, $size_array, $image_src, $image_meta, $attachment_id)
  {
    if ((function_exists('is_amp_endpoint') && is_amp_endpoint())) {
      return $sources;
    }

    return $sources;

    $pseudoSources = array();
    if ($sources) {
      foreach ($sources as $key => $data) {
        if ($this->urlIsExcluded($data['url'])) {
          //if any of the items are excluded, don't replace
          return $sources;
        }
      }
    }

    return $sources;
  }


  public function preloadPageStatusUpdate()
  {
    //Setting preload status
    if (isset($_GET['preload_status'])) {
      global $post;
      $status = sanitize_text_field($_GET['preload_status']);

      $preloaded_pages = get_option('wpc_preloaded_status');
      if ($preloaded_pages === false) {
        $preloaded_pages = array();
      }

      $preloaded_pages[$post->ID] = $status;
      update_option('wpc_preloaded_status', $preloaded_pages);
      wp_send_json_success();
    }
  }

  public function checkFavicon()
  {
    $favicon = get_transient('wps_ic_favicon');
    if (empty($favicon)) {
      $faviconLocation = ABSPATH . 'favicon.ico';
      $site_icon_id = get_option('site_icon');
      if (!file_exists($faviconLocation) && empty($site_icon_id)) {
      }
    }
  }

  public function exclude_from_autoptimize($config)
  {
    $config['autoptimize_js_exclude'] = array_merge($config['autoptimize_js_exclude'], ['plugins/wp-compress-image-optimizer']);

    return $config;
  }

  public function exclude_wpc($excluded)
  {
    $excluded = array_merge($excluded, ['/wp-content/plugins/wp-compress-image-optimizer/assets/js/(.*).js', 'jquery']);

    return $excluded;
  }

  public function geoLocateAjax()
  {
    if (!is_multisite()) {
      $siteurl = site_url();
    } else {
      $siteurl = network_site_url();
    }

    $call = wp_remote_get('https://cdn.zapwp.net/?action=geo_locate&domain=' . urlencode($siteurl), ['timeout' => 30, 'sslverify' => false, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0']);

    if (wp_remote_retrieve_response_code($call) == 200) {
      $body = wp_remote_retrieve_body($call);
      $body = json_decode($body);

      if ($body->success) {
        update_option('wps_ic_geo_locate', $body->data);
      } else {
        update_option('wps_ic_geo_locate', ['country' => 'EU', 'server' => 'frankfurt.zapwp.net']);
      }

      wp_send_json_success($body->data);
    } else {
      update_option('wps_ic_geo_locate', ['country' => 'EU', 'server' => 'frankfurt.zapwp.net']);
    }

    return false;
  }

  /**
   * GeoLocation which is required for Local to work faster
   * @return void
   */
  public function geoLocate()
  {
    $call = wp_remote_get('https://cdn.zapwp.net/?action=geo_locate&domain=' . urlencode(site_url()), ['timeout' => 30, 'sslverify' => false, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0']);

    if (wp_remote_retrieve_response_code($call) == 200) {
      $body = wp_remote_retrieve_body($call);
      $body = json_decode($body);

      if ($body->success) {
        update_option('wps_ic_geo_locate', $body->data);
      } else {
        update_option('wps_ic_geo_locate', ['country' => 'EU', 'server' => 'frankfurt.zapwp.net']);
      }
    } else {
      update_option('wps_ic_geo_locate', ['country' => 'EU', 'server' => 'frankfurt.zapwp.net']);
    }
  }


}


function wps_ic_format_bytes($bytes, $force_unit = null, $format = null, $si = false)
{
  // Format string
  $format = ($format === null) ? '%01.2f %s' : (string)$format;

  // IEC prefixes (binary)
  if ($si == false or strpos($force_unit, 'i') !== false) {
    $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
    $mod = 1000;
  } // SI prefixes (decimal)
  else {
    $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
    $mod = 1000;
  }
  // Determine unit to use
  if (($power = array_search((string)$force_unit, $units)) === false) {
    $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
  }

  return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
}


function wps_ic_size_format($bytes, $decimals)
{
  $quant = ['TB' => 1000 * 1000 * 1000 * 1000, 'GB' => 1000 * 1000 * 1000, 'MB' => 1000 * 1000, 'KB' => 1000, 'B' => 1,];

  if ($bytes == 0) {
    return '0 MB';
  }

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

// TODO: maybe set in if (lazy_enabled==1)
add_filter('wp_lazy_loading_enabled', '__return_false', 1);

// TODO: Maybe it's required on some themes?
// Backend
$wpsIc = new wps_ic();
add_action('init', [$wpsIc, 'init'], 100);

// Frontend do replace
$cdn = new wps_cdn_rewrite();
add_action('plugins_loaded', [$cdn, 'checkCache'], 1);
add_action('init', [$cdn, 'checkCache'], 1);
add_action('init', [$cdn, 'buffer_callback_v3'], 110);


add_filter('upgrader_post_install', ['wps_ic_cache', 'update_css_hash'], 1);
add_action('activate_plugin', ['wps_ic_cache', 'update_css_hash'], 1);
add_action('deactivate_plugin', [$wpsIc, 'deactivation'], 1);

// Remove Critical CSS Generated & Preloaded Tags
add_filter('upgrader_post_install', [$wpsIc, 'onUpgrade_force_regen'], 1);
add_action('activate_plugin', [$wpsIc, 'onUpgrade_force_regen'], 1);

add_action('upgrader_process_complete', ['wps_ic_cache', 'update_css_hash'], 1);

register_activation_hook(WPC_PLUGIN_FILE, [$wpsIc, 'activation']);
register_deactivation_hook(WPC_PLUGIN_FILE, [$wpsIc, 'deactivation']);