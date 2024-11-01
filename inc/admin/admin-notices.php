<?php
/**
 *	Admin notices
 *	@package Ultimo WooEmail
 *	@author Ultimo Cms Booster
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UwooE_Admin_Notices' ) ) :

class UwooE_Admin_Notices {

	private $user_id, $meta_key;

	public function __construct() {

		$this->hooks();
	}

	/**
	 *	Run
	 */
	public function hooks() {

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_ajax_uw_dismiss_notice', array( $this, 'dismiss_notices' ) );
		add_action( 'wp_ajax_uw_subscibe_offers', array( $this, 'subscibe_offers' ) );
	}

	/**
	 *	Notices displayed across the admin
	 */
	public function admin_notices() {
		$this->subscribe_newsletter();
	}
	
	/**
	 * Output the script when needed for dismissing an admin notice
	 *
	 * @param [string] $notice Notice identifier
	 *
	 * @return void
	 */
	private function dismiss_script( $notice ) { 

		if ( ! is_admin() ) {
			return;
		}

		?>
		<script>
			jQuery(document).ready(function($) {
				$('.uw-close-notice').on('click', function(e){
					e.preventDefault();
					var $notice = $("#<?php echo $notice; ?>");
					$notice.fadeOut();
					$.post(
						ajaxurl,
						{
							action: "uw_dismiss_notice",
							nonce: "<?php echo wp_create_nonce( 'uw_dismiss_notice' ); ?>",
							notice: "<?php echo $notice; ?>",
						},
						function(response) {
							if ( response.success === true ) {
								
							} else {
								$notice.fadeIn();
								console.log(response.data.message);
							}
						}
					);
				});
			});
		</script>
	<?php }


	/**
	 * Dismiss the 1.0 poll nag
	 */
	public function dismiss_notices() {

		if ( ! isset( $_POST['notice'] ) ) {
			die();
		}

		if ( ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), 'uw_dismiss_notice' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Operation failed.', 'ultimo-wooemail' )
			), 401 );
		}

		$saved = update_user_meta( get_current_user_id(), sanitize_text_field(wp_unslash($_POST['notice'])), 'dismissed' );

		if ( $saved ) {
			wp_send_json_success( array(
				'message' => __( 'Notice successfully dismissed.' )
			));
		} else {
			wp_send_json_error( array(
				'message' => __( 'Notice could not be dismissed.' )
			));
		}
	}
	
	/**
	* Function to add admin notice to subscribe newsletter
	**/
	public function subscribe_newsletter()
	{
		if ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash($_GET['page'])) === "uwooe" ) {
			if ( ! current_user_can( 'manage_options' ) || get_user_meta( get_current_user_id(), 'uw_subscribe', true ) === 'dismissed' ) {
				return;
			}
			?>
			<div class="welcome-panel notice uw-notice" id="uw_subscribe" style="padding: 23px;">
				<div class="row">
					<div class="col-md-3 center">
						<img class="img-offers" src="<?php echo plugins_url( '/../assets/img/offers.png', dirname(__FILE__)); ?>"/>
					</div>
					<div class="col-md-9">
						<h2><?php _e( 'Get updates for the latest offers and new releases!', 'ultimo-wooemail' ); ?></h2>
						<p class="about-description"><?php _e( 'Subscribe to Newsletter', 'ultimo-wooemail' ); ?></p>
						<p>
							<div class="row">
								<form id="subscribe_offers" method="post" action="">
									<div class="col-md-6">
										<div class="form-group">
											<input required type="email" id="user_email" name="user_email" class="form-control" placeholder="<?php echo __( 'Enter Email Address', 'ultimo-wooemail' )?>">
										</div>
									</div>
									<div class="col-md-6">
										<button type="submit" name="subscribe_offers" id="submit_offers" class="btn btn-success"><?php echo __( 'Subscribe', 'ultimo-wooemail' )?></button>
									</div>
								</form>
								<div class="response" id="success-response" style="display:none"></div>
							</div>
						</p>
					</div>
				</div>
				<a id="dismiss_uw_subscribe" class="welcome-panel-close uw-close-notice" href="" aria-label="Dismiss the UwooE 1.0 subscribe notice.">Dismiss</a>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$('#subscribe_offers').on('submit', function(e){
						var $user_email = $("#user_email").val();
						event.preventDefault()
						$.post(
							ajaxurl,
							{
								action: "uw_subscibe_offers",
								nonce: "<?php echo wp_create_nonce( 'uw_subscibe_offers' ); ?>",
								notice: "uw_subscribe",
								user_email: $user_email,
							},
							function(response) {
								//alert(response)
								if ( response.success === true ) {
									$("#uw_subscribe").fadeOut();
								} else {
									$("#uw_subscribe").fadeIn();
									console.log(response.data.message);
								}
							}
						);
					});
				});
			</script>
			<?php
			$this->dismiss_script( 'uw_subscribe' );
		}
	}
	
	/**
	 * Subscribe to the mailing list
	 */
	public function subscibe_offers() {

		if ( ! isset( $_POST['notice'] ) ) {
			die();
		}

		if ( ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), 'uw_subscibe_offers' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Operation failed.', 'ultimo-wooemail' )
			), 401 );
		}

		$saved = update_user_meta( get_current_user_id(), sanitize_text_field(wp_unslash($_POST['notice'])), 'dismissed' );
		
		// API to mailchimp

		$authToken = '2472f4882f9a9dc528d84055046c7ae9-us20';

		// Send request to api
		$args = array(
			'method' => 'POST',
			'headers' => array(
				'Authorization' => 'apikey ' . $authToken,
				'Content-Type' => 'application/json'
			),
			'body' => json_encode(array(
				"email_address" => sanitize_email($_POST['user_email']), 
				"status" => "subscribed"
			))
		);
		$response = wp_remote_post( 'https://us20.api.mailchimp.com/3.0/lists/d59394cf53/members/', $args );
		
		// End API to mailchimp
		
		if ( $saved ) {
			wp_send_json_success( array(
				'message' => __( 'Notice successfully dismissed.' )
			));
		} else {
			wp_send_json_error( array(
				'message' => __( 'Notice could not be dismissed.' )
			));
		}
	}
}

endif;

new UwooE_Admin_Notices;