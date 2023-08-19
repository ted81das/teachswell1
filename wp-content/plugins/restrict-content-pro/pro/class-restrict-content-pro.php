<?php
/**
 * Restrict Content Pro Base Class
 * @package restrict-content-pro
 * @copyright Copyright (c) 2021, iThemes, LLC
 * @license GPL2+
 */

defined( 'ABSPATH' ) || exit;

/**
 * Include the pro files
 *
 * @since 3.6
 */
function include_pro_files() {

	// Payment Gateways
	require_once( RCP_PLUGIN_DIR . 'pro/includes/gateways/class-rcp-payment-gateway-braintree.php' );
	require_once( RCP_PLUGIN_DIR . 'pro/includes/gateways/braintree/functions.php' );
	require_once( RCP_PLUGIN_DIR . 'pro/includes/gateways/class-rcp-payment-gateway-paypal.php' );
	require_once( RCP_PLUGIN_DIR . 'pro/includes/gateways/class-rcp-payment-gateway-paypal-pro.php' );
	require_once( RCP_PLUGIN_DIR . 'pro/includes/gateways/class-rcp-payment-gateway-paypal-express.php' );
	require_once( RCP_PLUGIN_DIR . 'pro/includes/gateways/paypal/functions.php' );
	require_once( RCP_PLUGIN_DIR . 'pro/includes/gateways/class-rcp-payment-gateway-2checkout.php' );
	require_once( RCP_PLUGIN_DIR . 'pro/includes/gateways/twocheckout/functions.php' );

	//Custom Redirects
	require_once( RCP_PLUGIN_DIR . 'pro/addons/custom-redirects/class-custom-redirects.php' );
}

/**
 * Include the admin files
 *
 * @since 3.6
 */
function include_pro_admin_files() {
	// Discounts
	require_once( RCP_PLUGIN_DIR . 'pro/includes/admin/discounts/discount-actions.php' );
	require_once( RCP_PLUGIN_DIR . 'pro/includes/admin/discounts/discount-codes.php' );

	// Reporting
	require_once( RCP_PLUGIN_DIR . 'pro/includes/admin/reports/report-actions.php' );
	require_once( RCP_PLUGIN_DIR . 'pro/includes/admin/reports/reports-page.php' );
}

/**
 * Include the additional admin submenu pages
 *
 * @since 3.6
 */
function include_pro_pages() {
	global $rcp_discounts_page, $rcp_reports_page;

	$rcp_discounts_page = add_submenu_page( 'rcp-members', __( 'Discounts', 'rcp' ), __( 'Discount Codes', 'rcp' ), 'rcp_view_discounts', 'rcp-discounts', 'rcp_discounts_page', 4 );
	$rcp_reports_page   = add_submenu_page( 'rcp-members', __( 'Reports', 'rcp' ), __( 'Reports', 'rcp' ), 'rcp_view_payments', 'rcp-reports', 'rcp_reports_page', 6 );

	if ( get_bloginfo( 'version' ) >= 3.3 ) {
		add_action( "load-$rcp_discounts_page", "rcp_help_tabs" );
		add_action( "load-$rcp_reports_page", "rcp_help_tabs" );
	}
	add_action( "load-$rcp_discounts_page", "rcp_screen_options" );
	add_action( "load-$rcp_reports_page", "rcp_screen_options" );
}

add_action( 'admin_menu', 'include_pro_pages', 11, 2 );

/**
 * Add the fields to set the max_connections_per_member to the misc_settings
 *
 * @since 3.6
 */
function rcp_add_max_connections_per_member() {
	global $rcp_options;
	?>
	<tr valign="top">
		<th>
			<label for="rcp_settings[no_login_sharing]"><?php _e( 'Maximum number of simultaneous connections per member', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="number"
				   value="<?php echo isset( $rcp_options['no_login_sharing'] ) ? intval( $rcp_options['no_login_sharing'] ) : '0'; ?>"
				   name="rcp_settings[no_login_sharing]" id="rcp_settings[no_login_sharing]" min="0"/>
			<p class="description"><?php _e( 'Set the default maximum number of simultaneous connections for each member.<br>Enter 0 to allow unlimited simultaneous connections.', 'rcp' ); ?></p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_after_after_discount_signup_fees_admin', 'rcp_add_max_connections_per_member', 10, 2 );

/**
 * Return the default Restrict Content Pro Payment Gateway array
 *
 * @param $gateways
 *
 * @return array
 * @since 3.6
 */
function rcp_get_pro_payment_gateways( $gateways ) {

	$pro_gateways = array(
			'manual'         => array(
					'label'       => __( 'Manual Payment', 'rcp' ),
					'admin_label' => __( 'Manual Payment', 'rcp' ),
					'class'       => 'RCP_Payment_Gateway_Manual'
			),
			'paypal'         => array(
					'label'       => __( 'PayPal', 'rcp' ),
					'admin_label' => __( 'PayPal Standard', 'rcp' ),
					'class'       => 'RCP_Payment_Gateway_PayPal'
			),
			'paypal_express' => array(
					'label'       => __( 'PayPal', 'rcp' ),
					'admin_label' => __( 'PayPal Express', 'rcp' ),
					'class'       => 'RCP_Payment_Gateway_PayPal_Express'
			),
			'paypal_pro'     => array(
					'label'       => __( 'Credit / Debit Card', 'rcp' ),
					'admin_label' => __( 'PayPal Pro', 'rcp' ),
					'class'       => 'RCP_Payment_Gateway_PayPal_Pro',
					'test_card'   => array(
							'number' => '4111111111111111',
							'cvc'    => '123',
							'link'   => 'https://developer.paypal.com/docs/classic/payflow/payflow-pro/payflow-pro-testing/#credit-card-numbers-for-testing'
					)
			),
			'stripe'         => array(
					'label'       => __( 'Credit / Debit Card', 'rcp' ),
					'admin_label' => __( 'Stripe', 'rcp' ),
					'class'       => 'RCP_Payment_Gateway_Stripe',
					'test_card'   => array(
							'number' => '4242424242424242',
							'cvc'    => '123',
							'zip'    => '45814',
							'link'   => 'https://stripe.com/docs/testing#cards'
					)
			),
			'twocheckout'    => array(
					'label'       => __( 'Credit / Debit Card', 'rcp' ),
					'admin_label' => __( '2Checkout', 'rcp' ),
					'class'       => 'RCP_Payment_Gateway_2Checkout',
					'test_card'   => array(
							'number' => '4000000000000002',
							'cvc'    => '123',
							'link'   => 'https://knowledgecenter.2checkout.com/Documentation/09Test_ordering_system/01Test_payment_methods#Test_cards'
					)
			),
			'braintree'      => array(
					'label'       => __( 'Credit / Debit Card', 'rcp' ),
					'admin_label' => __( 'Braintree', 'rcp' ),
					'class'       => 'RCP_Payment_Gateway_Braintree',
					'test_card'   => array(
							'number' => '4111111111111111',
							'cvc'    => '123',
							'link'   => 'https://developers.braintreepayments.com/reference/general/testing/php#valid-card-numbers'
					)
			)
	);

	return array_merge( $gateways, $pro_gateways );
}

add_filter( 'rcp_payment_gateways', 'rcp_get_pro_payment_gateways' );

function rcp_add_payment_gateway_configuration_fields( $rcp_options ) {
	?>
<table class="form-table">
<tr >
		<th colspan=2><h3><?php _e( 'PayPal Settings', 'rcp' ); ?></h3></th>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[paypal_email]"><?php _e( 'PayPal Address', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[paypal_email]" style="width: 300px;"
				   name="rcp_settings[paypal_email]" value="<?php if ( isset( $rcp_options['paypal_email'] ) ) {
				echo $rcp_options['paypal_email'];
			} ?>"/>

			<p class="description"><?php _e( 'Enter your PayPal email address.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th><?php _e( 'PayPal API Credentials', 'rcp' ); ?></th>
		<td>
			<p><?php printf( __( 'The PayPal API credentials are required in order to use PayPal Standard, PayPal Express, and PayPal Pro. Test API credentials can be obtained through <a href="%s" target="_blank">the PayPal developer website</a>. For more information, see our <a href="%s" target="_blank">documentation article</a>.', 'rcp' ), esc_url( 'https://developer.paypal.com/' ), esc_url( 'http://docs.restrictcontentpro.com/article/1548-setting-up-paypal-sandbox-accounts' ) ); ?></p>
		</td>
	</tr>
	<?php if ( ! function_exists( 'rcp_register_paypal_pro_express_gateway' ) ) : ?>
		<tr>
			<th>
				<label for="rcp_settings[test_paypal_api_username]"><?php _e( 'Test API Username', 'rcp' ); ?></label>
			</th>
			<td>
				<input type="text" class="regular-text" id="rcp_settings[test_paypal_api_username]"
					   style="width: 300px;" name="rcp_settings[test_paypal_api_username]"
					   value="<?php if ( isset( $rcp_options['test_paypal_api_username'] ) ) {
						   echo trim( $rcp_options['test_paypal_api_username'] );
					   } ?>"/>

				<p class="description"><?php _e( 'Enter your test API username.', 'rcp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="rcp_settings[test_paypal_api_password]"><?php _e( 'Test API Password', 'rcp' ); ?></label>
			</th>
			<td>
				<input type="<?php echo  isset( $rcp_options['test_paypal_api_password'] ) ? 'password' : 'text';  ?>"
				 		class="regular-text" id="rcp_settings[test_paypal_api_password]"
						style="width: 300px;" name="rcp_settings[test_paypal_api_password]"
						value="<?php if ( isset( $rcp_options['test_paypal_api_password'] ) ) {
							 echo trim( $rcp_options['test_paypal_api_password'] );
						} ?>"/>

				<button type="button" class="button button-secondary">
					 <span toggle="rcp_settings[test_paypal_api_password]"
							class="dashicons dashicons-hidden toggle-credentials"></span>
				</button>

				<p class="description"><?php _e( 'Enter your test API password.', 'rcp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="rcp_settings[test_paypal_api_signature]"><?php _e( 'Test API Signature', 'rcp' ); ?></label>
			</th>
			<td>
				<input type="<?php echo  isset( $rcp_options['test_paypal_api_password'] ) ? 'password' : 'text';  ?>"
						class="regular-text" id="rcp_settings[test_paypal_api_signature]"
						style="width: 300px;" name="rcp_settings[test_paypal_api_signature]"
						value="<?php if ( isset( $rcp_options['test_paypal_api_signature'] ) ) {
							 echo trim( $rcp_options['test_paypal_api_signature'] );
						} ?>"/>

				<button type="button" class="button button-secondary">
					 <span toggle="rcp_settings[test_paypal_api_signature]"
							class="dashicons dashicons-hidden toggle-credentials"></span>
				</button>

				<p class="description"><?php _e( 'Enter your test API signature.', 'rcp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="rcp_settings[live_paypal_api_username]"><?php _e( 'Live API Username', 'rcp' ); ?></label>
			</th>
			<td>
				<input type="text"
					   class="regular-text" id="rcp_settings[live_paypal_api_username]"
					   style="width: 300px;" name="rcp_settings[live_paypal_api_username]"
					   value="<?php if ( isset( $rcp_options['live_paypal_api_username'] ) ) {
						   echo trim( $rcp_options['live_paypal_api_username'] );
					   } ?>"/>

				<p class="description"><?php _e( 'Enter your live API username.', 'rcp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="rcp_settings[live_paypal_api_password]"><?php _e( 'Live API Password', 'rcp' ); ?></label>
			</th>
			<td>
				<input type="<?php echo  isset( $rcp_options['live_paypal_api_password'] ) ? 'password' : 'text';  ?>"
						class="regular-text" id="rcp_settings[live_paypal_api_password]"
						style="width: 300px;" name="rcp_settings[live_paypal_api_password]"
						value="<?php if ( isset( $rcp_options['live_paypal_api_password'] ) ) {
							 echo trim( $rcp_options['live_paypal_api_password'] );
						} ?>"/>

				<button type="button" class="button button-secondary">
					 <span toggle="rcp_settings[live_paypal_api_password]"
							class="dashicons dashicons-hidden toggle-credentials"></span>
				</button>
				<p class="description"><?php _e( 'Enter your live API password.', 'rcp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="rcp_settings[live_paypal_api_signature]"><?php _e( 'Live API Signature', 'rcp' ); ?></label>
			</th>
			<td>
				<input type="<?php echo  isset( $rcp_options['live_paypal_api_signature'] ) ? 'password' : 'text';  ?>"
						class="regular-text" id="rcp_settings[live_paypal_api_signature]"
						style="width: 300px;" name="rcp_settings[live_paypal_api_signature]"
						value="<?php if ( isset( $rcp_options['live_paypal_api_signature'] ) ) {
							 echo trim( $rcp_options['live_paypal_api_signature'] );
						} ?>"/>

				<button type="button" class="button button-secondary">
					 <span toggle="rcp_settings[live_paypal_api_signature]"
							class="dashicons dashicons-hidden toggle-credentials"></span>
				</button>

				<p class="description"><?php _e( 'Enter your live API signature.', 'rcp' ); ?></p>
			</td>
		</tr>
	<?php endif; ?>
	<tr valign="top">
		<th>
			<label for="rcp_settings[paypal_page_style]"><?php _e( 'PayPal Page Style', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[paypal_page_style]" style="width: 300px;"
				   name="rcp_settings[paypal_page_style]"
				   value="<?php if ( isset( $rcp_options['paypal_page_style'] ) ) {
					   echo trim( $rcp_options['paypal_page_style'] );
				   } ?>"/>

			<p class="description"><?php _e( 'Enter the PayPal page style name you wish to use, or leave blank for default.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th>
			<label for="rcp_settings[disable_curl]"><?php _e( 'Disable CURL', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="checkbox" value="1" name="rcp_settings[disable_curl]"
				   id="rcp_settings[disable_curl]" <?php if ( isset( $rcp_options['disable_curl'] ) ) {
				checked( '1', $rcp_options['disable_curl'] );
			} ?>/>
			<span class="description"><?php _e( 'Only check this option if your host does not allow cURL.', 'rcp' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th>
			<label for="rcp_settings[disable_ipn_verify]"><?php _e( 'Disable IPN Verification', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="checkbox" value="1" name="rcp_settings[disable_ipn_verify]"
				   id="rcp_settings[disable_ipn_verify]" <?php if ( isset( $rcp_options['disable_ipn_verify'] ) ) {
				checked( '1', $rcp_options['disable_ipn_verify'] );
			} ?>/>
			<span class="description"><?php _e( 'Only check this option if your members statuses are not getting changed to "active".', 'rcp' ); ?></span>
		</td>
	</tr>
	<tr valign="top" class="twocheckout_settings">
		<th colspan=2>
			<h3><?php _e( '2Checkout Settings', 'rcp' ); ?></h3>
		</th>
	</tr>
	<?php // 2checkout Secret Word ?>
	<tr class="twocheckout_secret_word">
		<th>
			<label for="rcp_settings[twocheckout_secret_word]"><?php _e( 'Secret Word', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[twocheckout_secret_word]" style="width: 300px;"
				   name="rcp_settings[twocheckout_secret_word]"
				   value="<?php if ( isset( $rcp_options['twocheckout_secret_word'] ) ) {
					   echo $rcp_options['twocheckout_secret_word'];
				   } ?>"/>
			<p class="description"><?php _e( 'Enter your secret word. This can be obtained from the <a href="https://sandbox.2checkout.com/sandbox/acct/detail_company_info" target="_blank">2Checkout Sandbox</a>.', 'rcp' ); ?></p>
		</td>
	</tr>
	<?php // 2checkout Test Private Key ?>
	<tr class="twocheckout_private_key">
		<th>
			<label for="rcp_settings[twocheckout_test_private]"><?php _e( 'Test Private Key', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[twocheckout_test_private]" style="width: 300px;"
				   name="rcp_settings[twocheckout_test_private]"
				   value="<?php if ( isset( $rcp_options['twocheckout_test_private'] ) ) {
					   echo $rcp_options['twocheckout_test_private'];
				   } ?>"/>
			<p class="description"><?php _e( 'Enter your test private key. Your test API keys can be obtained from the <a href="https://sandbox.2checkout.com/sandbox/api" target="_blank">2Checkout Sandbox</a>.', 'rcp' ); ?></p>
		</td>
	</tr>
	<?php // 2checkout Test Publishable Key ?>
	<tr class="twocheckout_test_publishable_key">
		<th>
			<label for="rcp_settings[twocheckout_test_publishable]"><?php _e( 'Test Publishable Key', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[twocheckout_test_publishable]"
				   style="width: 300px;" name="rcp_settings[twocheckout_test_publishable]"
				   value="<?php if ( isset( $rcp_options['twocheckout_test_publishable'] ) ) {
					   echo $rcp_options['twocheckout_test_publishable'];
				   } ?>"/>
			<p class="description"><?php _e( 'Enter your test publishable key.', 'rcp' ); ?></p>
		</td>
	</tr>
	<?php // 2checkout Test Seller ID ?>
	<tr class="twocheckout_test_seller_id">
		<th>
			<label for="rcp_settings[twocheckout_test_seller_id]"><?php _e( 'Test Seller ID', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[twocheckout_test_seller_id]" style="width: 300px;"
				   name="rcp_settings[twocheckout_test_seller_id]"
				   value="<?php if ( isset( $rcp_options['twocheckout_test_seller_id'] ) ) {
					   echo $rcp_options['twocheckout_test_seller_id'];
				   } ?>"/>
			<p class="description"><?php _e( 'Enter your test Seller ID. <a href="http://help.2checkout.com/articles/FAQ/Where-is-my-Seller-ID" target="_blank">Where is my Seller ID?</a>.', 'rcp' ); ?></p>
		</td>
	</tr>
	<?php // 2checkout Live Private Key ?>
	<tr class="twocheckout_live_private_key">
		<th>
			<label for="rcp_settings[twocheckout_live_private]"><?php _e( 'Live Private Key', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[twocheckout_live_private]" style="width: 300px;"
				   name="rcp_settings[twocheckout_live_private]"
				   value="<?php if ( isset( $rcp_options['twocheckout_live_private'] ) ) {
					   echo $rcp_options['twocheckout_live_private'];
				   } ?>"/>
			<p class="description"><?php _e( 'Enter your live secret key. Your API keys can be obtained from the <a href="https://pci.trustwave.com/2checkout" target="_blank">2Checkout PCI Program</a>.', 'rcp' ); ?></p>
		</td>
	</tr>
	<?php // 2checkout Live Publishable Key ?>
	<tr class="twocheckout_live_publishable_key">
		<th>
			<label for="rcp_settings[twocheckout_live_publishable]"><?php _e( 'Live Publishable Key', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[twocheckout_live_publishable]"
				   style="width: 300px;" name="rcp_settings[twocheckout_live_publishable]"
				   value="<?php if ( isset( $rcp_options['twocheckout_live_publishable'] ) ) {
					   echo $rcp_options['twocheckout_live_publishable'];
				   } ?>"/>
			<p class="description"><?php _e( 'Enter your live publishable key.', 'rcp' ); ?></p>
		</td>
	</tr>
	<?php // 2checkout Live Seller ID ?>
	<tr class="twocheckout_live_seller_id">
		<th>
			<label for="rcp_settings[twocheckout_live_seller_id]"><?php _e( 'Live Seller ID', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[twocheckout_live_seller_id]" style="width: 300px;"
				   name="rcp_settings[twocheckout_live_seller_id]"
				   value="<?php if ( isset( $rcp_options['twocheckout_live_seller_id'] ) ) {
					   echo $rcp_options['twocheckout_live_seller_id'];
				   } ?>"/>
			<p class="description"><?php _e( 'Enter your live Seller ID. <a href="http://help.2checkout.com/articles/FAQ/Where-is-my-Seller-ID" target="_blank">Where is my Seller ID?</a>.', 'rcp' ); ?></p>
		</td>
	</tr>

	<!-- Braintree Gateway -->
	<?php
	require_once RCP_ROOT . 'pro/includes/gateways/braintree/braintree-settings.php';
} // end function rcp_add_payment_gateway_configuration_fields.

add_action( 'rcp_after_stripe_payment_configuration_admin', 'rcp_add_payment_gateway_configuration_fields', 10, 2 );


/**
 * Adds the free trial input field to the edit membership level page
 * Takes in the membership level object
 *
 * @param $level
 *
 * @since 3.6
 */
function rcp_add_free_trials_to_membership_levels_edit( $level ) {
	?>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="trial_duration"><?php _e( 'Free Trial Duration', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" id="trial_duration" name="trial_duration"
				   value="<?php echo absint( $level->get_trial_duration() ); ?>"/>
			<select name="trial_duration_unit" id="trial_duration_unit">
				<option value="day" <?php selected( $level->get_trial_duration_unit(), 'day' ); ?>><?php _e( 'Day(s)', 'rcp' ); ?></option>
				<option value="month" <?php selected( $level->get_trial_duration_unit(), 'month' ); ?>><?php _e( 'Month(s)', 'rcp' ); ?></option>
				<option value="year" <?php selected( $level->get_trial_duration_unit(), 'year' ); ?>><?php _e( 'Year(s)', 'rcp' ); ?></option>
			</select>
			<p class="description">
				<?php _e( 'Length of time the free trial should last. Enter 0 for no free trial.', 'rcp' ); ?>
				<span alt="f223" class="rcp-help-tip dashicons dashicons-editor-help"
					  title="<?php _e( '<strong>Example</strong>: setting this to 7 days would give the member a 7-day free trial. The member would be billed at the end of the trial. <p><strong>Note:</strong> If you enable a free trial, the regular membership duration and price must be greater than 0.</p>', 'rcp' ); ?>"></span>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_edit_subscription_after_set_trial_duration', 'rcp_add_free_trials_to_membership_levels_edit', 10, 2 );

/**
 * Add the capability to add a free trial to a membership_level
 *
 * @since 3.6
 */
function rcp_add_free_trials_to_membership_levels_new() {
	?>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="trial_duration"><?php _e( 'Free Trial Duration', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" id="trial_duration" name="trial_duration" value="0"/>
			<select name="trial_duration_unit" id="trial_duration_unit">
				<option value="day"><?php _e( 'Day(s)', 'rcp' ); ?></option>
				<option value="month"><?php _e( 'Month(s)', 'rcp' ); ?></option>
				<option value="year"><?php _e( 'Year(s)', 'rcp' ); ?></option>
			</select>
			<p class="description">
				<?php _e( 'Length of time the free trial should last. Enter 0 for no free trial.', 'rcp' ); ?>
				<span alt="f223" class="rcp-help-tip dashicons dashicons-editor-help"
					  title="<?php _e( '<strong>Example</strong>: setting this to 7 days would give the member a 7-day free trial. The member would be billed at the end of the trial.<p><strong>Note:</strong> If you enable a free trial, the regular membership duration and price must be greater than 0.</p>', 'rcp' ); ?>"></span>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_new_subscription_after_set_trial_duration', 'rcp_add_free_trials_to_membership_levels_new', 10, 2 );

/**
 * Add the general email settings
 *
 * @since 3.6
 */
function rcp_add_general_email_fields( $rcp_options ) {
	?>
	<tr>
		<th colspan=2><h3><?php _e( 'General', 'rcp' ); ?></h3></th>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[email_template]"><?php _e( 'Template', 'rcp' ); ?></label>
		</th>
		<td>
			<?php $emails      = new RCP_Emails;
			$selected_template = isset( $rcp_options['email_template'] ) ? $rcp_options['email_template'] : ''; ?>
			<select id="rcp_settings[email_template]" name="rcp_settings[email_template]">
				<?php foreach ( $emails->get_templates() as $id => $template ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $id, $selected_template ); ?>><?php echo $template; ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php _e( 'Select the template used for email design.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[email_header_text]"><?php _e( 'Email Header', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[email_header_text]" style="width: 300px;"
				   name="rcp_settings[email_header_text]"
				   value="<?php echo esc_attr( $rcp_options['email_header_text'] ); ?>"/>
			<p class="description"><?php _e( 'Text shown at top of email notifications.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[email_header_img]"><?php _e( 'Email Logo', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text rcp-upload-field" id="rcp_settings[email_header_img]"
				   style="width: 300px;" name="rcp_settings[email_header_img]"
				   value="<?php echo esc_attr( $rcp_options['email_header_img'] ); ?>"/>
			<button class="rcp-upload button"><?php _e( 'Choose Image', 'rcp' ); ?></button>
			<p class="description"><?php _e( 'Image shown at top of email notifications.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[from_name]"><?php _e( 'From Name', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[from_name]" style="width: 300px;"
				   name="rcp_settings[from_name]" value="<?php if ( isset( $rcp_options['from_name'] ) ) {
				echo $rcp_options['from_name'];
			} else {
				echo get_bloginfo( 'name' );
			} ?>"/>
			<p class="description"><?php _e( 'The name that emails come from. This is usually the name of your business.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[from_email]"><?php _e( 'From Email', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[from_email]" style="width: 300px;"
				   name="rcp_settings[from_email]" value="<?php if ( isset( $rcp_options['from_email'] ) ) {
				echo $rcp_options['from_email'];
			} else {
				echo get_bloginfo( 'admin_email' );
			} ?>"/>
			<p class="description"><?php _e( 'The email address that emails are sent from.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[admin_notice_emails]"><?php _e( 'Admin Notification Email', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[admin_notice_emails]" style="width: 300px;"
				   name="rcp_settings[admin_notice_emails]"
				   value="<?php if ( isset( $rcp_options['admin_notice_emails'] ) ) {
					   echo $rcp_options['admin_notice_emails'];
				   } else {
					   echo get_bloginfo( 'admin_email' );
				   } ?>"/>
			<p class="description"><?php _e( 'Admin notices are sent to this email address. Separate multiple emails with a comma.', 'rcp' ); ?></p>
		</td>
	</tr>

	<tr valign="top">
		<th>
			<label for="rcp_settings[email_verification]"><?php _e( 'Email Verification', 'rcp' ); ?></label>
		</th>
		<td>
			<?php $verify = isset( $rcp_options['email_verification'] ) ? $rcp_options['email_verification'] : 'off'; ?>
			<select id="rcp_settings[email_verification]" name="rcp_settings[email_verification]"
					class="rcp-disable-email">
				<option value="off" <?php selected( $verify, 'off' ); ?>><?php _e( 'Off', 'rcp' ); ?></option>
				<option value="free" <?php selected( $verify, 'free' ); ?>><?php _e( 'On for free membership levels', 'rcp' ); ?></option>
				<option value="all" <?php selected( $verify, 'all' ); ?>><?php _e( 'On for all membership levels', 'rcp' ); ?></option>
			</select>
			<span alt="f223" class="rcp-help-tip dashicons dashicons-editor-help"
				  title="<?php esc_attr_e( 'If "On for free membership levels" is chosen, memberships with a 0 price in the level settings will require email verification. This does not include registrations that have been made free with a discount code or credits.', 'rcp' ); ?>"></span>
			<p class="description"><?php _e( 'Require that new members verify their email address before gaining access to restricted content.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( ! isset( $rcp_options['email_verification'] ) || 'off' == $rcp_options['email_verification'] ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[verification_subject]"><?php _e( 'Email Verification Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[verification_subject]" style="width: 300px;"
				   name="rcp_settings[verification_subject]"
				   value="<?php echo ! empty( $rcp_options['verification_subject'] ) ? esc_attr( $rcp_options['verification_subject'] ) : esc_attr__( 'Please confirm your email address', 'rcp' ); ?>"/>
			<p class="description"><?php _e( 'The subject line for the email verification message.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( ! isset( $rcp_options['email_verification'] ) || 'off' == $rcp_options['email_verification'] ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[verification_email]"><?php _e( 'Email Verification Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$verification_email = isset( $rcp_options['verification_email'] ) ? wptexturize( $rcp_options['verification_email'] ) : sprintf( __( 'Click here to confirm your email address and activate your account: %s', 'rcp' ), '%verificationlink%' );
			wp_editor( $verification_email, 'rcp_settings_verification_email', array(
					'textarea_name' => 'rcp_settings[verification_email]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php printf( __( 'This is the message for the verification email. Use the %s template tag for the verification URL.', 'rcp' ), '<code>%verificationlink%</code>' ); ?></p>
		</td>
	</tr>

	<tr>
		<th>
			<label><?php _e( 'Available Template Tags', 'rcp' ); ?></label>
		</th>
		<td>
			<p class="description"><?php _e( 'The following template tags are available for use in all of the email settings below.', 'rcp' ); ?></p>
			<?php echo rcp_get_emails_tags_list(); ?>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_settings_tab_start', 'rcp_add_general_email_fields', 10, 2 );

/**
 * Add the paid Membership Activation email fields to the settings page
 *
 * @since 3.6
 */
function rcp_add_paid_membership_activation_email_member_fields() {
	global $rcp_options;
	?>
	<tr<?php echo ( isset( $rcp_options['disable_active_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[active_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[active_subject]" style="width: 300px;"
				   name="rcp_settings[active_subject]" value="<?php if ( isset( $rcp_options['active_subject'] ) ) {
				echo $rcp_options['active_subject'];
			} ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to users when their membership becomes active.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_active_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[active_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$active_email = isset( $rcp_options['active_email'] ) ? wptexturize( $rcp_options['active_email'] ) : '';
			wp_editor( $active_email, 'rcp_settings_active_email', array(
					'textarea_name' => 'rcp_settings[active_email]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to users when their membership becomes active.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'active_email' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=active' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_paid_membership_activation_email_member', 'rcp_add_paid_membership_activation_email_member_fields', 10, 2 );

function rcp_add_paid_membership_activation_email_admin_fields() {
	global $rcp_options;
	?>
	<tr<?php echo ( isset( $rcp_options['disable_active_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[active_subject_admin]"><?php _e( 'Admin Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[active_subject_admin]" style="width: 300px;"
				   name="rcp_settings[active_subject_admin]"
				   value="<?php if ( isset( $rcp_options['active_subject_admin'] ) ) {
					   echo $rcp_options['active_subject_admin'];
				   } ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to the admin when a member\'s membership becomes active.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_active_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[active_email_admin]"><?php _e( 'Admin Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$active_email = isset( $rcp_options['active_email_admin'] ) ? wptexturize( $rcp_options['active_email_admin'] ) : '';
			wp_editor( $active_email, 'rcp_settings_active_email_admin', array(
					'textarea_name' => 'rcp_settings[active_email_admin]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to the admin when a member\'s membership becomes active.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'active_email_admin' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=active_admin' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_paid_membership_activation_email_admin', 'rcp_add_paid_membership_activation_email_admin_fields', 10, 2 );

function rcp_add_free_membership_activation_email_member_fields() {
	global $rcp_options;
	?>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_free_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[free_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[free_subject]" style="width: 300px;"
				   name="rcp_settings[free_subject]" value="<?php if ( isset( $rcp_options['free_subject'] ) ) {
				echo $rcp_options['free_subject'];
			} ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to users when they sign up for a free membership.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_free_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[free_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$free_email = isset( $rcp_options['free_email'] ) ? wptexturize( $rcp_options['free_email'] ) : '';
			wp_editor( $free_email, 'rcp_settings_free_email', array(
					'textarea_name' => 'rcp_settings[free_email]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to users when they sign up for a free account.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'free_email' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=free' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_free_membership_activation_email_member', 'rcp_add_free_membership_activation_email_member_fields', 10, 2 );

function rcp_add_free_membership_activation_email_admin_fields() {
	global $rcp_options;
	?>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_free_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[free_subject_admin]"><?php _e( 'Admin Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[free_subject_admin]" style="width: 300px;"
				   name="rcp_settings[free_subject_admin]"
				   value="<?php if ( isset( $rcp_options['free_subject_admin'] ) ) {
					   echo $rcp_options['free_subject_admin'];
				   } ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to the admin when a user signs up for a free membership.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_free_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[free_email_admin]"><?php _e( 'Admin Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$free_email = isset( $rcp_options['free_email_admin'] ) ? wptexturize( $rcp_options['free_email_admin'] ) : '';
			wp_editor( $free_email, 'rcp_settings_free_email_admin', array(
					'textarea_name' => 'rcp_settings[free_email_admin]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to the admin when a user signs up for a free account.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'free_email_admin' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=free_admin' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_free_membership_activation_email_admin', 'rcp_add_free_membership_activation_email_admin_fields', 10, 2 );

/**
 * Adds the trial membership activation email fields to settings page emails tab
 *
 * @since 3.6
 */
function rcp_add_trial_membership_activation_email() {
	global $rcp_options;
	?>
	<tr valign="top">
		<th colspan=2>
			<h3><?php _e( 'Trial Membership Activation Email', 'rcp' ); ?></h3>
		</th>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[disable_trial_email]"><?php _e( 'Disable for Member', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="checkbox" value="1" name="rcp_settings[disable_trial_email]"
				   id="rcp_settings[disable_trial_email]"
				   class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_trial_email'] ) ); ?>/>
			<span><?php _e( 'Check this to disable the email sent to a member when they sign up with a trial.', 'rcp' ); ?></span>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_trial_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[trial_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[trial_subject]" style="width: 300px;"
				   name="rcp_settings[trial_subject]" value="<?php if ( isset( $rcp_options['trial_subject'] ) ) {
				echo $rcp_options['trial_subject'];
			} ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to users when they sign up for a free trial.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_trial_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[trial_email]"><?php _e( 'Member Trial Email Message', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$trial_email = isset( $rcp_options['trial_email'] ) ? wptexturize( $rcp_options['trial_email'] ) : '';
			wp_editor( $trial_email, 'rcp_settings_trial_email', array(
					'textarea_name' => 'rcp_settings[trial_email]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to users when they sign up for a free trial.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'trial_email' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=trial' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[disable_trial_email_admin]"><?php _e( 'Disable for Admin', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="checkbox" value="1" name="rcp_settings[disable_trial_email_admin]"
				   id="rcp_settings[disable_trial_email_admin]"
				   class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_trial_email_admin'] ) ); ?>/>
			<span><?php _e( 'Check this to disable the email sent to the administrator when a member signs up with a trial.', 'rcp' ); ?></span>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_trial_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[trial_subject_admin]"><?php _e( 'Admin Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[trial_subject_admin]" style="width: 300px;"
				   name="rcp_settings[trial_subject_admin]"
				   value="<?php if ( isset( $rcp_options['trial_subject_admin'] ) ) {
					   echo $rcp_options['trial_subject_admin'];
				   } ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to the admin when a user signs up for a free trial.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_trial_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[trial_email_admin]"><?php _e( 'Admin Trial Email Message', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$trial_email = isset( $rcp_options['trial_email_admin'] ) ? wptexturize( $rcp_options['trial_email_admin'] ) : '';
			wp_editor( $trial_email, 'rcp_settings_trial_email_admin', array(
					'textarea_name' => 'rcp_settings[trial_email_admin]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to the admin when a user signs up for a free trial.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'trial_email_admin' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=trial_admin' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_before_trial_membership_activation_email', 'rcp_add_trial_membership_activation_email', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_cancelled_membership_email_member
 *
 * Add cancelled membership email member inputs
 *
 * @since 3.6
 */
function rcp_add_cancelled_membership_email_member() {
	global $rcp_options
	?>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_cancelled_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[cancelled_subject]"><?php _e( 'Member Subject line', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[cancelled_subject]" style="width: 300px;"
				   name="rcp_settings[cancelled_subject]"
				   value="<?php if ( isset( $rcp_options['cancelled_subject'] ) ) {
					   echo $rcp_options['cancelled_subject'];
				   } ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to users when their membership is cancelled.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_cancelled_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[cancelled_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$cancelled_email = isset( $rcp_options['cancelled_email'] ) ? wptexturize( $rcp_options['cancelled_email'] ) : '';
			wp_editor( $cancelled_email, 'rcp_settings_cancelled_email', array(
					'textarea_name' => 'rcp_settings[cancelled_email]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to users when their membership is cancelled.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'cancelled_email' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=cancelled' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_cancelled_membership_email_member', 'rcp_add_cancelled_membership_email_member', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_cancelled_membership_email_admin
 *
 * Add cancelled membership email admin inputs
 *
 * @since 3.6
 */
function rcp_add_cancelled_membership_email_admin() {
	global $rcp_options
	?>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_cancelled_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[cancelled_subject_admin]"><?php _e( 'Admin Subject line', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[cancelled_subject_admin]" style="width: 300px;"
				   name="rcp_settings[cancelled_subject_admin]"
				   value="<?php if ( isset( $rcp_options['cancelled_subject_admin'] ) ) {
					   echo $rcp_options['cancelled_subject_admin'];
				   } ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to the admin when a member\'s membership is cancelled.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_cancelled_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[cancelled_email_admin]"><?php _e( 'Admin Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$cancelled_email = isset( $rcp_options['cancelled_email_admin'] ) ? wptexturize( $rcp_options['cancelled_email_admin'] ) : '';
			wp_editor( $cancelled_email, 'rcp_settings_cancelled_email_admin', array(
					'textarea_name' => 'rcp_settings[cancelled_email_admin]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to the admin when a member\'s membership is cancelled.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'cancelled_email_admin' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=cancelled_admin' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_cancelled_membership_email_admin', 'rcp_add_cancelled_membership_email_admin', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_expired_membership_email_member
 *
 * Add the expired membership member inputs
 *
 * @since 3.6
 */
function rcp_add_expired_membership_email_member() {
	global $rcp_options;
	?>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_expired_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[expired_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[expired_subject]" style="width: 300px;"
				   name="rcp_settings[expired_subject]" value="<?php if ( isset( $rcp_options['expired_subject'] ) ) {
				echo $rcp_options['expired_subject'];
			} ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to users when their membership is expired.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_expired_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[expired_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$expired_email = isset( $rcp_options['expired_email'] ) ? wptexturize( $rcp_options['expired_email'] ) : '';
			wp_editor( $expired_email, 'rcp_settings_expired_email', array(
					'textarea_name' => 'rcp_settings[expired_email]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to users when their membership is expired.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'expired_email' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=expired' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_expired_membership_email_member', 'rcp_add_expired_membership_email_member', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_expired_membership_email_admin
 *
 * Add the expired membership email admin inputs
 *
 * @since 3.6
 */
function rcp_add_expired_membership_email_admin() {
	global $rcp_options;
	?>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_expired_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[expired_subject_admin]"><?php _e( 'Admin Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[expired_subject_admin]" style="width: 300px;"
				   name="rcp_settings[expired_subject_admin]"
				   value="<?php if ( isset( $rcp_options['expired_subject_admin'] ) ) {
					   echo $rcp_options['expired_subject_admin'];
				   } ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to the admin when a member\'s membership is expired.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_expired_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[expired_email_admin]"><?php _e( 'Admin Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$expired_email = isset( $rcp_options['expired_email_admin'] ) ? wptexturize( $rcp_options['expired_email_admin'] ) : '';
			wp_editor( $expired_email, 'rcp_settings_expired_email_admin', array(
					'textarea_name' => 'rcp_settings[expired_email_admin]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to the admin when a member\'s membership is expired.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'expired_email_admin' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=expired_admin' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_expired_membership_email_admin', 'rcp_add_expired_membership_email_admin', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_membership_expiration_reminders
 *
 * Add the inputs for the Expiration Reminders
 *
 * @since 3.6
 */
function rcp_add_membership_expiration_reminders() {
	global $rcp_options;
	?>
	<tr valign="top">
		<th colspan="2"><h3><?php _e( 'Expiration Reminders', 'rcp' ); ?></h3></th>
	</tr>
	<tr valign="top">
		<th>
			<?php _e( 'Membership Expiration Reminders', 'rcp' ); ?>
		</th>
		<td>
			<p class="description"><?php _e( 'Expiration reminders are sent to "active" and "cancelled" memberships that <strong>do not</strong> have auto renew enabled. They can be used to inform customers that their memberships will not be automatically renewed and they will need to do a manual renewal to retain access to their content.', 'rcp' ); ?></p>
			<?php rcp_subscription_reminder_table( 'expiration' ); ?>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_membership_expiration_reminders', 'rcp_add_membership_expiration_reminders', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_membership_renewal_reminders
 *
 * Add the inputs for the Renewal Reminders
 *
 * @since 3.6
 */
function rcp_add_membership_renewal_reminders() {
	global $rcp_options;
	?>
	<tr valign="top">
		<th colspan="2"><h3><?php _e( 'Renewal Reminders', 'rcp' ); ?></h3></th>
	</tr>
	<tr valign="top">
		<th>
			<?php _e( 'Membership Renewal Reminders', 'rcp' ); ?>
		</th>
		<td>
			<p class="description"><?php _e( 'Renewal reminders are sent to "active" memberships that <strong>do</strong> have auto renew enabled. They can be used to inform customers that their memberships will be automatically renewed and give them a chance to cancel if they do not wish to continue.', 'rcp' ); ?></p>
			<?php rcp_subscription_reminder_table( 'renewal' ); ?>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_membership_renewal_reminders', 'rcp_add_membership_renewal_reminders', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_payment_received_email_member
 *
 * Add the inputs for payment received email member
 *
 * @since 3.6
 */
function rcp_add_payment_received_email_member() {
	global $rcp_options;
	?>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_payment_received_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[payment_received_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[payment_received_subject]" style="width: 300px;"
				   name="rcp_settings[payment_received_subject]"
				   value="<?php if ( isset( $rcp_options['payment_received_subject'] ) ) {
					   echo $rcp_options['payment_received_subject'];
				   } ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to users upon a successful payment being received.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_payment_received_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[payment_received_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$payment_received_email = isset( $rcp_options['payment_received_email'] ) ? wptexturize( $rcp_options['payment_received_email'] ) : '';
			wp_editor( $payment_received_email, 'rcp_settings_payment_received_email', array(
					'textarea_name' => 'rcp_settings[payment_received_email]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to users after a payment has been received from them.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'payment_received_email' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=payment_received' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_payment_received_email_member', 'rcp_add_payment_received_email_member', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_payment_received_email_admin
 *
 * Add the inputs for payment received email admin
 *
 * @since 3.6
 */
function rcp_add_payment_received_email_admin() {
	global $rcp_options;
	?>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_payment_received_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[payment_received_subject_admin]"><?php _e( 'Admin Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[payment_received_subject_admin]"
				   style="width: 300px;" name="rcp_settings[payment_received_subject_admin]"
				   value="<?php echo ! empty( $rcp_options['payment_received_subject_admin'] ) ? esc_attr( $rcp_options['payment_received_subject_admin'] ) : ''; ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to the admin upon a successful payment being received.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_payment_received_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[payment_received_email_admin]"><?php _e( 'Admin Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$payment_received_email = isset( $rcp_options['payment_received_email_admin'] ) ? wptexturize( $rcp_options['payment_received_email_admin'] ) : '';
			wp_editor( $payment_received_email, 'rcp_settings_payment_received_email_admin', array(
					'textarea_name' => 'rcp_settings[payment_received_email_admin]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to the admin after a payment has been received.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'payment_received_email_admin' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=payment_received_admin' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_payment_received_email_admin', 'rcp_add_payment_received_email_admin', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_renewal_payment_failed_member
 *
 * Add the inputs for renewal payment failed email member
 *
 * @since 3.6
 */
function rcp_add_renewal_payment_failed_email_member() {
	global $rcp_options;
	?>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_renewal_payment_failed_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[renewal_payment_failed_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[renewal_payment_failed_subject]"
				   style="width: 300px;" name="rcp_settings[renewal_payment_failed_subject]"
				   value="<?php if ( isset( $rcp_options['renewal_payment_failed_subject'] ) ) {
					   echo esc_attr( $rcp_options['renewal_payment_failed_subject'] );
				   } ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to users when a renewal payment fails.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_renewal_payment_failed_email'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[renewal_payment_failed_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$renewal_payment_failed_email = isset( $rcp_options['renewal_payment_failed_email'] ) ? wptexturize( $rcp_options['renewal_payment_failed_email'] ) : '';
			wp_editor( $renewal_payment_failed_email, 'rcp_settings_renewal_payment_failed_email', array(
					'textarea_name' => 'rcp_settings[renewal_payment_failed_email]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to users when a renewal payment fails.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'renewal_payment_failed_email' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=renewal_payment_failed' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_renewal_payment_failed_member', 'rcp_add_renewal_payment_failed_email_member', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_renewal_payment_failed_email_admin
 *
 * Add the inputs for renewal payment failed email admin
 *
 * @since 3.6
 */
function rcp_add_renewal_payment_failed_email_admin() {
	global $rcp_options;
	?>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_renewal_payment_failed_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[renewal_payment_failed_subject_admin]"><?php _e( 'Admin Subject', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[renewal_payment_failed_subject_admin]"
				   style="width: 300px;" name="rcp_settings[renewal_payment_failed_subject_admin]"
				   value="<?php if ( isset( $rcp_options['renewal_payment_failed_subject_admin'] ) ) {
					   echo esc_attr( $rcp_options['renewal_payment_failed_subject_admin'] );
				   } ?>"/>
			<p class="description"><?php _e( 'The subject line for the email sent to the admin when a renewal payment fails.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr valign="top"<?php echo ( isset( $rcp_options['disable_renewal_payment_failed_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
		<th>
			<label for="rcp_settings[renewal_payment_failed_email_admin]"><?php _e( 'Admin Email Body', 'rcp' ); ?></label>
		</th>
		<td>
			<?php
			$renewal_payment_failed_email = isset( $rcp_options['renewal_payment_failed_email_admin'] ) ? wptexturize( $rcp_options['renewal_payment_failed_email_admin'] ) : '';
			wp_editor( $renewal_payment_failed_email, 'rcp_settings_renewal_payment_failed_email_admin', array(
					'textarea_name' => 'rcp_settings[renewal_payment_failed_email_admin]',
					'teeny'         => true
			) );
			?>
			<p class="description"><?php _e( 'This is the email message that is sent to the admin when a renewal payment fails.', 'rcp' ); ?></p>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'rcp_preview_email' => 'renewal_payment_failed_email_admin' ), home_url() ) ); ?>"
				   class="button-secondary" target="_blank"><?php _e( 'Preview Email', 'rcp' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=rcp-settings&rcp-action=send_test_email&email=renewal_payment_failed_admin' ), 'rcp_send_test_email' ) ); ?>"
				   class="button-secondary"><?php _e( 'Send Test Email', 'rcp' ); ?></a>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_renewal_payment_failed_email_admin', 'rcp_add_renewal_payment_failed_email_admin', 10, 2 );

/**
 * relevant action: rcp_emails_tab_after_new_user_notifications
 *
 * Add the New User Notification and the input fields to Admin > Settings > Emails
 *
 * @since 3.6
 */
function rcp_emails_tab_add_new_user_notifications() {
	global $rcp_options;
	?>
	<tr valign="top">
		<th colspan=2>
			<h3><?php _e( 'New User Notifications', 'rcp' ); ?></h3>
		</th>
	</tr>
	<tr valign="top">
		<th>
			<label for="rcp_settings[disable_new_user_notices]"><?php _e( 'Disable New User Notifications', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="checkbox" value="1" name="rcp_settings[disable_new_user_notices]"
				   id="rcp_settings[disable_new_user_notices]" <?php if ( isset( $rcp_options['disable_new_user_notices'] ) ) {
				checked( '1', $rcp_options['disable_new_user_notices'] );
			} ?>/>
			<span class="description"><?php _e( 'Check this option if you do NOT want to receive emails when new users signup.', 'rcp' ); ?></span>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_emails_tab_after_new_user_notifications', 'rcp_emails_tab_add_new_user_notifications', 10, 2 );

/**
 * Send the RCP_Reminders
 *
 * @param RCP_Reminders $rcpReminders
 *
 * @since 3.6
 */
function rcp_send_reminders( RCP_Reminders $rcpReminders ) {
	$rcp_email      = new RCP_Emails;
	$reminder_types = $rcpReminders->get_notice_types();

	foreach ( $reminder_types as $type => $name ) {

		$notices = $rcpReminders->get_notices( $type );

		foreach ( $notices as $notice_id => $notice ) {

			$levels     = ! empty( $notice['levels'] ) && is_array( $notice['levels'] ) ? $notice['levels'] : 'all';
			$levels_log = is_array( $levels ) ? implode( ', ', $levels ) : $levels;

			rcp_log( sprintf( 'Processing %s reminder. ID: %d; Period: %s; Levels: %s.', $type, $notice_id, $notice['send_period'], $levels_log ) );

			// Skip if this reminder isn't enabled.
			if ( empty( $notice['enabled'] ) ) {
				rcp_log( 'Reminder is not enabled - exiting.' );

				continue;
			}

			// Skip if subject or message isn't filled out.
			if ( empty( $notice['subject'] ) || empty( $notice['message'] ) ) {
				rcp_log( 'Empty subject or message - exiting.' );

				continue;
			}

			$memberships = $rcpReminders->get_reminder_subscriptions( $notice['send_period'], $type, $levels );

			if ( ! $memberships ) {
				rcp_log( 'No memberships found for reminder - exiting.' );

				continue;
			}

			foreach ( $memberships as $membership ) {

				rcp_log( sprintf( 'Processing %s reminder for membership #%d.', $type, $membership->get_id() ) );

				// Ensure an expiration notice isn't sent to an auto-renew membership.
				if ( $type == 'expiration' && $membership->is_recurring() && $membership->is_active() ) {
					rcp_log( sprintf( 'Skipping membership #%d - expiration reminder but membership is recurring and active.', $membership->get_id() ) );

					continue;
				}

				$user_id = $membership->get_user_id();
				$user    = get_userdata( $user_id );

				$sent_time = rcp_get_membership_meta( $membership->get_id(), '_reminder_sent_' . $notice_id, true );

				if ( empty( $sent_time ) ) {
					// Check deprecated meta. We have two of these... lol.

					$sent_time = get_user_meta( $user_id, sanitize_key( '_rcp_reminder_sent_' . $membership->get_id() . '_' . $notice_id ), true );

					if ( empty( $sent_time ) ) {
						$sent_time = get_user_meta( $user_id, sanitize_key( '_rcp_reminder_sent_' . $membership->get_object_id() . '_' . $notice_id ), true );
					}
				}

				if ( $sent_time ) {
					rcp_log( sprintf( 'Skipping membership #%d - reminder #%d has already been sent.', $membership->get_id(), $notice_id ) );

					continue;
				}

				$rcp_email->member_id  = $user->ID;
				$rcp_email->membership = $membership;
				$rcp_email->send( $user->user_email, stripslashes( $notice['subject'] ), $notice['message'] );

				$membership->add_note( sprintf( __( '%s notice was emailed to the member - %s.', 'rcp' ), ucwords( $type ), $rcpReminders->get_notice_period_label( $notice_id ) ) );

				// Prevents reminder notices from being sent more than once per membership.
				rcp_update_membership_meta( $membership->get_id(), '_reminder_sent_' . $notice_id, current_time( 'mysql' ) );
			}
		}
	}
}

add_action( 'rcp_after_send_reminder_emails', 'rcp_send_reminders', 10, 2 );
/**
 * Add the input for discount signup fees to settings
 *
 * @param $rcp_options
 *
 * @since 3.6
 */
function rcp_add_discount_signup_fees_to_settings( $rcp_options ) {
	?>
	<tr valign="top">
		<th>
			<label for="rcp_settings[discount_fees]"><?php _e( 'Discount Signup Fees', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="checkbox" value="1" name="rcp_settings[discount_fees]"
				   id="rcp_settings[discount_fees]"<?php checked( ! empty( $rcp_options['discount_fees'] ) ); ?>/>
			<span class="description"><?php _e( 'If enabled, discount codes will apply to signup fees. If not enabled, only the base price gets discounted and signup fees do not.', 'rcp' ); ?></span>
		</td>
	</tr>
	<?php
}

add_action( 'rcp_after_content_excerpts_admin', 'rcp_add_discount_signup_fees_to_settings', 10, 2 );
