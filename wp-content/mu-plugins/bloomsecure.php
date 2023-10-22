<?php
/*
Plugin Name: Bloom MU Tools
Description: Enhancements and utilities
Version: 9.0
Author: iWebbloom
Author URI: https://convertbloom.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

//hide admin bar

add_action( 'admin_footer', 'hideitems' );
function hideitems() {
   if ( ! is_super_admin() ) {
      echo "<style>
         #wpadminbar {
            display: none !important;
         }
      </style> ";
   }
}


//add logout button

add_action(
   'admin_menu',
   function () {
      add_menu_page(
         'Logout',
         'Logout',
         'read',
         'wp_custom_logout_menu',
         '__return_false',
         'dashicons-marker',
         1 // Here use 1 for placing menu on top or PHP_MAX_INT to place it at the bottom,
      );
   }
);

add_action(
   'admin_init',
   function () {
      if ( isset( $_GET['page'] ) && $_GET['page'] == 'wp_custom_logout_menu' ) {
         wp_redirect( wp_logout_url() );
         exit();
      }
   }
);

// unregister all widgets
function unregister_default_widgets() {
unregister_widget('WP_Widget_Pages');
unregister_widget('WP_Widget_Calendar');
unregister_widget('WP_Widget_Archives');
unregister_widget('WP_Widget_Links');
unregister_widget('WP_Widget_Meta');
unregister_widget('WP_Widget_Search');
//unregister_widget('WP_Widget_Text');
//unregister_widget('WP_Widget_Categories');
//unregister_widget('WP_Widget_Recent_Posts');
//unregister_widget('WP_Widget_Recent_Comments');
//unregister_widget('WP_Widget_RSS');
//unregister_widget('WP_Widget_Tag_Cloud');
//unregister_widget('WP_Nav_Menu_Widget');
}
add_action('widgets_init', 'unregister_default_widgets', 11);



function remove_dashboard_widgets() {
remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal'); // Incoming Links
remove_meta_box('dashboard_plugins', 'dashboard', 'normal'); // Plugins
remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); // Quick Press
remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side'); // Recent Drafts
remove_meta_box('dashboard_primary', 'dashboard', 'side'); // WordPress blog
remove_meta_box('dashboard_secondary', 'dashboard', 'side'); // Other WordPress News
remove_meta_box('wpdm_dashboard_widget', 'dashboard', 'normal'); // Right Now
remove_meta_box('dashboard_custom_feed', 'dashboard', 'normal'); // Latest from ButlerBlog
remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); // News and Updates
}
add_action('wp_dashboard_setup', 'remove_dashboard_widgets');


//force light color theme for all users - DOES NOT ALLOW USER TO CHANGE

add_filter( 'get_user_option_admin_color', 'update_user_option_admin_color', 5 );
function update_user_option_admin_color( $color_scheme ) {
    $color_scheme = 'light';

    return $color_scheme;
}



//hide nags
add_action('admin_enqueue_scripts', 'ds_admin_theme_style');
add_action('login_enqueue_scripts', 'ds_admin_theme_style');
function ds_admin_theme_style() {
	if (!current_user_can( 'update_core' )) {
		echo '<style>.update-nag, .updated, .error, .is-dismissible { display: none; }</style>';
	}
}



//HIDE MENU PAGES

add_action( 'admin_init', 'my_remove_menu_pages' );
function my_remove_menu_pages() {


  global $user_ID;

  if ( $user_ID != 1 ) { //your user id

 //  remove_menu_page('edit.php'); // Posts
  // remove_menu_page('upload.php'); // Media
  // remove_menu_page('link-manager.php'); // Links
  // remove_menu_page('edit-comments.php'); // Comments
  // remove_menu_page('edit.php?post_type=page'); // Pages
  // remove_menu_page('plugins.php'); // Plugins
  // remove_menu_page('themes.php'); // Appearance
  // remove_menu_page('users.php'); // Users
   remove_menu_page('tools.php'); // Tools
      remove_menu_page('options-general.php?page=amazon-s3-and-cloudfront'); // Offlowd Media
          remove_menu_page('admin.php?page=rcp-addons'); // RCP Add on
        remove_menu_page('admin.php?page=rcp-need-help'); // RCP Help
                        remove_menu_page('admin.php?page=tutor-pro-license'); // Tutot license
                         remove_menu_page('theme-editor.php'); // Tutot license
//   remove_menu_page('options-general.php'); // Settings
   remove_menu_page('options-general.php?page=uabb-builder-settings'); // Settings
remove_menu_page('options-general.php?page=mainwp_child_tab'); // Settings
remove_menu_page('options-general.php?page=white-label'); // Settings
remove_menu_page('plugin-editor.php'); // Settings
remove_menu_page('admin.php?page=qubely'); // Settings - getting started
//remove_menu_page('options-general.php?page=menu_editor'); // Settings
//remove_menu_page('options-general.php?page=menu_editor&sub_section=settings'); // Settings
  }
}

class JPB_User_Caps {

  // Add our filters
  function __construct(){
    add_filter( 'editable_roles', array($this, 'editable_roles'));
    add_filter( 'map_meta_cap', array($this, 'map_meta_cap'), 10, 4);
  }

  // Remove 'Administrator' from the list of roles if the current user is not an admin
  function editable_roles( $roles ){
    if( isset( $roles['administrator'] ) && !current_user_can('administrator') ){
      unset( $roles['administrator']);
    }
    return $roles;
  }

  // If someone is trying to edit or delete and admin and that user isn't an admin, don't allow it
  function map_meta_cap( $caps, $cap, $user_id, $args ){

    switch( $cap ){
        case 'edit_user':
        case 'remove_user':
        case 'promote_user':
            if( isset($args[0]) && $args[0] == $user_id )
                break;
            elseif( !isset($args[0]) )
                $caps[] = 'do_not_allow';
            $other = new WP_User( absint($args[0]) );
            if( $other->has_cap( 'administrator' ) ){
                if(!current_user_can('administrator')){
                    $caps[] = 'do_not_allow';
                }
            }
            break;
        case 'delete_user':
        case 'delete_users':
            if( !isset($args[0]) )
                break;
            $other = new WP_User( absint($args[0]) );
            if( $other->has_cap( 'administrator' ) ){
                if(!current_user_can('administrator')){
                    $caps[] = 'do_not_allow';
                }
            }
            break;
        default:
            break;
    }
    return $caps;
  }

}

$jpb_user_caps = new JPB_User_Caps();


//CUSTOM PANEL FOR WELCOME TO BE UPDATED
add_filter( 'wpforms_admin_dashboardwidget', '__return_false' );
remove_action('welcome_panel', 'wp_welcome_panel');
function wpex_wp_welcome_panel() { ?>

	<div class="custom-welcome-panel-content">
		<h3 style="background-color:powderblue;"><?php _e( 'Welcome to your Bloom Funnel!' ); ?></h3>
		<p class="about-description" style="background-color:#ffffff;font-size=x-small "><?php _e( 'Harness the power of digital marketing automation with curated tools.' ); ?></p>
		

		<div class="welcome-panel-column-container">
		
			<div class="welcome-panel-column">
				<h4><?php _e( 'Next Steps' ); ?></h4>
				<ul>
				<?php if ( 'page' == get_option( 'show_on_front' ) && ! get_option( 'page_for_posts' ) ) : ?>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page">' . __( 'Edit your front page' ) . '</a>', get_edit_post_link( get_option( 'page_on_front' ) ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page">' . __( 'Add landing pages' ) . '</a>', admin_url( 'post-new.php?post_type=page' ) ); ?></li>
				<?php elseif ( 'page' == get_option( 'show_on_front' ) ) : ?>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page">' . __( 'Edit your front page' ) . '</a>', get_edit_post_link( get_option( 'page_on_front' ) ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page">' . __( 'Add static landing pages' ) . '</a>', admin_url( 'post-new.php?post_type=staticfunnelpages' ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page">' . __( 'Add Check Out pages' ) . '</a>', admin_url( 'post-new.php?post_type=checkoutpages' ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-write-blog">' . __( 'Add a blog post' ) . '</a>', admin_url( 'post-new.php' ) ); ?></li>
				<?php else : ?>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-write-blog">' . __( 'Write your first blog post' ) . '</a>', admin_url( 'post-new.php' ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page">' . __( 'Add offerings' ) . '</a>', admin_url( 'post-new.php?post_type=sc_product' ) ); ?></li>
				<?php endif; ?>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-view-site">' . __( 'Login to Studio POS System' ) . '</a>', "https://roundsalon.co.in/get-oauth-two" ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-view-site">' . __( 'Login to Email Automation' ) . '</a>', "https://cloud.convertbloom.com/mailspace" ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-view-site">' . __( 'Additional Marketing Tools' ) . '</a>', "https://cloud.convertbloom.com/additional-tools/" ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-view-site">' . __( 'View your site' ) . '</a>', home_url( '/' ) ); ?></li>
					
				</ul>
			</div><!-- .welcome-panel-column -->
			<div class="welcome-panel-column welcome-panel-last">
				<h4><?php _e( 'More Actions' ); ?></h4>
				<ul>
					<li><?php printf( '<div class="welcome-icon welcome-widgets-menus">' . __( 'Manage <a href="%1$s">widgets</a> or <a href="%2$s">menus</a>' ) . '</div>', admin_url( 'widgets.php' ), admin_url( 'nav-menus.php' ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-comments">' . __( 'Set up your checkout' ) . '</a>', admin_url( 'admin.php?page=sc-admin' ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-comments">' . __( 'Set up chatbots' ) . '</a>', "https://app.covertmantra.com" ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-comments">' . __( 'Update CRM System' ) . '</a>', admin_url( 'https://cloud.convertbloom.com/goworkspace' ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-learn-more">' . __( 'Update ERP Suite'  ) . '</a>', __( 'https://cloud.convertbloom.com/gosuitespace' ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-comments">' . __( 'Update CRM System' ) . '</a>', admin_url( 'admin.php?page=rcp-settings' ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-learn-more">' . __( 'Login to Conf Suite'  ) . '</a>', __( 'https://my.roundbolt.io/rcx-my-account' ) ); ?></li>
				</ul>
			</div><!-- .welcome-panel-column welcome-panel-last -->
			
				<div class="welcome-panel-column">
				
			<a class="button button-primary button-hero load-customize hide-if-no-customize" href="https://desk.crobz.win/bloom"><?php _e( 'Contact us !' ); ?></a>
					<p class="hide-if-no-customize"><?php printf( __( 'or, <a href="%s">edit your Web-Pro settings</a>' ), admin_url( 'options-general.php' ) ); ?></p>
				<p class="hide-if-no-customize"><?php printf( __( 'or, <a href="%s">edit Checkout Funnel settings</a>' ), admin_url( 'admin.php?page=sc-admin' ) ); ?></p>
				<p class="hide-if-no-customize"><?php printf( __( 'or, <a href="%s">First Set up SMTP email</a>  ' ), admin_url( 'options-general.php?page=smtp-settings' ) ); ?> </p> <p>Learn how to set up <a href="https://desk.crobz.win/knowledge-base/set-up-email">SMTP email</p>
				
			</div><!-- .welcome-panel-column -->
			
			
		</div><!-- .welcome-panel-column-container -->
				
	</div><!-- .custom-welcome-panel-content -->

<?php }
add_action( 'welcome_panel', 'wpex_wp_welcome_panel' );
