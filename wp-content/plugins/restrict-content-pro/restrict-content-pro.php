<?php
/**
 * Plugin Name: Restrict Content Pro
 * Plugin URI: https://restrictcontentpro.com
 * Description: Set up a complete membership system for your WordPress site and deliver premium content to your members. Unlimited membership packages, membership management, discount codes, registration / login forms, and more.
 * Version: 3.5.36
 * Author: Restrict Content Pro
 * Author URI: https://restrictcontentpro.com/
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: rcp
 * Domain Path: languages
 * iThemes Package: restrict-content-pro
 *
 * @package restrict-content-pro
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

define( 'RCP_PLUGIN_FILE', __FILE__ );
define( 'RCP_ROOT', plugin_dir_path( __FILE__ ));
define( 'RCP_WEB_ROOT', plugin_dir_url( __FILE__ ));

if ( file_exists( RCP_ROOT . 'pro/class-restrict-content-pro.php') ) {
	define( 'IS_PRO', true );
}

// Load Strauss autoload.
require_once plugin_dir_path( __FILE__ ) . 'vendor/strauss/autoload.php';
// Load Composer autoload file only if we've not included this file already.
require_once dirname( RCP_PLUGIN_FILE ) . '/vendor/autoload.php';

use RCP\StellarWP\Telemetry\Config;
use RCP\StellarWP\Telemetry\Core as Telemetry;
use RCP\Container;
/**
 * Class RCP_Requirements_Check
 *
 * @since 3.0
 */
final class RCP_Requirements_Check {

	/**
	 * Plugin file
	 *
	 * @since 3.0
	 * @var string
	 */
	private $file = '';

	/**
	 * Plugin basename
	 *
	 * @since 3.0
	 * @var string
	 */
	private $base = '';

	/**
	 * Requirements array
	 *Yeah
	 * @var array
	 * @since 3.0
	 */
	private $requirements = array(

		// PHP
		'php' => array(
			'minimum' => '5.6.0',
			'name'    => 'PHP',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false
		),

		// WordPress
		'wp' => array(
			'minimum' => '4.4.0',
			'name'    => 'WordPress',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false
		)
	);

	/**
	 * @var bool Prevent autoload initialization
	 */
	private $should_prevent_autoload_init = false;

	/**
	 * Plugin file.
	 *
	 * @var string
	 */
	public $plugin_file;

	/**
	 * Plugin directory.
	 *
	 * @var string
	 */
	public $plugin_dir;

	/**
	 * Plugin path.
	 *
	 * @var string
	 */
	public $plugin_path;

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Setup plugin requirements
	 *
	 * @since 3.0
	 */
	public function __construct() {

		// Setup file & base
		$this->plugin_file = $this->file = RCP_PLUGIN_FILE;
		$this->base        = plugin_basename( $this->file );
		$this->plugin_path = trailingslashit( dirname( $this->plugin_file ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = str_replace( basename( $this->plugin_file ), '', plugins_url( basename( $this->plugin_file ), $this->plugin_file ) );

		// Load or quit
		$this->met()
			? $this->load()
			: $this->quit();
	}

	/**
	 * Plugins shouldn't include their functions before `plugins_loaded` because this will allow
	 * better compatibility with the autoloader methods.
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		if ( $this->should_prevent_autoload_init ) {
			return;
		}
		// Initialize Telemetry.
		$container = new Container();
		Config::set_hook_prefix( 'rcp' );
		Config::set_stellar_slug( 'restrict-content-pro' );
		if ( defined('TELEMETRY_SERVER') ) {
			Config::set_server_url( TELEMETRY_SERVER );
		}
		else {
			Config::set_server_url( 'https://telemetry.stellarwp.com/api/v1' );
		}
		Config::set_container( $container );
		Telemetry::instance()->init( __FILE__ );

		$this->bootstrap();
		$rcp_telemetry = new RCP_Telemetry();
		$rcp_telemetry->init();
	}

	/**
	 * Test PHP and WordPress versions for compatibility
	 *
	 * @param string $system - system to be tested such as 'php' or 'wordpress'
	 *
	 * @return boolean - is the existing version of the system supported?
	 */
	public function is_supported_version( $system ) {
		if ( $supported = wp_cache_get( $system, 'rcp_version_test' ) ) {
			return $supported;
		} else {
			switch ( strtolower( $system ) ) {
				case 'wordpress' :
					$supported = version_compare( get_bloginfo( 'version' ), $this->requirements['wp']['minimum'], '>=' );
					break;
				case 'php' :
					$supported = version_compare( phpversion(), $this->requirements['php']['minimum'], '>=' );
					break;
			}
			$supported = apply_filters( 'rcp_events_supported_version', $supported, $system );
			wp_cache_set( $system, $supported, 'rcp_version_test' );

			return $supported;
		}
	}

	/*
	/**
	 * Quit without loading
	 *
	 * @since 3.0
	 */
	private function quit() {
		add_action( 'admin_head',                        array( $this, 'admin_head'        ) );
		add_filter( "plugin_action_links_{$this->base}", array( $this, 'plugin_row_links'  ) );
		add_action( "after_plugin_row_{$this->base}",    array( $this, 'plugin_row_notice' ) );
	}

	/** Specific Methods ******************************************************/

	/**
	 * Load normally
	 *
	 * @since 3.0
	 */
	private function load() {

		// Maybe include the bundled bootstrapper
		if ( ! class_exists( 'Restrict_Content_Pro' ) ) {
			require_once dirname( $this->file ) . '/core/includes/class-restrict-content.php';
		}

		// Maybe hook-in the bootstrapper
		if ( class_exists( 'Restrict_Content_Pro' ) ) {

			// Bootstrap to plugins_loaded before priority 10 to make sure
			// add-ons are loaded after us.
			add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ], 0 );

			// Register the activation hook
			register_activation_hook( $this->file, array( $this, 'install' ) );
		}
	}

	/**
	 * Install, usually on an activation hook.
	 *
	 * @since 3.0
	 */
	public function install() {

		// Bootstrap to include all of the necessary files
		$this->bootstrap();

		// Network wide?
		$network_wide = ! empty( $_GET['networkwide'] )
			? (bool) $_GET['networkwide']
			: false;

		// Call the installer directly during the activation hook
		rcp_options_install( $network_wide );
	}

	/**
	 * Bootstrap everything.
	 *
	 * @since 3.0
	 */
	public function bootstrap() {
		$this->load_textdomain();
		Restrict_Content_Pro::instance( $this->file );
	}

	/**
	 * Plugin specific URL for an external requirements page.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_url() {
		return 'https://docs.restrictcontentpro.com/article/2077-minimum-requirements';
	}

	/**
	 * Plugin specific text to quickly explain what's wrong.
	 *
	 * @since 3.0
	 * @return void
	 */
	private function unmet_requirements_text() {
		esc_html_e( 'This plugin is not fully active.', 'rcp' );
	}

	/**
	 * Plugin specific text to describe a single unmet requirement.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_description_text() {
		return esc_html__( 'Requires %s (%s), but (%s) is installed.', 'rcp' );
	}

	/**
	 * Plugin specific text to describe a single missing requirement.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_missing_text() {
		return esc_html__( 'Requires %s (%s), but it appears to be missing.', 'rcp' );
	}

	/**
	 * Plugin specific text used to link to an external requirements page.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_link() {
		return esc_html__( 'Requirements', 'rcp' );
	}

	/**
	 * Plugin specific aria label text to describe the requirements link.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_label() {
		return esc_html__( 'Restrict Content Pro Requirements', 'rcp' );
	}

	/**
	 * Plugin specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_name() {
		return 'rcp-requirements';
	}

	/** Agnostic Methods ******************************************************/

	/**
	 * Plugin agnostic method to output the additional plugin row
	 *
	 * @since 3.0
	 */
	public function plugin_row_notice() {
		?><tr class="active <?php echo esc_attr( $this->unmet_requirements_name() ); ?>-row">
		<th class="check-column">
			<span class="dashicons dashicons-warning"></span>
		</th>
		<td class="column-primary">
			<?php $this->unmet_requirements_text(); ?>
		</td>
		<td class="column-description">
			<?php $this->unmet_requirements_description(); ?>
		</td>
		</tr><?php
	}

	/**
	 * Plugin agnostic method used to output all unmet requirement information
	 *
	 * @since 3.0
	 */
	private function unmet_requirements_description() {
		foreach ( $this->requirements as $properties ) {
			if ( empty( $properties['met'] ) ) {
				$this->unmet_requirement_description( $properties );
			}
		}
	}

	/**
	 * Plugin agnostic method to output specific unmet requirement information
	 *
	 * @since 3.0
	 * @param array $requirement
	 */
	private function unmet_requirement_description( $requirement = array() ) {

		// Requirement exists, but is out of date
		if ( ! empty( $requirement['exists'] ) ) {
			$text = sprintf(
				$this->unmet_requirements_description_text(),
				'<strong>' . esc_html( $requirement['name']    ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['current'] ) . '</strong>'
			);

			// Requirement could not be found
		} else {
			$text = sprintf(
				$this->unmet_requirements_missing_text(),
				'<strong>' . esc_html( $requirement['name']    ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>'
			);
		}

		// Output the description
		echo '<p>' . $text . '</p>';
	}

	/**
	 * Plugin agnostic method to output unmet requirements styling
	 *
	 * @since 3.0
	 */
	public function admin_head() {

		// Get the requirements row name
		$name = $this->unmet_requirements_name(); ?>

		<style id="<?php echo esc_attr( $name ); ?>">
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] td,
			.plugins .<?php echo esc_html( $name ); ?>-row th,
			.plugins .<?php echo esc_html( $name ); ?>-row td {
				background: #fff5f5;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th {
				box-shadow: none;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row th span {
				margin-left: 6px;
				color: #dc3232;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins .<?php echo esc_html( $name ); ?>-row th.check-column {
				border-left: 4px solid #dc3232 !important;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p {
				margin: 0;
				padding: 0;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p:not(:last-of-type) {
				margin-bottom: 8px;
			}
		</style>
		<?php
	}

	/**
	 * Plugin agnostic method to add the "Requirements" link to row actions
	 *
	 * @since 3.0
	 * @param array $links
	 * @return array
	 */
	public function plugin_row_links( $links = array() ) {

		// Add the Requirements link
		$links['requirements'] =
			'<a href="' . esc_url( $this->unmet_requirements_url() ) . '" aria-label="' . esc_attr( $this->unmet_requirements_label() ) . '">'
			. esc_html( $this->unmet_requirements_link() )
			. '</a>';

		// Return links with Requirements link
		return $links;
	}

	/** Checkers **************************************************************/

	/**
	 * Plugin specific requirements checker
	 *
	 * @since 3.0
	 */
	private function check() {

		// Loop through requirements
		foreach ( $this->requirements as $dependency => $properties ) {

			// Which dependency are we checking?
			switch ( $dependency ) {

				// PHP
				case 'php' :
					$version = phpversion();
					break;

				// WP
				case 'wp' :
					$version = get_bloginfo( 'version' );
					break;

				// Unknown
				default :
					$version = false;
					break;
			}

			// Merge to original array
			if ( ! empty( $version ) ) {
				$this->requirements[ $dependency ] = array_merge( $this->requirements[ $dependency ], array(
					'current' => $version,
					'checked' => true,
					'met'     => version_compare( $version, $properties['minimum'], '>=' )
				) );
			}
		}
	}

	/**
	 * Have all requirements been met?
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	public function met() {

		// Run the check
		$this->check();

		// Default to true (any false below wins)
		$retval  = true;
		$to_meet = wp_list_pluck( $this->requirements, 'met' );

		// Look for unmet dependencies, and exit if so
		foreach ( $to_meet as $met ) {
			if ( empty( $met ) ) {
				$retval = false;
				continue;
			}
		}

		// Return
		return $retval;
	}

	/** Translations **********************************************************/

	/**
	 * Plugin specific text-domain loader.
	 *
	 * @since 1.4
	 * @return void
	 */
	public function load_textdomain() {
		// Clean up plugin dependencies.
		$this->clean_plugin_sources();

		// Set filter for plugin's languages directory
		$rcp_lang_dir = dirname( $this->base ) . '/languages/';
		$rcp_lang_dir = apply_filters( 'rcp_languages_directory', $rcp_lang_dir );


		// Traditional WordPress plugin locale filter

		$get_locale = get_locale();

		if ( version_compare( get_bloginfo( 'version' ), '4.7', '>=' ) ) {

			$get_locale = get_user_locale();
		}

		/**
		 * Defines the plugin language locale used in RCP.
		 *
		 * @var string $get_locale The locale to use. Uses get_user_locale()` in WordPress 4.7 or greater,
		 *                  otherwise uses `get_locale()`.
		 */
		$locale        = apply_filters( 'plugin_locale',  $get_locale, 'rcp' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'rcp', $locale );

		// Setup paths to current locale file
		$mofile_local  = $rcp_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/rcp/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/rcp folder
			load_textdomain( 'rcp', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/rcp/languages/ folder
			load_textdomain( 'rcp', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'rcp', false, $rcp_lang_dir );
		}

	}

	/**
	 * Cleans folders that are created in the build process or any other development workflow.
	 *
	 * @since 3.5.36
	 *
	 * @return void
	 */
	private function clean_plugin_sources() {
		// Make sure that we clean up the `lang` folder created by the packager.
		$builder_lang_dir = RCP_ROOT . 'lang/';
		$this->delete_directory( $builder_lang_dir );
	}
	/**
	 * Recursively deletes a folder and its content.
	 *
	 * @since 3.5.35
	 * @param string $dir The directory that you want to delete.
	 * @return void
	 */
	private function delete_directory( $dir ) {
		if ( ! is_dir( $dir ) ) {
			// Bail since it is not a directory.
			return;
		}

		$files = array_diff( scandir( $dir ), array( '.', '..' ) );

		foreach ( $files as $file ) {
			if ( is_dir( "$dir/$file" ) ) {
				$this->deleteDirectory( "$dir/$file" );
			} else {
				unlink( "$dir/$file" );
			}
		}

		rmdir( $dir );
	}
}

// Invoke the checker
$GLOBALS['rcp_requirements_check'] = new RCP_Requirements_Check();

if ( ! function_exists( 'ithemes_repository_name_updater_register' ) ) {
	function ithemes_repository_name_updater_register( $updater ) {
		$updater->register( 'restrict-content-pro', __FILE__ );
	}
	add_action( 'ithemes_updater_register', 'ithemes_repository_name_updater_register' );

	require( __DIR__ . '/lib/updater/load.php' );
}
register_activation_hook( __FILE__, function () {
	if ( current_user_can( 'manage_options' ) ) {
		add_option( 'Restrict_Content_Plugin_Activated', 'restrict-content' );
	}
} );

function restrict_content_plugin_activation_redirect() {
	if ( is_admin() && get_option( 'Restrict_Content_Plugin_Activated' ) === 'restrict-content' ) {
		delete_option( 'Restrict_Content_Plugin_Activated' );
		wp_safe_redirect( admin_url( 'admin.php?page=restrict-content-pro-welcome' ) );
		die();
	}
}

add_action( 'admin_init', 'restrict_content_plugin_activation_redirect' );
