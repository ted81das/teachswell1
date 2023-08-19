<?php
class wpc_gui_v4 extends wps_ic
{


  public static $options;
  public static $default;
  public static $safe;
  public static $stats_local;
  public static $stats_local_sum;
  public static $stats_live;
  public static $user_credits;
  public static $accountQuota;

  public function __construct($options = array())
  {
    if (is_multisite()) {
      $current_blog_id = get_current_blog_id();
      switch_to_blog($current_blog_id);
    }

    $options_class = new wps_ic_options();
    self::$default = $options_class->getDefault();
    self::$safe = $options_class->getSafe();

    if (empty($options)) {
      self::$options = get_option(WPS_IC_SETTINGS);
    } else {
      self::$options = $options;
    }

    // Update Stats
    $lastUpdate = get_transient('wps_ic_stats_update');
    if (empty($lastUpdate) || !$lastUpdate) {
      $settings = get_option(WPS_IC_OPTIONS);
      if (!empty($settings['api_key'])) {
        $getStats = wp_remote_get(WPS_IC_KEYSURL . '?apikey=' . $settings['api_key'] . '&action=pullStats', ['timeout' => 10, 'sslverify' => 'false', 'user-agent' => 'Compress-API Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0']);

        // Set transient only if the response is 200 for stats update
        if (wp_remote_retrieve_response_code($getStats) == 200) {
          set_transient('wps_ic_stats_update', 'true', 60*5);
        }
      }
    }

    $statsclass = new wps_ic_stats();

    if (empty(self::$settings['live-cdn']) || self::$settings['live-cdn'] == '0') {
      // Local
      $stats_local = $statsclass->fetch_local_stats();
      $stats_local_sum = $statsclass->fetch_local_sum_stats();

      if (isset ($stats_local) && !empty($stats_local)) {
        self::$stats_local = $stats_local->data;
      }

      if (isset ($stats_local_sum) && !empty($stats_local_sum)) {
        self::$stats_local_sum = $stats_local_sum->data;
      }
    } else {
      // Live
      $stats_live = $statsclass->fetch_live_stats();

      if (isset ($stats_live) && !empty($stats_live)) {
        self::$stats_live = $stats_live->data;
      }
    }


    self::$user_credits = parent::getAccountStatusMemory('true');

    if (!self::$user_credits) {

    } else {

      if (empty(self::$user_credits->account->quotaType)) {
        self::$user_credits->account->quotaType = 'requests';
      }

      // Get Account Quota
      self::$accountQuota = parent::getAccountQuota(self::$user_credits, self::$user_credits->account->quotaType);
    }
  }


  public static function optimizationLevel($title = 'Demo', $id = 'optimizationLevel', $description = 'Demo', $icon = '', $notify = '', $option = 'default', $locked = false, $value = '1', $configure = false, $tooltip = false, $tooltipPosition = 'left')
  {
    $html = '';

    $active = false;
    $circleActive = '';


    if (!is_array($option)) {
      $tooltipID = 'option_tooltip_' . $option;
    } else {
      $tooltipID = 'option_tooltip_' . $option[0] . '_' . $option[1];
    }

    $html .= '<div class="d-flex align-items-top gap-3 option-box optimization-level wpc-checkbox-description-outer-v1">';
    $html .= '<div class="wpc-checkbox-icon"><img src="' . WPS_IC_ASSETS . '/v4/images/' . $icon . '" /></div>';

    $html .= '<div class="wpc-checkbox-description">
                  <h4 class="fs-500 text-dark-300 fw-500 p-inline bp-10">' . $title . '</h4>';

    if ($tooltip) {
      $html .= '<span class="wpc-custom-tooltip" data-tooltip-id="' . $tooltipID . '" data-tooltip-position="left"><i class="tooltip-icon"></i></span>';
    }

    if (!empty($configure) && $configure !== false) {
      $html .= '<p class="fs-200 text-dark-300 fw-400 p-inline p-float-right"><a href="#" class="wps-ic-configure-popup" data-popup="' . $configure . '" data-popup-width="750">Configure</a></p>';
    }

    if (!$tooltip) {
      $html .= '<p class="fs-300 text-secondary-400 fw-400">' . $description . '</p>';
    } else {
      $html .= '<div id="' . $tooltipID . '" class="wpc-ic-popup wpc-ic-popup-position-' . $tooltipPosition . '" style="display: none;">';

      if (!empty($title)) {
        $html .= '<div class="pop-header">
                      ' . $title . '
                    </div>';
      }

      $html .= '<p class="pop-text">
                      ' . $description . '
                    </p>
                  </div>';
    }

    if (!empty($notify)) {
      $html .= '<div class="activate-notification" style="display:none;">
                    <img src="' . WPS_IC_URI . 'assets/v2/assets/images/notification.png" alt="">
                    <p>' . $notify . '</p>
                  </div>';
    }

    $html .= '</div>';
    $html .= '<div class="form-check">';

    $qualityLevel = 1;
      switch (self::$options['optimization']) {
        case '1':
        case 'lossless':
          $qualityLevel = 1;
          break;
        case '2':
        case 'intelligent':
          $qualityLevel = 2;
          break;
        case '3':
        case 'ultra':
          $qualityLevel = 3;
          break;
      }



    $html .= '<div class="wpc-slider">
                    <div class="wpc-range-slider">
                        <input id="' . $id . '" name="options[qualityLevel]" type="range" step="1" value="' . $qualityLevel . '" min="1" max="3">
                    </div>
                    <div class="wpc-slider-text d-flex align-items-center justify-content-between">
                        <div class="text-min" data-value="1">Lossless</div>
                        <div class="text-middle" data-value="2">Intelligent</div>
                        <div class="text-max" data-value="3">Ultra</div>
                    </div>
                </div>';

    $html .= '</div>';
    $html .= '</div>';

    return $html;
  }


  public static function checkboxDescription($title = 'Demo', $description = 'Demo', $icon = '', $notify = '', $option = 'default', $locked = false, $value = '1', $configure = false, $tooltip = false, $tooltipPosition = 'left')
  {
    $html = '';

    $active = false;
    $circleActive = '';

    if (!is_array($option)) {
      #$optionName = $option;
      $optionName = 'options[' . $option . ']';
      $tooltipID = 'option_tooltip_' . $option;

      if (isset(self::$options[$option]) && self::$options[$option] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option]) && self::$default[$option] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option]) && self::$safe[$option] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    } else {
      #$optionName = $option[0].','.$option[1];
      $optionName = 'options[' . $option[0] . '][' . $option[1] . ']';
      $tooltipID = 'option_tooltip_' . $option[0] . '_' . $option[1];

      if (isset(self::$options[$option[0]][$option[1]]) && self::$options[$option[0]][$option[1]] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option[0]][$option[1]]) && self::$default[$option[0]][$option[1]] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option[0]][$option[1]]) && self::$safe[$option[0]][$option[1]] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    }

    $html .= '<div class="d-flex align-items-top gap-3 option-box wpc-checkbox-description-outer-v1">';
    $html .= '<div class="wpc-checkbox-icon"><img src="' . WPS_IC_ASSETS . '/v4/images/' . $icon . '" /></div>';

    $html .= '<div class="wpc-checkbox-description">
                  <h4 class="fs-500 text-dark-300 fw-600 bp-10 p-inline">' . $title . '</h4>';

    if ($tooltip) {
      $html .= '<span class="wpc-custom-tooltip" data-tooltip-id="' . $tooltipID . '" data-tooltip-position="left"><i class="tooltip-icon"></i></span>';
    }

    if (!empty($configure) && $configure !== false) {
      $html .= '<p class="fs-200 text-dark-300 fw-400 p-inline p-float-right"><a href="#" class="wps-ic-configure-popup" data-popup="' . $configure . '" data-popup-width="750">Configure</a></p>';
    }

    if (!$tooltip) {
      $html .= '<p class="fs-400 text-secondary-400 fw-400">' . $description . '</p>';
    } else {
      $html .= '<div id="' . $tooltipID . '" class="wpc-ic-popup wpc-ic-popup-position-' . $tooltipPosition . '" style="display: none;">';

      if (!empty($title)) {
        $html .= '<div class="pop-header">
                      ' . $title . '
                    </div>';
      }

      $html .= '<p class="pop-text">
                      ' . $description . '
                    </p>
                  </div>';
    }

    if (!empty($notify)) {
      $html .= '<div class="activate-notification" style="display:none;">
                    <img src="' . WPS_IC_URI . 'assets/v2/assets/images/notification.png" alt="">
                    <p>' . $notify . '</p>
                  </div>';
    }

    $html .= '</div>';
    /*
    $html .= '<div class="form-check">';

    if ($active) {
      $html .= '<input class="form-check-input checkbox mt-0 wpc-ic-settings-v2-checkbox" data-option-name="' . $optionName . '" type="checkbox" checked="checked" value="1" id="' . $optionName . '" name="' . $optionName . '"  data-recommended="' . $default . '" data-safe="' . $safe . '">';
      $html .= '<label for="' . $optionName . '"><span></span></label>';
    } else {
      $html .= '<input class="form-check-input checkbox mt-0 wpc-ic-settings-v2-checkbox" data-option-name="' . $optionName . '"  type="checkbox" value="1" id="' . $optionName . '" name="' . $optionName . '"  data-recommended="' . $default . '" data-safe="' . $safe . '">';
      $html .= '<label for="' . $optionName . '"><span></span></label>';
    }

    $html .= '</div>';
    */

    $html .= '<div class="wpc-switch-holder">';
    if ($active) {
      $html .= '<label class="wpc-switch">
  <input type="checkbox" class="wpc-ic-settings-v2-checkbox" data-option-name="' . $optionName . '" checked="checked" value="1" id="' . $optionName . '" name="' . $optionName . '"  data-recommended="' . $default . '" data-safe="' . $safe . '"/>
  <span class="wpc-switch-slider wpc-switch-round"></span>
  </label>';
    } else {
      $html .= '<label class="wpc-switch">
  <input type="checkbox" class="wpc-ic-settings-v2-checkbox" data-option-name="' . $optionName . '" value="1" id="' . $optionName . '" name="' . $optionName . '"  data-recommended="' . $default . '" data-safe="' . $safe . '"/>
  <span class="wpc-switch-slider wpc-switch-round"></span>
  </label>';
    }
    $html .= '</div>';

    $html .= '</div>';

    return $html;
  }


  public static function checkboxTabTitle_Horizontal($title = 'Demo', $description = '', $icon = '', $notify = '', $option = 'default', $locked = false, $value = '1', $configure = false, $tooltip = false, $tooltipPosition = 'left')
  {
    $html = '';

    $active = false;

    $html .= '<div class="d-flex align-items-top gap-3 tab-title-checkbox">';
    $html .= '<div class="wpc-checkbox-icon"><img src="' . WPS_IC_ASSETS . '/v4/images/' . $icon . '" /></div>';

    $html .= '<div class="wpc-checkbox-description">
                <div class="wpc-checkbox-description-inner">
                  <h4 class="fs-500 text-dark-300 fw-500 p-inline">' . $title . '</h4>';

    $html .= '<div class="form-check wpc-horizontal">';
    $html .= '<input class="form-check-input checkbox mt-0 wpc-checkbox-select-all" data-for-div-id="' . $option . '" type="checkbox" value="1" id="select-all-' . $option . '" name="select-all-' . $option . '">';
    $html .= '<label class="with-label" for="select-all-' . $option . '"><div>Select All</div><span></span></label>';
    $html .= '</div>';
    $html .= '</div>';

    if (!empty($description)) {
      $html .= '<p>' . $description . '</p>';
    }


    $html .= '</div>';

    $html .= '</div>';

    return $html;
  }


  /**
   * @param array $args
   * $args = [
   * 'title' => Title of box
   * 'description' => Description
   * 'icon' => icon inside v4/images folder
   * 'optionID' => some unique option id
   * 'connected_to' => option name of other checkbox to connect to
   * ]
   * @return string
   */
  public static function checkboxTabTitle_connected(array $args)
  {
    $html = '';

    $html .= '<div class="d-flex align-items-top gap-3 tab-title-checkbox">';
    $html .= '<div class="wpc-checkbox-icon"><img src="' . WPS_IC_ASSETS . '/v4/images/' . $args['icon'] . '" /></div>';

    $html .= '<div class="wpc-checkbox-description">
                  <h4 class="fs-500 text-dark-300 fw-500 p-inline">' . $args['title'] . '</h4>';

    if (!empty($args['description'])) {
      $html .= '<p>' . $args['description'] . '</p>';
    }

    $html .= '</div>';


    /**
     * Check if connected option is active or not
     */
    $active = false;

    if (is_array($args['connected_to'])) {
      $option = $args['connected_to'];
      $optionNameClean = 'options_' . $option[0] . '_' . $option[1] . '';
      $optionName = 'options[' . $option[0] . '][' . $option[1] . ']';
      if (isset(self::$options[$option[0]][$option[1]]) && self::$options[$option[0]][$option[1]] == '1') {
        // Active
        $active = true;
      } else {
        // Not Active
      }
    } else {
      $option = $args['connected_to'];
      $optionNameClean = 'options_' . $option;
      $optionName = 'options[' . $option . ']';
      if (isset(self::$options[$option]) && self::$options[$option] == '1') {
        // Active
        $active = true;
      } else {
        // Not Active
      }
    }


    if (!empty($args['connected_to'])) {
      if ($active) {
        $html .= '<label class="wpc-switch" for="connected-option-' . $args['optionID'] . '">';
        $html .= '<input type="checkbox" data-connected-option="' . $optionNameClean . '" class="form-check-input checkbox mt-0 wpc-checkbox-connected-option" value="1" checked="checked" id="connected-option-' . $args['optionID'] . '" name="connected-option-' . $args['optionID'] . '"/>';
        $html .= '<span class="wpc-switch-slider wpc-switch-round"></span>';
        $html .= '</label>';
      } else {
        $html .= '<label class="wpc-switch" for="connected-option-' . $args['optionID'] . '">';
        $html .= '<input type="checkbox" data-connected-option="' . $optionNameClean . '" class="form-check-input checkbox mt-0 wpc-checkbox-connected-option" value="1" id="connected-option-' . $args['optionID'] . '" name="connected-option-' . $args['optionID'] . '"/>';
        $html .= '<span class="wpc-switch-slider wpc-switch-round"></span>';
        $html .= '</label>';
      }
    }

    $html .= '</div>';

    return $html;
  }


  public static function checkboxTabTitleCheckbox($title = 'Demo', $description = '', $icon = '', $notify = '', $option = 'default', $locked = false, $value = '1', $configure = false, $tooltip = false, $tooltipPosition = 'left')
  {
    $html = '';


    $html .= '<div class="d-flex align-items-top gap-3 tab-title-checkbox">';
    $html .= '<div class="wpc-checkbox-icon"><img src="' . WPS_IC_ASSETS . '/v4/images/' . $icon . '" /></div>';

    $html .= '<div class="wpc-checkbox-description">';

    if (!$configure) {
      $html .= '<h4 class="fs-500 text-dark-300 fw-500 p-inline">' . $title . '</h4>';
    } else {
      $html .= '<h4 class="fs-500 text-dark-300 fw-500 p-inline" style="display:flex;align-items:center;">' . $title;
      $html .= '<a href="#" class="wps-ic-configure-popup" data-popup="' . $configure . '" data-popup-width="750" style="margin-left:10px">';
      $html .= '<img src="' . WPS_IC_ASSETS . '/v4/images/cog.svg"/>';
      $html .= '</a>';
      $html .= '</h4>';
    }

    if (!empty($description)) {
      $html .= '<p>' . $description . '</p>';
    }


    $html .= '</div>';

    $active = false;
    $circleActive = '';

    if (!is_array($option)) {
      #$optionName = $option;
      $optionName = 'options[' . $option . ']';
      $tooltipID = 'option_tooltip_' . $option;

      if (isset(self::$options[$option]) && self::$options[$option] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option]) && self::$default[$option] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option]) && self::$safe[$option] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    } else {
      #$optionName = $option[0].','.$option[1];
      $optionName = 'options[' . $option[0] . '][' . $option[1] . ']';
      $tooltipID = 'option_tooltip_' . $option[0] . '_' . $option[1];

      if (isset(self::$options[$option[0]][$option[1]]) && self::$options[$option[0]][$option[1]] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option[0]][$option[1]]) && self::$default[$option[0]][$option[1]] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option[0]][$option[1]]) && self::$safe[$option[0]][$option[1]] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    }

    if (!empty($option)) {
      $html .= '<div class="form-check">';
      $html .= '<input class="form-check-input checkbox mt-0 wpc-checkbox-select-all" data-for-div-id="' . $option . '" type="checkbox" value="1" id="select-all-' . $option . '" name="select-all-' . $option . '">';
      $html .= '<label class="with-label" for="select-all-' . $option . '"><div>Select All</div><span></span></label>';
      $html .= '</div>';

      //      $html .= '<label class="wpc-switch" for="select-all-' . $option . '">';
      //      $html .= '<input type="checkbox" data-for-div-id="' . $option . '" class="form-check-input checkbox mt-0 wpc-checkbox-select-all" value="1" id="select-all-' . $option . '" name="select-all-' . $optionName . '"/>';
      //      $html .= '<span class="wpc-switch-slider wpc-switch-round"></span>';
      //      $html .= '</label>';
    }


    $html .= '</div>';

    return $html;
  }

  public static function checkboxTabTitle($title = 'Demo', $description = '', $icon = '', $notify = '', $option = '', $locked = false, $value = '1', $configure = false, $tooltip = false, $tooltipPosition = 'left')
  {
    $html = '';


    $html .= '<div class="d-flex align-items-top gap-3 tab-title-checkbox">';
    $html .= '<div class="wpc-checkbox-icon"><img src="' . WPS_IC_ASSETS . '/v4/images/' . $icon . '" /></div>';

    $html .= '<div class="wpc-checkbox-description">';

    if (!$configure) {
      $html .= '<h4 class="fs-500 text-dark-300 fw-500 p-inline">' . $title . '</h4>';
    } else {
      $html .= '<h4 class="fs-500 text-dark-300 fw-500 p-inline" style="display:flex;align-items:center;">' . $title;
      $html .= '<a href="#" class="wps-ic-configure-popup" data-popup="' . $configure . '" data-popup-width="750" style="margin-left:10px">';
      $html .= '<img src="' . WPS_IC_ASSETS . '/v4/images/cog.svg"/>';
      $html .= '</a>';
      $html .= '</h4>';
    }

    if (!empty($description)) {
      $html .= '<p>' . $description . '</p>';
    }


    $html .= '</div>';

    $active = false;
    $circleActive = '';

    if (!is_array($option)) {
      #$optionName = $option;
      $optionName = 'options[' . $option . ']';
      $tooltipID = 'option_tooltip_' . $option;

      if (isset(self::$options[$option]) && self::$options[$option] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option]) && self::$default[$option] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option]) && self::$safe[$option] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    } else {
      #$optionName = $option[0].','.$option[1];
      $optionName = 'options[' . $option[0] . '][' . $option[1] . ']';
      $tooltipID = 'option_tooltip_' . $option[0] . '_' . $option[1];

      if (isset(self::$options[$option[0]][$option[1]]) && self::$options[$option[0]][$option[1]] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option[0]][$option[1]]) && self::$default[$option[0]][$option[1]] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option[0]][$option[1]]) && self::$safe[$option[0]][$option[1]] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    }

    if (!empty($option)) {
      //    $html .= '<div class="form-check">';
      //      $html .= '<input class="form-check-input checkbox mt-0 wpc-checkbox-select-all" data-for-div-id="' . $option . '" type="checkbox" value="1" id="select-all-' . $option . '" name="select-all-' . $option . '">';
      //      $html .= '<label class="with-label" for="select-all-' . $option . '"><div>Select All</div><span></span></label>';
      //      $html .= '</div>';

      $html .= '<label class="wpc-switch" for="select-all-' . $option . '">';
      $html .= '<input type="checkbox" data-for-div-id="' . $option . '" class="form-check-input checkbox mt-0 wpc-checkbox-select-all" value="1" id="select-all-' . $option . '" name="select-all-' . $optionName . '"/>';
      $html .= '<span class="wpc-switch-slider wpc-switch-round"></span>';
      $html .= '</label>';
    }


    $html .= '</div>';

    return $html;
  }


  public static function textArea_v4($title = 'Demo', $description = 'Demo', $labelTitle = '', $labelValue = '', $notify = '', $option = 'default', $locked = false, $value = '1', $configure = false, $tooltip = false, $tooltipPosition = 'left', $beta = false)
  {
    $html = '';

    $optionValue = '';
    $active = false;
    $circleActive = '';

    if (!is_array($option)) {
      #$optionName = $option;
      $optionName_cleaned = 'options_' . $option;
      $optionName = 'options[' . $option . ']';
      $tooltipID = 'option_tooltip_' . $option;

      if (!empty(self::$options[$option])) {
        $optionValue = self::$options[$option];
      }

    } else {
      $optionName_cleaned = 'options_' . $option[0] . '_' . $option[1];
      $optionName = 'options[' . $option[0] . '][' . $option[1] . ']';
      $tooltipID = 'option_tooltip_' . $option[0] . '_' . $option[1];


      if (!empty(self::$options[$option[0]][$option[1]])) {
        $optionValue = self::$options[$option[0]][$option[1]];
      }
    }

    $cssClass = '';
    if (empty($description)) {
      $cssClass = 'no-description';
    }

    $html = '<div class="wpc-box-for-textarea ' . $cssClass . '">
                                       <div class="wpc-box-content">
                                       <div class="wpc-checkbox-title-holder">
                                           <div class="circle-check active"></div>
                                           ';

    if (!empty($configure) && $configure !== false) {
      $html .= '<h4>' . $title . '';
      if ($beta) {
        $html .= '<span class="wpc-beta-badge">BETA</span>';
      }
      $html .= '<a href="#" class="wps-ic-configure-popup" data-popup="' . $configure . '" data-popup-width="750">';
      $html .= '<img src="' . WPS_IC_ASSETS . '/v4/images/cog.svg"/>';
      $html .= '</a>';
      $html .= '</h4>';
    } else {
      $html .= '<h4>' . $title;
      if ($beta) {
        $html .= '<span class="wpc-beta-badge">BETA</span>';
      }
      $html .= '</h4>';
    }

    $html .= '</div>';

    if (!empty($description)) {
      $html .= '<p>' . $description . '</p>';
    }

    $html .= '</div>';

    $html .= '<div class="wpc-box-textarea">';


    $html .= '<div class="wpc-input-holder">';
    $html .= '<textarea name="' . $optionName . '">' . $optionValue . '</textarea>';
    $html .= '</div>';

    $html .= '</div>';

    $html .= '</div>';

    return $html;
  }

  public static function inputDescription_v4($title = 'Demo', $description = 'Demo', $labelTitle = '', $labelValue = '', $notify = '', $option = 'default', $locked = false, $value = '1', $configure = false, $tooltip = false, $tooltipPosition = 'left', $beta = false)
  {
    $html = '';

    $optionValue = '';
    $active = false;
    $circleActive = '';

    if (!is_array($option)) {
      #$optionName = $option;
      $optionName_cleaned = 'options_' . $option;
      $optionName = 'options[' . $option . ']';
      $tooltipID = 'option_tooltip_' . $option;

      if (!empty(self::$options[$option])) {
        $optionValue = self::$options[$option];
      }

    } else {
      $optionName_cleaned = 'options_' . $option[0] . '_' . $option[1];
      $optionName = 'options[' . $option[0] . '][' . $option[1] . ']';
      $tooltipID = 'option_tooltip_' . $option[0] . '_' . $option[1];


      if (!empty(self::$options[$option[0]][$option[1]])) {
        $optionValue = self::$options[$option[0]][$option[1]];
      }
    }

    $cssClass = '';
    if (empty($description)) {
      $cssClass = 'no-description';
    }

    $html = '<div class="wpc-box-for-input ' . $cssClass . '">
                                       <div class="wpc-box-content">
                                       <div class="wpc-checkbox-title-holder">
                                           <div class="circle-check active"></div>
                                           ';

    if (!empty($configure) && $configure !== false) {
      $html .= '<h4>' . $title . '';
      if ($beta) {
        $html .= '<span class="wpc-beta-badge">BETA</span>';
      }
      $html .= '<a href="#" class="wps-ic-configure-popup" data-popup="' . $configure . '" data-popup-width="750">';
      $html .= '<img src="' . WPS_IC_ASSETS . '/v4/images/cog.svg"/>';
      $html .= '</a>';
      $html .= '</h4>';
    } else {
      $html .= '<h4>' . $title;
      if ($beta) {
        $html .= '<span class="wpc-beta-badge">BETA</span>';
      }
      $html .= '</h4>';
    }

    $html .= '</div>';

    if (!empty($description)) {
      $html .= '<p>' . $description . '</p>';
    }

    $html .= '</div>';

    $html .= '<div class="wpc-box-check">';

    $html .= '<div class="wpc-input-holder">';
    $html .= '<span>' . $labelTitle. '</span>';
    $html .= '<input type="text" name="' . $optionName . '" value="' . $optionValue . '" placeholder="'.$value.'" />';
    $html .= '<span>' . $labelValue . '</span>';
    $html .= '</div>';

    $html .= '</div>';

    $html .= '</div>';

    return $html;
  }


  public static function buttonDescription_v4($title = 'Demo', $description = 'Demo', $icon = '', $notify = '', $option = 'default', $locked = false, $value = '1', $configure = false, $tooltip = false, $tooltipPosition = 'left', $beta = false)
  {
    $html = '';

    $active = false;
    $circleActive = '';

    if (!is_array($option)) {
      #$optionName = $option;
      $optionName_cleaned = 'options_' . $option;
      $optionName = 'options[' . $option . ']';
      $tooltipID = 'option_tooltip_' . $option;

      if (isset(self::$options[$option]) && self::$options[$option] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option]) && self::$default[$option] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option]) && self::$safe[$option] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    } else {
      $optionName_cleaned = 'options_' . $option[0] . '_' . $option[1];
      $optionName = 'options[' . $option[0] . '][' . $option[1] . ']';
      $tooltipID = 'option_tooltip_' . $option[0] . '_' . $option[1];

      $value = get_option($option[0]);
      if (!empty($value[$option[1]][0])) {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option[0]][$option[1]]) && self::$default[$option[0]][$option[1]] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option[0]][$option[1]]) && self::$safe[$option[0]][$option[1]] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    }

    $cssClass = '';
    if (empty($description)) {
      $cssClass = 'no-description';
    }

    $html = '<div class="wpc-box-for-checkbox ' . $cssClass . '">
                                       <div class="wpc-box-content">
                                       <div class="wpc-checkbox-title-holder">
                                           <div class="circle-check ' . $circleActive . '"></div>
                                           ';


      $html .= '<h4>' . $title;
      if ($beta) {
        $html .= '<span class="wpc-beta-badge">BETA</span>';
      }
      $html .= '</h4>';

    $html .= '</div>';

    if (!empty($description)) {
      $html .= '<p>' . $description . '</p>';
    }

    $html .= '</div>';

    $html .= '<div class="wpc-box-button">';


    $popupData = '';
    $popup = false;
    if (!empty($notify)) {
      $popupData = $notify;
      $popup = 'wpc-show-popup wpc-popup-' . $notify;
    }

    $contactSupport = 'data-custom-buttons="false"';
    if ($notify == 'delay-js' || $notify == 'combine-css' || $notify == 'combine-js') {
      $contactSupport = 'data-custom-buttons="true"';
    }

    $html .= '<a href="#" class="wps-ic-configure-popup" data-popup="' . $configure . '" data-popup-width="750">';
    $html .= 'Configure';
    $html .= '</a>';

    $html .= '</div>';

    $html .= '</div>';

    return $html;
  }


  public static function checkboxDescription_v4($title = 'Demo', $description = 'Demo', $icon = '', $notify = '',
$option = 'default', $locked = false, $value = '1', $configure = false, $tooltip = false, $tooltipPosition = 'left', $beta = false)
  {
    $html = '';

    $active = false;
    $circleActive = '';

    if (!is_array($option)) {
      #$optionName = $option;
      $optionName_cleaned = 'options_' . $option;
      $optionName = 'options[' . $option . ']';
      $tooltipID = 'option_tooltip_' . $option;

      if (isset(self::$options[$option]) && self::$options[$option] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option]) && self::$default[$option] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option]) && self::$safe[$option] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    } else {
      $optionName_cleaned = 'options_' . $option[0] . '_' . $option[1];
      $optionName = 'options[' . $option[0] . '][' . $option[1] . ']';
      $tooltipID = 'option_tooltip_' . $option[0] . '_' . $option[1];

      if (isset(self::$options[$option[0]][$option[1]]) && self::$options[$option[0]][$option[1]] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option[0]][$option[1]]) && self::$default[$option[0]][$option[1]] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option[0]][$option[1]]) && self::$safe[$option[0]][$option[1]] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    }

    $cssClass = '';
    if (empty($description)) {
      $cssClass = 'no-description';
    }

    $html = '<div class="wpc-box-for-checkbox ' . $cssClass . '">
                                       <div class="wpc-box-content">
                                       <div class="wpc-checkbox-title-holder">
                                           <div class="circle-check ' . $circleActive . '"></div>
                                           ';

    if (!empty($configure) && $configure !== false) {
      $html .= '<h4>' . $title . '';
      if ($beta) {
        $html .= '<span class="wpc-beta-badge">BETA</span>';
      }
      $html .= '<a href="#" class="wps-ic-configure-popup" data-popup="' . $configure . '" data-popup-width="750">';
      $html .= '<img src="' . WPS_IC_ASSETS . '/v4/images/cog.svg"/>';
      $html .= '</a>';
      $html .= '</h4>';
    } else {
      $html .= '<h4>' . $title;
      if ($beta) {
        $html .= '<span class="wpc-beta-badge">BETA</span>';
      }
      $html .= '</h4>';
    }

    $html .= '</div>';

    if (!empty($description)) {
      $html .= '<p>' . $description . '</p>';
    }

    $html .= '</div>';

    $html .= '<div class="wpc-box-check">';


    $popupData = '';
    $popup = false;
    if (!empty($notify)) {
      $popupData = $notify;
      $popup = 'wpc-show-popup wpc-popup-' . $notify;
    }

    $contactSupport = 'data-custom-buttons="false"';
    if ($notify == 'delay-js' || $notify == 'combine-css' || $notify == 'combine-js') {
      $contactSupport = 'data-custom-buttons="true"';
    }

    if ($active) {
      $html .= '<label class="wpc-switch">
  <input type="checkbox" class="wpc-ic-settings-v4-checkbox ' . $popup . '" data-popup="' . $popupData . '" '.$contactSupport.' checked="checked" value="1" id="' . $optionName_cleaned . '" name="' . $optionName . '"  data-recommended="' . $default . '" data-safe="' . $safe . '" data-connected-slave-option="' . $optionName_cleaned . '"/>
  <span class="wpc-switch-slider wpc-switch-round"></span>
  </label>';
    } else {
      $html .= '<label class="wpc-switch">
  <input type="checkbox" class="wpc-ic-settings-v4-checkbox ' . $popup . '" data-popup="' . $popupData . '" '.$contactSupport.' value="1" id="' . $optionName_cleaned . '" name="' . $optionName . '"  data-recommended="' . $default . '" data-safe="' . $safe . '" data-connected-slave-option="' . $optionName_cleaned . '"/>
  <span class="wpc-switch-slider wpc-switch-round"></span>
  </label>';
    }

    $html .= '</div>';

    $html .= '</div>';

	  if ($option == 'exclude-url-from-all' || (is_array($option) && $option[0] == 'exclude-url')) {
		  include WPS_IC_DIR . 'templates/admin/partials/popups/exclude-url.php';
	  }

    return $html;
  }


  public static function getSetting($name)
  {
    return self::$options[$name];
  }


  public static function iconCheckBox($title = 'Demo', $icon = '', $option = 'default')
  {
    $html = '';

    $active = false;
    $circleActive = '';
    if (!is_array($option)) {
      #$optionName = $option;
      $optionName = 'options[' . $option . ']';
      $tooltipID = 'option_tooltip_' . $option;

      if (!empty(self::$options[$option]) && self::$options[$option] == '1') {
        $active = true;
        $circleActive = 'active';
      }

//      $html .= print_r($active,true);
//      $html .= print_r($option,true);
//      $html .= print_r(self::$options,true);
//      $html .= print_r(self::$options[$option],true);

      if (!empty(self::$default[$option]) && self::$default[$option] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (!empty(self::$safe[$option]) && self::$safe[$option] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    } else {
      #$optionName = $option[0].','.$option[1];
      $optionName = 'options[' . $option[0] . '][' . $option[1] . ']';
      $tooltipID = 'option_tooltip_' . $option[0] . '_' . $option[1];

      if (!empty(self::$options[$option[0]][$option[1]]) && self::$options[$option[0]][$option[1]] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (!empty(self::$default[$option[0]][$option[1]]) && self::$default[$option[0]][$option[1]] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (!empty(self::$safe[$option[0]][$option[1]]) && self::$safe[$option[0]][$option[1]] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    }

    $html .= '
<div class="wpc-iconcheckbox ' . $circleActive . '">
    <div class="wpc-iconcheckbox-icon">
        <img src="' . WPS_IC_ASSETS . '/v4/images/' . $icon . '"/>
    </div>
    <div class="wpc-iconcheckbox-title">
        ' . $title . '
    </div>
    <div class="wpc-iconcheckbox-toggle">';


    if ($active) {
      $html .= '<input class="form-check-input checkbox mt-0 wpc-ic-settings-v4-iconcheckbox" data-option-name="' . $optionName . '" type="checkbox" checked="checked" value="1" id="' . $optionName . '" name="' . $optionName . '"
                         data-recommended="' . $default . '" data-safe="' . $safe . '">';
      $html .= '<label for="' . $optionName . '"><span></span></label>';
    } else {
      $html .= '<input class="form-check-input checkbox mt-0 wpc-ic-settings-v4-iconcheckbox" data-option-name="' . $optionName . '" type="checkbox" value="1" id="' . $optionName . '" name="' . $optionName . '"
                         data-recommended="' . $default . '" data-safe="' . $safe . '">';
      $html .= '<label for="' . $optionName . '"><span></span></label>';
    }

    $html .= '
    </div>
</div>';

    return $html;
  }


  public static function checkBoxOption($title = 'Demo', $option = 'default', $locked = false, $value = '1', $align = 'right', $description = '', $tooltip = false, $tooltipPosition = 'top')
  {
    $html = '';

    $active = false;
    $circleActive = '';

    if (!is_array($option)) {
      #$optionName = $option;
      $optionName = 'options[' . $option . ']';
      $tooltipID = 'option_tooltip_' . $option;
      if (isset(self::$options[$option]) && self::$options[$option] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option]) && self::$default[$option] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option]) && self::$safe[$option] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    } else {
      #$optionName = $option[0].','.$option[1];
      $optionName = 'options[' . $option[0] . '][' . $option[1] . ']';
      $tooltipID = 'option_tooltip_' . $option[0] . '_' . $option[1];

      if (isset(self::$options[$option[0]][$option[1]]) && self::$options[$option[0]][$option[1]] == '1') {
        $active = true;
        $circleActive = 'active';
      }

      if (isset(self::$default[$option[0]][$option[1]]) && self::$default[$option[0]][$option[1]] == '1') {
        $default = 1;
      } else {
        $default = 0;
      }

      if (isset(self::$safe[$option[0]][$option[1]]) && self::$safe[$option[0]][$option[1]] == '1') {
        $safe = 1;
      } else {
        $safe = 0;
      }
    }

    if ($align == 'right') {
      $html .= '
<div class="accordion-item option-item option-box">
    <h2 class="accordion-header d-flex align-items-center justify-content-between gap-2 fs-400" id="flush-headingOne">
        <div class="d-flex align-items-center gap-2">
            <div class="circle-check ' . $circleActive . '"></div>
            <p class="fs-300 text-dark-300">' . $title . '</p>';

      if ($tooltip) {
        $html .= '<span class="wpc-custom-tooltip" data-tooltip-id="' . $tooltipID . '"
                            data-tooltip-position="' . $tooltipPosition . '"><i class="tooltip-icon"></i></span>';
      }

      $html .= '
        </div>
        <div class="form-check">';

      if ($locked) {
        $html .= '<input class="form-check-input checkbox mt-0 locked-checkbox" type="checkbox" value="1"
                             id="flexCheckDefault">';
      } else {
        if ($active) {
          $html .= '<input class="form-check-input checkbox mt-0 wpc-ic-settings-v2-checkbox"
                             data-option-name="' . $optionName . '" type="checkbox" checked="checked" value="1"
                             id="' . $optionName . '" name="' . $optionName . '" data-recommended="' . $default . '"
                             data-safe="' . $safe . '">';
          $html .= '<label for="' . $optionName . '"><span></span></label>';
        } else {
          $html .= '<input class="form-check-input checkbox mt-0 wpc-ic-settings-v2-checkbox"
                             data-option-name="' . $optionName . '" type="checkbox" value="1" id="' . $optionName . '"
                             name="' . $optionName . '" data-recommended="' . $default . '" data-safe="' . $safe . '">';
          $html .= '<label for="' . $optionName . '"><span></span></label>';
        }
      }

      if ($tooltip) {

        $html .= '
            <div id="' . $tooltipID . '" class="wpc-ic-popup wpc-ic-popup-position-' . $tooltipPosition . '"
                 style="display: none;">';

        if (!empty($title)) {
          $html .= '
                <div class="pop-header">
                    ' . $title . '
                </div>
                ';
        }

        $html .= '<p class="pop-text">
                    ' . $description . '
                </p>
            </div>
            ';
      }

      $html .= '
        </div>
    </h2>
</div>';
    } else {
      $html .= '
<div class="accordion-item option-item">
    <h2 class="accordion-header d-flex align-items-center justify-content-between gap-2 fs-400" id="flush-headingOne">
        <div class="d-flex align-items-center gap-2">';

      if ($locked) {
        $html .= '<input class="form-check-input checkbox mt-0 locked-checkbox" type="checkbox" value="1"
                             id="flexCheckDefault">';
      } else {
        if ($active) {
          $html .= '<input class="form-check-input checkbox mt-0 wpc-ic-settings-v2-checkbox"
                             data-option-name="' . $optionName . '" type="checkbox" checked="checked" value="1"
                             id="' . $optionName . '" name="' . $optionName . '" data-recommended="' . $default . '"
                             data-safe="' . $safe . '">';
          $html .= '<label for="' . $optionName . '"><span></span></label>';
        } else {
          $html .= '<input class="form-check-input checkbox mt-0 wpc-ic-settings-v2-checkbox"
                             data-option-name="' . $optionName . '" type="checkbox" value="1" id="' . $optionName . '"
                             name="' . $optionName . '" data-recommended="' . $default . '" data-safe="' . $safe . '">';
          $html .= '<label for="' . $optionName . '"><span></span></label>';
        }
      }

      $html .= '<p class="text-dark-300">' . $title . '</p>';
      $html .= '
        </div>
        <div class="form-check">';

      $html .= '
        </div>
    </h2>
</div>';
    }

    return $html;
  }


  public static function presetModes()
  {
    $html = '';

    $html .= '<div class="wpc-preset-modes-container">
                                <div class="wpc-preset-modes-container-inner">
                                <div class="wpc-preset-modes-icon">
                                  <img src="' . WPS_IC_URI . 'assets/v4/images/preset-modes.svg" style="width:80px;margin-left:20px;margin-right:40px;"/>
                                </div>';


    $html .= '<div class="wpc-preset-mode-title">
                    <div>
                       <h4 class="fs-500 text-dark-300 fw-500 p-inline mb-10" style="margin-top:0;margin-bottom:10px;">Preset Optimization Modes</h4>
                    </div>
                    <div class="setting-value setting-configure">
                         <p style="margin:0;">One-click configure recommended image optimization settings and performance tweaks based on your preferences and website compatibility.</p>
                    </div>
                </div>';


    $preset_config = get_option(WPS_IC_PRESET);
    $preset = array('recommended' =>'Recommended Mode',
										'safe' => 'Safe Mode',
                    'aggressive' =>'Aggressive Mode',
                    'custom' =>'Custom');

    if (empty($preset_config)) {
      update_option('wps_ic_preset_setting', 'custom');
      $preset_config = 'custom';
    }

    $html .= '<input type="hidden" name="wpc_preset_mode" value="' . $preset_config . '" />
<div class="wpc-dropdown wpc-dropdown-trigger-popup">
  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    ' . $preset[$preset_config] . '
  </button>
  <div class="wpc-dropdown-menu">';

    foreach ($preset as $k => $v) {
      $s = '';
      if ($k == $preset_config) {
        $s = 'active';
      }
      $html .= '<a class="dropdown-item ' . $s . '" data-preset-title="' . $v . '" data-value="' . $k . '">' . $v . '</a>';
    }

    $html .= '</div>
</div>';


    $html .= '</div>
            </div>';

    return $html;
  }


  public static function cname()
  {
    $html = '';
    $html .= '<div class="wpc-tab-content-box wpc-tab-content-cname" style="display:flex;align-items:center;justify-content: space-between;">
                                <div style="display:flex;align-items:center;">
                                    <img src="' . WPS_IC_URI . 'assets/images/icon-exclude-list.svg"
                                         style="width:60px;margin-left:20px;margin-right:40px;"/>';

    $zone_name = get_option('ic_custom_cname');
    if (!empty($zone_name)) {
      $html .= '<div style="flex-direction: column;display: flex;justify-content: center;">
                    <div>
                       <h4 class="fs-500 text-dark-300 fw-500 p-inline mb-10" style="margin-top:0;margin-bottom:10px;">Custom CDN Domain</h4>
                    </div>
                    <div class="setting-value setting-configured cname-configured">
                       <strong>Connected Domain: ' . $zone_name . '</strong><br/>
                    </div>
                    <div class="setting-value setting-configure" style="display: none;">
                         <p style="margin:0;">Use <strong>any domain</strong> you own to serve images and assets.</p>
                    </div>
                </div>';

    } else {
      $html .= '<div style="flex-direction: column;display: flex;justify-content: center;">
                    <div>
                        <h4 class="fs-500 text-dark-300 fw-500 p-inline" style="margin-top:0;margin-bottom:10px;">Custom CDN Domain</h4>
                    </div>
                    <div class="setting-value setting-configured cname-configured" style="display: none;">
                        <strong>Connected Domain: ' . $zone_name . '</strong><br/>
                    </div>
                    <div class="setting-value setting-configure">
                        <p style="margin:0;">Use <strong>any domain</strong> you own to serve images and assets.</p>
                    </div>
                </div>';
    }

    $html .= '</div>
              <div>';

    $zone_name = get_option('ic_custom_cname');
    if (!empty($zone_name)) {
      $html .= '<a href="#" class="wps-ic-configure-popup setting-configured" data-popup="remove-custom-cdn">
                <i class="icon-trash"></i> Remove</a>
                <a href="#" class="wps-ic-configure-popup setting-configure" data-popup-width="600" data-popup="custom-cdn" style="display:none;">Configure</a>';

    } else {
      $html .= '
    <a href="#" class="wps-ic-configure-popup setting-configured" data-popup="remove-custom-cdn" style="display: none;">
    <iclass="icon-trash"></i> Remove</a>
    <a href="#" class="wps-ic-configure-popup setting-configure" data-popup-width="600" data-popup="custom-cdn">Configure</a>';
    }

    $html .= '</div>
            </div>';

    return $html;
  }


  public static function usageGraph()
  {

    include WPS_IC_DIR . 'templates/admin/partials/v4/chart.php';

  }


  public static function usageStats()
  {
    include WPS_IC_DIR . 'templates/admin/partials/v4/stats.php';
    include WPS_IC_DIR . 'templates/admin/partials/v4/footer-scripts.php';
  }

}