<?php
/**
 *	Add and display main admin settings page
 *
 *	@package Ultimo WooEmail
 *	@author Ultimo Cms Booster
 *	@since 1.0
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ultimo_WooEmail_Settings_Page' ) ) :

class Ultimo_WooEmail_Settings_Page {

	private $screen_id, $settings;

	public $pages;

	public function __construct() {
		$this->includes();
		$this->hooks();	
	}

	public function includes() {
		require_once 'tool-sections.php';
		require_once 'process-settings.php';
		require_once 'lib/simpleadminui/loader.php';
	}

	public function hooks() {

		add_action( 'current_screen', array( $this, 'set_screen_id' ) );

		add_action( 'init', array( $this, 'register_admin_page' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );

		// Main page
		// {page_slug}_meta_boxes_{tab_key}
		add_action( 'uwooe_meta_boxes', array( $this, 'tools_section' ) );
	}

	/**
	 *	Set the current screen ID
	 */
	public function set_screen_id() {
		if ( is_admin() ) {
			$this->settings = uwooe_get_settings();
			$this->screen_id = get_current_screen()->id;
		}
	}

	/**
	 *	Set admin notices
	 */
	public function admin_notices() {
		
		if ( isset( $_GET['settings-updated'] ) && sanitize_text_field(wp_unslash($_GET['settings-updated'])) == 'true' ) {
			include_once 'templates/settings-updated.php';
		}
	}

	/**
	 *	Load admin styles/scripts
	 */
	public function enqueues() {

		// Load on our settings page
		if ( get_current_screen()->base !== 'woocommerce_page_uwooe' ) {
			return;
		}

		// Admin CSS
		
		wp_enqueue_style( 'uwooe-google-font', '//fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i&display=swap', array(), null );
		wp_enqueue_style( 'uwooe-admin-styles', UWOOE_PLUGIN_DIR_URL . 'assets/css/admin.css', '', ULTIMO_WOOEMAIL_VERSION );
		wp_enqueue_style( 'uwooe-admin-bootstrap', UWOOE_PLUGIN_DIR_URL . 'assets/css/bootstrap.min.css', '', ULTIMO_WOOEMAIL_VERSION );


	}

	/**
	 *	Add the settings page
	 */
	public function register_admin_page() {

		if ( ! is_admin() ) {
			return;
		}

		$this->pages = array(
			'uwooe' => array(
				'page_title' => __( 'Ultimo Woocommerce Email', 'ultimo-wooemail' ), // page title
				'menu_title' => __( 'Ultimo Woocommerce Email', 'ultimo-wooemail' ), // menu title
				'capabilities' => 'manage_options', // capability a user must have to see the page
				'priority' => 999, // priority for menu positioning
				'icon' => '', // URL to an icon, or name of a Dashicons helper class to use a font icon
				'body_content' => '', // callback that prints to the page, above the metaboxes
				'parent_slug' => 'woocommerce', // If subpage, slug of the parent page (i.e. woocommerce), or file name of core WordPress page (i.e. edit.php); leave empty for a top-level page
				'sortable' => false, // whether the meta boxes should be sortable
				'collapsable' => false, // whether the meta boxes should be collapsable
				'contains_media' => false, // whether the page utilizes the media uploader
				
			)
		);

		// Register them all
		new \UwooE\AdminPages\Admin_Pages( $this->pages );
	}

	/**
	 *	Add Modules tab meta boxes
	 */
	public function tools_section() {
			// Add a meta box for each section of tools
			foreach ( uwooe_get_tool_sections() as $key => $section ) {
				add_meta_box(
					"uwooe_tools_{$key}",
					$section['section_title'],
					array( $this, 'render_tools_mb' ),
					$this->screen_id,
					'normal',
					'high',
					array(
						'key' => $key,
						'section' => $section
					)
				);
			}
	}

	/**
	 *	Tools meta boxes
	 */
	public function render_tools_mb( $post, $args ) {
		include 'templates/tool-boxes.php';
	}

	/**
	 *	Active Tools meta box
	 */
	public function render_active_tools() {

		$all_tools = uwooe_get_all_tools();

		if ( isset( $this->settings['tools'] ) && ! empty( $this->settings['tools'] ) ) {

			echo '<ol>';
			foreach ( $this->settings['tools'] as $key => $active_tool ) {
				printf( '<li><a href="#%1$s">%2$s</a></li>', $key, $all_tools[$key] );
			}
			echo '</ol>';
		
		} else {

			_e( 'You have no active tools right now.', 'ultimo-wooemail' );
		}
	}
}

endif;

new Ultimo_WooEmail_Settings_Page;