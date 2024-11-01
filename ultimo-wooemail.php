<?php
/**
 * Plugin Name: Ultimo Woocommerce Email
 * Plugin URI: https://ultimoepro.com/item/ultimo-woocommerce-email-master/
 * Description: Best email plugin to Boost up your Woocommerce Shop Emailing upto next level. Any Task related to Email can easily be performed by this.
 * Version: 1.1
 * Author: UltimoEpro
 * Author URI: https://ultimoepro.com/
 * Text Domain: ultimo-wooemail
 * Domain Path: /languages
 *
 * WC tested up to: 5.x
 *
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */


//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ultimo_WooEmail' ) ) :

    class Ultimo_WooEmail {

	private static $instance;

	//public $licenses;

	public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Ultimo_WooEmail ) ) {
			
			self::$instance = new Ultimo_WooEmail;

			self::$instance->constants();
			self::$instance->includes();
			self::$instance->hooks();
		}

		return self::$instance;
	}
        /**
	 *	Constants
	 */
	public function constants() {

		// Plugin version
		if ( ! defined( 'ULTIMO_WOOEMAIL_VERSION' ) ) {
			define( 'ULTIMO_WOOEMAIL_VERSION', '1.0' );
		}

		// Database version
		if ( ! defined( 'ULTIMO_WOOEMAIL_DATABASE_VERSION' ) ) {
			define( 'ULTIMO_WOOEMAIL_DATABASE_VERSION', '1.0.0' );
		}

		// Plugin file
		if ( ! defined( 'UWOOE_PLUGIN_FILE' ) ) {
			define( 'UWOOE_PLUGIN_FILE', __FILE__ );
		}

		// Plugin basename
		if ( ! defined( 'UWOOE_PLUGIN_BASENAME' ) ) {
			define( 'UWOOE_PLUGIN_BASENAME', plugin_basename( UWOOE_PLUGIN_FILE ) );
		}

		// Plugin directory path
		if ( ! defined( 'UWOOE_PLUGIN_DIR_PATH' ) ) {
			define( 'UWOOE_PLUGIN_DIR_PATH', trailingslashit( plugin_dir_path( UWOOE_PLUGIN_FILE )  ) );
		}

		// Plugin directory URL
		if ( ! defined( 'UWOOE_PLUGIN_DIR_URL' ) ) {
			define( 'UWOOE_PLUGIN_DIR_URL', trailingslashit( plugin_dir_url( UWOOE_PLUGIN_FILE )  ) );
		}

		// Settings page URL
		if ( ! defined( 'UWOOE_SETTINGS_PAGE_URL' ) ) {
			define( 'UWOOE_SETTINGS_PAGE_URL', add_query_arg( 'page', 'uwooe', admin_url( 'admin.php' ) ) );
		}

		// Admin settings directory path
		if ( ! defined( 'UWOOE_SETTINGS_DIR' ) ) {
			define( 'UWOOE_SETTINGS_DIR', UWOOE_PLUGIN_DIR_PATH . 'inc/admin/' );
		}

		// Modules directory URL
		if ( ! defined( 'UWOOE_TOOLS_URL' ) ) {
			define( 'UWOOE_TOOLS_URL', UWOOE_PLUGIN_DIR_URL . 'tools/' );
		}

		// Modules directory path
		if ( ! defined( 'UWOOE_TOOLS_DIR' ) ) {
			define( 'UWOOE_TOOLS_DIR', UWOOE_PLUGIN_DIR_PATH . 'tools/' );
		}

		// SV framework file
		if ( ! defined( 'SV_WC_FRAMEWORK_FILE' ) ) {
			define( 'SV_WC_FRAMEWORK_FILE', UWOOE_PLUGIN_DIR_PATH . 'tools/woocommerce/class-sv-wc-framework-bootstrap.php' );
		}

		// UwooE website - tools page
		if ( ! defined( 'UW_TOOLS_WEBSITE_PAGE' ) ) {
			define( 'UW_TOOLS_WEBSITE_PAGE', 'https://ultimoepro.com/item/ultimo-woocommerce-email-master/' );
		}
	}
        /**
	 *	Include PHP files
	 */
	public function includes() {

		// Admin includes
		include_once UWOOE_SETTINGS_DIR . 'admin-page.php';
		include_once UWOOE_SETTINGS_DIR . 'admin-notices.php';

		// Helper functions
		include_once UWOOE_PLUGIN_DIR_PATH . 'inc/helper-functions.php';

		// Database update
		include_once UWOOE_PLUGIN_DIR_PATH . 'inc/class-database-update.php';

		$options = uwooe_get_settings();

		// Exit if no options
		if ( ! $options || ! isset( $options['tools'] ) ) {
			return;
		}
                // Module files
		foreach ( uwooe_get_tool_sections() as $section ) {

			foreach ( $section['section_tools'] as $tool ) {

				// Check if tool is in enabled tools array
				$key = array_key_exists( $tool['key'], $options['tools'] ) ? intval( $options['tools'][$tool['key']] ) : '';

				if ( $key === 1 ) {
					include_once UWOOE_TOOLS_DIR . $tool['include_path'];
				}
			}
		}
	}

	/**
	 *	Action/filter hooks
	 */
	public function hooks() {

		register_activation_hook( UWOOE_PLUGIN_FILE, array( $this, 'activate' ) );

		add_action( 'plugins_loaded', array( $this, 'loaded' ) );

		add_filter( 'plugin_action_links_' . UWOOE_PLUGIN_BASENAME, array( $this, 'action_links' ) );
		
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_links' ), 10, 2 );
	}

	/**
	 *	Check to see if WooCommerce is active, and initialize the options in database
	 */
	public function activate() {

		// Deactivate and die if WooCommerce is not active
		if ( ! is_woocommerce_active() ) {
			deactivate_plugins( UWOOE_PLUGIN_BASENAME );
			wp_die( __( 'Whoops! Ultimo Woocommerce Email requires you to install and activate WooCommerce first.', 'ultimo-wooemail' ) );
		}

		// Current plugin settings, and default settings for new installs
		$options = uwooe_get_settings();
		$options = is_array( $options ) ? $options : array();
		$initial_options = array( 'db_version' => ULTIMO_WOOEMAIL_DATABASE_VERSION );

		// Add option with initial data for fresh installs
		if ( ! isset( $options['db_version'] ) && ! get_option( 'uwooe_license_status' ) ) {
			update_option( 'uwooe', array_merge( $options, $initial_options ) );
		}
	}

	/**
	 *	Load plugin text domain
	 */
	public function loaded() {

		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'ultimo-wooemail' );
		
		unload_textdomain( 'ultimo-wooemail' );
		load_textdomain( 'ultimo-wooemail', WP_LANG_DIR . '/ultimo-wooemail/ultimo-wooemail-' . $locale . '.mo' );
		load_plugin_textdomain( 'ultimo-wooemail', false, dirname( __FILE__ ) . '/languages' );
	}

	/**
	 *	Plugin action links
	 */
	public function action_links( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', UWOOE_SETTINGS_PAGE_URL, __( 'Settings', 'ultimo-wooemail' ) );
		return $links;
	}

	/**
	 *	Plugin info row links
	 */
	public function plugin_row_links( $links, $file ) {

		if ( $file == UWOOE_PLUGIN_BASENAME ) {

			$links[] = sprintf( '<a href="https://ultimoepro.com/item/ultimo-woocommerce-email-master/" target="_blank">%s</a>', __( 'Support', 'ultimo-wooemail' ) );
		}

		return $links;
	}
}

endif;

/**
 *	Main function
 *	@return object Ultimo_WooEmail instance
 */
function Ultimo_WooEmail() {
	return Ultimo_WooEmail::instance();
}

Ultimo_WooEmail();