<?php
/**
 * Braintree Functions
 *
 * @package     Restrict Content Pro
 * @subpackage  Gateways/Braintree/Functions
 * @copyright   Copyright (c) 2017, Sandhills Development
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.8
 */

/**
 * Determines if a membership is Braintree subscription.
 *
 * @param int|RCP_Membership $membership_object_or_id Membership ID or object.
 *
 * @since 3.0
 * @return bool
 */
function rcp_is_braintree_membership( $membership_object_or_id ) {

	if ( ! is_object( $membership_object_or_id ) ) {
		$membership = rcp_get_membership( $membership_object_or_id );
	} else {
		$membership = $membership_object_or_id;
	}

	$is_braintree = false;

	if ( ! empty( $membership ) && $membership->get_id() > 0 ) {
		$subscription_id = $membership->get_gateway_customer_id();

		if ( false !== strpos( $subscription_id, 'bt_' ) ) {
			$is_braintree = true;
		}
	}

	/**
	 * Filters whether or not the membership is a Braintree subscription.
	 *
	 * @param bool           $is_braintree
	 * @param RCP_Membership $membership
	 *
	 * @since 3.0
	 */
	return (bool) apply_filters( 'rcp_is_braintree_membership', $is_braintree, $membership );

}

/**
 * Determines if all necessary Braintree API credentials are available.
 *
 * @since  2.7
 * @return bool
 */
function rcp_has_braintree_api_access() {

	global $rcp_options;

	if ( rcp_is_sandbox() ) {
		$merchant_id    = ! empty( $rcp_options['braintree_sandbox_merchantId'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_merchantId'] ) : '';
		$public_key     = ! empty( $rcp_options['braintree_sandbox_publicKey'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_publicKey'] ) : '';
		$private_key    = ! empty( $rcp_options['braintree_sandbox_privateKey'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_privateKey'] ) : '';
		$encryption_key = ! empty( $rcp_options['braintree_sandbox_encryptionKey'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_encryptionKey'] ) : '';

	} else {
		$merchant_id    = ! empty( $rcp_options['braintree_live_merchantId'] ) ? sanitize_text_field( $rcp_options['braintree_live_merchantId'] ) : '';
		$public_key     = ! empty( $rcp_options['braintree_live_publicKey'] ) ? sanitize_text_field( $rcp_options['braintree_live_publicKey'] ) : '';
		$private_key    = ! empty( $rcp_options['braintree_live_privateKey'] ) ? sanitize_text_field( $rcp_options['braintree_live_privateKey'] ) : '';
		$encryption_key = ! empty( $rcp_options['braintree_live_encryptionKey'] ) ? sanitize_text_field( $rcp_options['braintree_live_encryptionKey'] ) : '';
	}

	if ( ! empty( $merchant_id ) && ! empty( $public_key ) && ! empty( $private_key ) && ! empty( $encryption_key ) ) {
		return true;
	}

	return false;
}

/**
 * Cancel a Braintree membership by subscription ID.
 *
 * @param string $subscription_id Braintree subscription ID.
 *
 * @since 3.0
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function rcp_braintree_cancel_membership( $subscription_id ) {

	global $rcp_options;

	$ret = true;

	if ( rcp_is_sandbox() ) {
		$merchant_id    = ! empty( $rcp_options['braintree_sandbox_merchantId'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_merchantId'] ) : '';
		$public_key     = ! empty( $rcp_options['braintree_sandbox_publicKey'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_publicKey'] ) : '';
		$private_key    = ! empty( $rcp_options['braintree_sandbox_privateKey'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_privateKey'] ) : '';
		$encryption_key = ! empty( $rcp_options['braintree_sandbox_encryptionKey'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_encryptionKey'] ) : '';
		$environment    = 'sandbox';

	} else {
		$merchant_id    = ! empty( $rcp_options['braintree_live_merchantId'] ) ? sanitize_text_field( $rcp_options['braintree_live_merchantId'] ) : '';
		$public_key     = ! empty( $rcp_options['braintree_live_publicKey'] ) ? sanitize_text_field( $rcp_options['braintree_live_publicKey'] ) : '';
		$private_key    = ! empty( $rcp_options['braintree_live_privateKey'] ) ? sanitize_text_field( $rcp_options['braintree_live_privateKey'] ) : '';
		$encryption_key = ! empty( $rcp_options['braintree_live_encryptionKey'] ) ? sanitize_text_field( $rcp_options['braintree_live_encryptionKey'] ) : '';
		$environment    = 'production';
	}

	if ( ! class_exists( 'Braintree\\Gateway' ) ) {
		require_once RCP_PLUGIN_DIR . 'pro/includes/libraries/braintree/lib/Braintree.php';
	}

	$gateway = new Braintree\Gateway( array(
		'environment' => $environment,
		'merchantId'  => $merchant_id,
		'publicKey'   => $public_key,
		'privateKey'  => $private_key
	) );

	try {
		$result = $gateway->subscription()->cancel( $subscription_id );

		if ( ! $result->success ) {

			$status = $result->errors->forKey( 'subscription' )->onAttribute( 'status' );

			/**
			 * Don't throw an exception if the subscription is already cancelled.
			 */
			if ( '81905' != $status[0]->code ) {
				$ret = new WP_Error( 'rcp_braintree_error', $result->message );
			}
		}

	} catch ( Exception $e ) {
		$ret = new WP_Error( 'rcp_braintree_error', $e->getMessage() );
	}

	return $ret;

}

/**
 * Checks for the legacy Braintree gateway
 * and deactivates it and shows a notice.
 *
 * @since 2.8
 * @return void
 */
function rcp_braintree_detect_legacy_plugin() {

	if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}

	if ( is_plugin_active( 'rcp-braintree/rcp-braintree.php' ) ) {
		deactivate_plugins( 'rcp-braintree/rcp-braintree.php', true );
	}

}
add_action( 'admin_init', 'rcp_braintree_detect_legacy_plugin' );

/**
 * Checks for legacy Braintree webhook endpoints
 * and fires off the webhook processing for those requests.
 *
 * @since 2.8
 * @return void
 */
add_action( 'init', function() {
	if ( ! empty( $_GET['bt_challenge'] ) || ( ! empty( $_POST['bt_signature'] ) && ! empty( $_POST['bt_payload'] ) ) ) {
		add_filter( 'rcp_process_gateway_webhooks', '__return_true' );
	}
}, -100000 ); // Must run before rcp_process_gateway_webooks which is hooked on -99999

/**
 * Add JS to the update card form
 *
 * @since 3.3
 * @return void
 */
function rcp_braintree_update_card_form_js() {
	global $rcp_membership;

	if ( ! rcp_is_braintree_membership( $rcp_membership ) || ! rcp_has_braintree_api_access() ) {
		return;
	}

	$gateway = new RCP_Payment_Gateway_Braintree();
	$gateway->scripts();

}
add_action( 'rcp_before_update_billing_card_form', 'rcp_braintree_update_card_form_js' );

/**
 * Update the billing card for a given membership
 *
 * @param RCP_Membership $membership
 *
 * @since 3.3
 * @return void
 */
function rcp_braintree_update_membership_billing_card( $membership ) {

	if ( ! $membership instanceof RCP_Membership ) {
		return;
	}

	if ( ! rcp_is_braintree_membership( $membership ) ) {
		return;
	}

	if ( empty( $_POST['payment_method_nonce'] ) ) {
		wp_die( __( 'Missing payment method nonce.', 'rcp' ) );
	}

	$subscription_id = $membership->get_gateway_subscription_id();

	if ( empty( $subscription_id ) ) {
		wp_die( __( 'Invalid subscription.', 'rcp' ) );
	}

	global $rcp_options;

	if ( rcp_is_sandbox() ) {
		$merchant_id = ! empty( $rcp_options['braintree_sandbox_merchantId'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_merchantId'] ) : '';
		$public_key  = ! empty( $rcp_options['braintree_sandbox_publicKey'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_publicKey'] ) : '';
		$private_key = ! empty( $rcp_options['braintree_sandbox_privateKey'] ) ? sanitize_text_field( $rcp_options['braintree_sandbox_privateKey'] ) : '';
		$environment = 'sandbox';

	} else {
		$merchant_id = ! empty( $rcp_options['braintree_live_merchantId'] ) ? sanitize_text_field( $rcp_options['braintree_live_merchantId'] ) : '';
		$public_key  = ! empty( $rcp_options['braintree_live_publicKey'] ) ? sanitize_text_field( $rcp_options['braintree_live_publicKey'] ) : '';
		$private_key = ! empty( $rcp_options['braintree_live_privateKey'] ) ? sanitize_text_field( $rcp_options['braintree_live_privateKey'] ) : '';
		$environment = 'production';
	}

	if ( ! class_exists( 'Braintree\\Gateway' ) ) {
		require_once RCP_PLUGIN_DIR . 'pro/includes/libraries/braintree/lib/Braintree.php';
	}

	$gateway = new Braintree\Gateway( array(
		'environment' => $environment,
		'merchantId'  => $merchant_id,
		'publicKey'   => $public_key,
		'privateKey'  => $private_key
	) );

	try {
		$gateway->subscription()->update( $subscription_id, array(
			'paymentMethodNonce' => sanitize_text_field( $_POST['payment_method_nonce'] )
		) );

		wp_redirect( add_query_arg( 'card', 'updated' ) ); exit;
	} catch ( \Exception $e ) {
		wp_die( sprintf( __( 'An error occurred: %s', 'rcp' ), $e->getMessage() ) );
	}

}
add_action( 'rcp_update_membership_billing_card', 'rcp_braintree_update_membership_billing_card' );

/**
 * The origin of this function was the migration of 3DS V1 to 3DS V2.
 *
 * Output the additional fields needed by Braintree to fulfill the 3DS2 such as address fields.
 *
 * @return void
 */
function rcp_braintree_additional_fields() { ?>
	<fieldset class="rcp_braintree_billing_info">
		<h3><?php echo apply_filters ( 'rcp_braintree_billing_legend_label', __( 'Billing Information', 'rcp' ) ); ?></h3>
		<p id="rcp_braintree_billing_phoneNumber_wrap">
			<label for="rcp_braintree_billing_phoneNumber"><?php echo apply_filters ( 'rcp_braintree_billing_phoneNumber_label', __( 'Phone Number', 'rcp' ) ); ?></label>
			<input name="rcp_braintree_billing_phoneNumber" id="rcp_braintree_billing_phoneNumber" class="required"
				   type="text" placeholder="1234567890"
					<?php if( isset( $_POST['rcp_braintree_billing_phoneNumber'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_braintree_billing_phoneNumber'] ) . '"'; } ?>/>
		</p>
		<p id="rcp_braintree_billing_firstname_wrap">
			<label for="rcp_braintree_billing_firstname"><?php echo apply_filters ( 'rcp_braintree_billing_firstname_label', __( 'Given Name', 'rcp' ) ); ?></label>
			<input name="rcp_braintree_billing_firstname" id="rcp_braintree_billing_firstname" class="required"
				   type="text" placeholder="First"
					<?php if( isset( $_POST['rcp_braintree_billing_firstname'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_braintree_billing_firstname'] ) . '"'; } ?>/>
		</p>
		<p id="rcp_braintree_billing_lastname_wrap">
			<label for="rcp_braintree_billing_lastname"><?php echo apply_filters ( 'rcp_braintree_billing_lastname_label', __( 'Surname', 'rcp' ) ); ?></label>
			<input name="rcp_braintree_billing_lastname" id="rcp_braintree_billing_lastname" class="required"
				   type="text" placeholder="Last"
					<?php if( isset( $_POST['rcp_braintree_billing_lastname'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_braintree_billing_lastname'] ) . '"'; } ?>/>
		</p>
		<input type="hidden" id="braintree_3ds_nonce" name="braintree_3ds_nonce" value="<?php echo esc_attr( wp_create_nonce( 'braintree_3ds' ) ); ?>">
	</fieldset>
<?php
}

add_action( 'rcp_braintree_additional_fields', 'rcp_braintree_additional_fields' );

/**
 * Sanitize the fields that the user enter and validate the nonce.
 *
 * @return void return json created by WordPress.
 */
function rcp_braintree_3ds_validation_fields() {
	$post = wp_unslash( $_POST );
	$nonce = wp_verify_nonce( sanitize_text_field( $post['nonce'] ),'braintree_3ds' );
	// Bail if nonce is not valid.
	if( false === $nonce ){
		wp_send_json_error( [
			'status' => 'failed',
			'message' => 'Invalid Nonce. Consider reloading the page.',
		], 401);
	}

	$billing_address = array_key_exists( 'billingAddress', $post) ? array_map( 'rcp_sanitize_fields', $post['billingAddress'] ) : $post;
	if(array_key_exists( 'additionalInformation', $post) ) {
		$additional_information = array_map( 'rcp_sanitize_fields', $post['additionalInformation'] );
		$additional_information['shippingAddress'] = array_key_exists( 'shippingAddress', $additional_information ) ? array_map( 'rcp_sanitize_fields', $additional_information['shippingAddress'] ) : $additional_information;
	}

	$result = [
			'billingAddress' => $billing_address,
			'additionalInformation' => $billing_address,
	];

	wp_send_json_success( $result, 200 );
}

/**
 * Function that will check if the current value if a string and sanitize it, otherwise it will just return the
 * Array|Object.
 *
 * @param array|string $_field The field to check.
 * @since 3.5.23.1
 * @return array|string The Sanitized String or the Array|Object.
 */
function rcp_sanitize_fields( $_field ) {
	if( is_object( $_field ) || is_array( $_field ) ) {
		return $_field;
	}

	return sanitize_text_field( $_field );
}
