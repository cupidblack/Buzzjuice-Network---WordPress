/**
 * Tests:Form
 *
 * Unit tests for alpine form library.
 *
 * @author Alex Standiford
 * @since 1.0.0
 *
 */

/**
 * Internal Dependencies
 */
import form from '@affiliatewp-portal/alpine-form';

/**
 * instance.
 *
 * Creates a form instance.
 *
 * @param fields Array of form fields to set
 */
const instance = function ( fields = [] ) {
	return { ...form, ...{ fields } };
}

/**
 * Object full of assorted invalid values for testing.
 *
 * @type {(boolean|string|number|{message: string}|Promise<unknown>)[]}
 */
const junk = [
	false,
	true,
	'invalid',
	undefined,
	null,
	123,
	123.32,
	{ message: 'this is invalid because it has no ID' },
	new Promise( ( res, rej ) => {
		res()
	} )
];

describe( 'alpine-form', () => {
	// addErrors
	describe( 'addErrors', () => {
		it( 'should add a single error without an array', () => {
			const form = instance();
			form.addErrors( { id: 'error', message: 'This is the message' } );
			expect( form.errors ).toEqual( [{ id: 'error', message: 'This is the message' }] )
		} )

		it( 'should add multiple errors with an array', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is the message' }
			];
			form.addErrors( errorsToAdd )
			expect( form.errors ).toEqual( errorsToAdd )
		} )

		it( 'should not add the same error ID twice', () => {
			const form = instance();
			form.addErrors( { id: 'error', message: 'This is the message' } );
			form.addErrors( { id: 'error', message: 'This is the message, but this one should not be added' } );

			expect( form.errors ).toEqual( [{ id: 'error', message: 'This is the message' }] )
		} )

		it( 'should not add the same error ID twice with arrays', () => {
			const form = instance();
			const error = { id: 'error', message: 'This is the message' };
			form.addErrors( error );
			form.addErrors( [error, {
				id: 'error2',
				message: 'This is another error message'
			}] );

			expect( form.errors ).toEqual( [
				{ id: 'error', message: 'This is the message' },
				{ id: 'error2', message: 'This is another error message' }
			] )
		} )

		it( 'Should ignore invalid values', () => {
			const form = instance();

			const valid = [
				{ id: 'error', message: 'This is a valid message' },
				{ id: 'valid' }
			];

			form.addErrors( [...valid, ...junk
			] );

			// set blank message for validation. When an error is instantiated, message defaults to an empty string.
			valid[1].message = '';

			expect( form.errors ).toEqual( valid );
		} )
	} )

	// removeErrors
	describe( 'removeErrors', () => {
		it( 'should remove a single error', () => {
			const form = instance();
			const errors = [{ id: 'error' }, { id: 'error2' }];

			// Add the error to remove
			form.addErrors( errors );

			//Just remove one of them.
			form.removeErrors( errors[0].id )

			expect( form.errors ).toHaveLength( 1 )
		} )

		it( 'should remove multiple errors', () => {
			const form = instance();
			const errors = [{ id: 'error' }, { id: 'error2' }];

			// Add the error to remove
			form.addErrors( errors );

			form.removeErrors( errors.map( error => error.id ) )

			expect( form.errors ).toHaveLength( 0 )
		} )

		it( 'should handle invalid values', () => {
			const form = instance();
			const errors = [{ id: 'error' }, { id: 'error2' }];

			// Add the error to remove
			form.addErrors( errors );

			form.removeErrors( junk )

			expect( form.errors ).toHaveLength( 2 )
		} )

	} )

	// getError
	describe( 'getError', () => {
		it( 'should get the error if it exists', () => {
			const form = instance();

			form.addErrors( { id: 'error', message: 'error message' } );

			expect( form.getError( 'error' ) ).toEqual( { id: 'error', message: 'error message' } );
		} )

		it( 'should return false if the error does not exist', () => {
			const form = instance();

			form.addErrors( { id: 'error', message: 'error message' } );

			expect( form.getError( 'invalid' ) ).toEqual( false )
		} )
	} );

	// getErrorMessage
	describe( 'getErrorMessage', () => {
		it( 'should get the error message if it exists', () => {
			const form = instance();

			form.addErrors( { id: 'error', message: 'error message' } );

			expect( form.getErrorMessage( 'error' ) ).toEqual( 'error message' );
		} )

		it( 'should return empty string if the error does not exist', () => {
			const form = instance();

			form.addErrors( { id: 'error', message: 'error message' } );

			expect( form.getErrorMessage( 'invalid' ) ).toEqual( '' )
		} )
	} );

	// hasAnyError
	describe( 'hasAnyError', () => {
		it( 'should return true if any of the error ids exist with single error', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasAnyError( 'errorOne' ) ).toEqual( true );
		} )

		it( 'should return true if any of the error ids exist with multiple errors', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasAnyError( ['invalid', 'errorOne'] ) ).toEqual( true );
		} )

		it( 'should return false if none of the error ids exist', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasAnyError( 'invalid' ) ).toEqual( false );
		} )

		it( 'should return false if none of the error ids exist with multiple errors', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasAnyError( ['invalid', 'also-invalid'] ) ).toEqual( false );
		} )

		it( 'should ignore invalid items', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasAnyError( [...['errorOne'], ...junk] ) ).toEqual( true );
		} )
	} );

	// hasErrors
	describe( 'hasErrors', () => {

		it( 'should return true if there are errors.', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasErrors() ).toEqual( true );
		} )

		it( 'should return false if there are no errors.', () => {
			const form = instance();
			expect( form.hasErrors() ).toEqual( false );
		} )

	} )

	// hasAllErrors
	describe( 'hasAllErrors', () => {
		it( 'should return true if all of the error ids exist with single error', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasAllErrors( 'errorOne' ) ).toEqual( true );
		} )

		it( 'should return true if all of the error ids exist with multiple errors', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasAllErrors( ['errorTwo', 'errorOne'] ) ).toEqual( true );
		} )

		it( 'should return false if any of the error ids do not exist', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasAllErrors( 'invalid' ) ).toEqual( false );
		} )

		it( 'should return false if any of the error ids do not exist with multiple errors', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasAllErrors( ['invalid', 'errorOne'] ) ).toEqual( false );
		} )

		it( 'should ignore invalid items', () => {
			const form = instance();
			const errorsToAdd = [
				{ id: 'errorOne', message: 'This is the message' },
				{ id: 'errorTwo', message: 'This is another message' }
			];
			form.addErrors( errorsToAdd );

			expect( form.hasAllErrors( [...['errorOne'], ...junk] ) ).toEqual( false );
		} )

	} );

	// getField
	describe( 'getField', () => {

		it( 'should return field if field exists', () => {
			const field = { id: 'fieldOne', value: 'fieldOneValue' };
			const form = instance( [field] );

			expect( form.getField( 'fieldOne' ) ).toEqual( field )
		} )

		it( 'should return false if field does not exist', () => {
			const field = { id: 'fieldOne', value: 'fieldOneValue' };
			const form = instance( [field] );

			expect( form.getField( 'invalid' ) ).toEqual( false )
		} )

		// Test against junk.
		junk.forEach( ( value ) => {
			it( `should ignore invalid ${typeof value} input`, () => {
				const field = { id: 'fieldOne', value: 'fieldOneValue' };
				const form = instance( [field] );

				expect( form.getField( value ) ).toEqual( false )
			} )
		} )

	} )

	// getFieldValue
	describe( 'getFieldValue', () => {

		it( 'should return field value if field exists', () => {
			const field = { id: 'fieldOne', value: 'fieldOneValue' };
			const form = instance( [field] );

			expect( form.getFieldValue( 'fieldOne' ) ).toEqual( 'fieldOneValue' )
		} )

		it( 'should return false if field does not exist', () => {
			const field = { id: 'fieldOne', value: 'fieldOneValue' };
			const form = instance( [field] );

			expect( form.getFieldValue( 'invalid' ) ).toEqual( '' )
		} )

		// Test against junk.
		junk.forEach( ( value ) => {
			it( `should ignore invalid ${typeof value} input`, () => {
				const field = { id: 'fieldOne', value: 'fieldOneValue' };
				const form = instance( [field] );

				expect( form.getFieldValue( value ) ).toEqual( '' )
			} )
		} )

	} )

	// updateFieldValue
	describe( 'updateFieldValue', () => {

		it( 'should update the field value if the field exists', () => {
			const field = { id: 'fieldOne', value: 'fieldOneValue' };
			const form = instance( [field] );
			form.updateFieldValue( 'fieldOne', 'updatedValue' )

			expect( form.getFieldValue( 'fieldOne' ) ).toEqual( 'updatedValue' )
		} )

		it( 'should return true if the field was updated', () => {
			const field = { id: 'fieldOne', value: 'fieldOneValue' };
			const form = instance( [field] );

			expect( form.updateFieldValue( 'fieldOne', 'updatedValue' ) ).toEqual( true )
		} )

		it( 'should return false if field does not exist', () => {
			const field = { id: 'fieldOne', value: 'fieldOneValue' };
			const form = instance( [field] );

			expect( form.updateFieldValue( 'invalid', 'updatedValue' ) ).toEqual( false )
		} )

	} )

	// setupField
	describe( 'setupField', () => {

		it( 'should return checked status for checkbox inputs', () => {
			const form = instance({id: 'fieldOneCheckbox', value: true});
			const directives = form.setupField('fieldOne','checkbox');

			expect( Object.keys(directives) ).toContain('x-bind:checked')
		} )

		it( 'should return checked status for radio inputs', () => {
			const form = instance({id: 'fieldOneCheckbox', value: true});
			const directives = form.setupField('fieldOne','radio');

			expect( Object.keys(directives) ).toContain('x-bind:checked')
		} )

		it( 'should return value status for text inputs', () => {
			const form = instance({id: 'fieldOneCheckbox', value: true});
			const directives = form.setupField('fieldOne');

			expect( Object.keys(directives) ).toContain('x-bind:value')
		} )

		it( 'should return value status for textarea inputs', () => {
			const form = instance({id: 'fieldOneCheckbox', value: true});
			const directives = form.setupField('fieldOne', 'textarea');

			expect( Object.keys(directives) ).toContain('x-bind:value')
		} )

		it( 'should return value status for number inputs', () => {
			const form = instance({id: 'fieldOneCheckbox', value: true});
			const directives = form.setupField('fieldOne', 'number');

			expect( Object.keys(directives) ).toContain('x-bind:value')
		} )

		it( 'should return value status for email inputs', () => {
			const form = instance({id: 'fieldOneCheckbox', value: true});
			const directives = form.setupField('fieldOne', 'email');

			expect( Object.keys(directives) ).toContain('x-bind:value')
		} )

		it( 'should return value status for number inputs', () => {
			const form = instance({id: 'fieldOneCheckbox', value: true});
			const directives = form.setupField('fieldOne', 'number');

			expect( Object.keys(directives) ).toContain('x-bind:value')
		} )

	} )

} );
