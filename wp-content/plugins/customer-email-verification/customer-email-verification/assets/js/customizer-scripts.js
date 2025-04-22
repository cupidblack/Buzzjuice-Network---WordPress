/*
 * Customizer Scripts
 * Need to rewrite and clean up this file.
 */

jQuery(document).ready(function() {

    /**
     * Change description
     */
	jQuery(cev_customizer.trigger_click).trigger( "click" );    
	jQuery('#customize-theme-controls #accordion-section-themes').hide();	
});

(function ( api ) {
    api.section( 'cev_email_content', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = cev_customizer.seperate_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'cev_email_style_section', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = cev_customizer.email_style_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'cev_new_account_email_section', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = cev_customizer.my_account_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'cev_verification_popup_style', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = cev_customizer.verification_widget_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'cev_verificaion_popup_message', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
				
				var cev_widget_type = jQuery("#_customize-input-cev_widget_type option:selected").val();	
				
				if(cev_widget_type == 'registration'){
					var preview_url = cev_customizer.verification_widget_preview_url;						
				} else if( cev_widget_type == 'checkout' ) {			
					var preview_url = cev_customizer.verification_widget_checkout_preview_url;			
				}	
                url = preview_url;				
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );



/* cev pro */
/*
 * Customizer Scripts
 * Need to rewrite and clean up this file.
 */

wp.customize( 'cev_widget_type', function( value ) {		
	value.bind( function( cev_order_status_email_type ) {		
		if(cev_order_status_email_type == 'registration'){
			var preview_url = cev_customizer.verification_widget_preview_url;						
		} else if( cev_order_status_email_type == 'checkout' ) {			
			var preview_url = cev_customizer.verification_widget_checkout_preview_url;			
		}		
		wp.customize.previewer.previewUrl(preview_url);			
		wp.customize.previewer.refresh();		
	});
});

	
