<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="cev_content_tools" class="cev_tab_section">
	<div class="accordion_container">
		<div class="accordion_set">
				<div class="tools_accordion heading">
					<div class="accordion-label disable_toggle">					
						<label>
							<?php esc_html_e( 'Tools', 'customer-email-verification' ); ?>						
						</label>
					</div>
				</div>
				<div class="panel active">
					<ul class="tools_ul">
						<li>
							<label class="tools_label"><?php esc_html_e( 'Manually verify the emails of all customers with unverified email address', 'customer-email-verification' ); ?></label>
							<div class="submit_tools">
								<div class="spinner"></div>
									<button class="button-primary cev-email-verify-button-tools"><span class="dashicons dashicons-yes yes-tools"></span>
								<?php esc_html_e( 'Verify all emails', 'customer-email-verification' ); ?></button>
							</div>
						</li>
						<li>
							<label class="tools_label"><?php esc_html_e( 'Resend verification code to all customers with unverified email address', 'customer-email-verification' ); ?></label>
							<div class="submit_tools">
								<div class="spinner"></div>
								<button class="button-primary cev-email-resend-button-tools cev-email-resend-bulk-button-tools"><span class="dashicons dashicons-image-rotate redo-tools"></span><?php esc_html_e( 'Resend verification code', 'customer-email-verification' ); ?></button>
							</div>
						</li>
						<li>
							<?php 
							$checked = ( 'yes' == get_option( 'cev_enable_tools' ) ) ? 'checked' : '';
							?>
							<input type="hidden" name="cev_enable_email_verification" value="0">
							<input class="tgl tgl-flat-cev" id="cev_enable_tools" name="cev_enable_tools" type="checkbox" <?php esc_html_e( $checked ); ?> value="1" wp_nonce="<?php esc_html_e( wp_create_nonce( 'wc_cev_delete_user' ) ); ?>">
							<label class="tgl-btn" for="cev_enable_tools"></label>
							<label for="" class="disible_textfiled"><?php esc_html_e( 'Automatically delete customers with unverified email after  ', 'customer-email-verification' ); ?> 
							<input type="number" min="0" style="width:50px; vertical-align:baseline;" id="cev_change_texbox_value" name="cev_change_texbox_value" value="<?php esc_html_e( get_option				('cev_change_texbox_value', 14) ); ?>" wp_nonce="<?php esc_html_e( wp_create_nonce( 'wc_cev_delete_user' ) ); ?>" /><?php esc_html_e( ' days', 'customer-email-verification' ); ?></label>
						</li>
					</ul>					
				</div>
		</div>
	</div>			
</section>
