<?php 
//global $wpdb,$current_user;
/*if ( ! current_user_can( 'manage_options' ) ) {
	return;
}*/
if(isset($_REQUEST["action"]) && sanitize_text_field(wp_unslash($_REQUEST["action"])) == "uwooe_update_settings")
{	
	// Get our settings
	$options = uwooe_get_settings();

	// Module settings were updated
	if ( is_array($_POST['uwooe']['tools']) ) {

		unset( $_POST['uwooe']['tools']['triggered'] );

		$new = array();
		$cleaned_tools_values = array();
		$tools_array = isset( $_POST['uwooe']['tools'] ) ? wp_unslash($_POST['uwooe']['tools']) : array();
		$all_tools = array_map( 'sanitize_text_field', $tools_array );
		// Modules are enabled, so sanitize the input values
		if ( !empty($all_tools) ) {
			foreach ( (array) $all_tools as $key => $val ) {
				$tool_name = sanitize_text_field($key);
				$cleaned_tools_values[$tool_name] = $val ? 1 : '';
			}
		}

		/**
		 *	Array of enabled tools or empty array
		 */
		$new['tools'] = $cleaned_tools_values;

		// Merge new tools array with other settings, and update option
		update_option( 'uwooe', array_merge( $options, $new ) );
	}
	die();
}
?>