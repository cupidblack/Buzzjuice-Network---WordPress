/**
 * Table.
 *
 * Works with pages with tabular data to handle data population, pagination, and filtering.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global table
 *
 */

/**
 * External dependencies
 */
import 'alpinejs';

/**
 * WordPress dependencies
 */
import {hasQueryArg, getQueryArg} from '@wordpress/url';

/**
 * Internal dependencies
 */
import {portalSchemaRows} from "@affiliatewp-portal/sdk";
import {scrollWrapperTo, getContentWrapper} from '@affiliatewp-portal/dom-helpers';
import {getPage, paginateUrl} from '@affiliatewp-portal/url-helpers';

const page = getPage( window.location.href );

/**
 * Table handler for AlpineJS.
 *
 * Works with pages with tabular data to handle data population, pagination, and filtering.
 *
 * @since 1.0.0
 * @access private
 * @global table
 *
 * @returns object The table AlpineJS object.
 */
export default {

	/**
	 * table type.
	 *
	 * Determines what table schema and data should be used. Intended to be extended by
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type string
	 */
	type: '',

	/**
	 * rows.
	 *
	 * Array of objects containing the values of each column.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type Array
	 */
	rows: [],

	/**
	 * pages.
	 *
	 * The number of pages to use for pagination.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type int the number of pages.
	 */
	pages: 1,

	/**
	 * current page.
	 *
	 * The current page for this table.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type int the page number.
	 */
	currentPage: page,

	/**
	 * next page.
	 *
	 * The next page for this table.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type int|undefined The next page, or undefined if there is not a next page.
	 */
	nextPage: page + 1,

	/**
	 * previous page.
	 *
	 * The previous page for this table.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type int|undefined The next page, or undefined if there is not a previous page.
	 */
	previousPage: page >= 1 ? page + 1 : 0,

	/**
	 * Per page.
	 *
	 * The number of results to display per-page.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type int Results per page
	 */
	perPage: 30,

	/**
	 * Order.
	 *
	 * Determines the order in which data should be displayed, and retrieved.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type string|boolean can be "asc" for ascending, "desc" for descending, or false for no sort.
	 */
	order: false,

	/**
	 * order by.
	 *
	 * Determines which column data should be ordered.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type string|boolean can be any valid column from the schema, or false for no specific order.
	 */
	orderby: false,

	/**
	 * Table schema.
	 *
	 * Dictates the column headers, as well as the data that is displayed in the table.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type array list of table headings.
	 */
	schema: [],

	/**
	 * Show Pagination.
	 *
	 * Automatically changes based on if pagination should be displayed.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type boolean true if pagination should be displayed, otherwise false.
	 */
	showPagination: undefined,

	/**
	 * Allow Sorting.
	 *
	 * Set this to false to disable sorting.
	 *
	 * @since 1.0.0
	 * @access public.
	 *
	 * @type boolean true if sorting should be allowed, otherwise false.
	 */
	allowSorting: true,

	/**
	 * Is Loading.
	 *
	 * Remains true until necessary data has been fetched from the server.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @type boolean
	 */
	isLoading: true,

	/**
	 * Set Order.
	 *
	 * Iterates the order and sets the orderby
	 *
	 * @since      1.0.0
	 * @access     public
	 * @param orderby {string} The column ID to set the orderby.
	 *
	 * @return {void}
	 */
	setOrder( orderby ) {
		const orders = [
			false,
			'asc',
			'desc',
		];

		if( orderby === this.orderby ) {
			const currentOrder = orders.findIndex( ( order ) => order === this.order );
			const nextOrder = currentOrder + 1;

			// If the provided order is invalid, reset the cycle.
			if( -1 === currentOrder || nextOrder === orders.length ) {
				this.order = orders[0];
				this.orderby = false;
				// Otherwise, increment to the next item in the cycle.
			}else {
				this.order = orders[nextOrder];
			}

		}else {
			this.orderby = orderby;
			this.order = orders[1];
		}
	},

	/**
	 * Get Sort Order.
	 *
	 * Checks to see if the specified column is currently being sorted. and returns the order.
	 *
	 * @since      1.0.0
	 * @access     public
	 * @param column {string} The column to check.
	 *
	 * @return {string|boolean} The order, or false. Can be "asc" or "desc". False if column is not sorted.
	 */
	getSortOrder( column ) {
		// Check to see if this column is valid, and currently being sorted.
		if( !this.getSchema( column ) || column !== this.orderby ) {
			return false;
		}

		// If the column is sorted, return the order.
		return this.order;
	},

	/**
	 * Get Schema.
	 *
	 * Retrieves the table schema object from the provided column name.
	 *
	 * @since      1.0.0
	 * @access     public
	 * @param column {string} The column to retrieve the schema by.
	 *
	 * @return {object|undefined} The object if found, otherwise undefined.
	 */
	getSchema( column ) {
		return this.schema.find( item => item.id === column );
	},

	/**
	 * Url For Page.
	 *
	 * Provides the URL for the specified page.
	 *
	 * @since      1.0.0
	 * @access     public
	 * @param page {string} The column to set the orderby to.
	 *
	 * @return {string} The pagination URL.
	 */
	urlForPage( page ) {

		const args = { page };

		if( false !== this.order ) {
			args.order = this.order
		}

		if( false !== this.orderby ) {
			args.orderby = this.orderby
		}

		return paginateUrl( window.location.href, args );
	},

	/**
	 * Get Cell.
	 *
	 * Retrieves the data for the row index, and the specified column..
	 *
	 * @since      1.0.0
	 * @access     public
	 * @param index {int} The row index.
	 * @param column {string} The column ID.
	 *
	 * @return {*} The cell value.
	 */
	getCell( index, column ) {
		let cell = '';
		const schemaColumn = this.getSchema( column );
		if( schemaColumn && this.rows[index] ) {
			if ( typeof schemaColumn.cell === 'function' ) {
				cell = schemaColumn.cell( this.rows[index] );
			} else {
				cell = this.rows[index][column];
			}
		}

		return cell;
	},

	/**
	 * Get Page Objects.
	 *
	 * Constructs page objects to display in the pagination.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @return {array} Array of pages to display.
	 */
	getPageObjects() {

		/**
		 * Pages.
		 * An array of page objects.
		 *
		 * @type {[]}
		 */
		let pages = [];

		/**
		 * Max Pages.
		 * The total number of pages to display in pagination.
		 *
		 * @type {number}
		 */
		const maxPages = 20;

		/**
		 * Midpoint
		 * The midpoint of the max pages. Equates to 1/2 of the maximum number of pages.
		 *
		 * @type {number}
		 */
		const midpoint = Math.round( maxPages / 2, 0 );

		/**
		 * Start Page.
		 * Evaluates to the first page that should be displayed in pagination.
		 *
		 * @type {number}
		 */
		let startPage;

		/**
		 * End Page.
		 * Evaluates to the last page that should be displayed in pagination.
		 *
		 * @type {number}
		 */
		let endPage;

		// If we're on an early page, start with page 1
		if ( this.currentPage - midpoint <= 0 ) {

			// Set the starting page to 1.
			startPage = 1;

			// Set the end page to either the number of pages, or if there are a lot of pages, use the max page amount.
			endPage = this.pages >= maxPages ? maxPages : this.pages;

			// If we're deep in pagination, start with 10 pages earlier than the current page.
		} else {

			// Offset our start page from our midpoint. This puts the current page in the middle of the pages rendered.
			startPage = this.currentPage - midpoint;

			// Set the end page to the start page plus the total pages. This fills in the pages _after_ the current page.
			endPage = startPage + maxPages;

			// If there are not many pages left to display, just display what is left.
			if ( endPage > this.pages ) {

				// Change the end page to stop at the last possible page in pagination.
				endPage = endPage - ( endPage - this.pages );
			}
		}

		// Construct the pages.
		for ( let page = startPage; page <= endPage; page++ ) {

			// If this is the current page, it should be disabled.
			const disabled = 1 === page ? this.currentPage <= 1 : this.currentPage === page;

			// Append the page to the list of pages.
			pages.push( {
				page,
				disabled
			} );
		}

		return pages;
	},

	/**
	 * Get Pages.
	 *
	 * Retrieves the pages to display in the pagination. Only runs if showPagination is true.
	 * If you need to get pages even if showPagination is false, use getPageObjects
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @return {array} Array of pages to display.
	 */
	getPages() {

		// Bail early if pagination is not used.
		if ( false === this.showPagination ) {
			return [];
		}

		// Return page objects
		return this.getPageObjects();
	},

	/**
	 * Handle Order Event.
	 *
	 * Event handler used for ordering data.
	 *
	 * @since      1.0.0
	 * @access     public
	 * @param e {EventListenerObject} The event
	 * @param orderby {string} The column to order the data by.
	 *
	 * @return {void}
	 */
	handleOrderEvent( e, orderby ) {
		e.preventDefault();
		if( true === this.allowSorting ) {
			scrollWrapperTo( getContentWrapper().scrollleft, 0 );
			this.setOrder( orderby );

			this.fetchPage( 1 );
		}
	},

	/**
	 * Handle Page Event.
	 *
	 * Event handler used for paginating through data.
	 *
	 * @since      1.0.0
	 * @access     public
	 * @param e {EventListenerObject} The event
	 * @param page {int} The page number to set
	 *
	 * @return {void}
	 */
	handlePageEvent( e, page ) {
		e.preventDefault();

		if( this.currentPage === page ) {
			return;
		}

		scrollWrapperTo( getContentWrapper().scrollleft, 0 );

		this.fetchPage( page );
	},

	/**
	 * Fetch Page.
	 *
	 * Fetches the data for the specified page.
	 *
	 * @since      1.0.0
	 * @access     public
	 * @param page        {int} The page number to set
	 * @param skipHistory {boolean} Set to true to prevent this update from being pushed to history.
	 *
	 * @return {Promise} The page data.
	 */
	fetchPage( page, skipHistory = false ) {
		this.isLoading = true;
		this.currentPage = page;

		this.nextPage = page + 1;
		this.previousPage = page - 1;
		const args = {
			page,
			number: this.perPage
		};

		args.number = this.perPage;

		if( false !== this.order ) {
			args.order = this.order;
		}

		if( false !== this.orderby ) {
			args.orderby = this.orderby;
		}

		return new Promise( async( res, rej ) => {
			const data = await portalSchemaRows( this.type, args );

			this.rows = data.rows;
			this.pages = data.pages;

			// If the pagination hasn't been explicitly set, dynamically display it if necessary.
			if ( undefined === this.showPagination ) {
				this.showPagination = data.pages > 1;
			}


			this.currentPage = page;
			this.previousPage = this.previousPage > 0 ? this.previousPage : page;
			this.nextPage = this.nextPage > this.pages ? page : this.nextPage;
			this.isLoading = false;

			this.updateUrl( args, skipHistory );

			res( data );
		} )
	},

	/**
	 * Update URL
	 *
	 * Updates the URL and adds it to the browser's history.
	 *
	 * @since      1.0.0
	 * @access     public
	 * @param args        {object}  Arguments to append to the URL
	 * @param skipHistory {boolean} Set to true to prevent this update from being pushed to history.
	 *
	 * @return {void}
	 */
	updateUrl( args, skipHistory = false ) {

		// Remove the number from the args. This prevents an un-necessary query param from being added to the URL.
		if ( args.number ) {
			delete args.number;
		}

		// Only push state if history isn't skipped, and the page has pagination.
		// If the page does not have pagination, there's no reason to push history since the app doesn't handle routes.
		if ( false === skipHistory && false !== this.showPagination && undefined !== this.showPagination ) {
			window.history.pushState( undefined, document.title, paginateUrl( window.location.href, args ) );
		}
	},

	/**
	 * Setup Columns.
	 *
	 * Sets up columns to use in this table.
	 *
	 * @since 1.0.4
	 * @access public
	 *
	 * @return void
	 */
	setupColumns() {
		this.schema = this.schema;
	},

	/**
	 * Init.
	 *
	 * Initializes the AlpineJS instance.
	 *
	 * @since      1.0.0
	 * @access     public
	 *
	 * @return void
	 */
	async init() {
		const currentPage = getPage( window.location.href )

		if ( false === this.order && hasQueryArg( window.location.href, 'order' ) ) {
			this.order = getQueryArg( window.location.href, 'order' )
		}

		if ( false === this.orderby && hasQueryArg( window.location.href, 'orderby' ) ) {
			this.orderby = getQueryArg( window.location.href, 'orderby' )
		}

		this.setupColumns();
		this.fetchPage( currentPage, true );

		this.isLoading = false;

		window.onpopstate = function ( e ) {
			this.init()
		}.bind( this );

		window.onpushstate = function ( e ) {
			this.init()
		}.bind( this );

	}
}