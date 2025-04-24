<?php
/**
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH\AutomaticRoleChanger\Classes
 */

if ( ! defined( 'YITH_WCARC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Premium Functions for admin.
 *
 * @class      YITH_Role_Changer_Admin_Premium
 * @since      Version 1.0.0
 * @author     YITH <plugins@yithemes.com>
 */

if ( ! class_exists( 'YITH_Role_Changer_Admin_Premium' ) ) {
	/**
	 * Class YITH_Role_Changer_Admin_Premium
	 *
	 */
	class YITH_Role_Changer_Admin_Premium extends YITH_Role_Changer_Admin {
		/**
		 * Construct
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			$this->show_premium_landing = false;

			parent::__construct();

			// Premium content for add-rule.php.
			add_action(
				'ywarc_before_specific_product_block',
				array( $this, 'add_rule_content_before_specific_product' ),
				10,
				3
			);
			add_action(
				'ywarc_after_specific_product_block',
				array( $this, 'add_rule_content_after_specific_product' ),
				10,
				3
			);

			// Select2 taxonomy searchers.
			add_action( 'wp_ajax_ywarc_category_search', array( $this, 'category_search' ) );
			add_action( 'wp_ajax_ywarc_tag_search', array( $this, 'tag_search' ) );

			// Alter the rule options for Premium.
			add_filter( 'ywarc_save_rule_array', array( $this, 'save_rule_array' ) );
		}

		/**
		 * Define plugin meta and slug
		 *
		 * @param mixed $new_row_meta_args New Row Meta Args.
		 * @param mixed $plugin_meta       Plugin current meta.
		 * @param mixed $plugin_file       Plugin file.
		 * @param mixed $plugin_data       Plugin data.
		 * @param mixed $status            CUrrent Status.
		 * @param mixed $init_file         File of the plugin's init.
		 *
		 * @return mixed
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCARC_INIT' ) {
			$new_row_meta_args = parent::plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file );

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Add Action Links to Plugin Management.
		 *
		 * @param mixed $links Links to be added.
		 *
		 * @return mixed
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->panel_page, true, YITH_WCARC_SLUG );

			return $links;
		}

		/** Include template of rules-tab. */
		public function rules_tab() {
			// No need to verify nonce here.
			if ( isset( $_GET['page'] ) && 'yith_wcarc_panel' === $_GET['page'] //phpcs:ignore WordPress.Security.NonceVerification
			     && file_exists( YITH_WCARC_TEMPLATE_PATH . '/admin/rules-tab.php' ) ) {
				include_once YITH_WCARC_TEMPLATE_PATH . '/admin/rules-tab-premium.php';
			}
		}

		/**
		 * Add Rule Content before a certain Product.
		 *
		 * @param mixed $new_rule New rule to append.
		 * @param mixed $rule     Existing rule.
		 * @param mixed $rule_id  Current Rule ID.
		 *
		 * @return void
		 */
		public function add_rule_content_before_specific_product( $new_rule, $rule, $rule_id ) {
			?>
			<div class="rule_radio_group block radio_group">
				<p>
					<b>
						<?php
						echo esc_html_x(
							'When:',
							'As used in the following sentence: "The user will adquire the role WHEN": ',
							'yith-automatic-role-changer-for-woocommerce'
						);
						?>
					</b>
					<span class="ywarc_required_field"></span>
				</p>
				<p>
					<label for="ywarc_rule_radio_product[<?php echo esc_attr( $rule_id ); ?>]">
						<input id="ywarc_rule_radio_product[<?php echo esc_attr( $rule_id ); ?>]" class="ywarc_rule_radio_button"
							   name="ywarc_rule_radio[<?php echo esc_attr( $rule_id ); ?>]" type="radio" value="product"
							<?php
							if ( ! $new_rule ) {
								$radio_group = $rule['radio_group'];
								echo checked( $radio_group, esc_attr( 'product' ), false );
							}
							?>
						>
						<?php
						esc_html_e(
							'User purchases a specific product',
							'yith-automatic-role-changer-for-woocommerce'
						);
						?>
					</label>
				</p>
				<p>
					<label for="ywarc_rule_radio_range[<?php echo esc_attr( $rule_id ); ?>]">
						<input id="ywarc_rule_radio_range[<?php echo esc_attr( $rule_id ); ?>]" class="ywarc_rule_radio_button"
							   name="ywarc_rule_radio[<?php echo esc_attr( $rule_id ); ?>]" type="radio" value="range"
							<?php
							if ( ! $new_rule ) {
								echo checked( $radio_group, esc_attr( 'range' ), false );
							}
							?>
						>
						<?php
						esc_html_e(
							'Order total is within the following price range',
							'yith-automatic-role-changer-for-woocommerce'
						);
						?>
					</label>
				</p>
				<p>
					<label for="ywarc_rule_radio_overall_range[<?php echo esc_attr( $rule_id ); ?>]">
						<input id="ywarc_rule_radio_overall_range[<?php echo esc_attr( $rule_id ); ?>]" class="ywarc_rule_radio_button"
							   name="ywarc_rule_radio[<?php echo esc_attr( $rule_id ); ?>]" type="radio" value="overall"
							<?php
							if ( ! $new_rule ) {
								echo checked( $radio_group, esc_attr( 'overall' ), false );
							}
							?>
						>
						<?php
						esc_html_e(
							"Customer's total spend falls within the following price range",
							'yith-automatic-role-changer-for-woocommerce'
						);
						?>
					</label>
				</p>
				<p>
					<label for="ywarc_rule_radio_taxonomy[<?php echo esc_attr( $rule_id ); ?>]">
						<input id="ywarc_rule_radio_taxonomy[<?php echo esc_attr( $rule_id ); ?>]" class="ywarc_rule_radio_button"
							   name="ywarc_rule_radio[<?php echo esc_attr( $rule_id ); ?>]" type="radio" value="taxonomy"
							<?php
							if ( ! $new_rule ) {
								echo checked( $radio_group, esc_attr( 'taxonomy' ), false );
							}
							?>
						>
						<?php
						esc_html_e(
							'User purchases products from specific categories or tags',
							'yith-automatic-role-changer-for-woocommerce'
						);
						?>
					</label>
				</p>
			</div>
			<?php
		}

		/**
		 * Add_rule_content_after_specific_product
		 *
		 * @param mixed $new_rule New rule to be added.
		 * @param mixed $rule     Existing rule.
		 * @param mixed $rule_id  Current rule ID.
		 *
		 * @return void
		 */
		public function add_rule_content_after_specific_product( $new_rule, $rule, $rule_id ) {
			?>

			<div class="specific_range_block block">
				<p>
					<b><?php esc_html_e( 'Select a price range: ', 'yith-automatic-role-changer-for-woocommerce' ); ?></b>
					<span class="ywarc_required_field ywarc_optional">(<?php esc_html_e( 'Fill at least one', 'yith-automatic-role-changer-for-woocommerce' ); ?>)</span>
				</p>
				<p>
					<span>
						<b><?php echo esc_html_x( 'From: ', 'start date', 'yith-automatic-role-changer-for-woocommerce' ); ?></b>
					</span>
					<?php
					$price_range_from = '';
					if ( ! $new_rule && ! empty( $rule['price_range_from'] ) ) {
						$price_range_from = $rule['price_range_from'];
					}
					?>
					<input class="wc_input_price range" type="text" placeholder="<?php esc_html_e( 'Amount&hellip;', 'yith-automatic-role-changer-for-woocommerce' ); ?>" name="price_range_from" maxlength="7" value="<?php echo esc_attr( $price_range_from ); ?>" />
					<span>
						<b><?php echo esc_html_x( 'To: ', 'end date', 'yith-automatic-role-changer-for-woocommerce' ); ?></b>
					</span>
					<?php
					$price_range_to = '';
					if ( ! $new_rule && ! empty( $rule['price_range_to'] ) ) {
						$price_range_to = $rule['price_range_to'];
					}
					?>
					<input class="wc_input_price range" type="text" placeholder="<?php esc_html_e( 'Amount&hellip;', 'yith-automatic-role-changer-for-woocommerce' ); ?>" name="price_range_to" maxlength="7" value="<?php echo esc_attr( $price_range_to ); ?>" />
					<span class="range ywarc_warning"><?php esc_html_e( '"To" field must be greather than "From" field', 'yith-automatic-role-changer-for-woocommerce' ); ?></span>
				</p>
			</div>

			<div class="specific_taxonomy_block block radio_group">
				<p>
					<b><?php esc_html_e( 'Select a taxonomy: ', 'yith-automatic-role-changer-for-woocommerce' ); ?></b>
					<span class="ywarc_required_field"></span>
				</p>
				<p>
					<label for="ywarc_tax_radio_cat[<?php echo esc_attr( $rule_id ); ?>]">
						<input id="ywarc_tax_radio_cat[<?php echo esc_attr( $rule_id ); ?>]" class="ywarc_tax_radio_button"
							   name="ywarc_tax_radio[<?php echo esc_attr( $rule_id ); ?>]" type="radio" value="category"
							<?php
							$tax_radio_group = ! empty( $rule['tax_radio_group'] ) ? $rule['tax_radio_group'] : 'category';

							if ( ! $new_rule ) {
								echo checked( $tax_radio_group, esc_attr( 'category' ), false );
							}
							?>
						/>
						<?php
						esc_html_e( 'Category', 'yith-automatic-role-changer-for-woocommerce' );
						?>
					</label>
				</p>
				<p>
					<label for="ywarc_tax_radio_tag[<?php echo esc_attr( $rule_id ); ?>]">
						<input id="ywarc_tax_radio_tag[<?php echo esc_attr( $rule_id ); ?>]" class="ywarc_tax_radio_button"
							   name="ywarc_tax_radio[<?php echo esc_attr( $rule_id ); ?>]" type="radio" value="tag"
							<?php
							if ( ! $new_rule ) {
								echo checked( $tax_radio_group, esc_attr( 'tag' ), false );
							}
							?>
						/>
						<?php
						esc_html_e( 'Tag', 'yith-automatic-role-changer-for-woocommerce' );
						?>
					</label>
				</p>
				<div class="category_search_block block">
					<?php
					$data_selected = array();
					if ( ! $new_rule && ! empty( $rule['categories_selected'] ) ) {
						$categories = is_array( $rule['categories_selected'] ) ? $rule['categories_selected'] : explode( ',', $rule['categories_selected'] );
						if ( $categories ) {
							foreach ( $categories as $category_id ) {
								$term                          = get_term_by( 'id', $category_id, 'product_cat', 'ARRAY_A' );
								$data_selected[ $category_id ] = $term['name'];
							}
						}
					}

					$search_cat_array = array(
						'type'             => '',
						'class'            => 'ywarc-category-search',
						'id'               => 'ywarc_category_selector[' . $rule_id . ']',
						'name'             => '',
						'data-placeholder' => esc_attr__( 'Search for a category&hellip;', 'yith-automatic-role-changer-for-woocommerce' ),
						'data-allow_clear' => false,
						'data-selected'    => $data_selected,
						'data-multiple'    => true,
						'data-action'      => '',
						'value'            => empty( $rule['categories_selected'] ) ? '' : $rule['categories_selected'],
						'style'            => '',
					);
					yit_add_select2_fields( $search_cat_array );
					?>
				</div>
				<div class="tag_search_block block">
					<?php
					$data_selected = array();
					if ( ! $new_rule && ! empty( $rule['tags_selected'] ) ) {
						$tags = is_array( $rule['tags_selected'] ) ? $rule['tags_selected'] : explode( ',', $rule['tags_selected'] );
						if ( $tags ) {
							foreach ( $tags as $tag_id ) {
								$term                     = get_term_by( 'id', $tag_id, 'product_tag', 'ARRAY_A' );
								$data_selected[ $tag_id ] = $term['name'];
							}
						}
					}

					$search_tag_array = array(
						'type'             => 'hidden',
						'class'            => 'ywarc-tag-search',
						'id'               => 'ywarc_tag_selector[' . $rule_id . ']',
						'name'             => '',
						'data-placeholder' => esc_attr__( 'Search for a tag&hellip;', 'yith-automatic-role-changer-for-woocommerce' ),
						'data-allow_clear' => false,
						'data-selected'    => $data_selected,
						'data-multiple'    => true,
						'data-action'      => '',
						'value'            => empty( $rule['tags_selected'] ) ? '' : $rule['tags_selected'],
						'style'            => '',
					);
					yit_add_select2_fields( $search_tag_array );
					?>
				</div>
			</div>

			<div class="date_range_block block">
				<p>
					<b><?php esc_html_e( 'Set a date range: ', 'yith-automatic-role-changer-for-woocommerce' ); ?></b>
					<span class="ywarc_optional">
				<?php esc_html_e( '(Optional)', 'yith-automatic-role-changer-for-woocommerce' ); ?>
				</span>
					<?php
					echo wc_help_tip(
						__( 'Note: If you do not either enter any end date or a duration in days, this role will be valid forever', 'yith-automatic-role-changer-for-woocommerce' )
					); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
					?>
				</p>
				<div class="date_ranges_group">
					<p class="form-field sale_price_dates_fields">
					<span>
						<b><?php echo esc_html_x( 'From: ', 'start date', 'yith-automatic-role-changer-for-woocommerce' ); ?></b>
					</span>
						<?php
						$date_from = '';
						if ( ! $new_rule && ! empty( $rule['date_from'] ) ) {
							$date_from = $rule['date_from'];
						}
						?>
						<input type="text" class="sale_price_dates_from range" maxlength="10" placeholder="<?php echo esc_html_x( 'From&hellip;', 'placeholder', 'woocommerce' ); ?> YYYY-MM-DD" value="<?php echo esc_attr( $date_from ); ?>" />
						<span>
						<b><?php echo esc_html_x( 'To: ', 'end date', 'yith-automatic-role-changer-for-woocommerce' ); ?></b>
					</span>
						<?php
						$date_to = '';
						if ( ! $new_rule && ! empty( $rule['date_to'] ) ) {
							$date_to = $rule['date_to'];
						}
						?>
						<input type="text" class="sale_price_dates_to range" maxlength="10" placeholder="<?php echo esc_html_x( 'To&hellip;', 'placeholder', 'woocommerce' ); ?> YYYY-MM-DD" value="<?php echo esc_attr( $date_to ); ?>" />
					</p>
				</div>
			</div>

			<div class="duration_block block">
				<p>
					<b>
						<?php
						esc_html_e(
							'Set a duration for the roles (days): ',
							'yith-automatic-role-changer-for-woocommerce'
						);
						?>
					</b>
					<span class="ywarc_optional">
				<?php
				esc_html_e( '(Optional)', 'yith-automatic-role-changer-for-woocommerce' );
				?>
					</span>
					<?php
					echo wc_help_tip(
						__(
							'Note: This role will last as long as specified in these settings, even though an end date for this rule has been already specified. If the rule has no end date, and you leave this field empty, the role will never be removed.',
							'yith-automatic-role-changer-for-woocommerce'
						)
					); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
					?>
				</p>
				<?php
				$duration = '';
				if ( ! $new_rule && ! empty( $rule['duration'] ) ) {
					$duration = $rule['duration'];
				}
				?>
				<input class="ywarc_duration" type="number" min="0" value="<?php echo $duration; ?>">

			</div>

			<div class="role_filter_selector_block block">
				<p>
					<b>
						<?php
						esc_html_e(
							'Do not apply this rule to users with the following role(s):',
							'yith-automatic-role-changer-for-woocommerce'
						);
						?>
					</b>
					<span class="ywarc_optional">
				<?php
				esc_html_e( '(Optional)', 'yith-automatic-role-changer-for-woocommerce' )
				?>
				</span>
				</p>

				<select multiple class="role_filter_selector">
					<?php
					if ( ! $new_rule && ! empty( $rule['role_filter'] ) ) {
						if ( is_array( $rule['role_filter'] ) ) {
							foreach ( array_reverse( get_editable_roles() ) as $role => $rolename ) :
								?>
								<option
									value="<?php echo esc_attr( $role ); ?>"
									<?php selected( in_array( $role, $rule['role_filter'], true ) ); ?>>
									<?php echo esc_attr( $rolename['name'] ); ?>
								</option>
							<?php
							endforeach;
						}
					} else {
						wp_dropdown_roles();
					}
					?>
				</select>
			</div>

			<?php
		}

		/** Save rule into array */
		public function save_rule_array() {
			//phpcs:disable
			if ( isset( $_REQUEST['yith-ywarc-save_rule-nonce'] ) && ! wp_verify_nonce( wp_unslash( $_REQUEST['yith-ywarc-save_rule-nonce'] ), 'yith-ywarc-save_rule' ) ) {
				return;
			}

			$new_rule_options = array(
				'title'               => isset( $_POST['title'] ) ? wp_unslash( $_POST['title'] ) : '',
				'rule_type'           => isset( $_POST['rule_type'] ) ? wp_unslash( $_POST['rule_type'] ) : 'add',
				'role_selected'       => isset( $_POST['role_selected'] ) ? wp_unslash( $_POST['role_selected'] ) : array(),
				'replace_roles'       => ! empty( $_POST['replace_roles'] ) ? array(
					isset( $_POST['replace_roles'][0][0] ) ? wp_unslash( $_POST['replace_roles'][0][0] ) : '',
					isset( $_POST['replace_roles'][1][0] ) ? wp_unslash( $_POST['replace_roles'][1][0] ) : '',
				) : '',
				'radio_group'         => isset( $_POST['radio_group'] ) ? wp_unslash( $_POST['radio_group'] ) : 'product',
				'product_selected'    => isset( $_POST['product_selected'] ) ? wp_unslash( $_POST['product_selected'] ) : '',
				'price_range_from'    => isset( $_POST['price_range_from'] ) ? wp_unslash( $_POST['price_range_from'] ) : '',
				'price_range_to'      => isset( $_POST['price_range_to'] ) ? wp_unslash( $_POST['price_range_to'] ) : '',
				'tax_radio_group'     => isset( $_POST['tax_radio_group'] ) ? wp_unslash( $_POST['tax_radio_group'] ) : '',
				'categories_selected' => isset( $_POST['categories_selected'] ) ? wp_unslash( $_POST['categories_selected'] ) : '',
				'tags_selected'       => isset( $_POST['tags_selected'] ) ? wp_unslash( $_POST['tags_selected'] ) : '',
				'date_from'           => isset( $_POST['date_from'] ) ? wp_unslash( $_POST['date_from'] ) : '',
				'date_to'             => isset( $_POST['date_to'] ) ? wp_unslash( $_POST['date_to'] ) : '',
				'duration'            => isset( $_POST['duration'] ) ? wp_unslash( $_POST['duration'] ) : '',
				'role_filter'         => ! empty( $_POST['role_filter'] ) ? wp_unslash( $_POST['role_filter'] ) : array(),
			);

			//phpcs:enable
			return $new_rule_options;
		}

		/** Search categories */
		public function category_search() {
			check_ajax_referer( 'search-categories', 'security' );

			ob_start();

			if ( isset( $_GET['term'] ) ) {
				if ( version_compare( WC()->version, '2.7', '<' ) ) {
					$term = (string) wc_clean( stripslashes( sanitize_text_field( wp_unslash( $_GET['term'] ) ) ) );
				} else {
					if ( isset( $_GET['term']['term'] ) ) {
						$term = (string) wc_clean( stripslashes( sanitize_text_field( wp_unslash( $_GET['term']['term'] ) ) ) );
					}
				}
			}

			if ( empty( $term ) ) {
				die();
			}
			global $wpdb;
			$terms = $wpdb->get_results( 'SELECT name, slug, wpt.term_id FROM ' . $wpdb->prefix . 'terms wpt, ' . $wpdb->prefix . 'term_taxonomy wptt WHERE wpt.term_id = wptt.term_id AND wptt.taxonomy = "product_cat" and wpt.name LIKE "%' . $term . '%" ORDER BY name ASC;' ); //phpcs:ignore

			$found_categories = array();

			if ( $terms ) {
				foreach ( $terms as $cat ) {
					$found_categories[ $cat->term_id ] = ( $cat->name ) ? $cat->name : 'ID: ' . $cat->slug;
				}
			}

			$found_categories = apply_filters( 'ywarc_json_search_categories', $found_categories );
			wp_send_json( $found_categories );
		}

		/** Search tags */
		public function tag_search() {
			check_ajax_referer( 'search-tags', 'security' );

			ob_start();
			if ( isset( $_GET['term'] ) ) {
				if ( version_compare( WC()->version, '2.7', '<' ) ) {
					$term = (string) wc_clean( stripslashes( sanitize_text_field( wp_unslash( $_GET['term'] ) ) ) );
				} else {
					if ( isset( $_GET['term']['term'] ) ) {
						$term = (string) wc_clean( stripslashes( sanitize_text_field( wp_unslash( $_GET['term']['term'] ) ) ) );
					}
				}
			}

			if ( empty( $term ) ) {
				die();
			}
			global $wpdb;
			$terms = $wpdb->get_results( 'SELECT name, slug, wpt.term_id FROM ' . $wpdb->prefix . 'terms wpt, ' . $wpdb->prefix . 'term_taxonomy wptt WHERE wpt.term_id = wptt.term_id AND wptt.taxonomy = "product_tag" and wpt.name LIKE "%' . $term . '%" ORDER BY name ASC;' ); //phpcs:ignore

			$found_tags = array();

			if ( $terms ) {
				foreach ( $terms as $tag ) {
					$found_tags[ $tag->term_id ] = ( $tag->name ) ? $tag->name : 'ID: ' . $tag->slug;
				}
			}

			$found_tags = apply_filters( 'ywarc_json_search_tags', $found_tags );
			wp_send_json( $found_tags );
		}

		/**
		 * Load admin JS & CSS.
		 *
		 * @param mixed $hook_suffix Current hook.
		 */
		public function enqueue_scripts( $hook_suffix ) {
			parent::enqueue_scripts( $hook_suffix );
			wp_enqueue_style(
				'ywarc-admin-style-premium',
				YITH_WCARC_ASSETS_URL . '/css/ywarc-admin-premium.css',
				array(),
				YITH_WCARC_VERSION
			);

			// No need to verify nonce here.
			if ( ! isset( $_GET['page'] ) || 'yith_wcarc_panel' !== $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification
				return;
			}
			wp_localize_script(
				'ywarc-admin',
				'localize_js_ywarc_admin',
				array(
					'ajax_url'                        => admin_url( 'admin-ajax.php' ),
					'before_2_7'                      => version_compare( WC()->version, '2.7', '<' ) ? true : false,
					'search_categories_nonce'         => wp_create_nonce( 'search-categories' ),
					'search_tags_nonce'               => wp_create_nonce( 'search-tags' ),
					'empty_name_msg'                  => esc_html__( 'Please, name this rule.', 'yith-automatic-role-changer-for-woocommerce' ),
					'duplicated_name_msg'             => esc_html__( 'This name already exists and is used to identify another rule. Please, try name.', 'yith-automatic-role-changer-for-woocommerce' ),
					'delete_rule_msg'                 => esc_html__( 'Are you sure you want to delete this rule?', 'yith-automatic-role-changer-for-woocommerce' ),
					'delete_all_rules_msg'            => esc_html__( 'Are you sure you want to delete all the rules? This cannot be undone.', 'yith-automatic-role-changer-for-woocommerce' ),
					'force_apply_rules_nonce'         => wp_create_nonce( 'force_apply_rules' ),
					'force_apply_rules_warning'       => esc_html__( 'Are you sure you want to apply this action? This cannot be undone.', 'yith-automatic-role-changer-for-woocommerce' ),
					'force_apply_all_rules'           => esc_html__( 'All the orders with valid rules will be processed and assign/switch user roles automatically.', 'yith-automatic-role-changer-for-woocommerce' ),
					'force_apply_date_range_rules'    => esc_html__( 'All orders with valid rules between {date_from} and {date_to} will be processed and will assign/switch user roles automatically.', 'yith-automatic-role-changer-for-woocommerce' ),
					'force_apply_date_from_rules'     => esc_html__( 'All the orders with valid rules since {date_from} will be processed and will assign/switch user roles automatically.', 'yith-automatic-role-changer-for-woocommerce' ),
					'force_apply_date_to_rules'       => esc_html__( 'All the orders with valid rules until {date_to} will be processed and will assign/switch user roles automatically.', 'yith-automatic-role-changer-for-woocommerce' ),
					'force_apply_rules_dates_warning' => esc_html__( 'Warning: "From" field must not be greater than "To" field', 'yith-automatic-role-changer-for-woocommerce' ),
				)
			);
		}

	}
}
