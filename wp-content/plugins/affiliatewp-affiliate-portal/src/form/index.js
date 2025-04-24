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
import portalForm from '@affiliatewp-portal/portal-form';

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
export default portalForm