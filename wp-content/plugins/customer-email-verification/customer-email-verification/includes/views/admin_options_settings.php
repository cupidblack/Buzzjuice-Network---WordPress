<?php
/**
 * Html Code for Settings tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section id="cev_content_settings" class="cev_tab_section">	
	<form method="post" id="cev_settings_form" action="" enctype="multipart/form-data"><?php #nonce ?>
		
		<div class="settings_accordion accordion_container">
		<div class="accordion_set">
				<div class="accordion heading cev-main-settings">
					
					<div class="accordion-open accordion-label">
						<?php esc_html_e( 'Email verification settings', 'customer-email-verification' ); ?>						
					</div>
					
					<div class="accordion-btn accordion-open">
						<span class="dashicons dashicons-arrow-right-alt2"></span>
						<div class="spinner workflow_spinner" style="float:none"></div>
						<button name="save" class="button-primary woocommerce-save-button cev_settings_save" type="submit" value="Save changes"><?php esc_html_e( 'Save & Close', 'customer-email-verification' ); ?></button>						
					</div>

				</div>
				<div class="panel">
					<?php $this->get_html( $this->get_cev_settings_data_new() ); ?>	
				</div>
			</div>
			<div class="accordion_set">
				<div class="accordion heading cev-main-settings">
					<?php 
					$checked = ( get_option( 'cev_enable_login_authentication', 0 ) ) ? 'checked' : ''; 
					$disable_toggle_class = get_option( 'cev_enable_login_authentication', 0 ) ? '' : 'disable_toggle';
					?>
					<div class="accordion-open accordion-label">						
						<?php esc_html_e( 'Login Authentication', 'customer-email-verification' ); ?>						
					</div>
					
					<div class="accordion-btn accordion-open">
						<span class="dashicons dashicons-arrow-right-alt2"></span>
						<div class="spinner workflow_spinner" style="float:none"></div>
						<button name="save" class="button-primary woocommerce-save-button cev_settings_save" type="submit" value="Save changes"><?php esc_html_e( 'Save & Close', 'customer-email-verification' ); ?></button>						
					</div>

				</div>
				<div class="panel">
					<?php $this->get_html( $this->get_cev_settings_data_login_otp() ); ?>
				</div>
			</div>			
			</div>
			

		<?php wp_nonce_field( 'cev_settings_form_nonce', 'cev_settings_form_nonce' ); ?>
		<input type="hidden" name="action" value="cev_settings_form_update">					
	</form>	
</section>
