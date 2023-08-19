<?php
/*
 * Configuration for the options function
 */

function is_wps_single() {
   if(!is_multisite())
	return true;
   elseif(is_multisite() && !defined('NETWORK_ADMIN_CONTROL'))
	return true;
   else return false;
}

function get_wps_options() {
  $blog_email = get_option('admin_email');
  $blog_from_name = get_option('blogname');

  if(is_wps_single()) {
    $wps_options = (is_serialized(get_option(WPSHAPERE_LITE_OPTIONS_SLUG))) ? unserialize(get_option(WPSHAPERE_LITE_OPTIONS_SLUG)) : get_option(WPSHAPERE_LITE_OPTIONS_SLUG);
  }
  else {
    $wps_options = (is_serialized(get_site_option(WPSHAPERE_LITE_OPTIONS_SLUG))) ? unserialize(get_site_option(WPSHAPERE_LITE_OPTIONS_SLUG)) : get_site_option(WPSHAPERE_LITE_OPTIONS_SLUG);
  }

  /**
  * get adminbar items
  *
  */
  if(is_wps_single()) {
    $adminbar_items = (is_serialized(get_option(WPS_ADMINBAR_LIST_SLUG))) ? unserialize(get_option(WPS_ADMINBAR_LIST_SLUG)) : get_option(WPS_ADMINBAR_LIST_SLUG);
  }
  else {
    $adminbar_items = (is_serialized(get_site_option(WPS_ADMINBAR_LIST_SLUG))) ? unserialize(get_site_option(WPS_ADMINBAR_LIST_SLUG)) : get_site_option(WPS_ADMINBAR_LIST_SLUG);
  }

  //get all admin users
  $admin_users_array = (is_serialized(get_option(WPS_ADMIN_USERS_SLUG))) ? unserialize(get_option(WPS_ADMIN_USERS_SLUG)) : get_option(WPS_ADMIN_USERS_SLUG);

  //get dashboard widgets
  if(is_wps_single()) {
    $dash_widgets_list = (is_serialized(get_option('wps_widgets_list'))) ? unserialize(get_option('wps_widgets_list')) : get_option('wps_widgets_list');
  }
  else {
    $dash_widgets_list = (is_serialized(get_site_option('wps_widgets_list'))) ? unserialize(get_site_option('wps_widgets_list')) : get_site_option('wps_widgets_list');
  }

  $wps_dash_widgets = array();
  $wps_dash_widgets['welcome_panel'] = "Welcome Panel";
  if(!empty($dash_widgets_list)) {
      foreach( $dash_widgets_list as $dash_widget ) {
          $dash_widget_name = (empty($dash_widget[1])) ? $dash_widget[0] : $dash_widget[1];
          $wps_dash_widgets[$dash_widget[0]] = $dash_widget_name;
      }
  }

  $panel_tabs = array(
      'general' => __( 'General Options', 'wps-lite' ),
      'login' => __( 'Login Options', 'wps-lite' ),
      'dash' => __( 'Dashboard Options', 'wps-lite' ),
      'adminbar' => __( 'Adminbar Options', 'wps-lite' ),
      'adminmenu' => __( 'Admin menu Options', 'wps-lite' ),
      'footer' => __( 'Footer Options', 'wps-lite' ),
      'selecttheme' => __( 'Select Theme', 'wps-lite' ),
      'privilege_users' => __( 'Upgrade to Premium Version', 'wps-lite' ),
      );

  $panel_fields = array();

  //General Options
  $panel_fields[] = array(
      'name' => __( 'General Options', 'wps-lite' ),
      'type' => 'openTab'
  );

  $panel_fields[] = array(
      'name' => __( 'Heading H1 color', 'wps-lite' ),
      'id' => 'h1_color',
      'type' => 'wpcolor',
      'default' => '#333333',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H2 color', 'wps-lite' ),
      'id' => 'h2_color',
      'type' => 'wpcolor',
      'default' => '#222222',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H3 color', 'wps-lite' ),
      'id' => 'h3_color',
      'type' => 'wpcolor',
      'default' => '#222222',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H4 color', 'wps-lite' ),
      'id' => 'h4_color',
      'type' => 'wpcolor',
      'default' => '#555555',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H5 color', 'wps-lite' ),
      'id' => 'h5_color',
      'type' => 'wpcolor',
      'default' => '#555555',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H6 color', 'wps-lite' ),
      'id' => 'h6_color',
      'type' => 'wpcolor',
      'default' => '#555555',
      );

  $panel_fields[] = array(
      'name' => __( 'Remove unwanted items', 'wps-lite' ),
      'id' => 'admin_generaloptions',
      'type' => 'multicheck',
      'desc' => __( 'Select whichever you want to remove.', 'wps-lite' ),
      'options' => array(
          '1' => __( 'Wordpress Help tab.', 'wps-lite' ),
          '2' => __( 'Screen Options.', 'wps-lite' ),
          '3' => __( 'Wordpress update notifications.', 'wps-lite' ),
      ),
      );

  $panel_fields[] = array(
      'name' => __( 'Disable automatic updates', 'wps-lite' ),
      'id' => 'disable_auto_updates',
      'type' => 'checkbox',
      'desc' => __( 'Select to disable all automatic background updates (Not recommended).', 'wps-lite' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Disable update emails', 'wps-lite' ),
      'id' => 'disable_update_emails',
      'type' => 'checkbox',
      'desc' => __( 'Select to disable emails regarding automatic updates.', 'wps-lite' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Hide update notifications', 'wps-lite' ),
      'id' => 'hide_update_note_plugins',
      'type' => 'checkbox',
      'desc' => __( 'Select to hide update notifications on plugins page (Not recommended).', 'wps-lite' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Hide Admin bar', 'wps-lite' ),
      'id' => 'hide_admin_bar',
      'type' => 'checkbox',
      'desc' => __( 'Select to hideadmin bar on frontend.', 'wps-lite' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Custom CSS for Admin pages', 'wps-lite' ),
      'id' => 'admin_page_custom_css',
      'type' => 'textarea',
      );


  //Login Options
  $panel_fields[] = array(
      'name' => __( 'Login Options', 'aof' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => __( 'Disable custom styles for login page.', 'wps-lite' ),
      'id' => 'disable_styles_login',
      'type' => 'checkbox',
      'desc' => __( 'Check to disable', 'wps-lite' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Login page title', 'wps-lite' ),
      'id' => 'login_page_title',
      'type' => 'text',
      'default' => get_bloginfo('name'),
      );

  $panel_fields[] = array(
      'name' => __( 'Background color', 'wps-lite' ),
      'id' => 'login_bg_color',
      'type' => 'wpcolor',
      'default' => '#292931',
      );

  $panel_fields[] = array(
      'name' => __( 'Background image', 'wps-lite' ),
      'id' => 'login_bg_img',
      'type' => 'upload',
      );

  $panel_fields[] = array(
      'name' => __( 'Background Repeat', 'wps-lite' ),
      'id' => 'login_bg_img_repeat',
      'type' => 'checkbox',
      'desc' => __( 'Check to repeat', 'wps-lite' ),
      'default' => true,
      );

  $panel_fields[] = array(
      'name' => __( 'Scale background image', 'wps-lite' ),
      'id' => 'login_bg_img_scale',
      'type' => 'checkbox',
      'desc' => __( 'Scale image to fit Screen size.', 'wps-lite' ),
      'default' => true,
      );

  $panel_fields[] = array(
      'name' => __( 'Upload Logo', 'wps-lite' ),
      'id' => 'admin_login_logo',
      'type' => 'upload',
      'desc' => __( 'Image to be displayed on login page. Maximum width should be under 450pixels.', 'wps-lite' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Logo url', 'wps-lite' ),
      'id' => 'login_logo_url',
      'type' => 'text',
      'default' => get_bloginfo('url'),
      );

  $panel_fields[] = array(
      'name' => __( 'Hide Back to blog link', 'wps-lite' ),
      'id' => 'hide_backtoblog',
      'type' => 'checkbox',
      'default' => false,
      'desc' => __( 'select to hide', 'wps-lite' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Hide Remember me', 'wps-lite' ),
      'id' => 'hide_remember',
      'type' => 'checkbox',
      'default' => false,
      'desc' => __( 'select to hide', 'wps-lite' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Login button colors', 'wps-lite' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => __( 'Button background  color', 'wps-lite' ),
      'id' => 'login_button_color',
      'type' => 'wpcolor',
      'default' => '#7ac600',
      );

  if(isset($wps_options['design_type']) && $wps_options['design_type'] == 2) {
    $panel_fields[] = array(
        'name' => __( 'Button border color', 'wps-lite' ),
        'id' => 'login_button_border_color',
        'type' => 'wpcolor',
        'default' => '#86b520',
        );

    $panel_fields[] = array(
        'name' => __( 'Button shadow color', 'wps-lite' ),
        'id' => 'login_button_shadow_color',
        'type' => 'wpcolor',
        'default' => '#98ce23',
        );
    }

  $panel_fields[] = array(
      'name' => __( 'Button text color', 'wps-lite' ),
      'id' => 'login_button_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Button hover background color', 'wps-lite' ),
      'id' => 'login_button_hover_color',
      'type' => 'wpcolor',
      'default' => '#29ac39',
      );

  $panel_fields[] = array(
      'name' => __( 'Button hover text color', 'wps-lite' ),
      'id' => 'login_button_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  if(isset($wps_options['design_type']) && $wps_options['design_type'] == 2) {
    $panel_fields[] = array(
        'name' => __( 'Button hover border color', 'wps-lite' ),
        'id' => 'login_button_hover_border_color',
        'type' => 'wpcolor',
        'default' => '#259633',
        );

    $panel_fields[] = array(
        'name' => __( 'Button hover shadow color', 'wps-lite' ),
        'id' => 'login_button_hover_shadow_color',
        'type' => 'wpcolor',
        'default' => '#3d7a0c',
        );
  }

  $panel_fields[] = array(
      'name' => __( 'Custom CSS', 'wps-lite' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => __( 'Custom CSS for Login page', 'wps-lite' ),
      'id' => 'login_custom_css',
      'type' => 'textarea',
      );


  //Dash Options
  $panel_fields[] = array(
      'name' => __( 'Dashboard Options', 'aof' ),
      'type' => 'openTab'
      );

  if(!empty($wps_dash_widgets) && is_array($wps_dash_widgets)) {
      $panel_fields[] = array(
          'name' => __( 'Remove unwanted Widgets', 'wps-lite' ),
          'id' => 'remove_dash_widgets',
          'type' => 'multicheck',
          'desc' => __( 'Select whichever you want to remove.', 'wps-lite' ),
          'options' => $wps_dash_widgets,
          );
  }

  //AdminBar Options
  $panel_fields[] = array(
      'name' => __( 'Adminbar Options', 'aof' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => __( 'Set default adminbar height.', 'wps-lite' ),
      'id' => 'default_adminbar_height',
      'type' => 'checkbox',
      'default' => false,
      'desc' => __( 'Select this option to set default admin bar height.', 'wps-lite' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Upload Logo', 'wps-lite' ),
      'id' => 'admin_logo',
      'type' => 'upload',
      'desc' => __( 'Image to be displayed in all pages. Maximum size 200x50 pixels.', 'wps-lite' ),
      );

  if(!empty($adminbar_items)) {
    $panel_fields[] = array(
        'name' => __( 'Remove Unwanted Menus', 'wps-lite' ),
        'id' => 'hide_admin_bar_menus',
        'type' => 'multicheck',
        'desc' => __( 'Select menu items to remove.', 'wps-lite' ),
        'options' => $adminbar_items,
        );
  }

  //Admin menu Options
  $panel_fields[] = array(
      'name' => __( 'Admin menu Options', 'aof' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => __( 'Admin menu width', 'wps-lite' ),
      'id' => 'admin_menu_width',
      'type' => 'number',
      'default' => '200',
      'min' => '160',
      'max' => '400',
      );


  //Footer Options
  $panel_fields[] = array(
      'name' => __( 'Footer Options', 'wps-lite' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => __( 'Footer Text', 'wps-lite' ),
      'id' => 'admin_footer_txt',
      'type' => 'wpeditor',
      'desc' => __( 'Put any text you want to show on admin footer.', 'wps-lite' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Select Theme', 'wps-lite' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => __( 'Choose a color theme', 'wps-lite' ),
      'id' => 'set_wps_theme',
      'type' => 'radio',
      'options' => array(
          '1' => __( 'Default', 'wps-lite' ),
          '2' => __( 'Pomegranate', 'wps-lite' ),
          '3' => __( 'Black and white', 'wps-lite' ),
          '4' => __( 'Beach', 'wps-lite' ),
          '5' => __( 'Africa', 'wps-lite' ),
      ),
      'default' => '1',
      );

  //Privilege feature
  $panel_fields[] = array(
      'name' => __( 'Upgrade to Premium Version', 'aof' ),
      'type' => 'openTab'
      );


  $panel_fields[] = array(
      'name' => __( 'Upgrade to Premium Version', 'wps-lite' ),
      'type' => 'note',
      'desc' => '<a href="https://wpshapere.com?utm_source=wpshaperelite-banner" target="_blank"><img src="'. WPSHAPERE_LITE_DIR_URI . '/assets/images/wpshapere-premium-banner.png" alt="WPShapere" /></a>',
      );

  $output = array('wps_tabs' => $panel_tabs, 'wps_fields' => $panel_fields);
  return $output;
}
