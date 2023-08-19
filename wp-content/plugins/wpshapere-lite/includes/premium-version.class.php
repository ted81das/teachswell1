<?php
/*
 * ALTER
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
*/

defined('ABSPATH') || die;

if (!class_exists('WPS_PREMIUM')) {

    class WPS_PREMIUM extends WPSHAPERE
    {
        public $aof_options;

        function __construct()
        {
            add_action('admin_menu', array($this, 'wps_premium_menu'));
        }

        function wps_premium_menu() {
            add_submenu_page( WPSHAPERE_MENU_SLUG, __('Upgrade to Premium', 'wps'), __('Premium Version', 'wps'), 'manage_options', 'wps_premium', array($this, 'wps_premium_page') );
        }

        function wps_premium_page()
        {
          ?>
          <div class="wrap wps-wrap">

            <div class="addons-heading wps-new-page-heading">
              <h1>WPShapere Premium<span> Customize the WordPress Admin theme and elements as your wish.</span></h1>
            </div>

            <div class="addons-content-wrap wps-new-content-wrap">

              <a target="_blank" class="addons-action-btn wps-addon-review-link" href="https://wpshapere.com?utm_source=wpshaperelite-banner">
                <?php echo __('Review Plugin', 'wps') ?>
              </a>
              <a target="_blank" class="addons-action-btn wps-addon-purchase-link" href="https://codecanyon.net/cart/configure_before_adding/22169580?license=regular&size=source&support=bundle_12month&utm_source=wpshapereplugin">
                <?php echo __('Purchase Now', 'wps') ?>
              </a>

              <img src="<?php echo WPSHAPERE_LITE_DIR_URI; ?>/assets/images/wpshapere-premium-banner.png" alt="WPShapere" />

            </div>

          </div>

          <?php
        }


    }

}

new WPS_PREMIUM();
