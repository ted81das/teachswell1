<?php
/**
 * Filters
 *
 * @package     RCP\Custom_Redirects\Filters
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter registration return URLs
 *
 * @since       1.0.0
 * @param       string $redirect The current redirect URL
 * @param       int    $user_id The ID for the logged in user
 * @return      string $redirect The redirect URL
 */
function rcp_custom_redirects_get_return_url( $redirect, $user_id ) {
	$level_id      = rcp_get_registration()->get_membership_level_id();
	$redirect_urls = get_option( 'rcp_custom_redirects_subscription_urls' );

	// Level ID won't be available via the registration class in PayPal Express due to the confirmation page.
	if ( empty( $level_id ) ) {
		if ( function_exists( 'rcp_get_customer_by_user_id' ) ) {

			/**
			 * RCP 3.0 and higher
			 */
			$customer = rcp_get_customer_by_user_id( $user_id );

			if ( empty( $customer ) ) {
				return $redirect;
			}

			// Get most recently modified membership.
			$memberships = $customer->get_memberships(
				array(
					'status__in' => array( 'pending', 'active' ),
					'orderby'    => 'date_modified',
					'order'      => 'DESC',
				)
			);

			if ( empty( $memberships ) || empty( $memberships[0] ) ) {
				return $redirect;
			}

			$level_id = $memberships[0]->get_object_id();

		} else {

			/**
			 * RCP 2.9 and lower
			 */
			$member   = new RCP_Member( $user_id );
			$level_id = $member->get_pending_subscription_id();

			if ( empty( $level_id ) ) {
				$level_id = $member->get_subscription_id();
			}
		}
	}

	if ( empty( $level_id ) ) {
		return $redirect;
	}

	if ( is_array( $redirect_urls ) && array_key_exists( $level_id, $redirect_urls ) ) {
		if ( $redirect_urls[ $level_id ] !== '' ) {
			$redirect = $redirect_urls[ $level_id ];
		}
	}

	return $redirect;
}
add_filter( 'rcp_return_url', 'rcp_custom_redirects_get_return_url', 10, 2 );

/**
 * Filter login redirect URL
 *
 * @param string  $redirect The current redirect URL.
 * @param WP_User $user     Object for the user logging in.
 *
 * @since 1.0.1
 * @return string $redirect The new redirect URL.
 */
function rcp_custom_redirects_get_login_redirect_url( $redirect, $user ) {

	if ( function_exists( 'rcp_get_customer_by_user_id' ) ) {

		/**
		 * RCP 3.0+
		 */
		$customer = rcp_get_customer_by_user_id( $user->ID );

		if ( empty( $customer ) ) {
			return $redirect;
		}

		// Order by price so that the highest price one takes priority.
		$memberships = $customer->get_memberships(
			array(
				'status__in' => array( 'active', 'cancelled' ),
			)
		);

		if ( empty( $memberships ) || empty( $memberships[0] ) ) {
			return $redirect;
		}

		/**
		 * @var RCP_Membership $membership
		 */
		$membership = false;

		// Determine the highest priced membership.
		foreach ( $memberships as $this_membership ) {
			/**
			 * @var RCP_Membership $this_membership
			 */
			if ( empty( $membership ) ) {
				$membership = $this_membership;
				continue;
			} else {
				$high_value = max( $this_membership->get_initial_amount(), $this_membership->get_recurring_amount() );

				if ( $high_value > max( $membership->get_initial_amount(), $membership->get_recurring_amount() ) ) {
					$membership = $this_membership;
				}
			}
		}

		$level_id = $membership->get_object_id();

	} else {

		/**
		 * RCP 2.9 and lower
		 */
		$level_id = rcp_get_subscription_id( $user->ID );
	}

	$redirect_urls = get_option( 'rcp_custom_redirects_login_urls' );

	if ( empty( $level_id ) ) {
		return $redirect;
	}

	if ( is_array( $redirect_urls ) && array_key_exists( $level_id, $redirect_urls ) ) {
		if ( ! empty( $redirect_urls[ $level_id ] ) ) {
			$redirect = $redirect_urls[ $level_id ];
		}
	}

	return $redirect;

}
add_filter( 'rcp_login_redirect_url', 'rcp_custom_redirects_get_login_redirect_url', 10, 2 );
