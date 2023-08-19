<?php
global $wps_ic, $wpdb;

if (is_multisite()) {
  $current_blog_id = get_current_blog_id();
  switch_to_blog($current_blog_id);
}

include WPS_IC_DIR . 'classes/gui-v4.class.php';
$cache = new wps_ic_cache_integrations();

if (!empty($_GET['stopBulk'])) {
  $local = new wps_ic_local();
  $send = $local->sendToAPI(array('stop'), '', 'stopBulk');
  if ($send) {
    delete_option('wps_ic_parsed_images');
    delete_option('wps_ic_BulkStatus');
    delete_option('wps_ic_bulk_process');
    set_transient('wps_ic_bulk_done', true, 60);

    // Delete all transients
    $query = $wpdb->query("DELETE FROM " . $wpdb->options . " WHERE option_name LIKE '%wps_ic_compress_%'");
    wp_send_json_success();
  } else {
    var_dump($send);
  }
}


$usageStatsWidth = '';
$hideSidebar = '';
if (!empty($_GET['showAdvanced'])) {
  if ($_GET['showAdvanced'] == 'true') {
    update_option('wpsShowAdvanced', 'true');
  } else {
    delete_option('wpsShowAdvanced');
  }
}

$advancedSettings = get_option('wpsShowAdvanced');
if (!empty($advancedSettings) && $advancedSettings == 'true') {
  $showAdvanced = true;
  $usageStatsWidth = '';
  $hideSidebar = '';
} else {
  $showAdvanced = false;
  $usageStatsWidth = 'wider';
  $hideSidebar = 'style="display:none;"';
}

if (!empty($_GET['selectModes'])) {
  $usageStatsWidth = 'wider';
  $hideSidebar = 'style="display:none;"';
  $modes = new wps_ic_modes();
  $modes->showPopup();
  #$modes->triggerPopup();
  #echo '<a href="#" class="wpc-select-modes">Select modes</a>';
}


if (!empty($_GET['show_hidden_menus'])) {
  update_option('wpc_show_hidden_menus', $_GET['show_hidden_menus']);
}


// Save Settings
if (!empty($_POST['options'])) {
  update_option(WPS_IC_PRESET, $_POST['wpc_preset_mode']);

  $submittedOptions = $_POST['options'];
  $optimizatonQuality = 'lossless';

  if (isset($submittedOptions['qualityLevel'])) {
    switch ($submittedOptions['qualityLevel']):
      case '1':
        $optimizatonQuality = 'lossless';
        break;
      case '2':
        $optimizatonQuality = 'intelligent';
        break;
      case '3':
        $optimizatonQuality = 'ultra';
        break;
    endswitch;
  }

  $submittedOptions['optimization'] = $optimizatonQuality;
  $options_class = new wps_ic_options();
  $options = $options_class->setMissingSettings($submittedOptions);


  if (isset($options['serve'])) {
    $cdnEnabled = '0';
    foreach ($options['serve'] as $key => $value) {
      if ($options['serve'][$key] == '1') {
        $cdnEnabled = '1';
        break;
      }
    }

    $options['live-cdn'] = $cdnEnabled;
  }

  $purgeList = $options_class->getPurgeList($options);


  update_option(WPS_IC_SETTINGS, $options);
  $cache::purgeAll(); //this only clears cache files


  //To edit what setting purges what, go to wps_ic_options->__construct()
  if (in_array('combine', $purgeList)) {
    $cache::purgeCombinedFiles();
  }

  if (in_array('critical', $purgeList)) {
    $cache::purgeCriticalFiles();
  }

  #$criticalCss = new wps_criticalCss();
  #$criticalCss->sendCriticalUrlPing('', 0);

  if (!empty($options['cache']['advanced']) && $options['cache']['advanced'] == '1') {
    $htacces = new wps_ic_htaccess();

    if (!empty($options['cache']['compatibility']) && $options['cache']['compatibility'] == '1' && $htacces->isApache) {
      // Modify HTAccess
      $htacces->checkHtaccess();
    } else {
      $htacces->removeHtaccessRules();
    }

    // Add WP_CACHE to wp-config.php
    $htacces->setWPCache(true);
    $htacces->setAdvancedCache();

    $this->cacheLogic = new wps_ic_cache();
    $this->cacheLogic::removeHtmlCacheFiles(0); // Purge & Preload
    $this->cacheLogic::preloadPage(0); // Purge & Preload
  } else {
    // Modify HTAccess
    $htacces = new wps_ic_htaccess();
    $htacces->removeHtaccessRules();

    // Add WP_CACHE to wp-config.php
    $htacces->setWPCache(false);
    $htacces->removeAdvancedCache();
  }
}

$gui = new wpc_gui_v4();


/**
 * GeoLocation Stuff
 */
$geolocation = get_option('wps_ic_geo_locate');
if (empty($geolocation)) {
  $geolocation = $this->geoLocate();
} else {
  $geolocation = (object)$geolocation;
}

$geolocation_text = $geolocation->country_name . ' (' . $geolocation->continent_name . ')';

$proSite = get_option('wps_ic_prosite');
$options = get_option(WPS_IC_OPTIONS);
$settings = get_option(WPS_IC_SETTINGS);
$bulkProcess = get_option('wps_ic_bulk_process');

$allowLocal = get_option('wps_ic_allow_local');
$allowLive = get_option('wps_ic_allow_live');

?>

  <div class="wpc-advanced-settings-container wpc-advanced-settings-container-v4 wps_ic_settings_page">
    <form method="POST" action="">

      <?php if (!empty($settings['live-cdn']) && $settings['live-cdn'] == '1') { ?>
        <input name="options[live-cdn]" type="hidden" value="1"/>
      <?php } else { ?>
        <input name="options[live-cdn]" type="hidden" value="0"/>
      <?php } ?>

      <!-- Header Start -->
      <div class="wpc-header">
        <?php if (!empty($hideSidebar)) { ?>
        <div class="wpc-header-left" style="max-width:500px;">
          <?php } else { ?>
          <div class="wpc-header-left">
            <?php } ?>
            <div class="wpc-header-logo">
              <img src="<?php echo WPS_IC_URI; ?>assets/v4/images/main-logo.svg"/>
            </div>
            <?php
            if (!$showAdvanced) {
              // Preset Modes
              $preset_config = get_option(WPS_IC_PRESET);
              $preset = array('recommended' => 'Recommended Mode', 'safe' => 'Safe Mode', 'aggressive' => 'Aggressive Mode', 'custom' =>'Custom');

              if (empty($preset_config)) {
                update_option('wps_ic_preset_setting', 'custom');
                $preset_config = 'custom';
              }

              if (empty($preset_config) || empty($preset[$preset_config])) {
                $preset_config = 'custom';
              }

              if (!empty($_GET['dbgPreset'])) {
                var_dump($preset);
                var_dump($preset_config);
                var_dump($preset[$preset_config]);
              }

              $html = '<input type="hidden" name="wpc_preset_mode" value="' . $preset_config . '" />
<div class="wpc-dropdown wpc-dropdown-left wpc-dropdown-trigger-popup">
  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    ' . $preset[$preset_config] . '
  </button></div>';
              echo $html;
            }

            if ($proSite) {
              echo '<div class="wpc-header-pro-site"><span>Unlimited</span></div>';
            }
            ?>
          </div>
          <div class="wpc-header-right">
            <div class="d-flex align-items-center gap-3 gap-md-4 wpc-header-right-inner" style="position: relative;">
              <div class="save-button"
                   style="display: none;">
                <div class="save-notification">
                  <div class="save-notification-inside">
                    <p class="cdn-active d-flex align-items-center gap-2 fs-400">
                      <i class="wpc-warning-icon"></i> We have detected you have made some changes, please save your changes!
                    </p>
                  </div>
                </div>
                <div class="save-button-inside">
                  <div>
                    <button type="submit" class="btn btn-gradient text-white fw-400 btn-radius wpc-save-button">
                      <i class="wpc-save-button-icon"></i> Save
                    </button>
                  </div>
                </div>
              </div>
              <div class="wpc-loading-spinner" style="display:none;">
                <div class="snippet" data-title=".dot-pulse">
                  <div class="stage">
                    <div class="dot-pulse"></div>
                  </div>
                </div>
              </div>
              <div class="addon-buttons">
                <?php
                if (!$showAdvanced) {
                ?>
                <a href="<?php echo admin_url('options-general.php?page='.$wps_ic::$slug.'&showAdvanced=true'); ?>"
                   class="wpc-plain-btn"><img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/popups/selectMode/advanced-settings.svg" title="Advanced Settings"/> Advanced Settings</a>
                <?php } else { ?>
                  <a href="<?php echo admin_url('options-general.php?page='.$wps_ic::$slug.'&showAdvanced=false'); ?>" class="wpc-plain-btn"><img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/popups/selectMode/advanced-settings.svg" title="Advanced Settings"/> Simple Settings</a>
                <?php } ?>
                <?php if (!empty($allowLocal)) { ?>
                  <a class="btn btn-gradient text-white fw-400 btn-radius" href="<?php echo admin_url('options-general.php?page=' . $wps_ic::$slug . '&view=bulk'); ?>">

                    <?php
                    if (!$bulkProcess || empty($bulkProcess)) {
                      ?>
                      <span>
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/menu-icons/image-optimization.svg"/>
                                </span>
                      <span style="display: none;" class="wpc-optimizer-running">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/loading-icon-media.svg"/>
                                </span>
                    <?php } else { ?>
                      <span style="display: none;">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/menu-icons/image-optimization.svg"/>
                                </span>
                      <span class="wpc-optimizer-running">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/loading-icon-media.svg"/>
                                </span>
                    <?php } ?>

                    Optimize Media Library
                  </a>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
        <!-- Header End -->
        <!-- Body Start -->
        <div class="wpc-settings-body">
          <div class="wpc-settings-tabs">
            <!-- Tab List Start -->
            <div class="wpc-settings-tab-list" <?php echo $hideSidebar; ?>>
              <ul>
                <li>
                  <a href="#" class="active" data-tab="dashboard">
                                <span class="wpc-icon-container">
                                <span class="wpc-icon">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/menu-icons/dashboard.svg"/>
                                </span>
                                </span>
                    <span class="wpc-title">Optimization Dashboard</span>
                  </a>
                </li>
                <?php if ($allowLive) { ?>
                  <li>
                    <a href="#" class="" data-tab="cdn-delivery-options">
                                <span class="wpc-icon-container">
                                <span class="wpc-icon">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/cdn-delivery-options.svg"/>
                                </span>
                                </span>
                      <span class="wpc-title">CDN Delivery</span>
                    </a>
                  </li>
                <?php } ?>
                <li>
                  <a href="#" class="wpc-menu-tooltip" title="Image Optimization" data-tab="image-optimization-options">
                                <span class="wpc-icon-container">
                                <span class="wpc-icon">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/menu-icons/image-optimization.svg"/>
                                </span>
                                </span>
                    <span class="wpc-title">Image Optimization</span>
                  </a>
                </li>
                <li>
                  <a href="#" class="" data-tab="performance-tweaks-options">
                                <span class="wpc-icon-container">
                                <span class="wpc-icon">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/menu-icons/rocket.svg"/>
                                </span>
                                </span>
                    <span class="wpc-title">Performance Tweaks</span>
                  </a>
                </li>
                <li>
                  <a href="#"
                     class=""
                     data-tab="other-optimization-options">
                                <span class="wpc-icon-container">
                                <span class="wpc-icon">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/menu-icons/other.svg"/>
                                </span>
                                </span>
                    <span class="wpc-title">Other Optimization</span>
                  </a>
                </li>
                <li>
                  <a href="#" class="" data-tab="ux-settings-options">
                                <span class="wpc-icon-container">
                                <span class="wpc-icon">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/menu-icons/ux.svg"/>
                                </span>
                                </span>
                    <span class="wpc-title">UX Settings</span>
                  </a>
                </li>
                <!--                            <li>-->
                <!--                                <a href="#" class="" data-tab="preload-cache-options">-->
                <!--                                <span class="wpc-icon-container">-->
                <!--                                <span class="wpc-icon">-->
                <!--                                    <img src="--><?php //echo WPS_IC_ASSETS; ?><!--/v4/images/css-optimization/menu-icon.svg"/>-->
                <!--                                </span>-->
                <!--                                </span>-->
                <!--                                    <span class="wpc-title">Preload Cache</span>-->
                <!--                                </a>-->
                <!--                            </li>-->
                <li>
                  <a href="#" class="" data-tab="critical-css-options">
                                <span class="wpc-icon-container">
                                <span class="wpc-icon">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/css-optimization/menu-icon.svg"/>
                                </span>
                                </span>
                    <span class="wpc-title">Generate Critical CSS</span>
                  </a>
                </li>
                  <!--
                <li>
                    <a href="#" class="" data-tab="exclude-url">
                                <span class="wpc-icon-container">
                                <span class="wpc-icon">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/css-optimization/menu-icon.svg"/>
                                </span>
                                </span>
                          <span class="wpc-title">Exclude URLs</span>
                    </a>
                </li>
                -->
                <?php
                if (get_option('wpc_show_hidden_menus') == 'true') {
                  ?>
                  <li>
                    <a href="#" class="" data-tab="system-information">
                                <span class="wpc-icon-container">
                                <span class="wpc-icon">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/css-optimization/menu-icon.svg"/>
                                </span>
                                </span>
                      <span class="wpc-title">System Information</span>
                    </a>
                  </li>
                  <li style="display: block;">
                    <a href="#" class="" data-tab="debug">
                                <span class="wpc-icon-container">
                                <span class="wpc-icon">
                                    <img src="<?php echo WPS_IC_ASSETS; ?>/v4/images/css-optimization/menu-icon.svg"/>
                                </span>
                                </span>
                      <span class="wpc-title">Debug</span>
                    </a>
                  </li>
                <?php } ?>
              </ul>
            </div>
            <!-- Tab List End -->
            <!-- Tab Content Start -->
            <div class="wpc-settings-tab-content">
              <div class="wpc-settings-tab-content-inner">
                <div class="wpc-tab-content active-tab" id="dashboard">

                  <div class="wpc-tab-content-box" style="padding: 20px 20px !important;display:none;">
                    <?php include WPS_IC_DIR . 'templates/admin/partials/v4/pull-stats.php'; ?>
                  </div>

                  <div class="wpc-tab-content-box">
                    <?php echo $gui::usageGraph(); ?>
                  </div>

                  <div class="wpc-tab-content-box wpc-usage-stats-width-<?php echo $usageStatsWidth; ?>">
                    <?php echo $gui::usageStats(); ?>
                  </div>

                  <?php if (empty($hideSidebar)) { ?>
                    <div class="wpc-tab-content-box">
                      <?php echo $gui::presetModes(); ?>
                    </div>
                  <?php } ?>

                </div>
                <div class="wpc-tab-content" id="cdn-delivery-options" style="display:none;">

                  <div class="wpc-tab-content-box">
                    <?php echo $gui::checkboxTabTitleCheckbox('Real-Time Optimization + CDN', 'Optimize your images & scripts in real-time via our top-rated global CDN.', 'tab-icons/real-time.svg', '', 'cdn-delivery-options', '', '', 'exclude-cdn-popup'); ?>

                    <div class="wpc-spacer"></div>

                    <div class="wpc-items-list-row real-time-optimization">

                      <?php echo $gui::iconCheckBox('JPG/JPEG', 'cdn-delivery/jpg.svg', ['serve', 'jpg']); ?>
                      <?php echo $gui::iconCheckBox('PNG', 'cdn-delivery/png.svg', ['serve', 'png']); ?>
                      <?php echo $gui::iconCheckBox('GIF', 'cdn-delivery/gif.svg', ['serve', 'gif']); ?>
                      <?php echo $gui::iconCheckBox('SVG', 'cdn-delivery/svg.svg', ['serve', 'svg']); ?>

                      <?php echo $gui::iconCheckBox('CSS', 'cdn-delivery/css.svg', 'css'); ?>
                      <?php echo $gui::iconCheckBox('JavaScript', 'cdn-delivery/js.svg', 'js'); ?>
                      <?php echo $gui::iconCheckBox('Fonts', 'cdn-delivery/font.svg', 'fonts'); ?>


                    </div>

                    <?php #echo $gui::iconCheckBox('JPG', 'cdn-delivery/jpg.svg', 'jpg'); ?>

                  </div>

                  <?php echo $gui::cname(); ?>

                </div>
                <div class="wpc-tab-content" id="image-optimization-options" style="display:none;">

                  <div class="wpc-tab-content-box" id="adaptive-images">
                    <?php echo $gui::checkboxTabTitleCheckbox('Adaptive Images', 'Intelligently adapt images based on the incoming visitors device, browser and location on page.', 'image-optimization/image-optimization.svg', '', 'adaptive-images'); ?>

                    <div class="wpc-spacer"></div>

                    <div class="wpc-items-list-row mb-20">

                      <?php echo $gui::checkboxDescription_v4('Resize by Incoming Device', 'Serve the ideal image based on the visitors device to slash file-sizes, improve load times and offer a better experience.', false, '0', 'generate_adaptive', false, 'right', 'exclude-adaptive-popup'); ?>

                      <?php echo $gui::checkboxDescription_v4('Serve WebP Images', 'Generate and serve next generation WebP images to supported browsers and devices.', false, '0', 'generate_webp', false, 'right', 'exclude-webp-popup'); ?>

                    </div>
                    <div class="wpc-items-list-row mb-20">

                      <?php echo $gui::checkboxDescription_v4('Serve Retina Images', 'Deliver higher resolution retina images so that your images look great on larger screens.', false, '0', 'retina', false, 'right'); ?>

                      <?php echo $gui::checkboxDescription_v4('Background Images', 'Serve background images over CDN with all the adaptive and quality options.', false, '0', 'background-sizing', false, 'right'); ?>

                    </div>

                    <div class="wpc-items-list-row mb-20">

                      <?php #echo $gui::checkboxDescription_v4('Remove Srcset', 'Disable theme srcset to avoid unintended conflicts with adaptive images or lazy loading.', false, '0', 'remove-srcset', false, 'right'); ?>

                      <?php //echo $gui::inputDescription_v4('Max Image Width', 'Insert maximum
                      // dimensions of images, we will scale your original images to that width.', false, '0', 'max-original-width', false, 'right'); ?>
                    </div>

                  </div>

                  <div class="wpc-tab-content-box" id="lazy-images">
                    <?php echo $gui::checkboxTabTitleCheckbox('Lazy Loading', 'Intelligently lazy load images based on the viewport position.', 'image-optimization/image-optimization.svg', '', 'lazy-images'); ?>

                    <div class="wpc-spacer"></div>

                    <div class="wpc-items-list-row mb-20">

                      <?php echo $gui::checkboxDescription_v4('Native Lazy', 'Lazy load images using browser methods, to save bandwidth and reduce overall page size.', false, '0', 'nativeLazy', false, 'right', 'exclude-lazy-popup'); ?>

                      <?php echo $gui::checkboxDescription_v4('Lazy Loading by Viewport', 'Load additional images as the user scrolls to save tons of bandwidth and slash overall page size.', false, '0', 'lazy', false, 'right', 'exclude-lazy-popup'); ?>

                    </div>

                  </div>

                  <?php if (!empty($allowLocal)) { ?>
                    <div class="wpc-tab-content-box">
                      <?php echo $gui::optimizationLevel('Optimization Level', 'optimizationLevel', 'Select your preferred image compression strength.', 'tab-icons/optimization-level.svg', '', 'optimizationLevel'); ?>
                    </div>

                    <div class="wpc-tab-content-box">
                      <?php echo $gui::checkboxDescription('Auto-Optimize on Upload', 'Automatically compress new media library images as they’re uploaded.', 'tab-icons/on-upload.svg', '', 'on-upload'); ?>
                    </div>
                  <?php } ?>

                  <?php /*
                                <div class="wpc-tab-content-box">
                                  <?php echo $gui::checkboxDescription('Local Backups', 'Backup original images on your local server.', 'tab-icons/backup-local.svg', '', ['backup', 'local']); ?>
                                </div> */ ?>

                </div>
                <div class="wpc-tab-content" id="ux-settings-options" style="display:none;">
                  <div class="wpc-tab-content-box" id="ux-settings">
                    <?php echo $gui::checkboxTabTitle('User Experience Settings', 'Tailor the plugin to your preferences or needs with customizable design options.', 'tab-icons/ux-settings.svg', '', ''); ?>

                    <div class="wpc-spacer"></div>

                    <div class="wpc-items-list-row mb-20">
                      <?php echo $gui::checkboxDescription_v4('Hide in Admin Bar', 'Admin bar will hide plugin icon with tools per page.', false, '0', ['status', 'hide_in_admin_bar'], false, 'right'); ?>

                      <?php if (!empty($allowLocal)) { ?>
                        <?php echo $gui::checkboxDescription_v4('Show in Media Library List', 'Compress, exclude and restore images in List Mode.', false, '0', ['local', 'media-library'], false, 'right'); ?>
                      <?php } ?>
                    </div>

                    <div class="wpc-items-list-row mb-20">

                      <?php echo $gui::checkboxDescription_v4('Hide Cache Status', 'Display Cache status in admin bar for the page.', false, '0', ['status', 'hide_cache_status'], false, 'right'); ?>
                      <?php echo $gui::checkboxDescription_v4('Hide Critical CSS Status', 'Display Critical CSS status in admin bar for the page.', false, '0', ['status', 'hide_critical_css_status'], false, 'right'); ?>

                    </div>

                    <div class="wpc-items-list-row mb-20">

                      <?php echo $gui::checkboxDescription_v4('Hide Preloading Status', 'Display Preloading status in admin bar for the page.', false, '0', ['status', 'hide_preload_status'], false, 'right'); ?>

                      <?php echo $gui::checkboxDescription_v4('Hide from WordPress', 'Totally hide
                                      // the plugin from the Admin Area.', false, 'hide_compress', 'hide_compress', false, 'right'); ?>

                    </div>

                  </div>
                </div>
                <div class="wpc-tab-content" id="performance-tweaks-options" style="display:none;">

                  <div class="wpc-tab-content-box" id="caching-options">

                    <?php echo $gui::checkboxTabTitle('Advanced Caching', 'Improve server response times by caching entire pages.', 'tab-icons/caching.svg', ''); ?>
                    <div class="wpc-spacer"></div>

                    <div class="wpc-items-list-row mb-0">

                      <?php echo $gui::checkboxDescription_v4('Enable Caching', 'Speed up your site by statically caching entire pages.', '', '', ['cache', 'advanced'], 0, '', ''); ?>

                      <?php echo $gui::buttonDescription_v4('Exclude From Caching', 'Choose specific URLs to exclude from caching.', '', '', ['wpc-excludes', 'cache'], 0, '', 'exclude-advanced-caching-popup'); ?>

                    </div>

                    <div class="wpc-items-list-row mb-0">

                      <?php echo $gui::checkboxDescription_v4('Cache Compatibility', 'Prevent cached webpages from opening as a download on LiteSpeed or OpenLiteSpeed servers.', '', '', ['cache', 'compatibility'], 0, '', ''); ?>

                      <?php echo $gui::inputDescription_v4('Expire Cache After', 'Recreate cache if it\'s stale or expired after a set duration.', 'Expire after', 'hours', false, ['cache', 'expire'], false, '6'); ?>

                    </div>


                  </div>

                  <div class="wpc-tab-content-box" id="css-optimization-options">
                    <?php echo $gui::checkboxTabTitle('CSS Optimizations', "Boost your site's performance by enabling global CSS optimization.", 'css-optimization/css-icon.svg', ''); ?>

                    <div class="wpc-spacer"></div>

                    <div class="wpc-items-list-row mb-0">

                      <?php echo $gui::checkboxDescription_v4('Critical CSS', 'Optimize initial page load by removing unused CSS.', '', '', ['critical', 'css'], 0, '1', 'exclude-critical-css', false, '', true); ?>

                      <?php echo $gui::checkboxDescription_v4('Combine CSS', 'Merge CSS files to minimize HTTP requests.', false, 'combine-css', 'css_combine', false, 'right', 'exclude-css-combine'); ?>

                    </div>
                    <div class="wpc-items-list-row mb-0" style="display: none;">

                      <?php #echo $gui::checkboxDescription_v4('Optimize Google Fonts', 'Optimize google fonts.', false, 'google_fonts', 'google_fonts', false, 'right', false); ?>

                      <?php echo $gui::checkboxDescription_v4('Minify CSS', 'Reduce CSS file sizes for faster loading.', false, '0', 'css_minify', false, 'right', 'exclude-css-minify'); ?>

                      <?php echo $gui::checkboxDescription_v4( 'Inline Combined CSS', 'Insert CSS files directly into your page.', false, '0', 'inline-css', false, 'right' ); ?>
                      <?php #echo $gui::checkboxDescription_v4( 'Remove Render Blocking', 'Insert CSS files directly into your page.', false, '0', 'remove-render-blocking', false, 'right' ); ?>

                    </div>

                  </div>

                  <div class="wpc-tab-content-box" id="javascript-optimization-options">
                    <?php echo $gui::checkboxTabTitle('JavaScript Optimizations', "Enhance your site performance by enabling global JavaScript optimization.", 'javascript-optimization/js-icon.svg', ''); ?>

                    <div class="wpc-spacer"></div>

                    <div class="wpc-items-list-row mb-20" style="display: none;">

                      <?php echo $gui::checkboxDescription_v4('Minify JavaScript', 'Reduce JavaScript file sizes for faster loading.', false, '0', 'js_minify', false, 'right', 'exclude-js-minify'); ?>

                      <?php echo $gui::checkboxDescription_v4('Combine JavaScript', ' Merge JavaScript files to minimize HTTP requests.', false, 'combine-js', 'js_combine', false, 'right', 'exclude-js-combine'); ?>

                    </div>
                    <div class="wpc-items-list-row mb-20" style="display: none;">

                      <?php echo $gui::checkboxDescription_v4('Move JavaScript to Footer', 'Improve page load time by moving JS to footer.', false, '0', 'scripts-to-footer', false, 'right', 'exclude-scripts-to-footer', false, false, true); ?>

                      <?php echo $gui::checkboxDescription_v4('Defer JavaScript', 'Improve interactivity by loading non-essential scripts later.', false, '0', 'js_defer', false, 'right', 'exclude-js-defer'); ?>

                    </div>
                    <div class="wpc-items-list-row mb-20">

                      <?php echo $gui::checkboxDescription_v4('Delay JavaScript', 'Speed up initial response times by delaying unnecessary JS.', false, 'delay-js', 'delay-js', false, 'right', 'exclude-js-delay', false, '', true); ?>


                      <?php echo $gui::checkboxDescription_v4('Inline JavaScript', 'Optimize page load by inserting JS directly into HTML.', false, '0', 'inline-js', false, 'right', 'inline-js'); ?>

                    </div>

                  </div>

                </div>
                <div class="wpc-tab-content" id="other-optimization-options" style="display:none;">

                  <div class="wpc-tab-content-box" id="other-optimization">
                    <?php echo $gui::checkboxTabTitle('Other Optimizations', 'Advanced tweaks to help for specific use cases, use only as needed.', 'other-optimization/tab-icon.svg', ''); ?>

                    <div class="wpc-spacer"></div>

                    <div class="wpc-items-list-row mb-20">

                      <?php echo $gui::checkboxDescription_v4('Disable Emoji', '', false, '0', 'emoji-remove', false, 'right', ''); ?>

                      <?php echo $gui::checkboxDescription_v4('Disable oEmbeds', '', false, '0', 'disable-oembeds', false, 'right', ''); ?>

                    </div>
                    <div class="wpc-items-list-row mb-20">

                      <?php echo $gui::checkboxDescription_v4('Disable Dashicons', '', false, '0', 'disable-dashicons', false, 'right', ''); ?>

                      <?php echo $gui::checkboxDescription_v4('Disable Gutenberg Block', '', false, '0', 'disable-gutenberg', false, 'right', ''); ?>

                    </div>
                    <div class="wpc-items-list-row mb-20">

                      <?php echo $gui::checkboxDescription_v4('Optimize External URLs', '', false, '0', 'external-url', false, 'right', ''); ?>

                      <?php echo $gui::checkboxDescription_v4('WooCommerce Tweaks', '', false, '0', 'disable-cart-fragments', false, 'right', ''); ?>

                    </div>
                    <div class="wpc-items-list-row mb-20">

                      <?php echo $gui::checkboxDescription_v4('Lazy Load iFrames', '', false, '0', 'iframe-lazy', false, 'right', ''); ?>

                      <?php echo $gui::checkboxDescription_v4('Minify HTML', '', false, '0', ['cache', 'minify'], false, 'right'); ?>

                    </div>

                      <div class="wpc-items-list-row mb-20">
			                  <?php echo $gui::checkboxDescription_v4('Exclude Specific URLs from Optimization', 'Bypass optimization for select pages (e.g., custom login etc).',
				                  false, '0', 'exclude-url-from-all', false, 'right', 'exclude-url-from-all'); ?>

                              <?php echo $gui::checkboxDescription_v4('Remove Srcset', 'Disable theme srcset to avoid unintended conflicts with adaptive images or lazy loading.', false, '0', 'remove-srcset', false, 'right'); ?>
                      </div>

                  </div>

                </div>
                <div class="wpc-tab-content" id="critical-css-options" style="display:none;">

                  <div class="wpc-tab-content-box">
                    <?php echo $gui::checkboxTabTitle_connected(array('title' => 'Critical CSS', 'description' => 'Critical CSS is auto-generated on the first page-view, but you may generate specific pages ahead of time.', 'icon' => 'css-optimization/css-icon.svg', 'optionID' => 'critical-css-remote', 'connected_to' => ['critical', 'css'])); ?>
                    <?php #echo $gui::checkboxDescription_v4('Critical CSS', 'Select for which pages you wish to generate critical.', 'css-optimization/css-icon.svg', '', ['critical', 'css']); ?>

                    <div class="wpc-spacer"></div>

                    <div class="wpc-items-list-row mb-20">

                      <table class="wps-critical-table" style="width: 100%;">
                        <thead>
                        <tr>
                          <th style="width: 250px">Page Title</th>
                          <th style="width: 200px;display: none;">Page URL</th>
                          <th style="width: 100px;text-align:center;">Images</th>
                          <th style="width: 70px;text-align:center;">CSS</th>
                          <th style="width: 70px;text-align:center;">JS</th>
                          <th style="width: 100px;text-align:center;">Status</th>
                          <th style="width: 100px;text-align:center;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        global $wps_ic;
                        $criticalCss = new wps_criticalCss();
                        $pages = $criticalCss->getCriticalPages();
                        $site_url = site_url();
                        foreach ($pages as $id => $page) {
                          $fullLink = $link = $page['link'];
                          $link = str_replace($site_url, '', $link);
                          $link = rtrim($link, '/');
                          if (empty($link)) {
                            $link = '/';
                          }
                          ?>
                          <tr>
                            <td class="wpc-critical-page-name"><?php echo $page['title']; ?></td>
                            <td style="display:none;">
                              <a href="<?php echo $fullLink; ?>"><?php echo $link; ?></a>
                            </td>
                            <td style="text-align:center;" id="assets_img_<?php echo $id; ?>"><?php echo $page['assets']['img']; ?></td>
                            <td style="text-align:center;" id="assets_js_<?php echo $id; ?>"><?php echo $page['assets']['js']; ?></td>
                            <td style="text-align:center;" id="assets_css_<?php echo $id; ?>"><?php echo $page['assets']['css']; ?></td>
                            <td style="text-align:center;" id="status_<?php echo $id; ?>">
                              <?php
                              $postID = sanitize_text_field($id);
                              $linkFull = get_permalink($postID);
                              $criticalCSSExists = $criticalCss->criticalExistsAjax($linkFull);
                              if (!empty($criticalCSSExists)) {
                                if (file_exists($criticalCSSExists)) {
                                  // Done
                                  echo '<div class="wpc-critical-circle done"></div>';
                                } else {
                                  // Unkown
                                  echo '<div class="wpc-critical-circle unknown"></div>';
                                }
                              } else {
                                echo '<div class="wpc-critical-circle unknown"></div>';
                              }
                              ?>
                            </td>
                            <td style="text-align:center;">
                              <a href="#" data-page-id="<?php echo $id; ?>" data-page-request="<?php echo $page['pageRequest']; ?>" class="ajax-run-critical">Run</a>
                            </td>
                          </tr>
                        <?php } ?>
                        </tbody>
                      </table>

                    </div>

                  </div>

                </div>
                <div class="wpc-tab-content" id="exclude-url"" style="display:none;">
                    <div class="wpc-tab-content-box" id="exclude-url">
                        <?php echo $gui::checkboxTabTitle('Exclude URLs from optimizations.', '', 'javascript-optimization/js-icon.svg', ''); ?>

                        <div class="wpc-spacer"></div>

                        <div class="wpc-items-list-row mb-20">
                                <?php echo $gui::checkboxDescription_v4('CDN', 'Exclude URL from CDN', false, '0', ['exclude-url', 'live-cdn'], false, 'right', 'exclude-url-cdn'); ?>

                                <?php echo $gui::checkboxDescription_v4('Image Optimizations', 'Exclude URL from Image Optimizations',
                                  false, '0', ['exclude-url', 'image_optimizations'], false, 'right', 'exclude-url-image-optimizations'); ?>

                        </div>
                        <div class="wpc-items-list-row mb-20">

                                <?php echo $gui::checkboxDescription_v4('Cache', 'Exclude URL from Cache', false, '0', ['exclude-url', 'cache'], false, 'right', 'exclude-url-cache'); ?>

                                <?php echo $gui::checkboxDescription_v4('Critical CSS', 'Exclude URL from Critical CSS',
                                    false, '0', ['exclude-url', 'critical'], false, 'right', 'exclude-url-critical'); ?>

                        </div>
                        <div class="wpc-items-list-row mb-20">

                                <?php echo $gui::checkboxDescription_v4('Combine CSS', 'Exclude URL from Combine CSS',
                                  false, '0', ['exclude-url', 'css_combine'], false, 'right', 'exclude-url-css-combine'); ?>

                                <?php echo $gui::checkboxDescription_v4('Combine JS', 'Exclude URL from Combine JS',
                                    false, '0', ['exclude-url', 'js_combine'], false, 'right', 'exclude-url-js-combine'); ?>

                        </div>
                        <div class="wpc-items-list-row mb-20">

                                <?php echo $gui::checkboxDescription_v4('Delay JS', 'Exclude URL from Delay JS',
                                    false, '0', ['exclude-url', 'delay-js'], false, 'right', 'exclude-url-js-delay'); ?>

                                <?php echo $gui::checkboxDescription_v4('JS to footer', 'Exclude URL from JS to footer',
                                    false, '0', ['exclude-url', 'scripts-to-footer'], false, 'right', 'exclude-url-scripts-to-footer'); ?>

                        </div>
                        <div class="wpc-items-list-row mb-20">

			                    <?php echo $gui::checkboxDescription_v4('Defer JS', 'Exclude URL from Defer JS',
				                    false, '0', ['exclude-url', 'js_defer'], false, 'right', 'exclude-url-js-defer'); ?>

			                    <?php echo $gui::checkboxDescription_v4('Inline JS', 'Exclude URL from Inline JS',
				                    false, '0', ['exclude-url', 'inline-js'], false, 'right', 'exclude-url-inline-js'); ?>

                        </div>
                    </div>
                </div>
                <div class="wpc-tab-content" id="system-information" style="display:none;">
                  <div class="wpc-tab-content-box">

                    <?php echo $gui::checkboxTabTitle('System Information', '', 'other-optimization/tab-icon.svg', ''); ?>

                    <div class="wpc-spacer"></div>

                    <?php
                    $location = get_option('wps_ic_geo_locate');
                    if (empty($location)) {
                      $location = $this->geoLocate();
                    }

                    if (is_object($location)) {
                      $location = (array)$location;
                    }
                    ?>

                    <div class="wpc-items-list-row mb-20" style="flex-direction:column;">
                      <ul class="wpc-list-item-ul">
                        <li>WP Version:
                          <strong><?php global $wp_version;
                            echo $wp_version; ?></strong>
                        </li>
                        <li>PHP Version:
                          <strong><?php echo phpversion() ?></strong>
                        </li>
                        <li>Site URL:
                          <strong><?php echo site_url() ?></strong>
                        </li>
                        <li>Home URL:
                          <strong><?php echo home_url() ?></strong>
                        </li>
                        <li>API Location:
                          <strong><?php echo print_r($location, true); ?></strong>
                        </li>
                        <li>Bulk Status:
                          <strong><?php echo print_r(get_option('wps_ic_BulkStatus'), true); ?></strong>
                        </li>
                        <li>Parsed Images:
                          <strong><?php echo print_r(get_option('wps_ic_parsed_images'), true); ?></strong>
                        </li>
                        <li>Multisite:
                          <strong><?php if (is_multisite()) {
                              echo 'True';
                            } else {
                              echo 'False';
                            } ?></strong>
                        </li>
                        <li>Maximum upload size:
                          <strong><?php echo size_format(wp_max_upload_size()) ?></strong>
                        </li>
                        <li>Memory limit:
                          <strong><?php echo ini_get('memory_limit') ?></strong>
                        </li>

                        <li>Thumbnails:
                          <strong><?php echo count(get_intermediate_image_sizes()); ?></strong>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>


                <div class="wpc-tab-content" id="debug" style="display:none;">
                  <?php include_once 'debug_tool.php'; ?>
                </div>
              </div>
            </div>
            <!-- Tab Content End -->
          </div>
        </div>
        <!-- Body End -->
    </form>
  </div>

<?php
include 'partials/popups/compatibility-popups.php';
include 'partials/popups/geolocation.php';
include 'partials/popups/cname.php';
include 'partials/popups/exclude-cdn.php';
include 'partials/popups/exclude-lazy.php';
include 'partials/popups/exclude-webp.php';
include 'partials/popups/exclude-adaptive.php';
include 'partials/popups/exclude-critical-css.php';

// HTML Optimizations
include 'partials/popups/exclude-minify-html.php';
include 'partials/popups/exclude-simple-caching.php';
include 'partials/popups/exclude-advanced-caching.php';
include 'partials/popups/exclude-critical-css.php';

// JS Optimizations
include 'partials/popups/js/exclude-js-minify.php';
include 'partials/popups/js/exclude-js-combine.php';
include 'partials/popups/js/exclude-scripts-to-footer.php';
include 'partials/popups/js/exclude-js-defer.php';
include 'partials/popups/js/exclude-js-delay.php';
include 'partials/popups/js/inline-js.php';

// CSS Optimizations
include 'partials/popups/css/exclude-css-combine.php';
include 'partials/popups/css/exclude-css-minify.php';
include 'partials/popups/css/exclude-css-render-blocking.php';
include 'partials/popups/css/inline-css.php';