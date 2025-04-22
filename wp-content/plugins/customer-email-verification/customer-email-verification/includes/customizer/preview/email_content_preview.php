<?php 
// Exit if accessed directly
if ( !defined('ABSPATH') ) {
	exit;
}
?>
	<head>
		<meta charset="<?php bloginfo('charset'); ?>" />
		<meta name="viewport" content="width=device-width" />
			<style type="text/css" id="cev_designer_custom_css">.woocommerce-store-notice.demo_store, .mfp-hide {display: none;}</style>
	</head>
	<body class="cev_preview_body">
		<div id="overlay"></div>
			<div id="cev_preview_wrapper" style="display: block;">
				<?php 
				$cev_content_widget_type = get_option('cev_content_widget_type', $this->defaults['cev_content_widget_type']);
				if ( 'checkout' == $cev_content_widget_type ) {
					$this->preview_checkout_email();	
				} else {
					$this->preview_account_email();	
				}
				?>
			</div>
				<?php
					do_action( 'woomail_footer' );
					wp_footer(); 
				?>
	</body>
</html>
