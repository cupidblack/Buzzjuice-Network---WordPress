/**
 * AffiliateWP Multi-Tier Commissions
 *
 * @since 1.0.0
 */

'use strict';

/* eslint-disable no-console, no-undef */
affiliatewp.attach(
	'mtc',
	/**
	 * MTC Front-end Component.
	 *
	 * @since 1.0.0
	 */
	{
		/**
		 * Initializes the draggable functionality for the network diagram.
		 *
		 * @since 1.0.0
		 */
		initDraggable() {

			const networkDiagram = document.querySelector( '.affwp-network ul' );
			const networkContainer = document.querySelector( '.affwp-network' );

			/**
			 * Used to keep the network diagram centered.
			 *
			 * @since 1.0.0
			 */
			function centerNetworkDiagram() {

				if ( ! networkDiagram ) {
					return; // Can't find network diagram.
				}

				const topAffiliate = networkDiagram.querySelector( 'li:first-child' );

				if ( ! topAffiliate ) {
					return; // Can't find the root affiliate.
				}

				const affiliateOffset = topAffiliate.offsetLeft + (topAffiliate.offsetWidth / 2);
				const containerCenter = networkContainer.offsetWidth / 2;

				networkContainer.scrollLeft = affiliateOffset - containerCenter;
			}

			centerNetworkDiagram();

			const scrollableDiv = document.querySelector( '.affwp-network' );

			if ( ! scrollableDiv ) {
				return; // Can't find network element.
			}

			let isDown = false;
			let startX;
			let startY;
			let scrollLeft;
			let scrollTop;

			scrollableDiv.addEventListener( 'mousedown', ( e ) => {

				isDown = true;
				scrollableDiv.style.cursor = 'grabbing';
				startX = e.pageX - scrollableDiv.offsetLeft;
				startY = e.pageY - scrollableDiv.offsetTop;
				scrollLeft = scrollableDiv.scrollLeft;
				scrollTop = scrollableDiv.scrollTop;
			} );

			scrollableDiv.addEventListener( 'mouseleave', () => {

				isDown = false;
				scrollableDiv.style.cursor = 'grab';
			} );

			scrollableDiv.addEventListener( 'mouseup', () => {

				isDown = false;
				scrollableDiv.style.cursor = 'grab';
			} );

			scrollableDiv.addEventListener( 'mousemove', ( e ) => {

				if ( ! isDown ) {
					return;
				}

				e.preventDefault();

				const x = e.pageX - scrollableDiv.offsetLeft;
				const y = e.pageY - scrollableDiv.offsetTop;

				const walkX = ( x - startX ) * 2;
				const walkY = ( y - startY ) * 2;

				scrollableDiv.scrollLeft = scrollLeft - walkX;
				scrollableDiv.scrollTop = scrollTop - walkY;
			} );
		},
	}
);
