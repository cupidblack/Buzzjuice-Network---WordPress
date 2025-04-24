/**
 * Custom Affiliate Slugs Settings Handler.
 *
 * Works with the settings page template to handle slug validation.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global CASSettings
 *
 */

/**
 * Internal Dependencies
 */
import prepareForm from '@affiliatewp-portal/portal-form';

/**
 * Custom Affiliate Slugs Settings screen AlpineJS handler.
 *
 * Works with the settings page template to handle slug validation.
 *
 * @since 1.0.0
 * @access public
 * @global CASSettings
 *
 * @returns object The AlpineJS object.
 */
function settings() {
	const form = prepareForm();
	return {
		...form, ...{

			/**
			 * Section ID.
			 *
			 * The section ID that contains the form fields.
			 *
			 * @since 1.0.0
			 *
			 * @type {string} The section ID
			 */
			sectionId: 'custom-affiliate-slugs-settings',

			/**
			 * Original Slug
			 *
			 * The original slug that was provided on page load.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @type string
			 */
			originalSlug: '',

			/**
			 * Show Confirm Field.
			 *
			 * Returns true if the confirm setting field should be visible.
			 *
			 * @since      1.0.0
			 * @access     public
			 *
			 * @returns {boolean} true if visible, otherwise false.
			 */
			showConfirmField() {
				const slug = this.getField( 'custom-affiliate-slug-setting' );

				if ( false === slug ) {
					return false;
				}

				if ( this.originalSlug === slug.value || "" === slug.value ) {
					return false;
				}

				return true;
			},

			/**
			 * Reset Confirmations.
			 *
			 * Resets the delete checkbox, the confirm slug, and their validations.
			 *
			 * @since      1.0.0
			 * @access     public
			 *
			 * @returns {Promise<void>}
			 */
			async resetConfirmations() {

				// Reset confirmation values
				this.updateFieldValue( "custom-affiliate-slug-confirm-delete", false );
				this.updateFieldValue( "custom-affiliate-slug-confirm", '' );
				this.isValidating = true;

				// Reset confirmations.
				this.removeErrors( ['custom-affiliate-slug-confirm', 'custom-affiliate-slug-confirm-delete'] )
				await Promise.all( [
					this.validateControl( 'custom-affiliate-slug-confirm' ),
					this.validateControl( 'custom-affiliate-slug-confirm-delete' )
				] );
				this.isValidating = false;
			},

			/**
			 * Validate Control.
			 *
			 * Validates a control by the provided ID, and sets the error if so
			 *
			 * @since 1.0.0
			 * @access public
			 * @param {String} id Control ID.
			 *
			 * @returns {Promise<void>}
			 */
			async validateControl( id ) {
				const validateControl = form.validateControl.bind( this );

				if ( 'custom-affiliate-slug-setting' === id ) {
					await this.resetConfirmations();
				}

				validateControl( id );
			},

			/**
			 * Show Confirm Delete Field.
			 *
			 * Returns true if the confirm delete setting checkbox should be visible.
			 *
			 * @since      1.0.0
			 * @access     public
			 *
			 * @returns {boolean} true if visible, otherwise false.
			 */
			showConfirmDeleteField() {
				if ( true === this.isLoading ) {
					return false;
				}

				const slug = this.getField( 'custom-affiliate-slug-setting' );

				if ( false === slug ) {
					return false;
				}

				if ( '' === this.originalSlug || "" !== slug.value ) {
					return false;
				}

				return true;
			},

			/**
			 * Submit Form.
			 *
			 * Actions that should be taken when the form is submitted.
			 *
			 * @since      1.0.0
			 * @access     public
			 *
			 * @returns {Promise<void>}
			 */
			async submitForm() {
				const submitForm = form.submitForm.bind( this );
				await submitForm();
				this.resetConfirmations();
				this.resetSlug();
			},

			/**
			 * Reset Slug.
			 *
			 * Resets the original slug value to whatever the current slug setting value is.
			 *
			 * @since 1.0.0
			 *
			 * @returns {Promise<void>}
			 */
			resetSlug() {
				// Just after setup is complete, get the field value.
				const slug = this.getField( 'custom-affiliate-slug-setting' );

				if ( false !== slug ) {
					this.originalSlug = slug.value;
				}
			},

			/**
			 * Init.
			 *
			 * Fires when this object is set up.
			 *
			 * @since      1.0.0
			 * @access     public
			 *
			 * @returns {Promise<void>}
			 */
			async init() {
				const init = form.init.bind( this );
				await init();
				this.resetSlug();
			}

		}
	}
}

export default settings;