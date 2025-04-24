<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- The name of the tile is common among others.
/**
 * Database Utilities
 *
 * @package     AffiliateWP
 * @subpackage  Utils
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.12.0
 * @author      Aubrey Portwood <aubrey@awesomeomotive.com>
 */

// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- We escape DB queries because we have to use str_replace.

namespace AffiliateWP\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( trait_exists( '\AffiliateWP\Utils\DB' ) ) {
	return;
}

affwp_require_util_traits( 'data' );

/**
 * Database Utilities
 *
 * @since 2.12.0
 *
 * @see Affiliate_WP_DB
 */
trait DB {

	use \AffiliateWP\Utils\Data;

	/**
	 * Does a column (in a table) exist in the database?
	 *
	 * @since  2.12.0
	 * @since  2.18.0 Updated to use lower-level SQL.
	 * @since  2.18.2 Now default to `$this->table_name`.
	 *
	 * @param  string $table          The table name.
	 * @param  string $column         The column.
	 *
	 * @return bool
	 *
	 * @throws \InvalidArgumentException If you do not supply non-empty strings for either `$table` or `$column`.
	 */
	protected function column_exists( string $table = '', string $column = '' ) {

		if ( empty( $table ) && isset( $this->table_name ) ) {
			$table = $this->table_name;
		}

		if ( ! $this->is_string_and_nonempty( $table ) ) {
			throw new \InvalidArgumentException( '$table must be a non-empty string.' ); // Error.
		}

		if ( ! $this->is_string_and_nonempty( $column ) ) {
			throw new \InvalidArgumentException( '$column must be a non-empty string.' ); // Error.
		}

		if ( ! $this->table_exists( $table ) ) {
			return false; // Test for the table first.
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- We are trusting $table.
		$results = $wpdb->get_results(
			sprintf(
				'DESC `%1$s` -- %2$s %3$s',
				$table,
				$column,
				wp_generate_uuid4() // Avoids reporting duplicate queries to Query Monitor.
			)
		); // Note: Same as check_column() but will just check for Field.

		if ( ! is_countable( $results ) ) {
			return false;
		}

		foreach ( $results as $row ) {

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Snake case here comes from SQL.
			if ( ( $row->Field ?? '' ) === $column ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Inject our table name into SQL.
	 *
	 * WordPress forces you to use placeholders when you want to place
	 * `{$this->table_name}`, but if you do use placeholders it adds tickmarks
	 * around the table name. So, we have to do it this way.
	 *
	 * @see https://wordpress.stackexchange.com/questions/191729/quotes-in-table-name
	 *
	 * @since 2.12.0
	 * @since 2.12.1 Removed `has_tampered_table_name()` check, see https://github.com/awesomemotive/AffiliateWP/issues/4612.
	 *
	 * @param  string $sql SQL to inject table name into.
	 * @return string      SQL with `{table_name}` replaced with our table name.
	 *
	 * @throws \Exception                If our table name appears to be tampered with (SQL injection attempt).
	 * @throws \Exception                If you try and use this method w/out `$this::$table_name` being unset.
	 * @throws \InvalidArgumentException If `$sql` is not a string.
	 */
	protected function inject_table_name( $sql ) {

		if ( ! isset( $this->table_name ) || ! $this->is_string_and_nonempty( $this->table_name ) ) {
			throw new \Exception( '$this::$table_name needs to be set to a non-empty string in order to use this method.' );
		}

		if ( ! $this->is_string_and_nonempty( $sql ) ) {
			throw new \InvalidArgumentException( '$sql must be a non-empty string.' );
		}

		global $wpdb;

		return str_replace( '{table_name}', $wpdb->_real_escape( "{$this->table_name}" ), $sql );
	}

	/**
	 * Does a table exist?
	 *
	 * @since  2.12.0
	 * @since  2.18.2 No longer throws errors.
	 * @since  2.18.2 Now default to `$this->table_name`.
	 *
	 * @param  string $table The table in the database.
	 *
	 * @return bool
	 */
	protected function table_exists( string $table = '' ) : bool {

		if ( empty( $table ) && isset( $this->table_name ) ) {
			$table = $this->table_name;
		}

		if ( ! $this->is_string_and_nonempty( $table ) ) {
			return false; // Empty table name.
		}

		global $wpdb;

		return $table === $wpdb->get_var(
			$wpdb->prepare(
				str_replace(
					array(
						'{uuid4}',
					),
					array(
						wp_generate_uuid4(),
					),
					'
						SELECT `TABLE_NAME`
						FROM INFORMATION_SCHEMA.TABLES
						WHERE `table_schema` = %s
						AND `table_name` = %s
						LIMIT 1; -- {uuid4}
					'
				),
				DB_NAME,
				$table
			)
		);
	}

	/**
	 * Get the column type for a table column.
	 *
	 * @since 2.13.0
	 *
	 * @param string $table_name The table name.
	 * @param string $column_name The column name.
	 *
	 * @return string|bool The `DATA_TYPE` of the column, or `false` if none.
	 */
	protected function get_column_type( string $table_name, string $column_name ) {

		global $wpdb;

		$data_type = $wpdb->get_var(
			$wpdb->prepare(
				'
					SELECT DATA_TYPE
					FROM INFORMATION_SCHEMA.COLUMNS
					WHERE `table_name` = %s
					AND `column_name` = %s
					AND `table_schema` = %s
				',
				$table_name,
				$column_name,
				DB_NAME
			)
		);

		return is_string( $data_type )
			? $data_type
			: false;
	}
}
