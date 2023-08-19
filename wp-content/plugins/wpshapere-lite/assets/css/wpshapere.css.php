<?php
/**
 * @package WPShapere Lite
 * @author   AcmeeDesign
 * @url     https://wpshapere.com
 * defining css styles for WordPress admin pages.
 */

$css_styles = '';
$css_styles .= '<style type="text/css">';

  if(empty($this->aof_options['default_adminbar_height'])) {
    $css_styles .= '#wpadminbar {height:50px;}';
    $css_styles .= '@media screen and (max-width: 782px){
      #wpadminbar .quicklinks .ab-empty-item, #wpadminbar .quicklinks a, #wpadminbar .shortlink-input {
          height: 46px;
      }
    }
    @media only screen and (min-width:782px) {
      html.wp-toolbar {padding-top: 50px;}
      #wpadminbar .quicklinks>ul>li>a, div.ab-empty-item { padding: 9px !important }
    }
    ';
  }

if( isset( $this->aof_options['bg_color'] ) ) {
  $css_styles .= 'html, #wpwrap, #wp-content-editor-tools { background: ' . $this->aof_options['bg_color'] . '; }';
  $css_styles .= 'ul#adminmenu a.wp-has-current-submenu:after, ul#adminmenu>li.current>a.current:after { ';
    if(is_rtl()) {
      $css_styles .= 'border-left-color: ' . $this->aof_options['bg_color'];
    }
    else {
      $css_styles .= 'border-right-color: ' . $this->aof_options['bg_color'];
    }
  $css_styles .= '}';
}

/* Headings */
$css_styles .= 'h1 { color: ' . $this->aof_options['h1_color'] . '}';
$css_styles .= 'h2 { color: ' . $this->aof_options['h2_color'] . '}';
$css_styles .= 'h3 { color: ' . $this->aof_options['h3_color'] . '}';
$css_styles .= 'h4 { color: ' . $this->aof_options['h4_color'] . '}';
$css_styles .= 'h5 { color: ' . $this->aof_options['h5_color'] . '}';

/* Site logo and title */
$css_styles .= '.quicklinks li.wpshapere_site_title {';
if( isset( $this->aof_options['logo_top_margin'] ) && $this->aof_options['logo_top_margin'] > 0 )
  $css_styles .= 'margin-top:-' . $this->aof_options['logo_top_margin'] . 'px !important;';

if( isset( $this->aof_options['logo_bottom_margin'] ) && $this->aof_options['logo_bottom_margin'] > 0)
  $css_styles .= 'margin-top:' . $this->aof_options['logo_bottom_margin'] . 'px !important;';

$css_styles .= '}';
$css_styles .= '.quicklinks li.wpshapere_site_title a{outline:none; border:none;}';

$adminbar_logo = (is_numeric($this->aof_options['admin_logo'])) ? $this->get_wps_image_url($this->aof_options['admin_logo']) : $this->aof_options['admin_logo'];

$logo_v_margins = '4px';
if(isset($this->aof_options['logo_bottom_margin']) && $this->aof_options['logo_bottom_margin'] != 0) {
  $logo_v_margins = $this->aof_options['logo_bottom_margin'] . 'px';
}
elseif(isset($this->aof_options['logo_top_margin']) && $this->aof_options['logo_top_margin'] != 0) {
  $logo_v_margins = '-' . $this->aof_options['logo_top_margin'] . 'px';
}
$logo_l_margins = 'left';
if(isset($this->aof_options['logo_left_margin']) && $this->aof_options['logo_left_margin'] != 0) {
  $logo_l_margins = $this->aof_options['logo_left_margin'] . 'px';
}

$css_styles .= '.quicklinks li.wpshapere_site_title a, .quicklinks li.wpshapere_site_title a:hover, .quicklinks li.wpshapere_site_title a:focus {';
if(!empty($adminbar_logo)){
  $css_styles .= 'background:url(' . $adminbar_logo . ') ' . $logo_l_margins . ' ' . $logo_v_margins . ' no-repeat !important; text-indent:-9999px !important; width: auto;';
}
if( isset( $this->aof_options['adminbar_logo_resize'] ) && 1 == $this->aof_options['adminbar_logo_resize'] && $this->aof_options['adminbar_logo_size_percent'] > 1) {
  $css_styles .= 'background-size:' . $this->aof_options['adminbar_logo_size_percent'] . '%!important;';
}
else $css_styles .= 'background-size:contain!important;';
$css_styles .= '}';

/* Left Menu */
if(isset($this->aof_options['admin_menu_width']) && !empty($this->aof_options['admin_menu_width'])) {
    $admin_menu_width = $this->aof_options['admin_menu_width'];
    $wp_content_margin = $admin_menu_width + 20;

    $css_styles .= '#adminmenu, #adminmenu .wp-submenu, #adminmenuback, #adminmenuwrap {
            width: ' . $admin_menu_width . 'px}';
    $css_styles .= '#wpcontent, #wpfooter {';
      if(is_rtl()) {
        $css_styles .= 'margin-right: '. $wp_content_margin . 'px';
      } else {
        $css_styles .= 'margin-left: '. $wp_content_margin . 'px';
      }
    $css_styles .= '}';
    $css_styles .= '#adminmenu .wp-submenu {';
      if(is_rtl())
        $css_styles .= 'right:' . $admin_menu_width . 'px';
      else $css_styles .= 'left:' . $admin_menu_width . 'px';
    $css_styles .= '}';
    $css_styles .= '.quicklinks li.wpshapere_site_title {
            width:'. $admin_menu_width . 'px !important}';
}
else {
      $css_styles .= '#adminmenu, #adminmenu .wp-submenu, #adminmenuback, #adminmenuwrap {
        width: 200px;
      }
    #wpcontent, #wpfooter {';
      if(is_rtl())
        $css_styles .= 'margin-right:220px';
      else $css_styles .= 'margin-left:220px';
    $css_styles .= '}';

    $css_styles .= '#adminmenu .wp-submenu {';
      if(is_rtl())
        $css_styles .= 'right: 200px';
      else $css_styles .= 'left:200px';
    $css_styles .= '}';

    $css_styles .= '.quicklinks li.wpshapere_site_title {
        width: 200px !important;
    }';
}

//gutenberg styles
if(isset($this->aof_options['admin_menu_width']) && !empty($this->aof_options['admin_menu_width'])) {
  $guttenberg_header_width = $this->aof_options['admin_menu_width'] . 'px';
}
else {
  $guttenberg_header_width = '200px';
}

$css_styles .= "@media screen and (min-width: 960px){
    body.block-editor-page #editor .edit-post-header,body.block-editor-page #editor .components-notice-list,
    body.auto-fold.block-editor-page #editor .block-editor-editor-skeleton,
    .auto-fold .edit-post-layout .components-editor-notices__snackbar,
    body.auto-fold #wpcontent .interface-interface-skeleton {
      left: $guttenberg_header_width;
    }
  }";
  if(empty($this->aof_options['default_adminbar_height'])) {
    $css_styles .= '@media screen and (min-width: 782px){
      .block-editor .edit-post-header {
          top: 50px!important;
      }
      .block-editor .edit-post-sidebar {
        top:105px;
      }
    }';

    $css_styles .= '@media (min-width: 783px) {
      .block-editor .interface-interface-skeleton {
          top: 50px;
      }
      .is-fullscreen-mode .block-editor .interface-interface-skeleton {
          top: 0;
      }
    }';
  }
  //gutenberg styles

$css_styles .= 'div[aria-labelledby="hor_1_tab_item-7"] h2 {
  display: none;
}
.wps-new-page-heading {
  background: #1789f8;
  color: #fff;
  padding: 50px 30px 90px;
  text-align: center;
}

.wps-new-page-heading h1 {
  color: #fff;
  font-size: 36px;
  line-height: 1.5em;
  font-weight: 500;
}
.wps-new-page-heading h1 span {
  display: block;
  font-size: 15px;
  font-weight: 400;
  line-height: 1.7em;
}
.wps-new-content-wrap {
  width: 80%;
  margin: -50px auto 0;
  background: #fff;
  padding: 45px;
}
.addons-content-wrap {
  width: 616px;
  text-align: center;
}
.wps-new-content-wrap h2 {
  color: #101010;
}
.addons-action-btn {
  text-transform: uppercase;
  margin-bottom:30px;
  background:#298af5;
  color:#fff;
  padding: 9px 15px;
  text-decoration: none;
  display: inline-block;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  -ms-border-radius: 3px;
  border-radius: 3px;
}
.addons-action-btn:hover {
  background: #12355B;
  color: #fff;
}
.addons-action-btn:hover, .addons-action-btn:focus, .addons-action-btn:active {
  border: none;
  outline: none;
  box-shadow:none;
}
.wps-addon-purchase-link {
  margin-left: 7px;
  font-weight: 600;
  padding: 9px 25px;
}
.wps-addon-review-link {
  background: #7da9a1;
}
';

//Impreza theme options header fixed positioning fix
$css_styles .= '.usof-header {
top:50px;
}
.usof-nav {
top:80px;
}';

//Yoast/Wordfence compatibility
$css_styles .= '.wpseo-admin-submit.wpseo-admin-submit-fixed,.wf-options-controls {
  left:200px;
}';


$css_styles .= $this->aof_options['admin_page_custom_css'];
$css_styles .= '</style>';

echo $this->wps_compress_css($css_styles);
