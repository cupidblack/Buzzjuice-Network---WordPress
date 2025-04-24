/**
 * Form.
 *
 * Works with forms to handle field validation, and submission.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global form
 *
 */

/**
 * External dependencies
 */
import 'alpinejs';

/**
 * Error constructor.
 *
 * Creates a single error instance.
 *
 * @param {object} args list of args to set the error.
 * @constructor
 */
const Error = function ( args ) {
	this.id = args.id || '';
	this.message = args.message || '';
}

/**
 * Form handler for AlpineJS.
 *
 * Works with forms to handle field validation, and submission.
 *
 * @since 1.0.0
 * @access private
 * @global form
 *
 * @returns object The form AlpineJS object.
 */
export default {

	/**
	 * fields.
	 *
	 * Array of objects containing the form fields.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type Array
	 */
	fields: [],

	/**
	 * errors.
	 *
	 * Array of objects containing current errors.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type Array
	 */
	errors: [],

	/**
	 * Add Errors.
	 *
	 * Adds an error, or multiple errors.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @param {object|array} errors A single error object, or an array of error objects.
	 *
	 * @returns {void}
	 */
	addErrors( errors ) {

		// Convert to array
		if ( !Array.isArray( errors ) ) {
			errors = [errors];
		}

		// Construct error objects from args.
		errors = errors.reduce( ( acc, error ) => {

			// Bail if null
			if ( null === error ) {
				return acc;
			}

			// Bail early if invalid
			if ( typeof error !== 'object' || undefined === error.id ) {
				return acc;
			}

			// If the error already exists, bail
			if ( false !== this.getError( error.id ) ) {
				return acc;
			}

			// Push the error instance to the accumulator.
			acc.push( new Error( error ) );

			return acc;
		}, [] );

		// Merge reduced error objects with current errors.
		this.errors = [...this.errors, ...errors];
	},

	/**
	 * Remove Errors.
	 *
	 * Removes an error, or multiple errors.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @param {string|array} errorIds a single error ID, or a list of error IDs to remove.
	 *
	 * @returns {void}
	 */
	removeErrors( errorIds ) {
		if ( !Array.isArray( errorIds ) ) {
			errorIds = [errorIds];
		}

		// Filter out the error specified by the ID.
		this.errors = this.errors.filter( error => !errorIds.includes( error.id ) );
	},

	/**
	 * Get Error.
	 *
	 * Retrieves an error by the provided error ID.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @param {string} errorId The error ID to fetch
	 *
	 * @returns {object|boolean} Error object, or false if error was not found.
	 */
	getError( errorId ) {
		// Search for the error.
		const error = this.errors.find( error => error.id === errorId );

		// Bail if the error is not valid.
		if ( typeof error !== 'object' ) {
			return false;
		}

		// Otherwise, return the error.
		return error;
	},

	/**
	 * Get Error Message.
	 *
	 * Retrieves an error message by the provided error ID.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @param {string} errorId The error ID to fetch
	 *
	 * @returns {string|boolean} Error message, or empty string if error was not found.
	 */
	getErrorMessage( errorId ) {
		const error = this.getError( errorId );

		if ( false === error || undefined === error.message ) {
			return '';
		}

		return error.message;
	},

	/**
	 * Has Any Error.
	 *
	 * Tests to see if any of the provided errors are currently set.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @param {string|array} errorIds The error ID, or array of IDs to find.
	 *
	 * @returns {boolean} True if any of the provided errors exist. Otherwise false.
	 */
	hasAnyError( errorIds = []) {
		if ( !Array.isArray( errorIds ) ) {
			errorIds = [errorIds];
		}


		return undefined !== errorIds.find( errorId => false !== this.getError( errorId ) );
	},

	/**
	 * Has Errors.
	 *
	 * Tests to see if there are any errors.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @returns {boolean} True if there are any errors. Otherwise false.
	 */
	hasErrors(){
		return this.errors.length > 0;
	},

	/**
	 * Has All Errors.
	 *
	 * Tests to see if all of the provided errors are currently set.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @param {string|array} errorIds The error ID, or array of IDs to find.
	 *
	 * @returns {boolean} True if all of the provided errors exist. Otherwise false.
	 */
	hasAllErrors( errorIds = [] ) {

		if ( !Array.isArray( errorIds ) ) {
			errorIds = [errorIds];
		}

		const foundErrors = this.errors.filter( error => errorIds.includes( error.id ) )

		return foundErrors.length === errorIds.length;
	},

	/**
	 * Get Field.
	 *
	 * Retrieves a single field by the provided ID.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @param {string} id The field ID
	 *
	 * @returns {object|boolean} The field object if it exists, otherwise false.
	 */
	getField( id ) {
		// Search for the field.
		const field = this.fields.find( field => field.id === id );

		// Bail if the field is not valid.
		if ( null === field || typeof field !== 'object' ) {
			return false;
		}

		// Otherwise, return the field.
		return field;
	},

	/**
	 * Get Field Value.
	 *
	 * Retrieves a single field value by the provided ID.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @param {string} id The field ID
	 *
	 * @returns {string|boolean} The field value if it exists, otherwise empty string.
	 */
	getFieldValue( id ) {
		const field = this.getField( id );

		if ( false === field || undefined === field.value ) {
			return '';
		}

		return field.value;
	},

	/**
	 * Update Field Value.
	 *
	 * Updates a single field value by the provided ID.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @param {string} id The field ID
	 * @param {mixed} value The field value to set.
	 *
	 * @returns {boolean} true if updated, otherwise false.
	 */
	updateFieldValue( id, value ) {
		const index = this.fields.findIndex( field => field.id === id );

		if ( index < 0 ) {
			return false;
		}

		this.fields[index].value = value;

		return true;
	},

	/**
	 * Update Field Update.
	 *
	 * Alpine-friendly update handler for inputs.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @param {string} id The field ID
	 * @param {Event} event The event.
	 *
	 * @returns {boolean} true if updated, otherwise false.
	 */
	handleFieldUpdate( id, event ) {
		let value = '';
		switch ( event.target.type ) {
			case 'checkbox':
				value = event.target.checked;
				break;
			case 'number':
				value = parseFloat( event.target.value );
				break;
			default:
				value = event.target.value
		}
		return this.updateFieldValue( id, value )
	},

	/**
	 * Setup Submit.
	 *
	 * Sets up the default directives for the submit button. Intended to be called using Alpine's x-spread directive.
	 *
	 * @returns {object} Directives that should be applied to the submit button by default.
	 */
	setupSubmit() {
		return {
			['x-bind:disabled']() {
				return this.hasErrors() || this.isLoading
			}
		}
	},

	/**
	 * Setup Field.
	 *
	 * Sets up the default directives for a field. Intended to be called using Alpine's x-spread directive.
	 *
	 * @param {string} id The field ID to set up.
	 * @param {string} type The field type. Only necessary when the field type is a checkbox or radio button.
	 * @param {string} value The field's value attribute. Only necessary when the field type is a radio button.
	 *
	 * @returns {object} Directives that should be applied to all inputs by default.
	 */
	setupField( id, type = '', value = '' ) {

		// Most fields just use value, but some use checked, instead.
		let valueDirective = 'x-bind:value';

		// If this is a field type that uses checked instead of value, set the directive.
		if ( ['checkbox', 'radio'].includes( type ) ) {
			valueDirective = 'x-bind:checked';
		}

		const result = {
			['x-on:input']( event ) {
				this.handleFieldUpdate( id, event )
			},
			[valueDirective]() {
				return this.getFieldValue( id )
			},
		}

		// Radio buttons work a little differently.
		if ( 'radio' === type ) {
			result[valueDirective] = function () {
				return value === this.getFieldValue( id );
			}
		}

		return result;
	},

	/**
	 * Init
	 *
	 * Fires when this object is set up.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @returns {void}
	 */
	init() {
	}
}
