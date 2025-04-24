<?php
/**
 * Class Affiliate_WP_Lifetime_Commissions_DB
 *
 * @since  1.4.1
 */
class Affiliate_WP_Lifetime_Commissions_DB extends Affiliate_WP_DB {

	/**
	 * Cache group for queries.
	 *
	 * @internal DO NOT change. This is used externally both as a cache group and shortcut
	 *           for accessing db class instances via affiliate_wp()->{$cache_group}->*.
	 *
	 * @access public
	 * @since  1.4.1
	 * @var    string
	 */
	public $cache_group = 'lifetime_customers';

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.4.1
	 */
	public function __construct() {

		global $wpdb;

		if ( defined( 'AFFILIATE_WP_NETWORK_WIDE' ) && AFFILIATE_WP_NETWORK_WIDE ) {

			// Allows a single lifetime customers table for the whole network.
			$this->table_name = 'affiliate_wp_lifetime_customers';

		} else {

			$this->table_name = $wpdb->prefix . 'affiliate_wp_lifetime_customers';

		}

		$this->primary_key = 'lifetime_customer_id';
		$this->version     = '1.0';
	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   1.4.1
	 */
	public function get_columns() {

		return array(
			'lifetime_customer_id' => '%d',
			'affwp_customer_id'    => '%d',
			'affiliate_id'         => '%d',
			'date_created'         => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   1.4.1
	 */
	public function get_column_defaults() {

		return array(
			'affwp_customer_id' => 0,
			'affiliate_id'      => 0,
			'date_created'      => gmdate( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Deletes a lifetime record, and related customer meta from the database.
	 *
	 * Please note: successfully deleting a record flushes the cache.
	 *
	 * @param int|string $row_id Row ID.
	 * @return bool False if the record could not be deleted, true otherwise.
	 */
	public function delete( $row_id = 0, $type = '' ) {
		$lifetime_customer = $this->get( $row_id );

		$deleted = parent::delete( $row_id, 'lifetime_customer' );

		if ( true === $deleted ) {
			// Delete all previous emails from customer meta, as well.
			affiliate_wp()->customer_meta->delete_meta( $lifetime_customer->affwp_customer_id, 'affwp_lc_customer_email' );
		}

		return $deleted;
	}

	/**
	 * Add a new lifetime customer.
	 *
	 * @access  public
	 * @since   1.4.1
	 *
	 * @param array $data {
	 *     Optional. Array of arguments for adding a new lifetime customer. Default empty
	 *     array.
	 *
	 *     @type int $affwp_customer_id ID of the customer. Default 0.
	 *     @type int $affiliate         ID of the affiliate that referred this customer. Default 0.
	 * }
	 * @return int|false Lifetime customer ID if successfully added, false otherwise.
	 */
	public function add( $data = array() ) {

		$defaults = array(
			'affwp_customer_id' => 0,
			'affiliate_id'      => 0,
			'date_created'      => gmdate( 'Y-m-d H:i:s' ),
		);

		$args = wp_parse_args( $data, $defaults );
		$add  = $this->insert( $args, 'lifetime_customer' );

		if ( $add ) {
			// Clean the query cache.
			wp_cache_set( 'last_changed', microtime(), $this->cache_group );

			/**
			 * Fires immediately after a lifetime customer has been added.
			 *
			 * @since 1.4.1
			 *
			 * @param int ID of the newly-added lifetime customer.
			 */
			do_action( 'affwp_lifetime_customer_insert_lifetime_customer', $add );

			return $add;
		}

		return false;

	}

	/**
	 * Retrieve lifetime customers for an affiliate from the database.
	 *
	 * @access public
	 * @since  1.4.1
	 * @param  array $args {
	 *    Optional. Array of arguments for retrieving lifetime customers for an affiliate. Default empty array.
	 *
	 *    @type int $affwp_customer_id ID of the customer. Default 0.
	 *    @type int $affwp_customer_id ID of the customer. Default 0.
	 * }
	 * @param  bool  $count  Return only the total number of results found (optional).
	 * @return array
	 */
	public function get_lifetime_customers( $args = array(), $count = false ) {

		$defaults = array(
			'number'            => 20,
			'offset'            => 0,
			'affwp_customer_id' => 0,
			'affiliate_id'      => 0,
			'order'             => 'DESC',
			'orderby'           => $this->primary_key,
			'date_created'      => '',
			'fields'            => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = $join = '';

		// Lifetime customers for specific affiliates.
		if ( ! empty( $args['affiliate_id'] ) ) {

			if ( is_array( $args['affiliate_id'] ) ) {
				$affiliate_ids = implode( ',', array_map( 'intval', $args['affiliate_id'] ) );
			} else {
				$affiliate_ids = intval( $args['affiliate_id'] );
			}

			$where .= "WHERE `affiliate_id` IN( {$affiliate_ids} ) ";

		}

		// lifetime customers for a date or date range.
		if ( ! empty( $args['date_created'] ) ) {
			$where = $this->prepare_date_query( $where, $args['date_created'] );
		}

		// order.
		if ( 'ASC' === strtoupper( $args['order'] ) ) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		// orderby.
		$orderby = array_key_exists( $args['orderby'], $this->get_columns() ) ? $args['orderby'] : $this->primary_key;

		// Overload args values for the benefit of the cache.
		$args['orderby'] = $orderby;
		$args['order']   = $order;

		// Fields.
		$callback = '';

		if ( 'ids' === $args['fields'] ) {
			$fields   = "$this->primary_key";
			$callback = 'intval';
		} else {
			$fields = $this->parse_fields( $args['fields'] );
		}

		$key = ( true === $count ) ? md5( 'affwp_lifetime_customer_count' . serialize( $args ) ) : md5( 'affwp_lifetime_customers_' . serialize( $args ) );

		$last_changed = wp_cache_get( 'last_changed', $this->cache_group );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, $this->cache_group );
		}

		$cache_key = "{$key}:{$last_changed}";

		$results = wp_cache_get( $cache_key, $this->cache_group );

		if ( false === $results ) {

			$clauses = compact( 'fields', 'join', 'where', 'orderby', 'order', 'count' );

			$results = $this->get_results( $clauses, $args, $callback );
		}

		wp_cache_add( $cache_key, $results, $this->cache_group, HOUR_IN_SECONDS );

		return $results;

	}

	/**
	 * Create the lifetime customers table
	 *
	 * @access  public
	 * @since   1.4.1
	 */
	public function create_table() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
			lifetime_customer_id bigint(20) NOT NULL AUTO_INCREMENT,
			affwp_customer_id bigint(20) NOT NULL,
			affiliate_id bigint(20) NOT NULL,
			date_created datetime NOT NULL,
			PRIMARY KEY  (lifetime_customer_id),
			KEY affwp_customer_id (affwp_customer_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

}
