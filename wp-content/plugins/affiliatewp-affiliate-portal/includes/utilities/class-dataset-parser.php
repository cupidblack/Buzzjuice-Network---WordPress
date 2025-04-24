<?php
/**
 * Reporting: Dataset Parser
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Utilities;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Dataset_Helper
 *
 *
 * @since
 * @package
 */
class Dataset_Parser {

	/**
	 * Date format for backfilling dates.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $backfill_date_format;

	/**
	 * Date difference (in seconds).
	 *
	 * @since 1.0.0
	 * @var   false|int
	 */
	private $date_diff;

	/**
	 * MYSQL-specific Date format.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $mysql_date_format;

	/**
	 * Date to filter from.
	 *
	 * @since 1.0.0
	 * @var false|int
	 */
	private $date_from;

	/**
	 * Date to filter to.
	 *
	 * @since 1.0.0
	 * @var   false|int
	 */
	private $date_to;

	/**
	 * Date query specifying start and end dates to filter for.
	 *
	 * @since 1.0.0
	 * @var   string[]
	 */
	private $date_query;

	/**
	 * Number of seconds to increment based on the difference in seconds.
	 *
	 * @since 1.0.0
	 * @var   float|int
	 */
	private $increment;

	/**
	 * Number of items (points on the chart) to iterate data sets for.
	 *
	 * @since 1.0.0
	 * @var   int
	 */
	private $item_count;

	/**
	 * Pretty Date format used by charts.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $pretty_date_format;

	/**
	 * Sets up the parser based on given date filters.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Arguments for filtering graph data.
	 *
	 *     @type string $range    Date range to filter for. Accepts 'this_month', 'last_month', 'today',
	 *                            'yesterday', 'this_week', 'last_week', 'this_quarter', 'last_quarter',
	 *                            'this_year', and 'last_year'. Default 'this_month'.
	 *     @type string $year     Beginning year to filter for. The year for the 'date_from' timestamp
	 *                            is used as a fallback, and the current year the fallback after that.
	 *     @type string $year_end Ending year to filter for. The year for the 'date_to' timestamp
	 *                            is used as a fallback, and the current year the fallback after that.
	 *     @type string $m_start  Beginning month to filter for. The month for the 'date_from' timestamp
	 *                            is used as a fallback, and the current month the fallback after that.
	 *     @type string $m_end    Ending month to filter for. The month for the 'date_to' timestamp
	 *                            is used as a fallback, and the current month the fallback after that.
	 *     @type string $day      Beginning day to filter for. The day for the 'date_from' timestamp
	 *                            is used as a fallback, and the current day the fallback after that.
	 *     @type string $day_end  Ending day to filter for. The day for the 'date_to' timestamp
	 *                            is used as a fallback, and the current day the fallback after that.
	 * }
	 */
	public function __construct( $args ) {
		$report_dates = affwp_get_report_dates( $args );

		$this->date_query = array(
			'start' => $report_dates['year'] . '-' . $report_dates['m_start'] . '-' . $report_dates['day'],
			'end'   => $report_dates['year_end'] . '-' . $report_dates['m_end'] . '-' . $report_dates['day_end'],
		);

		$this->date_to   = strtotime( $this->date_query['start'] );
		$this->date_from = strtotime( $this->date_query['end'] );
		$this->date_diff = $this->date_from - $this->date_to;


		switch ( true ) {
			// If the query is more than a month, query by month/year.
			case $this->date_diff > MONTH_IN_SECONDS:
				$this->mysql_date_format    = '%M/%Y';
				$this->backfill_date_format = 'F/Y';
				$this->pretty_date_format   = 'F/Y';
				$this->increment            = MONTH_IN_SECONDS;
				$this->item_count           = $report_dates['m_end'] - $report_dates['m_start'] + 1;
				break;
			// Otherwise, if the query is more than a day, query by day/month.
			case $this->date_diff > DAY_IN_SECONDS:
				$this->mysql_date_format    = '%d/%M';
				$this->backfill_date_format = 'd/F';
				$this->pretty_date_format   = 'd/F';
				$this->increment            = DAY_IN_SECONDS;
				$this->item_count           = (int) floor( $this->date_diff / $this->increment ) + 1;
				break;
			// If this is less than a day, break the query down into hours.
			default:
				$this->mysql_date_format    = '%H';
				$this->backfill_date_format = 'H';
				$this->pretty_date_format   = 'g:i:a';
				$this->increment            = HOUR_IN_SECONDS;
				$this->item_count           = 24;
		}
	}

	/**
	 * Back-fills data for points where there is no data so the consumer gets a complete dataset.
	 *
	 * @since 1.0.0
	 *
	 * @param object[] $data {
	 *     Array of data objects to (maybe) backfill.
	 *
	 *     @type string    $date           Date string for the data item.
	 *     @type string    $formatted_date Formatted date for the item.
	 *     @type int|float $amount_sum     Amount sum for the given parameters of the query.
	 * }
	 * @return object[] (Maybe) backfilled array of data objects.
	 */
	public function backfill_data( $data ) {

		$result = array();

		// Backfill dates that were not present in the database.
		for ( $i = 0; $i < $this->item_count; $i++ ) {
			if ( MONTH_IN_SECONDS === $this->increment ) {
				$timestamp = strtotime( "+{$i} month", $this->date_to );
			} else {
				$timestamp = $this->date_to + $this->increment * $i;
			}
			$existing_item = wp_list_filter( $data, array( 'formatted_date' => date( $this->backfill_date_format, $timestamp ) ) );
			$pretty_date   = date( $this->pretty_date_format, $timestamp );

			// Retrieve the date if it is already in the array
			if ( ! empty( $existing_item ) ) {

				// Get the item key from the existing item
				$key = array_keys( $existing_item );
				$key = array_pop( $key );

				// Unset existing item so we don't search for it again.
				unset( $data[ $key ] );

				$item = $existing_item[$key];

				// Transform the formatted date to the pretty date format.
				$item->formatted_date = $pretty_date;

				// Append item to results
				$result[] = $item;

				// Otherwise maybe backfill a new one
			} else {

				// Ignore if this is in the future.
				if ( date( 'U', $timestamp ) > date( 'U' ) ) {
					continue;
				}

				// If not, backfill a zero-value
				$item                 = new \stdClass();
				$item->date           = date( 'Y-m-d H:i:s', $timestamp );
				$item->formatted_date = $pretty_date;
				$item->data           = 0;


				$result[] = $item;
			}
		}

		return $result;
	}

	/**
	 * Magic getter for private properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Property key.
	 * @return mixed|null Property value if set, otherwise null.
	 */
	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		}

		return null;
	}

}