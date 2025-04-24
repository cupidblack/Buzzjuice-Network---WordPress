/**
 * Form.
 *
 * Works with forms to handle data validation and other form interactions.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global form
 *
 */

/**
 * Internal Dependencies
 */
import form from '@affiliatewp-portal/alpine-form';
import { pause } from "@affiliatewp-portal/helpers";
import { portalSectionFields, validateControl, submitSection } from '@affiliatewp-portal/sdk';

/**
 * Form handler.
 *
 * Works with forms to handle field validation, and submission.
 *
 * @param {string} sectionId The Section ID from which the fields should be fetched.
 *
 * @since 1.0.0
 * @access private
 * @global form
 *
 * @returns object The form AlpineJS object.
 */
export default ( sectionId ) => {
	return {
		...form, ...{

			/**
			 * Section ID.
			 *
			 * The section ID that contains the form fields.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @type {string} The section ID
			 */
			sectionId,

			/**
			 * Is Loading.
			 *
			 * Set to true if this item is loading.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @type {boolean} True if loading, otherwise false.
			 */
			isLoading: true,

			/**
			 * Is Validating.
			 *
			 * Set to true if this item is validating fields.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @type {boolean} True if loading, otherwise false.
			 */
			isValidating: false,

			/**
			 * Is Submitting.
			 *
			 * Set to true if this item is submitting the form.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @type {boolean} True if loading, otherwise false.
			 */
			isSubmitting: false,

			/**
			 * Showing success message.
			 *
			 * Whether or not the success message is showing (during submission).
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @type boolean
			 */
			showingSuccessMessage: false,

			/**
			 * Export Fields.
			 *
			 * Converts Alpine form fields to key => value pairs for REST submissions & validation.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @returns object Object of values keyed by the field ID.
			 */
			exportFields() {
				return this.fields.reduce( ( acc, field ) => {
					acc[field.id] = field.value;
					return acc;
				}, {} );
			},

			/**
			 * Has Validations.
			 *
			 * Returns true if the specified control has validations.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @param {String} id Control ID.
			 *
			 * @returns {boolean} True if the field has validations, otherwise false.
			 */
			hasValidations( id ) {
				const field = this.getField( id );
				if ( false === field ) {
					return false;
				}

				return true === field.hasValidations;
			},

			/**
			 * Validate Control.
			 *
			 * Validates a control by the provided ID, and sets the error if so
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @param {String} id Control ID.
			 *
			 * @returns {Promise<void>}
			 */
			async validateControl( id ) {

				// Bail early if this field has no validations.
				if ( false === this.hasValidations( id ) ) {
					return;
				}

				this.isValidating = true;

				const response = await validateControl( id, this.exportFields() );

				// Get the passed IDs.
				const passed = response.validations.passed.map( validation => validation.id );

				// Remove all errors that passed this time
				this.removeErrors( passed );

				// Add any errors that failed.
				this.addErrors( response.validations.failed );

				this.isValidating = false;
			},

			/**
			 * Setup Submit.
			 *
			 * Sets up the default directives for the submit button. Intended to be called using Alpine's x-spread directive.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @returns {object} Directives that should be applied to the submit button by default.
			 */
			setupSubmit() {
				return {
					['x-bind:disabled']() {
						return this.hasErrors() || this.isLoading || this.isValidating || this.isSubmitting;
					},
				}
			},

			/**
			 * Default Directives.
			 *
			 * Sets up the default directives for a field.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @param {string} id The control ID from which directives should be constructed.
			 * @param {string} type The input type, such as text, or checkbox.
			 *
			 * @returns {object} Directives that should be applied to all inputs by default.
			 */
			setupField( id, type = '', value = '' ) {
				// Bind the parent function to this instance. this is kind-of like running parent::function() in PHP.
				const setupControl = form.setupField.bind( this );

				// Get the default directives
				const parentDirectives = setupControl( id, type, value );
				const parentInput = parentDirectives['x-on:input'].bind( this );

				// A list of validations that should not have an input delay.
				const hasNoDelay = ['checkbox', 'select', 'radio'].includes( type );

				// AP-specific directives.
				const additionalDirectives = {
					['x-on:input']( event ) {
						parentInput( event )

						// Run field validations.
						if ( hasNoDelay ) {
							this.validateControl( id )
						} else {
							const fieldIndex = this.fields.findIndex( ( field ) => field.id === id );

							// Maybe reset the timeout, if it is already set.
							if ( undefined !== this.fields[fieldIndex].validating ) {
								window.clearTimeout( this.fields[fieldIndex].validating );
							}

							this.isLoading = true;

							this.fields[fieldIndex].validating = window.setTimeout( () => {
								this.validateControl( id )
								delete this.fields[fieldIndex].validating;
								this.isLoading = false;
							}, 200 )
						}
					},
					['x-on:blur']() {
						this.validateControl( id )
						this.isLoading = false;
					}
				}

				// Spread (combine) the two objects into a single object.
				return { ...parentDirectives, ...additionalDirectives };
			},

			/**
			 * Submit Form.
			 *
			 * Actions that should be taken when the form is submitted.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @returns {Promise<void>}
			 */
			async submitForm() {
				this.isSubmitting = true;
				const response = await submitSection( this.sectionId, this.exportFields() );
				// remove all errors.
				this.removeErrors( response.validations.passed );
				this.addErrors( response.validations.failed );

				this.isSubmitting = false;

				if ( !this.hasErrors() ) {
					this.flashSuccessMessage();
				}
			},

			/**
			 * Flash Success Message.
			 *
			 * Flashes the success message.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @returns {Promise<void>}
			 */
			async flashSuccessMessage() {
				this.showingSuccessMessage = true;
				await pause( 1000 );
				this.showingSuccessMessage = false;
			},

			/**
			 * Sets up the form.
			 *
			 * @since  1.0.0
			 * @access public
			 *
			 * @returns {Promise<void>}
			 */
			setupForm() {
				return {
					async ['x-on:submit']( event ) {
						event.preventDefault();
						this.submitForm();
					}
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

				// simulate a fetch request.
				const response = await portalSectionFields( this.sectionId )

				this.fields = response.fields.map( ( field ) => {
					if ( 'checkbox' === field.type ) {
						if ( 'on' === field.value ) {
							field.value = true;
						}

						if ( 'off' === field.value ) {
							field.value = false;
						}
					}

					return field;
				} );

				this.isLoading = false;
			}
		}
	}
};
