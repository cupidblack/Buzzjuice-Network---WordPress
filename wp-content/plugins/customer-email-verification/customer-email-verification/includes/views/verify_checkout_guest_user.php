<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cev_user_verified_data_raw = WC()->session->get('cev_user_verified_data');
$cev_user_verification_data = !is_null($cev_user_verified_data_raw) ? json_decode($cev_user_verified_data_raw) : null;

if ( !empty( $cev_user_verification_data ) && isset( $cev_user_verification_data->email ) ) {
	$cev_already_verify_style = 'pointer-events: all';
	$email = $cev_user_verification_data->email;
} else {
	$cev_already_verify_style = 'pointer-events: none;color: rgba(0,0,0,.6)';
	$email = 'youremail@gmail.com';	
}

$Try_again = __('resend email', 'customer-email-verification');	
$CEV_Customizer_Options = new CEV_Customizer_Options();
$cev_button_color_widget_header =  get_option('cev_button_color_widget_header', '#212121');
$cev_button_text_color_widget_header =  get_option('cev_button_text_color_widget_header', '#ffffff');
$cev_widget_header_image_width =  get_option('cev_widget_header_image_width', '80');

$cev_button_text_header_font_size = get_option('cev_button_text_header_font_size', '22');

$verification_popup_button_size = get_option('cev_popup_button_size', $CEV_Customizer_Options->defaults['cev_popup_button_size']);
$cev_button_text_size_widget_header = ( 'large' == $verification_popup_button_size ) ? 18 : 16 ;
$cev_button_padding_size_widget_header = ( 'large' == $verification_popup_button_size ) ? '15px 25px' : '12px 20px';

$heading_default = __('Verify its you.', 'customer-email-verification');
$heading = get_option( 'cev_header_text_checkout', $heading_default ); 	

$image = get_option('cev_verification_image', cev_pro()->plugin_dir_url() . 'assets/images/email-verification.png');
$message = get_option( 'cev_verification_widget_message_checkout', $CEV_Customizer_Options->defaults['cev_verification_widget_message_checkout']);
$cev_content_align = get_option( 'cev_content_align', 'center' );
$email_address = __( 'Email address', 'customer-email-verification'); 
$verify_send = get_option('cev_verification_header_send_verify_button_text', $CEV_Customizer_Options->defaults['cev_verification_header_send_verify_button_text'] );
$cev_verification_widget_hide_otp_section =  get_option('cev_verification_widget_hide_otp_section', $CEV_Customizer_Options->defaults['cev_verification_widget_hide_otp_section'] );
$sample_toggle_switch_cev = get_option('sample_toggle_switch_cev', $CEV_Customizer_Options->defaults['sample_toggle_switch_cev']  );
$width = ( '1' == $sample_toggle_switch_cev ) ? '100%' : 'auto';
?>

<div class="cev-authorization-grid__visual" style="display:block;">
	<div class="cev-authorization-grid__holder ">
		<div class="cev-authorization-grid__inner" style="max-width: <?php esc_html_e( get_option('cev_widget_content_width', '460px') ); ?>px;">
			<div class="cev-authorization" style="background: <?php esc_html_e( get_option('cev_verification_popup_background_color', '#fafafa') ); ?>;">
				<form  id="cev_verify_email" class="cev_pin_verification_form_pro" method="post">
					<section class="cev-authorization__holder" style="text-align: <?php esc_html_e( $cev_content_align ); ?>;  padding: <?php esc_html_e( get_option('cev_widget_content_padding', '40') ); ?>px; ">
						<div class="popup_image">
							<?php 
							if ( !empty( $image ) ) {
								?>
								<img src="<?php esc_html_e( $image ); ?>" style="width:<?php esc_html_e( $cev_widget_header_image_width ); ?>px;">
							<?php } ?>
						</div>
						
						<div class="cev-authorization__heading" >
							<span class="cev-authorization__title" style="font-size: <?php esc_html_e( $cev_button_text_header_font_size ); ?>px;">
								<?php esc_html_e( $heading ); ?>
							</span>
							<span class="cev-authorization__description" style="text-align:<?php esc_html_e( $cev_content_align ); ?>;">
							<?php echo wp_kses_post( $message ); ?>
							</span>
						</div>
					
						<div class="cev-field cev-field_size_extra-large cev-field_icon_left cev-field_event_right cev-hide-success-message">
							
							<h5 class="required-filed-email" style="text-align:<?php esc_html_e( $cev_content_align ); ?>;">
								<?php esc_html_e( $email_address ); ?>
							</h5>
							
							<input class="cev_pin_box_email" style="text-align:<?php esc_html_e( $cev_content_align ); ?>;" id="cev_pin_email" name="cev_pin_email" type="email" placeholder="<?php esc_html_e('Enter your email address', 'customer-email-verification'); ?>" required />
							<a href="javascript:;" class="cev_already_verify" style="text-align: <?php esc_html_e( $cev_content_align ); ?>;<?php esc_html_e( $cev_already_verify_style ); ?>" ><?php esc_html_e( 'Already have verification code?', 'customer-email-verification' ); ?></a>
							
							<div class="cev_send_email_tag" style="text-align: <?php esc_html_e( $cev_content_align ); ?>; ">
								<button wp_nonce="<?php esc_html_e( wp_create_nonce( 'wc_cev_email_guest_user' ) ); ?>" class="cev-button cev-button_color_success cev-send-verification-code" type="button" style="background-color:<?php esc_html_e( $cev_button_color_widget_header ); ?>; color:<?php esc_html_e($cev_button_text_color_widget_header); ?>; font-size:<?php esc_html_e( $cev_button_text_size_widget_header ); ?>px; width:<?php esc_html_e( $width ); ?>; padding: <?php esc_html_e( $cev_button_padding_size_widget_header ); ?>;">
									<?php 
									if ( !empty( $verify_send ) ) { 
										esc_html_e( $verify_send ); 
									} else {
										esc_html_e('Verify Your Email', 'customer-email-verification');
									} 
									?>
								</button> 
								<div class="cev_limit_message" style="display: none;">
									<?php esc_html_e( apply_filters( 'cev_resend_limit_message', __( 'Too many attempts, please contact us for further assistance', 'customer-email-verification' ) ) ); ?>
								</div>
							</div>
						</div>
						
						<div class="cev-authorization__heading cev-show-reg-content" style="display:none;">
							<span class="cev-authorization__title" style="font-size: <?php esc_html_e( $cev_button_text_header_font_size ); ?>px;">
								<?php esc_html_e( get_option( 'cev_verification_header', $heading_default ) ); ?>
							</span>
							<p class="cev-authorization__description" style="text-align:<?php esc_html_e( $cev_content_align ); ?>;">
								<?php
								/* translators: %s: search send verification code */
								$message = sprintf( __( 'We sent verification code to <span>%s</span>. To verify your email address, please check your inbox and enter the code below.', 'customer-email-verification'), $email );
								//$message = apply_filters( 'cev_verification_popup_message', $message, $email );
								echo wp_kses_post( $message );
								?>
							</p>
						</div>
						<?php if ( 0 == $cev_verification_widget_hide_otp_section ) { ?>
								<div class="cev-pin-verification" style="display:none;">
									<div class="cev-pin-verification__row">
										<div class="cev-field cev-field_size_extra-large cev-field_icon_left cev-field_event_right cev-show-sent-success-msg">
											<h5 class="required-filed-email" style="text-align:<?php esc_html_e( $cev_content_align ); ?>;">
											<?php 
											$codelength = apply_filters( 'cev_verification_code_length', __( '4-digits code', 'customer-email-verification' ) ); 
											esc_html_e( $codelength, 'customer-email-verification' ); 
											?>
											*
											</h5>
											<input style="text-align:<?php esc_html_e( $cev_content_align ); ?>; display:none;" class="cev_pin_box_email" id="cev_pin_box_code" name="cev_pin_box_code" type="text" placeholder="<?php esc_html_e( 'Enter', 'customer-email-verification' ); ?> <?php esc_html_e( $codelength, 'customer-email-verification' ); ?>" >
											<?php 
											$code_length = get_option('cev_verification_code_length', 4);
											if ( '1' == $code_length ) {
												$digits = 4;
											} else if ( '2' == $code_length ) {
												$digits = 6;
											} else {
											// Default value in case $code_length is neither '1' nor '2'
												$digits = 4;
											}
											?>
									<div class="otp-container">
										<?php for ( $i = 1; $i <= $digits; $i++ ) : ?>
											<input type="text" maxlength="1" class="otp-input-checkout" id="otp_input_<?php echo esc_attr('otp_input_' . $i); ?>" />
										<?php endfor; ?>
									</div>
										</div>
									</div>
									<div class="cev-pin-verification__failure js-pincode-invalid" style="display: none;">
										<div class="cev-alert cev-alert_theme_red">
											<span class="js-pincode-error-message"><?php esc_html_e( 'Verification code does not match', 'customer-email-verification' ); ?></span>
										</div>
									</div>
									<div class="cev-pin-verification__events cev-show-sent-success-msg">
										<input type="hidden" name="cev_user_id" value="<?php echo wp_kses_post( get_current_user_id() ); ?>">
										<button wp_nonce="<?php esc_html_e( wp_create_nonce( 'wc_cev_email_guest_user_verify' ) ); ?>" class="cev-button cev-button_color_success" id="cev_submit_pin_button" type="button" style="background-color:<?php echo wp_kses_post( $cev_button_color_widget_header ); ?>; color:<?php esc_html_e($cev_button_text_color_widget_header ); ?>; font-size:<?php esc_html_e ($cev_button_text_size_widget_header ); ?>px;padding: <?php esc_html_e( $cev_button_padding_size_widget_header ); ?>;width:<?php esc_html_e( $width ); ?>; display:none">
										<?php 
										$verify_default = __('Verify Code', 'customer-email-verification');
										$verify = get_option( 'cev_verification_header_verify_button_text' );
										if ( !empty($verify) ) { 
											echo wp_kses_post( $verify ); 
										} else {
											echo wp_kses_post ( $verify_default );
										} 
										?>
										</button>
									</div>
								</div>
							<?php } ?>
						<footer class="cev-authorization__footer cev_send_email_span_tag" style="text-align: <?php echo wp_kses_post( get_option('cev_content_align', 'center') ); ?>;" >
							<?php echo wp_kses_post ( get_option( 'cev_verification_widget_message_footer_checkout_pro', $CEV_Customizer_Options->defaults['cev_verification_widget_message_footer_checkout_pro'] ) ); ?>
						</footer>
						<footer class="cev-authorization__footer cev-rge-content" style="text-align: <?php echo wp_kses_post( get_option('cev_content_align', 'center') ); ?>;display:none">
							<?php 
								$footer_message = get_option( 'cev_verification_widget_footer', $CEV_Customizer_Options->defaults['cev_verification_widget_footer']); 										
								$footer_message = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $footer_message );			
								echo wp_kses_post ( $footer_message );
							?>
						</footer>
					</section>										
				</form>
			</div>
		</div>
	</div>
</div>
<?php $cev_verification_overlay_color = get_option('cev_verification_popup_overlay_background_color', '#e0e0e0'); ?>
<style>
	.cev-authorization-grid__visual{
		background-color: <?php echo wp_kses_post( cev_pro()->hex2rgba($cev_verification_overlay_color) ); ?>;
	}
	html { background: none;}
	
	.customize-partial-edit-shortcut-button {
		display: none;
	}
</style>
