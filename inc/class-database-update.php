<?php
/**
 *	Update the database when needed
 *
 *	@package Ultimo WooEmail
 *	@author Ultimo Cms Booster
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UwooE_Database_Update' ) ) :

class UwooE_Database_Update {

	private $options, $db_version, $new_options;

	public function __construct() {

		$this->options = uwooe_get_settings();

		add_action( 'admin_notices', array( $this, 'db_update_notice' ) );

		add_action( 'admin_init', array( $this, 'update_database' ) );
	}

	/**
	 *	Admin notice for updating database
	 */
	public function db_update_notice() {

		if ( ! isset( $this->options['db_version'] ) || version_compare( $this->options['db_version'], ULTIMO_WOOEMAIL_DATABASE_VERSION, '<' ) ) : ?>

		<div class="notice updated" style="margin-left: 0;">
			<p><?php _e( 'To complete your Ultimo Woocommerce Email installation/update, please run the database update. Before running the update, make sure you have a full backup of your database.', 'ultimo-wooemail' ); ?></p>
			<p><a href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'uwooe_update_database' ), UWOOE_SETTINGS_PAGE_URL ), 'uwooe_update_db_nonce' ); ?>" id="uwooe-db-update" class="button button-primary" onclick="return confirm('<?php _e( "This will modify your database. Make sure to back up your database before proceeding.", "ultimo-wooemail" ); ?>');">Run Database Update</a></p>
		</div>

		<?php endif;
	}

	/**
	 *	Update the database when the action is set
	 */
	public function update_database() {

		// Exit if update action was not requested
		if ( ! isset( $_GET['action'] ) || sanitize_text_field(wp_unslash($_GET['action'])) !== 'uwooe_update_database' ) {
			return;
		}

		// Security check failed
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'uwooe_update_db_nonce' ) ) {
			wp_die( __( 'You are not authorized to perform this action.', 'ultimo-wooemail' ) );
		}

		// Get all database options
		$all_options = wp_load_alloptions();

		$this->new_options = array();

		// Continue only if no DB version or DB version is old
		if ( ! isset( $this->options['db_version'] ) || version_compare( $this->options['db_version'], ULTIMO_WOOEMAIL_DATABASE_VERSION, '<' ) ) {

			// Get old options prefix
			$prefix = 'ultimo_';
			$length = strlen( $prefix );

			// Loop through every option
			foreach( $all_options as $key => $val ) {

				// Only process old plugin options (has prefix)
				if ( ( substr( $key, 0, $length ) === $prefix ) ) {

					// Option has a value
					if ( $val ) {

						// Get the key, without old prefix
						$new_key = str_replace( $prefix, '', $key );

						// Check if option is tool setting
						$this->new_options['tools'][$new_key] = $val;
					}

					// Delete old option
					delete_option( $key );
				}
			}

			// Set database version element
			$this->new_options['db_version'] = ULTIMO_WOOEMAIL_DATABASE_VERSION;

			// Merge current plugin options with new options and update the option
			update_option( 'uwooe', array_merge( $this->options, $this->new_options ) );

			// Redirect
			wp_redirect( UWOOE_SETTINGS_PAGE_URL );
			exit;
		}
	}
}

endif;

new UwooE_Database_Update;