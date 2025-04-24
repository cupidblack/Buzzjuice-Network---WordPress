<?php

class AffiliateWP_Recurring_Affiliates_Edit {

	public function __construct() {

		if( ! function_exists( 'affwp_get_affiliate_meta' ) ) {
			return;
		}

		add_action( 'affwp_new_affiliate_end', array( $this, 'new_affiliate_settings' ), 20 );
		add_action( 'affwp_edit_affiliate_end', array( $this, 'edit_affiliate_settings' ), 20 );

		add_action( 'affwp_insert_affiliate', array( $this, 'add_affiliate' ), -1 );
		add_action( 'affwp_update_affiliate', array( $this, 'update_affiliate' ), -1 );

		add_filter( 'affwp_new_affiliate_bottom', array( $this, 'recurring_rates_table' ) );
		add_filter( 'affwp_edit_affiliate_bottom', array( $this, 'recurring_rates_table' ) );

	}

	/**
	 * Add per affiliate recurring referral settings to edit affiliate page.
	 *
	 * @since 1.5
	 */
	public function edit_affiliate_settings( $affiliate ) {

		$limit     = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'recurring_referral_limit', true );
		$rate_type = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'recurring_rate_type', true );
		$rate      = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'recurring_rate',      true );
		$rate      = ! empty( $rate ) ? ( ( 'flat' === $rate_type ) ? affwp_format_amount( $rate ) : $rate ) : $rate;
		$disabled  = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'recurring_disabled',  true );
		?>
		<table class="form-table">

			<tbody>

			<tr><th scope="row"><label for="affwp_settings[recurring_referral_header]"><?php _e( 'Recurring Referrals', 'affiliate-wp-recurring-referrals' ); ?></label></th><td><hr></td></tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="recurring_rate"><?php _e( 'Recurring Referral Rate', 'affiliate-wp-recurring-referrals' ); ?></label>
				</th>

				<td>
					<input type="number" class="small-text" name="recurring_rate" id="recurring_rate" value="<?php echo esc_attr( affwp_abs_number_round( $rate ) ); ?>" step="0.01" min="0" max="999999" placeholder="<?php echo esc_attr( affwp_abs_number_round( affiliate_wp()->settings->get( 'recurring_rate', '' ) ) ); ?>"/>
					<p class="description"><?php _e( 'The affiliate\'s recurring referral rate, such as 20 for 20%. If left blank, the default recurring rate will be used.', 'affiliate-wp-recurring-referrals' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="recurring_rate_type"><?php _e( 'Recurring Referral Rate Type', 'affiliate-wp-recurring-referrals' ); ?></label>
				</th>

				<td>
					<select name="recurring_rate_type" id="recurring_rate_type">
						<option value=""><?php _e( 'Site Default', 'affiliate-wp-recurring-referrals' ); ?></option>
						<?php foreach( affwp_get_affiliate_rate_types() as $key => $type ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $rate_type, $key ); ?>><?php echo esc_html( $type ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php _e( 'The affiliate&#8217;s recurring referral rate type.', 'affiliate-wp-recurring-referrals' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="recurring_referral_limit"><?php _e( 'Recurring Referral Limit', 'affiliate-wp-recurring-referrals' ); ?></label>
				</th>

				<td>
					<input type="number" class="small-text" name="recurring_referral_limit" id="recurring_referral_limit" value="<?php echo esc_attr( affwp_abs_number_round( $limit ) ); ?>" step="1" min="0" max="999999" placeholder="<?php echo esc_attr( affwp_abs_number_round( affiliate_wp()->settings->get( 'recurring_referral_limit' ) ) ); ?>"/>
					<p class="description"><?php _e( 'The affiliate\'s recurring referral limit, such as 20 for twenty allowed instances of recurring referrals per parent transaction. If left blank, the global recurring referral limit will be used, if set.', 'affiliate-wp-recurring-referrals' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="recurring_disabled"><?php _e( 'Disable Recurring Referrals', 'affiliate-wp-recurring-referrals' ); ?></label>
				</th>

				<td>
					<input type="checkbox" name="recurring_disabled" id="recurring_disabled" value="1"<?php checked( 1, $disabled ); ?>/>
					<p class="description"><?php _e( 'Disable recurring referrals for this affiliate?', 'affiliate-wp-recurring-referrals' ); ?></p>
				</td>

			</tr>

			</tbody>

		</table>

		<?php
	}

	/**
	 * Add per affiliate recurring referral settings to new affiliate page.
	 *
	 * @since 1.7
	 */
	public function new_affiliate_settings() {

		?>
		<table class="form-table">

			<tbody>

			<tr><th scope="row"><label for="affwp_settings[recurring_referral_header]"><?php _e( 'Recurring Referrals', 'affiliate-wp-recurring-referrals' ); ?></label></th><td><hr></td></tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="recurring_rate"><?php _e( 'Recurring Referral Rate', 'affiliate-wp-recurring-referrals' ); ?></label>
				</th>

				<td>
					<input type="number" class="small-text" name="recurring_rate" id="recurring_rate" value="" step="0.01" min="0" max="999999" placeholder="<?php echo esc_attr( affwp_abs_number_round( affiliate_wp()->settings->get( 'recurring_rate', 20 ) ) ); ?>" disabled="disabled" />
					<p class="description"><?php _e( 'The affiliate\'s recurring referral rate, such as 20 for 20%. If left blank, the default recurring rate will be used.', 'affiliate-wp-recurring-referrals' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="recurring_rate_type"><?php _e( 'Recurring Referral Rate Type', 'affiliate-wp-recurring-referrals' ); ?></label>
				</th>

				<td>
					<select name="recurring_rate_type" id="recurring_rate_type" disabled="disabled">
						<option value=""><?php _e( 'Site Default', 'affiliate-wp-recurring-referrals' ); ?></option>
						<?php foreach( affwp_get_affiliate_rate_types() as $key => $type ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $type ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php _e( 'The affiliate&#8217;s recurring referral rate type.', 'affiliate-wp-recurring-referrals' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="recurring_referral_limit"><?php _e( 'Recurring Referral Limit', 'affiliate-wp-recurring-referrals' ); ?></label>
				</th>

				<td>
					<input type="number" class="small-text" name="recurring_referral_limit" id="recurring_referral_limit" value="" step="1" min="0" max="999999" placeholder="<?php echo esc_attr( affwp_abs_number_round( affiliate_wp()->settings->get( 'recurring_referral_limit', 0 ) ) ); ?>" disabled="disabled"/>
					<p class="description"><?php _e( 'The affiliate\'s recurring referral limit, such as 20 for twenty allowed instances of recurring referrals per parent transaction. If left blank, the global recurring referral limit will be used, if set.', 'affiliate-wp-recurring-referrals' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="recurring_disabled"><?php _e( 'Disable Recurring Referrals', 'affiliate-wp-recurring-referrals' ); ?></label>
				</th>

				<td>
					<input type="checkbox" name="recurring_disabled" id="recurring_disabled" value="1" disabled="disabled"/>
					<p class="description"><?php _e( 'Disable recurring referrals for this affiliate?', 'affiliate-wp-recurring-referrals' ); ?></p>
				</td>

			</tr>

			</tbody>

		</table>

		<?php
	}

	/**
	 * Save recurring referral option in the affiliate's user meta table when adding an affiliate.
	 *
	 * @since  1.5
	 */
	public function add_affiliate( $affiliate_id = 0 ) {

		if ( isset( $_POST['recurring_disabled'] ) ) {
			affwp_update_affiliate_meta( $affiliate_id, 'recurring_disabled', 1 );
		} else {
			affwp_delete_affiliate_meta( $affiliate_id, 'recurring_disabled' );
		}

		if ( isset( $_POST['recurring_rate_type'] ) ) {
			affwp_update_affiliate_meta( $affiliate_id, 'recurring_rate_type', sanitize_text_field( $_POST['recurring_rate_type'] ) );
		} else {
			affwp_delete_affiliate_meta( $affiliate_id, 'recurring_rate_type' );
		}

		if ( isset( $_POST['recurring_rate'] ) ) {
			affwp_update_affiliate_meta( $affiliate_id, 'recurring_rate', sanitize_text_field( $_POST['recurring_rate'] ) );
		} else {
			affwp_delete_affiliate_meta( $affiliate_id, 'recurring_rate' );
		}

		if ( isset( $_POST['recurring_referral_limit'] ) ) {
			affwp_update_affiliate_meta( $affiliate_id, 'recurring_referral_limit', absint( $_POST['recurring_referral_limit'] ) );
		} else {
			affwp_delete_affiliate_meta( $affiliate_id, 'recurring_referral_limit' );
		}

		$recurring_product_rates = array();

		if ( ! empty( $_POST['recurring_product_rates'] ) ) {

			if ( ! is_array( $_POST['recurring_product_rates'] ) ) {
				$_POST['recurring_product_rates'] = array();
			}

			foreach ( $_POST['recurring_product_rates'] as $integration_key => $rates_array ) {

				foreach ( $rates_array as $key => $rate ) {

					if ( empty( $rate['products'] ) || empty( $rate['rate'] ) ) {
						// Don't save incomplete rates.
						unset( $rates_array[ $key ] );

					} else {
						// Allow for 0 values.
						$rate_value = affwp_abs_number_round( $rate['rate'] );

						$recurring_product_rates[ $integration_key ][ $key ]['products'] = $rate['products'];
						$recurring_product_rates[ $integration_key ][ $key ]['rate']     = sanitize_text_field( $rate_value );
						$recurring_product_rates[ $integration_key ][ $key ]['type']     = sanitize_text_field( $rate['type'] );

					}

				}

			}

		}

		if ( empty( $recurring_product_rates ) ) {
			affwp_delete_affiliate_meta( $affiliate_id, 'recurring_product_rates' );
		} else {
			affwp_update_affiliate_meta( $affiliate_id, 'recurring_product_rates', $recurring_product_rates );
		}

	}

	/**
	 * Save recurring referral option in the affiliate's user meta table when updating an affiliate.
	 *
	 * @since  1.5
	 */
	public function update_affiliate( $data ) {

		if ( empty( $data['affiliate_id'] ) ) {
			return false;
		}

		if ( ! is_admin() ) {
			return false;
		}

		if ( ! current_user_can( 'manage_affiliates' ) ) {
			wp_die( __( 'You do not have permission to manage affiliates', 'affiliate-wp-recurring-referrals' ), __( 'Error', 'affiliate-wp-recurring-referrals' ), array( 'response' => 403 ) );
		}

		if ( isset( $_POST['recurring_disabled'] ) ) {
			affwp_update_affiliate_meta( $data['affiliate_id'], 'recurring_disabled', 1 );
		} else {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'recurring_disabled' );
		}

		if ( isset( $_POST['recurring_rate_type'] ) ) {
			affwp_update_affiliate_meta( $data['affiliate_id'], 'recurring_rate_type', sanitize_text_field( $_POST['recurring_rate_type'] ) );
		} else {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'recurring_rate_type' );
		}

		if (  ! empty ( $_POST['recurring_rate'] ) ) {
			$type = ( isset( $_POST['recurring_rate_type'] ) ) ? $_POST['recurring_rate_type'] : null;

			$rate = sanitize_text_field( $_POST['recurring_rate'] );
			$rate = ( 'flat' === $type ) ? affwp_format_amount( $rate ) : $rate;

			affwp_update_affiliate_meta( $data['affiliate_id'], 'recurring_rate', $rate );
		} else {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'recurring_rate' );
		}

		if ( ! empty ( $_POST['recurring_referral_limit'] ) ) {
			affwp_update_affiliate_meta( $data['affiliate_id'], 'recurring_referral_limit', absint( $_POST['recurring_referral_limit'] ) );
		} else {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'recurring_referral_limit' );
		}

		$recurring_product_rates = array();

		if ( ! empty( $_POST['recurring_product_rates'] ) ) {

			if ( ! is_array( $_POST['recurring_product_rates'] ) ) {
				$_POST['recurring_product_rates'] = array();
			}

			foreach ( $_POST['recurring_product_rates'] as $integration_key => $rates_array ) {

				foreach ( $rates_array as $key => $rate ) {

					if ( empty( $rate['products'] ) || empty( $rate['rate'] ) ) {
						// Don't save incomplete rates.
						unset( $rates_array[ $key ] );

					} else {
						// Allow for 0 values.
						$rate_value = affwp_abs_number_round( $rate['rate'] );

						$recurring_product_rates[ $integration_key ][ $key ]['products'] = $rate['products'];
						$recurring_product_rates[ $integration_key ][ $key ]['rate']     = sanitize_text_field( $rate_value );
						$recurring_product_rates[ $integration_key ][ $key ]['type']     = sanitize_text_field( $rate['type'] );

					}

				}

			}

		}

		if ( empty( $recurring_product_rates ) ) {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'recurring_product_rates' );
		} else {
			affwp_update_affiliate_meta( $data['affiliate_id'], 'recurring_product_rates', $recurring_product_rates );
		}

	}

	/**
	 * Add the per affiliate recurring product rates table to the new/edit affiliate page.
	 *
	 * @since 1.7
	 */
	public function recurring_rates_table() {

		$affiliate_id = isset( $_GET['affiliate_id'] ) ? $_GET['affiliate_id'] : '';

		$affiliate    = affwp_get_affiliate( absint( $affiliate_id ) );
		$affiliate_id = isset( $affiliate->affiliate_id ) ? $affiliate->affiliate_id : '';

		// Get the affiliate's recurring product rates.
		$rates = affiliate_wp_recurring()->get_affiliate_recurring_product_rates( $affiliate_id );

		if ( ! $rates ) {
			$rates = array();
		}

		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				// remove rate
				$('.affwp_remove_rate').on('click', function(e) {
					e.preventDefault();
					$(this).closest('tr').remove();
				});

				// add new rate
				$('.affwp_new_recurring_rate').on('click', function(e) {

					e.preventDefault();

					var ClosestRatesTable = $(this).closest('.affiliate-wp-recurring-rates');

					// clone the last row of the closest rates table
					var row = ClosestRatesTable.find( 'tbody tr:last' );

					// clone it
					clone = row.clone();

					// count the number of rows
					var count = ClosestRatesTable.find( 'tbody tr' ).length;

					// find and clear all inputs
					clone.find( 'td input' ).val( '' );

					// insert our clone after the last row
					clone.insertAfter( row );

					// empty the <td> that has the cloned select2
					clone.find( 'td:first' ).empty();

					// find the original select2
					var original = row.find('select.arr-select-multiple');

					// clone it
					var cloned = original.clone();

					// insert after last
					clone.find('td:first').append( cloned );

					// reinitialize the select2
					cloned.show().select2();

					var clonedName = cloned.attr('name');
					clonedName = clonedName.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');

					cloned.attr( 'name', clonedName ).attr( 'id', clonedName );

					// replace the name of each input with the count
					clone.find( '.test' ).each(function() {
						var name = $( this ).attr( 'name' );

						name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');

						$( this ).attr( 'name', name ).attr( 'id', name );
					});


				});

				$('select.arr-select-multiple').select2({
					placeholder: "Select a recurring Product",
					allowClear: true
				});

			});
		</script>

		<style type="text/css">
			.select2-container {
				width: 100%;
			}
			.affwp-recurring-product-rates-header {
				font-size: 14px;
			}
			.recurring-product-rates { margin-top: 20px; }
			.affiliate-wp-recurring-rates th { padding-left: 10px; }
			.affwp_remove_rate { margin: 8px 0 0 0; cursor: pointer; width: 10px; height: 10px; display: inline-block; text-indent: -9999px; overflow: hidden; }
			.affwp_remove_rate:active, .affwp_remove_rate:hover { background-position: -10px 0!important }
			.affiliate-wp-recurring-rates.widefat th, .affiliate-wp-recurring-rates.widefat td { overflow: auto; }
			.arr-select-multiple { width: 100%; }
		</style>

		<?php
		$supported_integrations = affiliate_wp_recurring()->per_affiliate_per_product_recurring_rates_supported_integrations();

		$enabled_integrations = affiliate_wp()->integrations->get_enabled_integrations();

		if ( $enabled_integrations ) {
			echo '<p class="affwp-recurring-product-rates-header"><strong>' . __( 'Recurring Product Rates', 'affiliate-wp-recurring-referrals' ) . '</strong></p>';
		}

		// add a table for each integration
		foreach ( $enabled_integrations as $integration_key => $integration ) {

			// make sure we only load a table for our supported integrations
			if ( in_array( $integration_key, $supported_integrations ) ) { ?>

				<div class="recurring-product-rates">
					<?php echo '<h3>' . $integration . '</h3>'; ?>

					<table class="form-table wp-list-table widefat fixed posts affiliate-wp-recurring-rates">
						<thead>
						<tr>
							<th><?php _e( 'Product(s)', 'affiliate-wp-recurring-referrals' ); ?></th>
							<th><?php _e( 'Recurring Rate', 'affiliate-wp-recurring-referrals' ); ?></th>
							<th><?php _e( 'Type', 'affiliate-wp-recurring-referrals' ); ?></th>
							<th style="width:5%;"></th>
						</tr>
						</thead>
						<tbody>

						<?php
						$count =  isset( $rates[$integration_key] ) ? $rates[$integration_key] : array();
						$count = count( $count );

						if ( isset( $rates[$integration_key] ) ) :
							// index the arrays numerically
							$rates[$integration_key] = array_values( $rates[$integration_key] );
							?>

							<?php foreach( $rates[$integration_key] as $key => $rates_array ) :

							$rate    = isset( $rates_array['rate'] ) ? $rates_array['rate'] : '';
							$type    = ! empty( $rates_array['type'] ) ? $rates_array['type'] : 'percentage';

							$products = affiliate_wp_recurring()->get_recurring_products( $integration_key );

							?>

							<tr class="row-<?php echo $key; ?>">
								<td>

									<select id="recurring_product_rates[<?php echo $integration_key;?>][<?php echo $key; ?>]" name="recurring_product_rates[<?php echo $integration_key;?>][<?php echo $key; ?>][products][]" data-placeholder="<?php _e( 'Select Product', 'affiliate-wp-recurring-referrals' ); ?>" multiple class="arr-select-multiple">
										<?php if ( $products ) :

											foreach ( $products as $product_id => $product_name ) {
												$selected = in_array( $product_id, $rates_array['products'] ) ? $product_id : '';
												?>
												<option value="<?php echo absint( $product_id ); ?>" <?php echo selected( $selected, $product_id, false ); ?>><?php echo esc_html( $product_name ); ?></option>

											<?php } ?>

										<?php else : ?>

											<option><?php _e( 'No recurring products found', 'affiliate-wp-recurring-referrals' ); ?></option>

										<?php endif; ?>

									</select>

								</td>
								<td>
									<input class="test" name="recurring_product_rates[<?php echo $integration_key;?>][<?php echo $key; ?>][rate]" type="text" value="<?php echo esc_attr( $rate ); ?>"/>
								</td>
								<td>
									<select class="test" name="recurring_product_rates[<?php echo $integration_key;?>][<?php echo $key; ?>][type]">
										<option value="percentage"<?php selected( 'percentage', $type ); ?>><?php _e( 'Percentage (%)', 'affiliate-wp-recurring-referrals' ); ?></option>
										<option value="flat"<?php selected( 'flat', $type ); ?>><?php printf( __( 'Flat %s', 'affiliate-wp-recurring-referrals' ), affwp_get_currency() ); ?></option>
									</select>
								</td>
								<td>
									<a href="#" class="affwp_remove_rate" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
								</td>

							</tr>

						<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="3"><?php _e( 'No recurring product rates created yet', 'affiliate-wp-recurring-referrals' ); ?></td>
							</tr>
						<?php endif; ?>
						<tr>
							<td>
								<select id="recurring_product_rates[<?php echo $integration_key;?>][<?php echo $count; ?>]" name="recurring_product_rates[<?php echo $integration_key;?>][<?php echo $count; ?>][products][]" data-placeholder="<?php _e( 'Select Product', '' ); ?>" multiple="multiple" class="arr-select-multiple">
									<?php

									$products = affiliate_wp_recurring()->get_recurring_products( $integration_key );

									if ( $products ) :

										foreach ( $products as $product_id => $product_name ) {
											?>
											<option value="<?php echo absint( $product_id ); ?>"><?php echo esc_html( $product_name ); ?></option>

										<?php } ?>

									<?php else : ?>

										<option><?php _e( 'No recurring products found', 'affiliate-wp-recurring-referrals' ); ?></option>

									<?php endif; ?>

								</select>
							</td>
							<td>
								<input name="recurring_product_rates[<?php echo $integration_key; ?>][<?php echo $count; ?>][rate]" type="text" value="" class="test" />
							</td>
							<td>
								<select name="recurring_product_rates[<?php echo $integration_key; ?>][<?php echo $count; ?>][type]" class="test">
									<option value="percentage"><?php _e( 'Percentage (%)', 'affiliate-wp-recurring-referrals' ); ?></option>
									<option value="flat"><?php printf( __( 'Flat %s', 'affiliate-wp-recurring-referrals' ), affwp_get_currency() ); ?></option>
								</select>
							</td>
							<td>
								<a href="#" class="affwp_remove_rate" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
							</td>
						</tr>
						</tbody>
						<tfoot>
						<tr>
							<th colspan="1">
								<button id="affwp_new_recurring_rate<?php echo '_' . $integration_key; ?>" name="affwp_new_recurring_rate" class="button affwp_new_recurring_rate"><?php _e( 'Add New Recurring Product Rate', 'affiliate-wp-recurring-referrals' ); ?></button>
							</th>
							<th colspan="3">

							</th>
						</tr>
						</tfoot>
					</table>
				</div>
			<?php }
		}
		?>

		<?php
	}

}
new AffiliateWP_Recurring_Affiliates_Edit;