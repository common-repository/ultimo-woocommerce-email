<?php

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *	Tool sections
 *	@package Ultimo WooEmail
 *	@author Ultimo Cms Booster
 *
 *	@return array
 */
function uwooe_get_tool_sections() {

	$sections = array(

		'email' => array( # Email

			'section_title' => __( 'Email Modules', 'ultimo-wooemail' ),
			'section_description' => __( 'Increase your communication with customers through effective use of email.', 'ultimo-wooemail' ),
			'section_tools' => array(
				array(

					'title' => __( 'Aweber', 'ultimo-wooemail' ),
					'key' => 'aweber',
					'include_path' => '/aweber/aweber-newsletter-subscription.php',
				),
				array(

					'title' => __( 'Drip', 'ultimo-wooemail' ),
					'key' => 'drip',
					'include_path' => '/drip/woocommerce-drip.php',
				),
				array(

					'title' => __( 'Constant Contact', 'ultimo-wooemail' ),
					'key' => 'constant_contact',
					'include_path' => '/constant-contact/woocommerce-constant-contact.php',
				),
				array(

					'title' => __( 'Email Customizer', 'ultimo-wooemail' ),
					'key' => 'email_customizer',
					'include_path' => '/email-customizer/woocommerce-email-customizer.php',
				),
				array(

					'title' => __( 'Follow Up Emails', 'ultimo-wooemail' ),
					'key' => 'follow_up_emails',
					'include_path' => '/follow-up-emails/woocommerce-follow-up-emails.php',
				),
				array(

					'title' => __( 'Newsletter', 'ultimo-wooemail' ),
					'key' => 'newsletter',
					'include_path' => 'subscribe-to-newsletter/woocommerce-subscribe-to-newsletter.php',
				),
			)
		),
               
		
		);

	return $sections;
}

/**
 *	Get a list of just the tools, without sections
 *
 *	@return array
 */
function uwooe_get_all_tools() {

	$all_tools = array();
	$sections = uwooe_get_tool_sections();

	foreach ( $sections as $section ) {
		foreach ( $section['section_tools'] as $section_tool ) {
			$all_tools[$section_tool['key']] = $section_tool['title'];
		}
	}

	return $all_tools;
}