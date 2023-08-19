<?PHP

if ( ! class_exists( 'RCP_Custom_Redirects' ) ) {


	/**
	 * Main RCP_Custom_Redirects class
	 *
	 * @since       1.0.0
	 */
	class RCP_Custom_Redirects {


		/**
		 * @var         RCP_Custom_Redirects $instance The one true RCP_Custom_Redirects
		 * @since       1.0.0
		 */
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true RCP_Custom_Redirects
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new RCP_Custom_Redirects();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		public function setup_constants() {
			// Plugin version
			define( 'RCP_CUSTOM_REDIRECTS_VER', '1.0.6' );

			// Plugin path
			define( 'RCP_CUSTOM_REDIRECTS_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'RCP_CUSTOM_REDIRECTS_URL', plugin_dir_url( __FILE__ ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			require_once RCP_CUSTOM_REDIRECTS_DIR . 'includes/functions.php';
			require_once RCP_CUSTOM_REDIRECTS_DIR . 'includes/filters.php';
			require_once RCP_CUSTOM_REDIRECTS_DIR . 'includes/actions.php';

			if ( is_admin() ) {
				require_once RCP_CUSTOM_REDIRECTS_DIR . 'admin/subscription/fields.php';
			}
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function hooks() {

			if ( class_exists( 'RCP_Add_On_Updater' ) ) {
				$updater = new RCP_Add_On_Updater( 449, __FILE__, RCP_CUSTOM_REDIRECTS_VER );
			}
		}
	}
}

/**
 * The main function responsible for returning the one true RCP_Custom_Redirects
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      RCP_Custom_Redirects The one true RCP_Custom_Redirects
 */


function rcp_check_and_deactivate_cr_plugin() {

	// Check if Custom redirects plugin is activated and deactivates if it is
	if ( is_plugin_active( 'rcp-custom-redirects/rcp-custom-redirects.php' ) ) {
		deactivate_plugins( 'rcp-custom-redirects/rcp-custom-redirects.php' );
		add_action( 'admin_notices', 'rcp_cr_plugin_deactivated_notice' );

	}

}

function rcp_cr_plugin_deactivated_notice() {
	?>
	<div class="notice notice-warning is-dismissible">
		<p><?php _e( 'The Custom Redirects addon has been deactivated since its functionality is now included in the RCP core plugin.', 'rcp' ); ?></p>
	</div>
	<?php
}

add_action( 'plugins_loaded', 'rcp_check_and_deactivate_cr_plugin' );

function rcp_custom_redirects_addon() {
	if ( is_plugin_active( 'rcp-custom-redirects/rcp-custom-redirects.php' ) ) {
		return;
	}
	return RCP_Custom_Redirects::instance();
}

add_action( 'plugins_loaded', 'rcp_custom_redirects_addon' );
