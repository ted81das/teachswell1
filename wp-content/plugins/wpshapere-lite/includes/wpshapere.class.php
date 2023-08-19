<?php
/**
 * @package  WPShapere Lite
 * @author   AcmeeDesign
 * @url      https://wpshapere.com
*/

defined('ABSPATH') || die;

if (!class_exists('WPSHAPERE')) {

  class WPSHAPERE
  {
  	private $wp_df_menu;
  	private $wp_df_submenu;
  	private $wps_options = WPSHAPERE_LITE_OPTIONS_SLUG;
    public $aof_options;
    private $do_not_save;

	function __construct()
	{
      $this->do_not_save = array('title', 'openTab', 'import', 'export');
      $this->aof_options = $this->get_wps_option_data($this->wps_options);
      add_action('admin_menu', array($this, 'wps_sub_menus'));
      add_action('wp_dashboard_setup', array($this, 'initialize_dash_widgets'), 999);

	    add_filter('admin_title', array($this, 'custom_admin_title'), 999, 2);
	    add_action( 'init', array($this, 'initFunctionss') );

	    add_action( 'admin_bar_menu', array($this, 'add_wpshapere_menus'), 1 );
      add_action( 'admin_bar_menu', array($this, 'wps_save_adminbar_nodes'), 9990 );
      add_action( 'wp_before_admin_bar_render', array($this, 'wps_save_adminbar_nodes'), 9990 );
	    add_action('wp_dashboard_setup', array($this, 'manage_widget_functions'), 9999);
      if( isset( $this->aof_options['disable_styles_login'] ) && 1 != $this->aof_options['disable_styles_login'] ) {
          if ( ! has_action( 'login_enqueue_scripts', array($this, 'wpshapereloginAssets') ) )
          add_action('login_enqueue_scripts', array($this, 'wpshapereloginAssets'), 10);
          add_action('login_head', array($this, 'wpshapeLogincss'));
          add_action('login_header', array($this, 'wps_login_form_wrap_start'), 1);
          add_action('login_footer', array($this, 'wps_login_form_wrap_close'), 99);
       }
	    add_action( 'admin_enqueue_scripts', array($this, 'wpshapereAssets'), 99999 );
      add_action('admin_head', array($this, 'wpshapeOptionscss'));
	    add_action('wp_before_admin_bar_render', array($this, 'wps_remove_bar_links'), 9999);
      if(!empty($this->aof_options['adminbar_custom_welcome_text']))
        add_action( 'admin_bar_menu', array($this, 'update_avatar_size'), 99 );
	    add_filter('login_headerurl', array($this, 'wpshapere_login_url'));
	    add_filter('login_headertext', array($this, 'wpshapere_login_title'));
	    add_action('admin_head', array($this, 'generalFns'));
	    add_action('wp_head', array($this, 'frontendActions'), 99999);
      add_action( 'aof_after_heading', array($this, 'wps_help_link'));
      add_filter( 'login_title', array($this, 'login_page_title') );
	}

  /*
  * WPShapere Knowledgebase link
  */
  function wps_help_link() {
    echo '<div class="wps_kb_link"><a class="wps_kb_link" target="_blank" href="https://kb.acmeedesign.com/kbase_categories/wpshapere/"><span class="dashicons dashicons-editor-help"></span> ';
    echo __('Visit Knowledgebase', 'wps');
    echo '</a></div>';
  }

  /* custom login page title */
  function login_page_title() {
    if(!empty($this->aof_options['login_page_title'])) {
      return $this->aof_options['login_page_title'];
    }
    else return get_bloginfo('name');
  }

  /*
  * function to determine multi customization is enabled
  */
	public function is_wps_single() {
	    if(!is_multisite())
		return true;
	    elseif(is_multisite() && !defined('NETWORK_ADMIN_CONTROL'))
		return true;
	    else return false;
	}

	public function initFunctionss(){
		if($this->aof_options['disable_auto_updates'] == 1)
			add_filter( 'automatic_updater_disabled', '__return_true' );

		if($this->aof_options['disable_update_emails'] == 1)
			add_filter( 'auto_core_update_send_email', '__return_false' );
	}

	public function wpshapereloginAssets()
	{
		wp_enqueue_script("jquery");
		wp_enqueue_script( 'loginjs-js', WPSHAPERE_LITE_DIR_URI . 'assets/js/loginjs.js', array( 'jquery' ), '', true );
	}
	public function wpshapereAssets($nowpage)
	{
    wp_enqueue_script('jquery');
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style('wps-theme', WPSHAPERE_LITE_DIR_URI . 'assets/css/' . $this->get_wps_set_theme(), '', WPSHAPERE_LITE_VERSION);
	}

	public function wpshapeLogincss()
	{
    include_once( WPSHAPERE_LITE_PATH . 'assets/css/wpshapere.login.css.php');
	}

  function wps_login_form_wrap_start() {
     echo '<div class="wps-login-container">
     <div class="wps-login-bg"></div>';
   }

   function wps_login_form_wrap_close() {
     ?>
     <div class="clear"></div></div>
     <?php
     //get login page path
     $login_page = basename($_SERVER['REQUEST_URI'], '?'.$_SERVER['QUERY_STRING']);
     if($login_page == 'wp-login.php' && isset($_GET['action']) && $_GET['action'] == 'rp')
      return;
      ?>
     <script type="text/javascript">
       jQuery(document).ready(function(){
         jQuery( "#user_login" ).before( "<div class='wps-icon-login'></div>" );
         jQuery( "#user_email" ).before( "<div class='wps-icon-email'></div>" );
         jQuery( "#user_pass" ).before( "<div class='wps-icon-pwd'></div>" );
         jQuery('#user_login,#user_email').attr('autocomplete', 'off');
       });
     </script>
     <?php
   }

	public function wpshapeOptionscss()
	{
	  include_once( WPSHAPERE_LITE_PATH . 'assets/css/wpshapere.css.php');
	}

  public function get_wps_set_theme() {
    $wps_themes = array();
    $wps_themes[1] = 'default';
    $wps_themes[2] = 'pomegranate';
    $wps_themes[3] = 'black-white';
    $wps_themes[4] = 'beach';
    $wps_themes[5] = 'africa';

    $wps_chosen_theme = ( !empty($this->aof_options['set_wps_theme']) ) ? $this->aof_options['set_wps_theme'] : null;

    if(!empty($wps_chosen_theme)) {
      $wps_theme_name = $wps_themes[$wps_chosen_theme];
    }
    else {
      $wps_theme_name = $wps_themes[1];
    }

    return $wps_theme_name . "-theme.css";
  }

	public function generalFns() {
	    $screen = get_current_screen();
      $admin_general_options_data = ( !empty($this->aof_options['admin_generaloptions']) ) ? $this->aof_options['admin_generaloptions'] : "";
      $admin_generaloptions = (is_serialized( $admin_general_options_data )) ? unserialize( $admin_general_options_data ) : $admin_general_options_data;
      if(!empty($admin_generaloptions)) {
        foreach($admin_generaloptions as $general_opt) {
          if(isset($screen) && $general_opt == 1) {
                  $screen->remove_help_tabs();
          }
          elseif($general_opt == 2) {
                  add_filter('screen_options_show_screen', '__return_false');
          }
          elseif($general_opt == 3) {
                  remove_action('admin_notices', 'update_nag', 3);
          }
          elseif($general_opt == 4) {
                  remove_submenu_page('index.php', 'update-core.php');
          }
        }
	    }
	    //footer contents
	    add_filter( 'admin_footer_text', array($this, 'wpsbrandFooter') );
	    //remove wp version
	    add_filter( 'update_footer', array($this, 'wpsremoveVersion'), 99);

	    //prevent access to wpshapere menu for non-superadmin
	    if( (!current_user_can('manage_network')) && defined('NETWORK_ADMIN_CONTROL') ){
        if(isset($screen->id)) {
      		if($screen->id == "toplevel_page_wpshapere-options" || $screen->id == "wpshapere-options_page_wps_admin_menuorder" || $screen->id == "wpshapere-options_page_wps_impexp_settings") {
      		    wp_die("<div style='width:70%; margin: 30px auto; padding:30px; background:#fff'><h4>Sorry, you don't have sufficient previlege to access to this page!</h4></div>");
      		    exit();
      		}
        }
	    }
	?>

		<?php
	}

	public function custom_admin_title($admin_title, $title)
	{
	    return $title . " &#45; " . get_bloginfo('name');
	}

	public function wps_sub_menus()
	{
    //Remove wpshapere menu
    if( defined('HIDE_WPSHAPERE_OPTION_LINK') || (!current_user_can('manage_network')) && defined('NETWORK_ADMIN_CONTROL') )
	    remove_menu_page('wpshapere-options');
	}

  function initialize_dash_widgets() {
      global $wp_meta_boxes;

      $context = array("normal","side","advanced");
      $priority =array("high","low","default","core");

      $wps_widgets_list = $wp_meta_boxes['dashboard'];
      $wps_get_dash_Widgets = array();
      if (!is_array($wps_widgets_list['normal']['core'])) {
          $wps_widgets_list = array('normal'=>array('core'=>array()), 'side'=>array('core'=>array()),'advanced'=>array('core'=>array()));
      }
      foreach ($context as $context_value)
      {
          foreach ($priority as $priority_value)
          {
              if(isset($wps_widgets_list[$context_value][$priority_value]) && is_array($wps_widgets_list[$context_value][$priority_value]))
              {
                  foreach ($wps_widgets_list[$context_value][$priority_value] as $key=>$data) {
                      $key = $key . "|".$context_value;
                      $widget_title = preg_replace("/Configure/", "", strip_tags($data['title']));
                      $wps_get_dash_Widgets[] = array($key, $widget_title);
                  }
              }
          }
      }

      $this->updateOption('wps_widgets_list', $wps_get_dash_Widgets);

  }

	function wpsbrandFooter()
	{
    echo $this->aof_options['admin_footer_txt'];
	}

	function wpsremoveVersion()
	{
		return '';
	}

  function wps_save_adminbar_nodes() {
    global $wp_admin_bar;
    if ( !is_object( $wp_admin_bar ) )
        return;

    $all_nodes = $wp_admin_bar->get_nodes();
    $adminbar_nodes = array();
    foreach( $all_nodes as $node )
    {
      if(!empty($node->parent)) {
        $node_data = $node->id . " <strong>(Parent: " . $node->parent . ")</strong>";
      }
      else {
        $node_data = $node->id;
      }
      $adminbar_nodes[$node->id] = $node_data;
    }

    $data = array();
    $saved_data = get_option(WPS_ADMINBAR_LIST_SLUG);
    if($saved_data){
        $data = array_merge($saved_data, $adminbar_nodes);
    }else{
        $data = $adminbar_nodes;
    }

    $this->updateOption(WPS_ADMINBAR_LIST_SLUG, $data);
  }

  /**
  * admin bar customization
  * @since 4.9 admin bar customization method rewritten
  * @return null
  */
	function wps_remove_bar_links()
	{
    global $wp_admin_bar;
    $current_user_id = get_current_user_id();

    if(isset($this->aof_options['hide_admin_bar_menus']) && !empty($this->aof_options['hide_admin_bar_menus'])) {
      foreach ($this->aof_options['hide_admin_bar_menus'] as $hide_bar_menu) {
              $wp_admin_bar->remove_menu($hide_bar_menu);
      }
    }

	}

	function add_wpshapere_menus($wp_admin_bar) {
    $admin_logo_url = (!empty($this->aof_options['adminbar_logo_link'])) ? $this->aof_options['adminbar_logo_link'] : admin_url();
		if(!empty($this->aof_options['admin_logo']) || !empty($this->aof_options['adminbar_external_logo_url'])) {
			$wp_admin_bar->add_node( array(
				'id'    => 'wpshapere_site_title',
				'href'  => $admin_logo_url,
				'meta'  => array( 'class' => 'wpshapere_site_title' )
			) );
		}
	}

	public function update_avatar_size( $wp_admin_bar ) {

		//update avatar size
		$user_id      = get_current_user_id();
		$current_user = wp_get_current_user();
		if ( ! $user_id )
			return;
		$avatar = get_avatar( $user_id, 36 );
    $welcome_text = sprintf( __('Howdy, %1$s'), $current_user->display_name );
		$account_node = $wp_admin_bar->get_node( 'my-account' );
		$title = $welcome_text . $avatar;
		$wp_admin_bar->add_node( array(
			'id' => 'my-account',
			'title' => $title
			) );

	}

	public function get_wps_option_data( $option_id ) {
    if($this->is_wps_single()) {
        $get_wps_option_data = (is_serialized(get_option($option_id))) ? unserialize(get_option($option_id)) : get_option($option_id);
     }
    else {
        $get_wps_option_data = (is_serialized(get_site_option($option_id))) ? unserialize(get_site_option($option_id)) : get_site_option($option_id);
    }
    return $get_wps_option_data;
	}

	function get_wps_image_url($imgid, $size='full') {
    global $switched, $wpdb;

    if ( is_numeric( $imgid ) ) {
  		if(!$this->is_wps_single()) {
          switch_to_blog(1);
          $imageAttachment = wp_get_attachment_image_src( $imgid, $size );
          restore_current_blog();
      }
      else $imageAttachment = wp_get_attachment_image_src( $imgid, $size );
  		return $imageAttachment[0];
    }
	}

  function wps_array_merge()
  {
      $output = array();
      foreach(func_get_args() as $array) {
          foreach($array as $key => $value) {
              $output[$key] = isset($output[$key]) ?
                  array_merge($output[$key], $value) : $value;
          }
      }
      return $output;
  }

  //fn to save options
  public function updateOption($option='', $data) {
      if(empty($option)) {
        $option = WPSHAPERE_LITE_OPTIONS_SLUG;
      }
      if(isset($data) && !empty($data)) {
        if($this->is_wps_single())
          update_option($option, $data);
        else
          update_site_option($option, $data);
      }
  }

	function wpshapere_login_url()
	{
		$login_logo_url = $this->aof_options['login_logo_url'];
		if(empty($login_logo_url))
			return site_url();
		else return $login_logo_url;
	}

	function wpshapere_login_title()
	{
		return get_bloginfo('name');
	}

  function wps_clean_slug($slug) {
      $clean_slug = trim(preg_replace("/[^a-zA-Z0-9]+/", "", $slug));
      return $clean_slug;
  }

  function clean_title($title) {
    $clean_title = trim(preg_replace('/[0-9]+/', '', $title));
    return $clean_title;
  }

  function wps_get_file_url_ext($url) {
      $ext = parse_url($url, PHP_URL_PATH);
      if (strpos($ext,'.') !== false) {
          $basename = explode('.', basename($ext));
          return $basename[1];
      }
  }

  function wps_get_domain_name($url) {
      $parse_url = parse_url($url);
      $hostname = explode('.', $parse_url['host']);
      return $hostname;
  }

  public function wps_get_icons(){
    //new method with fa icons php array written instead of using wp_remote_get method
  	$pattern = '/\.(fa-(?:\w+(?:-)?)+):before\s+{\s*content:\s*"(.+)";\s+}/';
  	$file_contents = wp_remote_get( WPSHAPERE_LITE_DIR_URI . 'assets/font-awesome/css/font-awesome.css' );

    if(is_wp_error($file_contents))
        return;
    if ( 200 == wp_remote_retrieve_response_code( $file_contents ) ) {
            $icon_contents = $file_contents['body'];
    }

  	$icons = array();
  	if(!empty($icon_contents)) {
        preg_match_all($pattern, $icon_contents, $matches, PREG_SET_ORDER);
        foreach($matches as $match){
                $icons[$match[1]] = $match[2];
        }
    }
    return $icons;
  }

    function wps_get_icon_class($iconData) {
        if(!empty($iconData)) {
            $icon_class = explode('|', $iconData);
            if(isset($icon_class[0]) && isset($icon_class[1])) {
                return $icon_class[0] . ' ' . $icon_class[1];
            }
        }
    }

    public function wps_compress_css($css) {
      $cssContents = "";
      // Remove comments
      $cssContents = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
      // Remove space after colons
      $cssContents = str_replace(': ', ':', $cssContents);
      // Remove whitespace
      $cssContents = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $cssContents);
      return $cssContents;
    }

    function get_privilege_users() {
      //return WPS privilege users
      $wps_privilege_users = (!empty($this->aof_options['privilege_users'])) ? $this->aof_options['privilege_users'] : array();
      return $wps_privilege_users;
    }

  function manage_dash_widgets() {
      if(!isset($this->aof_options['remove_dash_widgets']))
          return;

      global $wp_meta_boxes;
      $dash_widgets_removal_data = $this->aof_options['remove_dash_widgets'];
      $remove_dash_widgets = (is_serialized($dash_widgets_removal_data)) ? unserialize($dash_widgets_removal_data) : $dash_widgets_removal_data;

      //Removing unwanted widgets
      if(!empty($remove_dash_widgets) && is_array($remove_dash_widgets)) {
          foreach ($remove_dash_widgets as $widget_to_rm) {
              if($widget_to_rm == "welcome_panel") {
                  remove_action('welcome_panel', 'wp_welcome_panel');
              }
              else {
                  $widget_data = explode("|", $widget_to_rm);
                  $widget_id = $widget_data[0];
                  $widget_pos = $widget_data[1];
                  unset($wp_meta_boxes['dashboard'][$widget_pos]['core'][$widget_id]);
              }
          }
      }
  }

  function manage_widget_functions() {

    $current_user_id = get_current_user_id();
    $wps_widgets_list_access = (isset($this->aof_options['show_all_widgets_to_admin'])) ? $this->aof_options['show_all_widgets_to_admin'] : "";
    $wps_privilege_users = (!empty($this->aof_options['privilege_users'])) ? $this->aof_options['privilege_users'] : array();

    global $wp_meta_boxes;
    $dash_widgets_removal_data = (isset($this->aof_options['remove_dash_widgets'])) ? $this->aof_options['remove_dash_widgets'] : "";
    $remove_dash_widgets = (is_serialized($dash_widgets_removal_data)) ? unserialize($dash_widgets_removal_data) : $dash_widgets_removal_data;

    //Removing unwanted widgets
    if(!empty($remove_dash_widgets) && is_array($remove_dash_widgets)) {

      foreach ($remove_dash_widgets as $widget_to_rm) {
          if($widget_to_rm == "welcome_panel") {
              remove_action('welcome_panel', 'wp_welcome_panel');
          }
          else {
              $widget_data = explode("|", $widget_to_rm);
              $widget_id = $widget_data[0];
              $widget_pos = $widget_data[1];
              unset($wp_meta_boxes['dashboard'][$widget_pos]['core'][$widget_id]);
          }
      }

    }

}

	public function frontendActions()
	{
      $css_styles = '';

	    //remove admin bar
	    if($this->aof_options['hide_admin_bar'] == 1) {
        add_filter( 'show_admin_bar', '__return_false' );
        echo '<style type="text/css">html { margin-top: 0 !important; }</style>';
	    }
	    else {

      include_once( WPSHAPERE_LITE_PATH . 'assets/css/wps-front-css.php');

		   $css_styles .= '<style type="text/css">
      		#wpadminbar, #wpadminbar .menupop .ab-sub-wrapper { background: '. $this->aof_options['admin_bar_color'] . '}
      #wpadminbar a.ab-item, #wpadminbar>#wp-toolbar span.ab-label, #wpadminbar>#wp-toolbar span.noticon { color: '. $this->aof_options['admin_bar_menu_color'] . '}
      #wpadminbar .ab-top-menu>li>.ab-item:focus, #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar .ab-top-menu>li:hover>.ab-item,
      #wpadminbar .ab-top-menu>li.hover>.ab-item, #wpadminbar .quicklinks .menupop ul li a:focus, #wpadminbar .quicklinks .menupop ul li a:focus strong,
      #wpadminbar .quicklinks .menupop ul li a:hover, #wpadminbar-nojs .ab-top-menu>li.menupop:hover>.ab-item, #wpadminbar .ab-top-menu>li.menupop.hover>.ab-item,
      #wpadminbar .quicklinks .menupop ul li a:hover strong, #wpadminbar .quicklinks .menupop.hover ul li a:focus, #wpadminbar .quicklinks .menupop.hover ul li a:hover,
      #wpadminbar li .ab-item:focus:before, #wpadminbar li a:focus .ab-icon:before, #wpadminbar li.hover .ab-icon:before, #wpadminbar li.hover .ab-item:before,
      #wpadminbar li:hover #adminbarsearch:before, #wpadminbar li:hover .ab-icon:before, #wpadminbar li:hover .ab-item:before,
      #wpadminbar.nojs .quicklinks .menupop:hover ul li a:focus, #wpadminbar.nojs .quicklinks .menupop:hover ul li a:hover, #wpadminbar li:hover .ab-item:after,
      #wpadminbar>#wp-toolbar a:focus span.ab-label, #wpadminbar>#wp-toolbar li.hover span.ab-label, #wpadminbar>#wp-toolbar li:hover span.ab-label {
        color: '. $this->aof_options['admin_bar_menu_hover_color'] . '}

      .quicklinks li.wpshapere_site_title { width: 200px !important; }
      .quicklinks li.wpshapere_site_title a{ outline:none; border:none;';

        if(!empty($this->aof_options['adminbar_external_logo_url']) && filter_var($this->aof_options['adminbar_external_logo_url'], FILTER_VALIDATE_URL)) {
          $adminbar_logo = esc_url( $this->aof_options['adminbar_external_logo_url']);
        }
        else {
          $adminbar_logo = (is_numeric($this->aof_options['admin_logo'])) ? $this->get_wps_image_url($this->aof_options['admin_logo']) : $this->aof_options['admin_logo'];
        }

        if(!empty($adminbar_logo)){
          $css_styles .= '.quicklinks li.wpshapere_site_title a{
            background-image:url('. $adminbar_logo . ') !important;
          background-repeat: no-repeat !important;
          background-position: center center !important;
          background-size: 70% auto !important;
          text-indent:-9999px !important;
          width: auto !important}';
        }

        $css_styles .= '#wpadminbar .ab-top-menu>li>.ab-item:focus, #wpadminbar-nojs .ab-top-menu>li.menupop:hover>.ab-item,
        #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar .ab-top-menu>li:hover>.ab-item,
        #wpadminbar .ab-top-menu>li.menupop.hover>.ab-item, #wpadminbar .ab-top-menu>li.hover>.ab-item { background: none }
        #wpadminbar .quicklinks .menupop ul li a, #wpadminbar .quicklinks .menupop ul li a strong, #wpadminbar .quicklinks .menupop.hover ul li a,
        #wpadminbar.nojs .quicklinks .menupop:hover ul li a { color:'. $this->aof_options['admin_bar_menu_color'] .'; font-size:13px !important }
        #wpadminbar .quicklinks li#wp-admin-bar-my-account.with-avatar>a img {
          width: 20px; height: 20px; border-radius: 100px; -moz-border-radius: 100px; -webkit-border-radius: 100px; 	border: none;
        }
      	</style>';

      }//end of else

  }

  public function hideupdateNotices() {
    echo '<style>.update-nag, .updated, .notice { display: none; }</style>';
  }

  }

}

$wpshapere = new WPSHAPERE();
