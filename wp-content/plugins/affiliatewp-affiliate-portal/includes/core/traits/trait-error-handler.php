<?php
/**
 * Traits: Error Handler
 *
 * @package   Core/Traits
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Traits;

/**
 * Implements logic for handling basic error management in an object.
 *
 * @since 1.0.0
 */
trait Error_Handler {

	/**
	 * WP_Error object.
	 *
	 * @since 1.0.0
	 * @var   \WP_Error
	 */
	private $errors;

	/**
	 * Initializes the WP_Error object.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->set_up_errors();
	}

	/**
	 * Sets the errors object.
	 *
	 * @since 1.0.0
	 */
	public function set_up_errors() {
		if ( ! $this->errors instanceof \WP_Error ) {
			$this->errors = new \WP_Error();
		}
	}

	/**
	 * Determines whether there are currently any collected errors.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether there are any collected errors.
	 */
	public function has_errors() {
		return method_exists( $this->errors, 'has_errors' ) ? $this->errors->has_errors() : ! empty( $this->errors->errors );
	}

	/**
	 * Helper for adding an error against the WP_Error object.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $code    Error code.
	 * @param string     $message Error message.
	 * @param mixed      $data    Optional. Error data.
	 */
	public function add_error( $code, $message, $data = '' ) {
		$this->errors->add( $code, $message, $data );
	}

	/**
	 * Retrieves a copy of the error object.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $reset Whether to reset the errors container after retrieving the errors. Default true.
	 * @return \WP_Error Error object.
	 */
	public function get_errors( $reset = true ) {
		$errors = $this->errors;

		if ( true === $reset ) {
			$this->errors = null;

			$this->set_up_errors();
		}

		return $errors;
	}

	/**
	 * Logs errors to the AffiliateWP debug log.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context Optional. Contextual data to include in the log message. Default null (unused).
	 */
	public function log_errors( $context = null ) {
		if ( null === $context ) {
			$message = 'Portal: There was an error with one or more elements on the page.';
		} else {
			$message = sprintf( 'Portal: There was an error with one or more elements on the page for the \'%1$s\' context.', $context );
		}

		affiliate_wp()->utils->log( $message, $this->get_errors() );
	}

}
