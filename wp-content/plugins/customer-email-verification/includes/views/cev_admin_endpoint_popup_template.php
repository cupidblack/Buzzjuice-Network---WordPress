<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$width = ( '1' == $sample_toggle_switch_cev ) ? '100%' : 'auto';
$CEV_Customizer_Options = new CEV_Customizer_Options();
$cev_verification_hide_otp_section =  get_option('cev_verification_hide_otp_section', $CEV_Customizer_Options->defaults['cev_verification_hide_otp_section'] );
?>
<div class="cev-authorization-grid__visual">
	<div class="cev-authorization-grid__holder ">
		<div class="cev-authorization-grid__inner" style="max-width: <?php echo wp_kses_post( get_option('cev_widget_content_width', '460px') ); ?>px;">
			<div class="cev-authorization" style="background: <?php echo wp_kses_post( get_option('cev_verification_popup_background_color', $CEV_Customizer_Options->defaults['cev_verification_popup_background_color']) ); ?>;">
				<form class="cev_pin_verification_form"  method="post">
					<section class="cev-authorization__holder" style="text-align: <?php echo wp_kses_post( get_option('cev_content_align', 'center') ); ?>;  padding: <?php echo wp_kses_post( get_option('cev_widget_content_padding', '40') ); ?>px; ">
						<div class="popup_image">
							<?php
							$image = get_option('cev_verification_image', cev_pro()->plugin_dir_url() . 'assets/css/images/email-verification-icon.svg'); 
							if ( !empty($image) ) {
								?>
								<img src="<?php echo wp_kses_post( $image ); ?>" style="width:<?php echo wp_kses_post( $cev_widget_header_image_width ); ?>px;">
							<?php } ?>
						</div>
						<div class="cev-authorization__heading">
							<span class="cev-authorization__title" style="font-size: <?php echo wp_kses_post( $cev_button_text_header_font_size ); ?>px;">
								<?php
								$heading_default = __('Verify its you.', 'customer-email-verification');
								$heading = get_option( 'cev_verification_header'); 
								if ( !empty($heading) ) { 
									echo wp_kses_post( $heading ); 
								} else {
									echo wp_kses_post( $heading_default ); 
								}
								?>
							</span>
							<span class="cev-authorization__description" style="text-align:<?php echo wp_kses_post( get_option('cev_content_align', 'center') ); ?>;">
								<?php
								/* translators: %s: search send verification code */
								$message = sprintf(__('We sent verification code to <strong>%s</strong>. To verify your email address, please check your inbox and enter the code below.', 'customer-email-verification'), $email);
								$message = apply_filters( 'cev_verification_popup_message', $message, $email );
								echo wp_kses_post( $message ); 
								?>
							</span>
						</div>
						<?php if ( 0 == $cev_verification_hide_otp_section ) { ?>
								<div class="cev-pin-verification">
									<div class="cev-pin-verification__row">
										<div class="cev-field cev-field_size_extra-large cev-field_icon_left cev-field_event_right cev-field_text_center">
											<h5 class="required-filed" style="text-align:<?php echo wp_kses_post( get_option('cev_content_align', 'center') ); ?>;">
											<?php 
											$codelength = apply_filters( 'cev_verification_code_length', __( '4-digits code', 'customer-email-verification' ) ); 
											echo  wp_kses_post( $codelength );									
											?>
											*
											</h5>
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
											<input type="text" maxlength="1" class="otp-input-email" id="otp_input_<?php echo esc_attr('otp_input_' . $i); ?>" />
										<?php endfor; ?>
									</div>
										</div>
									</div>
									<div class="cev-pin-verification__failure js-pincode-invalid" style="display: none;">
										<div class="cev-alert cev-alert_theme_red">										
											<span class="js-pincode-error-message">
											<?php
											esc_html_e( 'Verification code does not match', 'customer-email-verification' ); 
											?>
											</span>
										</div>
									</div>
									<div class="cev-pin-verification__events">
										<input type="hidden" name="cev_user_id" value="<?php echo esc_html_e( get_current_user_id() ); ?>">
										<?php wp_nonce_field( 'cev_verify_user_email_with_pin', 'cev_verify_user_email_with_pin' ); ?>
										<input type="hidden" name="action" value="cev_verify_user_email_with_pin">
										<button class="cev-button  cev-button_size_promo cev-button_type_block cev-pin-verification__button is-disabled" type="submit" style="background-color:<?php echo wp_kses_post( $cev_button_color_widget_header ); ?>; color:<?php echo wp_kses_post( $cev_button_text_color_widget_header ); ?>; font-size:<?php echo wp_kses_post( $cev_button_text_size_widget_header ); ?>px; width: <?php esc_html_e( $width ); ?>; padding: <?php echo wp_kses_post( $button_padding ); ?>; display:none;" id="pin_verification_button">
										<?php 
										$verify = get_option( 'cev_verification_header_button_text' );
										if ( !empty($verify) ) { 
											echo wp_kses_post( $verify ); 
										} else {
											esc_html_e('Verify Code', 'customer-email-verification');
										} 
										?>
										</button>
									</div>
								</div>
							<?php } ?>
						<footer class="cev-authorization__footer" style="text-align: <?php echo wp_kses_post( get_option('cev_content_align', 'center') ); ?>; ">
						<?php 
							$CEV_Customizer_Options = new CEV_Customizer_Options();
							$footer_message = get_option( 'cev_verification_widget_footer', $CEV_Customizer_Options->defaults['cev_verification_widget_footer']);
							$footer_message = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $footer_message );			
							echo wp_kses_post( $footer_message );
						?>
						</footer>
					</section>
				</form>            
			</div>
		</div>
	</div>
</div>
