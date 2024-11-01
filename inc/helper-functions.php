<?php
/**
 *	Helper functions
 *	@package Ultimo WooEmail
 *	@author Ultimo Cms Booster
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *	WooCommerce dependencies
 */
if ( ! class_exists( 'WC_Dependencies' ) ) {
	require_once 'class-wc-dependencies.php';
}

/**
 *	WC Detection
 *	@return (boolean) True if WooCommerce is active, else false
 */
if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		return WC_Dependencies::woocommerce_active_check();
	}
}

/**
 *	Get the UwooE settings
 *	@return (array) Plugin settings or empty array
 */
function uwooe_get_settings() {

	$options = get_option( 'uwooe' );

	if ( ! is_array( $options )  ) {
		$options = array();
	}
	
	return $options;
}

/**
 *	Get the tools settings
 *	@return (array) Tools settings or empty array
 */
function uwooe_get_tools_settings() {

	$options = uwooe_get_settings();

	if ( $options['tools'] ) {
		return $options['tools'];
	} else {
		return array();
	}
}


/**
 *	Formatted renewal URL - does not output
 *	@return (string) Formatted HTML link
 */
function uwooe_get_renewal_link( $classes = '' ) {
	return sprintf( '&nbsp;<a href="%s" class="%s" target="_blank">%s</a>', uwooe_get_renewal_url(), $classes, __( 'Renew License', 'ultimo-wooemail' ) );
}