<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cev_verification_image_content = get_option('cev_verification_image_content', cev_pro()->plugin_dir_url() . 'assets/css/images/email-verification.png');
$cev_new_email_image_width  =  get_option( 'cev_email_content_widget_header_image_width', cev_pro()->customizer_options->defaults['cev_email_content_widget_header_image_width'] );
$cev_content_align_style = get_option( 'cev_content_align_style', cev_pro()->customizer_options->defaults['cev_content_align_style'] );
$cev_new_acoount_button_text = get_option( 'cev_new_acoount_button_text', cev_pro()->customizer_options->defaults['cev_new_acoount_button_text'] );
$cev_verification_content_font_color = get_option( 'cev_verification_content_font_color', cev_pro()->customizer_options->defaults['cev_verification_content_font_color'] );
$cev_header_content_font_size = get_option( 'cev_header_content_font_size', cev_pro()->customizer_options->defaults['cev_header_content_font_size'] );

$cev_widget_content_width_style = get_option( 'cev_widget_content_width_style', cev_pro()->customizer_options->defaults['cev_widget_content_width_style'] );
$cev_verification_content_background_color = get_option( 'cev_verification_content_background_color', cev_pro()->customizer_options->defaults['cev_verification_content_background_color'] );
$cev_verification_content_border_color = get_option( 'cev_verification_content_border_color', cev_pro()->customizer_options->defaults['cev_verification_content_border_color'] );
$cev_widget_content_padding_style = get_option( 'cev_widget_content_padding_style', cev_pro()->customizer_options->defaults['cev_widget_content_padding_style'] );
?>
<style>
#template_header {
	display:none;
}
#template_container,#template_body{
	width: <?php esc_html_e( $cev_widget_content_width_style ); ?>;
	border-color: <?php esc_html_e( $cev_verification_content_border_color ); ?>;
}
#body_content{
	background-color: <?php esc_html_e( $cev_verification_content_background_color ); ?>;
	padding: <?php esc_html_e( $cev_widget_content_padding_style ); ?>px !important;
}
#body_content table tr td {
	padding: 0;
}
#template_header_image{
	display: none;
}
div#wrapper {
	padding-top: 40px;
}
</style>
<div style="width:100%; margin-bottom: 0; text-align:<?php esc_html_e( $cev_content_align_style ); ?>;" >
	<?php if ( $cev_verification_image_content ) { ?>	
		<img src="<?php esc_html_e( $cev_verification_image_content ); ?>" width="<?php esc_html_e( $cev_new_email_image_width ); ?>">
	<?php } ?>
	<div style="padding-top: 15px;"> 
		<p style="font-weight: 600; font-size: <?php esc_html_e( $cev_header_content_font_size ); ?>px ; color:<?php esc_html_e( $cev_verification_content_font_color ); ?>; text-align:<?php esc_html_e( 	$cev_content_align_style ); ?>;">
			<?php esc_html_e( $email_heading ); ?>
		</p>
	</div>   
	<div style="text-align:<?php esc_html_e( $cev_content_align_style ); ?>;">
		<?php echo wp_kses_post( wpautop( $content ) );	?>
	</div>
</div>
<div style="text-align:<?php esc_html_e( $cev_content_align_style ); ?>;"><?php echo wp_kses_post( $footer_content ); ?></div>
