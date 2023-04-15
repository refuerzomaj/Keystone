<?php
namespace Essential\Restate\Front\Error;

use Essential\Restate\Traitval\Traitval;

/**
 * Front class loads the frontend.
 *
 * since 1.0.0
 */
class Error {

	use Traitval;

	public function initialize() {
		add_action( 'cl_purchase_form_before_submit', array( $this, 'cl_print_errors' ) );
		add_action( 'cl_ajax_checkout_errors', array( $this, 'cl_print_errors' ) );
		add_action( 'cl_print_errors', array( $this, 'cl_print_errors' ) );
	}

	function cl_print_errors() {
		$errors = $this->cl_get_errors();
		if ( $errors ) {
			$classes = apply_filters(
				'cl_error_class',
				array(
					'cl_errors',
					'cl-alert',
					'cl-alert-error',
				)
			);
			echo '<div class="' . implode( ' ', $classes ) . '">';
			// Loop error codes and display errors
			foreach ( $errors as $error_id => $error ) {
				echo '<p class="cl_error" id="cl_error_' . esc_attr( $error_id ) . '"><strong>' . __( 'Error', 'essential-wp-real-estate' ) . '</strong>: ' . esc_html( $error ) . '</p>';
			}
			echo '</div>';
			$this->cl_clear_errors();
		}
	}

	/**
	 * Get Errors
	 *
	 * Retrieves all error messages stored during the checkout process.
	 * If errors exist, they are returned.
	 *
	 * @since 1.0
	 * @uses CL_Session::get()
	 * @return mixed array if errors are present, false if none found
	 */
	function cl_get_errors() {
		return WPERECCP()->front->session->get( 'cl_errors' );
	}

	/**
	 * Set Error
	 *
	 * Stores an error in a session var.
	 *
	 * @since 1.0
	 * @uses CL_Session::get()
	 * @param int    $error_id ID of the error being set
	 * @param string $error_message Message to store with the error
	 * @return void
	 */
	function cl_set_error( $error_id, $error_message ) {
		$errors = $this->cl_get_errors();
		if ( ! $errors ) {
			$errors = array();
		}
		$errors[ $error_id ] = $error_message;
		WPERECCP()->front->session->set( 'cl_errors', $errors );
	}

	/**
	 * Clears all stored errors.
	 *
	 * @since 1.0
	 * @uses CL_Session::set()
	 * @return void
	 */
	function cl_clear_errors() {
		WPERECCP()->front->session->set( 'cl_errors', null );
	}

	/**
	 * Removes (unsets) a stored error
	 *
	 * @since 1.3.4
	 * @uses CL_Session::set()
	 * @param int $error_id ID of the error being set
	 * @return string
	 */
	function cl_unset_error( $error_id ) {
		$errors = $this->cl_get_errors();
		if ( $errors ) {
			unset( $errors[ $error_id ] );
			WPERECCP()->front->session->set( 'cl_errors', $errors );
		}
	}

	/**
	 * Register die handler for cl_die()
	 *
	 * @author Sunny Ratilal
	 * @since 1.6
	 * @return void
	 */
	function _cl_die_handler() {
		if ( defined( 'CL_UNIT_TESTS' ) ) {
			return '_cl_die_handler';
		} else {
			die();
		}
	}

	/**
	 * Wrapper function for wp_die(). This function adds filters for wp_die() which
	 * kills execution of the script using wp_die(). This allows us to then to work
	 * with functions using cl_die() in the unit tests.
	 *
	 * @author Sunny Ratilal
	 * @since 1.6
	 * @return void
	 */
	function cl_die( $message = '', $title = '', $status = 400 ) {
		add_filter( 'wp_die_ajax_handler', 10, 3 );
		add_filter( 'wp_die_handler', array( $this, '_cl_die_handler' ), 10, 3 );
		wp_die( $message, $title, array( 'response' => $status ) );
	}
}
