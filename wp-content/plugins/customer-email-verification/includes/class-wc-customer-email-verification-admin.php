<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Customer_Email_Verification_Admin_Pro {
	
	public $my_account_id;
	
	/**
	* Initialize the main plugin function
	*/
	public function __construct() {
		add_action('init', array($this, 'init'));
	}
	
	/**
	* Instance of this class.
	*
	* @var object Class Instance
	*/
	private static $instance;
	
	/**
	* Get the class instance
	*
	* @return woo_customer_email_verification_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init() {
		
		add_action( 'wp_ajax_cev_settings_form_update', array( $this, 'cev_settings_form_update_fun') );
		// Manage user Column
		add_filter( 'manage_users_columns', array( $this, 'add_column_users_list' ), 10, 1 );
		add_filter( 'manage_users_custom_column', array( $this, 'add_details_in_custom_users_list' ), 10, 3 );
		
		//User Email verify or unverify 
		add_action( 'show_user_profile', array( $this, 'show_cev_fields_in_single_user' ) );
		add_action( 'edit_user_profile', array( $this, 'show_cev_fields_in_single_user' ) );

		
		add_action( 'admin_head', array( $this, 'cev_manual_verify_user' ) );
		
		add_filter( 'cev_verification_code_length', array( $this, 'cev_verification_code_length_callback'), 10, 1 );

		/*** Sort and Filter Users ***/
		add_action('restrict_manage_users', array( $this, 'filter_user_by_verified' ));
		add_filter('pre_get_users', array( $this, 'filter_users_by_user_by_verified_section' ));
		
		/*** Bulk actions for Users ***/
		add_filter( 'bulk_actions-users', array( $this, 'add_custom_bulk_actions_for_user' ) );
		add_filter( 'handle_bulk_actions-users', array( $this, 'users_bulk_action_handler' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'user_bulk_action_notices' ) );
		
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if ( 'customer-email-verification-for-woocommerce' == $page ) {
			// Hook for add admin body class in settings page
			add_filter( 'admin_body_class', array( $this, 'cev_post_admin_body_class' ), 100 );
		}
		add_action( 'wp_ajax_cev_manualy_user_verify_in_user_menu', array( $this, 'cev_manualy_user_verify_in_user_menu') );
		//Custom Woocomerce menu
		add_action('admin_menu', array( $this, 'register_woocommerce_menu' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 4);

		add_action( 'wp_ajax_delete_user', array( $this, 'cev_delete_user' ), 4);
		add_action( 'wp_ajax_delete_users', array( $this, 'cev_delete_users' ), 4);
		
	}
	public function cev_delete_user() {
		// Check nonce for security
		check_ajax_referer('delete_user_nonce', 'nonce');
	
		if (isset($_POST['id'])) {
			global $wpdb;
			$id = intval($_POST['id']);
			$table_name = $wpdb->prefix . 'cev_user_log';
			$result = $wpdb->delete($table_name, array('id' => $id), array('%d'));
	
			if (false !== $result) {
				echo json_encode(array('success' => true));
			} else {
				echo json_encode(array('success' => false));
			}
		}
		wp_die(); // Required to terminate immediately and return a proper response
	}
	
	public function cev_delete_users() {
		// Check nonce for security
		check_ajax_referer('delete_user_nonce', 'nonce');
		global $wpdb;
		$table_name = $wpdb->prefix . 'cev_user_log';
		
		// Initialize an array to hold the results
		$results = [
			'success' => 0,
			'failure' => 0,
			'messages' => []
		];
	
		// Check if 'ids' is set and is an array
		if (isset($_POST['ids']) && is_array($_POST['ids'])) {
			// Sanitize and validate input IDs
			$ids = array_map('intval', $_POST['ids']); // Sanitize IDs
			
			foreach ($ids as $id) {
				if ($id <= 0) {
					$results['failure']++;
					$results['messages'][] = "Invalid ID: $id";
					continue; // Skip to the next ID
				}
	
				// Perform the delete operation
				$result = $wpdb->delete($table_name, ['id' => $id], ['%d']);
				if ($result) {
					$results['success']++;
				} else {
					$results['failure']++;
					$results['messages'][] = "Failed to delete ID: $id";
				}
			}
		} else {
			$results['failure']++;
			$results['messages'][] = 'Invalid IDs';
		}
	
		// Return a single JSON response
		echo json_encode($results);
		wp_die(); // Required to terminate immediately and return a proper response
	}
	/*
	* Admin Menu add function
	* WC sub menu
	*/
	public function register_woocommerce_menu() {
		add_submenu_page( 'woocommerce', 'Customer Verification', 'Email Verification', 'manage_woocommerce', 'customer-email-verification-for-woocommerce', array( $this, 'wc_customer_email_verification_page_callback' ) ); 
	}
	
	/*
	* add class in body tag
	*/
	public function cev_post_admin_body_class( $body_class ) {
		$body_class .= ' customer-email-verification-for-woocommerce';
		return $body_class;
	}
	
	/**
	* Load admin styles.
	*/
	public function admin_styles( $hook ) {
		if ( !isset( $_GET['page'] ) || 'customer-email-verification-for-woocommerce' != $_GET['page'] ) {
			return;
		}
	
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
		// Register and enqueue scripts and styles in the correct order
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
		wp_enqueue_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.4' );
		wp_enqueue_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_enqueue_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
	
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), time() );
	
		wp_enqueue_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), time(), true );
	
		wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), time(), true);
		wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css', array(), time());

	
		wp_enqueue_script( 'cev_pro_admin_js', cev_pro()->plugin_dir_url() . 'assets/js/admin.js', array('jquery', 'wp-util', 'datatables-js'), time(), true );   
		
		wp_enqueue_style( 'cev-pro-admin-css', cev_pro()->plugin_dir_url() . 'assets/css/admin.css', array(), time() );
	
		wp_localize_script( 'cev_pro_admin_js', 'cev_pro_admin_js', array() );
		wp_localize_script('cev_pro_admin_js', 'iconUrls', array(
			'verified' => cev_pro()->plugin_dir_url() . 'assets/css/images/checked.png',
			'unverified' => cev_pro()->plugin_dir_url() . 'assets/css/images/cross.png',
		));
		wp_localize_script('cev_pro_admin_js', 'cev_vars', array(
			'delete_user_nonce' => wp_create_nonce('delete_user_nonce'),
			'ajax_url' => admin_url('admin-ajax.php')
		)); 
		wp_enqueue_script( 'media-lib-uploader-js' );
		wp_enqueue_media();
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
	}
	

	/*
	* callback for Customer Email Verification page
	*/
	public function wc_customer_email_verification_page_callback() {
		
		wp_enqueue_script( 'customer_email_verification_table_rows' );
		?>
		<div class="zorem-layout-cev__header">			
			<h1 class="page_heading">
				<a href="javascript:void(0)"><?php esc_html_e( 'Customer Email Verification', 'customer-email-verification' ); ?></a> <span class="dashicons dashicons-arrow-right-alt2"></span> <span class="breadcums_page_heading"><?php esc_html_e( 'Settings', 'customer-email-verification' ); ?></span>
			</h1>
			<!--img class="zorem-layout-cev__header-logo" src="<?php echo esc_url( cev_pro()->plugin_dir_url() ); ?>assets/images/cev-logo.png"-->
		</div>
		<div class="woocommerce cev_admin_layout">
			<div class="cev_admin_content" >
				<?php include 'views/activity_panel.php'; ?>
				<div class="cev_nav_div">
					<?php
						$this->get_html_menu_tab( $this->get_cev_tab_settings_data());	
					?>
					<div class="menu_devider"></div>
					<?php						
						require_once( 'views/admin_options_settings.php' );
						require_once( 'views/cev_users_tab.php');
						require_once( 'views/admin_tools_tab.php' );
					?>
				</div>
			</div>
		</div> 
	<?php
	}

	/*
	* html for menu tab
	*/
	public function get_html_menu_tab( $arrays ) {
		 
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'email-verification';
		
		foreach ( ( array ) $arrays as $id => $array ) {
			
			if ( isset( $array['type'] ) && 'link' == $array['type'] ) {
				?>
				<a class="menu_cev_link" href="<?php esc_html_e( esc_url( $array['link'] ) ); ?>"><?php esc_html_e( $array['title'] ); ?></a>
			<?php 
			} else {
				$checked = ( $tab == $array['data-tab'] ) ? 'checked' : '';
				?>
				<input class="cev_tab_input" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $array['name'] ); ?>" type="radio"  data-tab="<?php esc_html_e( $array['data-tab'] ); ?>" data-label="<?php esc_html_e( $array['data-label'] ); ?>" <?php esc_html_e( $checked ); ?>/>
				<label class="<?php esc_html_e( $array['class'] ); ?>" for="<?php esc_html_e( $id ); ?>"><?php esc_html_e( $array['title'] ); ?></label>
				<?php
			}
		}	
	}
	
	/*
	* html array for menu tab
	*/
	public function get_cev_tab_settings_data() {
		$setting_data = array(
			'setting_tab' => array(
				'title'		=> __( 'Settings', 'customer-email-verification' ),
				'show'      => true,
				'class'     => 'cev_tab_label first_label',
				'data-tab'  => 'email-verification',
				'data-label' => __( 'Email verification', 'customer-email-verification' ),
				'name'  => 'tabs',
			),
			'customize' => array(					
				'title'		=> __( 'Customize', 'customer-email-verification' ),
				'type'		=> 'link',
				'link'		=> admin_url( 'admin.php?page=cev_customizer&preview=email_registration' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'trackship',
				'data-label' => __( 'Customize', 'customer-email-verification' ),
				'name'  => 'tabs',
			),
			'user_tab' => array(
				'title'		=> __( 'Unverified Users', 'customer-email-verification' ),
				'show'      => true,
				'class'     => 'cev_tab_label',
				'data-tab'  => 'unverified-users',
				'data-label' => __( 'Unverified Users', 'customer-email-verification' ),
				'name'  => 'tabs',
			),
			'tool_tab' => array(
				'title'		=> __( 'Tools', 'customer-email-verification' ),
				'show'      => true,
				'class'     => 'cev_tab_label',
				'data-tab'  => 'tools',
				'data-label' => __( 'Tools', 'customer-email-verification' ),
				'name'  => 'tabs',
			),		
		);
		return $setting_data;
	}			
	
	/*
	* get html get_html of fields
	*/	
	public function get_html( $arrays ) { 
		
		$checked = '';
		?>
		<ul class="settings_ul">
			<?php 
			foreach ( ( array ) $arrays as $id => $array ) {
				$disabled='';
				if ( 'cev_verification_code_length' == $id ) {
					if ( 'link' == get_option( 'cev_verification_type') ) {
						$disabled = 'disabled';
					} else {
						$disabled = '';
					}
				}
				if ( 'cev_verification_code_expiration' == $id ) {
					if ( 'link' == get_option( 'cev_verification_type') ) {
						$disabled = 'disabled';
					} else {
						$disabled = '';
					}
				}	
				if ( $array['show'] ) {
					$class = isset( $array['class'] ) ? $array['class'] : '';
					?>
					<li class="<?php esc_html_e( $class ); ?>">
					<?php 
					if ( 'desc' != $array['type'] && 'checkbox' != $array['type'] && 'checkbox_select' != $array['type'] && 'toogel' != $array['type'] && 'separator' != $array['type'] ) { 
						?>
					<label class="settings_label <?php esc_html_e( $disabled ); ?>">
						<?php 
						esc_html_e( $array['title'] );
						if ( isset( $array['tooltip'] ) ) {
							?>
							<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
						<?php } ?>
					</label>
					<?php
					}
					if ( isset( $array['type'] ) && 'dropdown' == $array['type'] ) { 
						$multiple = isset( $array['multiple'] ) ? 'multiple' : '';
						$field_id = isset( $array['multiple'] ) ? $array['multiple'] : $id;
						
						?>
						<fieldset>
							<select class="select select2" id="<?php esc_html_e( $field_id ); ?>" name="<?php esc_html_e( $id ); ?>" <?php esc_html_e( $multiple ); ?> <?php esc_html_e( $disabled ); ?>>  
								<?php 
								foreach ( ( array ) $array['options'] as $key => $val ) {
									$selected = ( get_option( $id, $array['Default'] ) == ( string ) $key ) ? 'selected' : '';										
									?>
									<option value="<?php esc_html_e( $key ); ?>" <?php esc_html_e( $selected ); ?> ><?php esc_html_e( $val ); ?></option>
									<?php 
								} 
								?>
							</select>
						</fieldset>	
						<?php
					} elseif ( isset( $array['type'] ) && 'multiple_select' == $array['type'] ) {
						?>
						<div class="multiple_select_container">	
							<select multiple class="wc-enhanced-select" name="<?php esc_html_e( $id ); ?>[]" id="<?php esc_html_e( $id ); ?>">
							<?php
							foreach ( (array) $array['options'] as $key => $val ) :
								$multi_checkbox_data = get_option( $id );
								$checked = isset( $multi_checkbox_data[$key] ) && 1 == $multi_checkbox_data[$key] ? 'selected' : '' ; 
								?>
								<option value="<?php esc_html_e( $key ); ?>" <?php esc_html_e( $checked ); ?>>
									<?php esc_html_e( $val ); ?>
								</option>
							<?php 
							endforeach;
							?>
							</select>	
						</div>
					<?php
					} elseif ( 'checkbox' == $array['type'] ) { 
						$checked = ( get_option( $id, 1 ) ) ? 'checked' : '';
						$disabled='';
						if ( 'cev_enable_email_verification_free_orders' == $id ) {
							if ( get_option( 'cev_enable_email_verification_checkout' ) ) {
								$disabled = '';
							} else {
								$disabled = 'disabled';
							}
						}
						if ( 'cev_enable_email_verification_cart_page' == $id ) {
							if ( get_option( 'cev_enable_email_verification_checkout' ) ) {
								$disabled = '';
							} else {
								$disabled = 'disabled';
							}
						}
						if ( 'enable_email_otp_for_account' == $id ) {
							if ( get_option( 'cev_enable_login_authentication' ) ) {
								$disabled = '';
							} else {
								$disabled = 'disabled';
							}
						}
						if ( 'enable_email_auth_for_new_device' == $id ) {
							if ( get_option( 'cev_enable_login_authentication' ) ) {
								$disabled = '';
							} else {
								$disabled = 'disabled';
							}
						}
						if ( 'enable_email_auth_for_new_location' == $id ) {
							if ( get_option( 'cev_enable_login_authentication' ) ) {
								$disabled = '';
							} else {
								$disabled = 'disabled';
							}
						}
						?>
						<label class="<?php esc_html_e( $disabled ); ?>" for="<?php esc_html_e( $id ); ?>">
							<input type="hidden" name="<?php esc_html_e( $id ); ?>" value="0"/>
							<input type="checkbox" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $id ); ?>" class="" <?php esc_html_e( $checked ); ?> value="1" <?php esc_html_e( $disabled ); ?>/>
							<span class="label">
								<?php 
								esc_html_e( $array['title'] );
								if ( isset( $array['tooltip'] ) ) {
									?>
									<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
								<?php } ?>
							</span>								
						</label>	
						<?php																						
					} elseif ( 'toogel' == $array['type'] ) { 
						if ( 'cev_enable_email_verification' == $id ) {
							$checked = get_option( 'cev_enable_email_verification', 1 ) ? 'checked' : ''; 
						} elseif ( 'cev_enable_email_verification_checkout' == $id ) {
							$checked = ( get_option( 'cev_enable_email_verification_checkout', 1 ) ) ? 'checked' : ''; 
						} elseif ( 'cev_enable_login_authentication' == $id ) {
							$checked = ( get_option( 'cev_enable_login_authentication', 1 ) ) ? 'checked' : ''; 
						}
						
						?>
						<div class="accordion-toggle">
							
							<input type="hidden" name="<?php esc_html_e( $id ); ?>" value="0"/>
							<input class="tgl tgl-flat-cev" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $id ); ?>" type="checkbox" <?php esc_html_e( $checked ); ?> value="1"/>
							<label class="tgl-btn tgl-panel-label" for="<?php esc_html_e( $id ); ?>"></label>
						
						</div>	
						<label class="settings_label">
							<?php 
								esc_html_e( $array['title'] );
							?>
						</label>	
						<?php																					
					} elseif ( 'checkbox_select' == $array['type'] ) { 
						$checked = ( get_option( $id, 1 ) ) ? 'checked' : '';
						if ( 'enable_email_auth_for_login_time' == $id ) {
							if ( get_option( 'cev_enable_login_authentication' ) ) {
								$disabled = '';
							} else {
								$disabled = 'disabled';
							}
						}
						?>
						<label class="<?php esc_html_e( $disabled ); ?>" for="<?php esc_html_e( $id ); ?>">
							<input type="hidden" name="<?php esc_html_e( $id ); ?>" value="0"/>
							<input type="checkbox" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $id ); ?>" class="" <?php esc_html_e( $checked ); ?> value="1" <?php esc_html_e( $disabled ); ?>/>
							<span class="label <?php esc_html_e( $disabled ); ?>">
								<?php 
								esc_html_e( $array['title'] );
								if ( !empty( $array['select'] ) ) {
									?>
									<select name="<?php esc_html_e( $array['select']['id'] ); ?>" id ="<?php esc_html_e( $array['select']['id'] ); ?>" style="width: auto;" <?php esc_html_e( $disabled ); ?>>
										<?php
										foreach ( $array['select']['options'] as $key => $val ) {
											$selected = ( get_option( $array['select']['id'], '' ) == $key ) ? 'selected' : '';
											?>
											<option value="<?php esc_html_e( $key ); ?>" <?php esc_html_e( $selected ); ?>><?php esc_html_e( $val ); ?></option>	
											<?php	
										}	
										?>
									</select>
								<?php
								}
								if ( isset( $array['tooltip'] ) ) {
									?>
									<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
								<?php } ?>
							</span>								
						</label>	
						<?php																						
					} elseif ( 'multiple_checkbox' == $array['type'] ) { 
						$op = 1;	
						foreach ( ( array ) $array['options'] as $key => $val ) {
																
							$multi_checkbox_data = get_option( $id );
							if ( isset( $multi_checkbox_data[ $key ] ) && 1 == $multi_checkbox_data[ $key ] ) {
								$checked = 'checked';
							} else {
								$checked='';
							}
							?>
							
							<span class="multiple_checkbox">
								<label class="" for="<?php esc_html_e( $key ); ?>">
									<input type="hidden" name="<?php esc_html_e( $id ); ?>[<?php esc_html_e( $key ); ?>]" value="0"/>
									<input type="checkbox" id="<?php esc_html_e( $key ); ?>" name="<?php esc_html_e( $id ); ?>[<?php esc_html_e( $key ); ?>]" class="" <?php esc_html_e( $checked ); ?> value="1"/>
									<span class="multiple_label"><?php esc_html_e( $val ); ?></span>	
									</br>
								</label>																		
							</span>												
						<?php								
						}																
					} elseif ( 'textarea' == $array['type'] ) {
						$placeholder = ( !empty( $array['placeholder'] ) ) ? $array['placeholder'] : '';		
						?>
						
						<fieldset>
							<textarea placeholder="<?php esc_html_e( $placeholder ); ?>" class="input-text regular-input" name="<?php esc_html_e( $id ); ?>" id="<?php esc_html_e( $id ); ?>"><?php esc_html_e( get_option( $id, $array['Default'] ) ); ?></textarea>                                
						</fieldset>
						<span class="" style="font-size: 12px;"><?php esc_html_e( $array['desc_tip'] ); ?></span>
					<?php
					} elseif ( 'tag_block' == $array['type'] ) {
						?>
						<fieldset class="tag_block">
							<code>{customer_email_verification_code}</code><code>{cev_user_verification_link}</code><code>{cev_resend_email_link}</code><code>{cev_display_name}</code><code>{cev_user_login}</code><code>{cev_user_email}</code> 								
						</fieldset>
					<?php
					} elseif ( 'desc' == $array['type'] ) {
						if ( 'login_auth_desc' == $id ) {
							if ( get_option( 'cev_enable_login_authentication' ) ) {
								$disabled = '';
							} else {
								$disabled = 'disabled';
							}
						}
						?>
						<p class="section_desc <?php esc_html_e( $disabled ); ?>" id="<?php esc_html_e( $id ); ?>"><?php esc_html_e( $array['title'] ); ?></p>
					<?php
					} elseif ( 'separator' ==  $array['type']) {
						?>
						<div class="separator"></div>
						<?php
					} else { 
						$placeholder = ( !empty( $array['placeholder'] ) ) ? $array['placeholder'] : '';
						?>
						<fieldset>
							<input class="input-text regular-input " type="text" name="<?php esc_html_e( $id ); ?>" id="<?php esc_html_e( $id ); ?>" style="" value="<?php esc_html_e( get_option( $id, $array['Default'] ) ); ?>" placeholder="<?php esc_html_e( $placeholder ); ?>">
						</fieldset>
					<?php } ?>
					</li>
					<?php
				}
			}
			?>
		</ul>		
	<?php 
	}
	
	/*
	* get settings tab array data
	* return array
	*/
	public function get_cev_settings_data() {	
	
		$page_list = wp_list_pluck( get_pages(), 'post_title', 'ID' );
								
		$form_data = array(			
			'cev_enter_account_after_registration' => array(
				'type'		=> 'checkbox',
				'show' => true,
				'tooltip' 		=> __('Allow your customers to access their account for the first time after registration before they verify the email address', 'customer-email-verification'),
				'title' => __( 'Allow first login after registration without email verification', 'customer-email-verification' ),				
				'Default'   => '',
				'class'     => '',
			),
			'delay_new_account_email_customer' => array(
				'type'		=> 'checkbox',
				'show' => true,
				'tooltip' 		=> __('Delay the wooCommerce new account email and send it to the customer only after successful verification.', 'customer-email-verification'),
				'title' => __( 'Delay the new account email to after email verification', 'customer-email-verification' ),				
				'Default'   => '',
				'class'     => '',
			),
			'cev_redirect_page_after_varification' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Page to redirect after successful verification', 'customer-email-verification' ),				
				'class'		=> 'cev-skip-top-padding cev-skip-bottom-padding',
				'show' => true,
				'tooltip'	=> __('select a page to redirect users after successful verification. In case the email verification was during checkout, the user will be directed to checkout', 'customer-email-verification'),
				'Default'   => get_option( 'woocommerce_myaccount_page_id' ),	
				'options'   => $page_list,
				'checkbox_array' => array(),			
			),							
		);
		
		return $form_data;
	}
	
	/*
	* get settings tab array data
	* return array
	*/
	public function get_cev_settings_data_checkout() {	
		
		$cev_verification_type  = array(
			1 => __( 'Popup', 'customer-email-verification' ),
			2 => __( 'Inline', 'customer-email-verification' ),
			
		);
	
		$form_data = array(
			'cev_verification_checkout_dropdown_option' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Verification type', 'customer-email-verification'),				
				'options'   => $cev_verification_type,
				'show' => true,
				'id' 		=> '',
				'Default'   => '',
				'class'     => 'cev-skip-bottom-padding',
			),
			// 'cev_enable_email_verification_free_orders' => array(
			// 	'type'		=> 'checkbox',
			// 	'title'		=> __( 'Require checkout verification only for free orders', 'customer-email-verification'),				
			// 	'show' => true,
			// 	'id' 		=> '',
			// 	'Default'   => '',
			// ),
			'cev_enable_email_verification_cart_page' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable the email verification on the cart page', 'customer-email-verification'),				
				'show' => true,
				'id' 		=> 'cev_enable_email_verification_cart_page',
				'Default'   => '',
			),
										
		);
		return $form_data;
	}

	/*
	* get settings tab array data
	* return array
	*/
	public function get_cev_settings_data_login_otp() {
		
		$form_data = array(
			'cev_enable_login_authentication' => array(
				'type'		=> 'toogel',
				'title'		=> __( 'Enable Login Authentication', 'customer-email-verification' ),				
				'show'		=> true,
				'class'     => 'toogel',
				'name'		=> 'cev_enable_login_authentication',
				'id'		=> 'cev_enable_login_authentication',
				'value'		=> '1',
				
			),
			'cev_settings_separator_6' => array(
				'type'		=> 'separator',
				'id'		=> 'cev_settings_separator_6',
				'show'		=> true,
			),
			'enable_email_otp_for_account' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Require OTP verification for unrecognized login', 'customer-email-verification'),				
				'show' => true,
				'id' 		=> '',
				'Default'   => 1,
			),
			'cev_settings_separator_4' => array(
				'type'		=> 'separator',
				'id'		=> 'cev_settings_separator_4',
				'show'		=> true,
			),
			'login_auth_desc' => array(
				'type'		=> 'desc',
				'title'		=> __( 'Unrecognized Login Conditions:'),				
				'show' => true,
				'id' 		=> '',				
			),
			'enable_email_auth_for_new_device' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Login from a new device', 'customer-email-verification'),				
				'show' => true,
				'id' 		=> '',
				'Default'   => 1,
			),
			'enable_email_auth_for_new_location' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Login from a new location', 'customer-email-verification'),				
				'show' => true,
				'id' 		=> '',
				'Default'   => 1,
			),
			'enable_email_auth_for_login_time' => array(
				'type'		=> 'checkbox_select',
				'title'		=> __( 'Last login more then', 'customer-email-verification'),
				'select'	=> array(												
									'options' => array(
										15 => __( '15 Days', 'customer-email-verification' ),
										30 => __( '30 Days', 'customer-email-verification' ),	
										60 => __( '60 Days', 'customer-email-verification' ),
									),									
									'id' => 'cev_last_login_more_then_time',									
								),							
				'show' => true,
				'id' 		=> '',
				'Default'   => 1,
				'class'	=> 'enable_email_auth_for_login_time',
			),
			
		);
		return $form_data;
	}
	
	/*
	* get settings tab array data
	* return array
	*/
	public function get_cev_settings_data_new() {
		
		global $wp_roles;
		$all_roles = $wp_roles->roles;
		$all_roles_array = array();
		foreach ( $all_roles as $key=>$role ) {
			if ( 'administrator' != $key ) {
				$role = array( $key => $role['name'] );
				$all_roles_array = array_merge($all_roles_array, $role);	
			}
		}	
		$cev_verification_type  = array(
			'otp' => __( 'Verification OTP', 'customer-email-verification' ),
			'link' => __( 'Verification Link', 'customer-email-verification' ),
			'both' => __( 'Verification Link or OTP', 'customer-email-verification' ),
			
		);	
		$code_length  = array(
			'1' => __( '4-digits', 'customer-email-verification' ),
			'2' => __( '6-digits', 'customer-email-verification' ),
			
		);		   
		$code_expiration  = array(
			'never' => __( 'Never', 'customer-email-verification' ),
			'600' => __( '10 min', 'customer-email-verification' ),
			'900' => __( '15 min', 'customer-email-verification' ),
			'1800' => __( '30 min', 'customer-email-verification' ),
			'3600' => __( '1 Hours', 'customer-email-verification' ),
			'86400' => __( '24 Hours', 'customer-email-verification' ),
			'259200' => __( '72 Hours', 'customer-email-verification' ),			
		);
		$limited_count = array(
			'1' => __( 'Allow 1 Attempt', 'customer-email-verification' ),	
			'3' => __( 'Allow 3 Attempt', 'customer-email-verification' ),
			'0' => __( 'Disable Resend', 'customer-email-verification' ),
		 );
		 $cev_verification_type_checkout  = array(
			1 => __( 'Popup', 'customer-email-verification' ),
			2 => __( 'Inline', 'customer-email-verification' ),
			
		);
	
		
			
		
		$form_data = array(		
			'cev_enable_email_verification' => array(
				'type'		=> 'toogel',
				'title'		=> __( 'Enable Signup Verification', 'customer-email-verification' ),				
				'show'		=> true,
				'class'     => 'toogel',
				'name'		=> 'cev_enable_email_verification',
				'id'		=> 'cev_enable_email_verification',
				
			),
			'cev_settings_separator_1' => array(
				'type'		=> 'separator',
				'id'		=> 'cev_settings_separator_1',
				'show'		=> true,
			),
			'cev_enable_email_verification_checkout' => array(
				'type'		=> 'toogel',
				'title'		=> __( 'Enable Checkout Verification', 'customer-email-verification' ),				
				'show'		=> true,
				'class'     => 'toogel',
				'name'		=> 'cev_enable_email_verification_checkout',
				'id'		=> 'cev_enable_email_verification_checkout',
				
			),
			'cev_verification_checkout_dropdown_option' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Checkout Verification type', 'customer-email-verification'),				
				'options'   => $cev_verification_type_checkout,
				'show' => true,
				'id' 		=> '',
				'Default'   => '',
				'class'     => 'cev-skip-bottom-padding',
			),
			// 'cev_settings_separator_5' => array(
			// 	'type'		=> 'separator',
			// 	'id'		=> 'cev_settings_separator_5',
			// 	'show'		=> true,
			// ),
			'cev_enable_email_verification_cart_page' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable the email verification on the cart page', 'customer-email-verification'),				
				'show' => true,
				'id' 		=> 'cev_enable_email_verification_cart_page',
				'Default'   => '',
			),
			'cev_enable_email_verification_free_orders' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Require checkout verification only for free orders', 'customer-email-verification'),				
				'show' => true,
				'id' 		=> 'cev_enable_email_verification_free_orders',
				'Default'   => '',
			),
			'cev_settings_separator_2' => array(
				'type'		=> 'separator',
				'id'		=> 'cev_settings_separator_2',
				'show'		=> true,
			),
			// 'cev_verification_type' => array(
			// 	'type'		=> 'dropdown',
			// 	'title'		=> __( 'How to Verify', 'customer-email-verification' ),				
			// 	'show'		=> true,
			// 	'class'     => 'dropdown',
			// 	'show' => true,
			// 	'Default'   => '',	
			// 	'options'   => $cev_verification_type,
			// ),
			'cev_verification_code_length' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'OTP length', 'customer-email-verification' ),				
				'class'		=> '',
				'show' => true,
				'Default'   => '',	
				'options'   => $code_length, 				
			),
			'cev_verification_code_expiration' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'OTP expiration', 'customer-email-verification' ),				
				'class'		=> '',
				'show' => true,
				'tooltip'	=> __('Choose if you wish to set expiry time to the OTP / link - Never, 10 min, 30 min, 1 Hour, 24 Hours, 72 Hours (defaults to Never expires)', 'customer-email-verification'),	
				'Default'   => '',
				'options'   => $code_expiration, 				
			),	
			'cev_settings_separator_3' => array(
				'type'		=> 'separator',
				'id'		=> 'cev_settings_separator_3',
				'show'		=> true,
			),
			'cev_redirect_limit_resend' => array(
				'type'		=> 'dropdown',
				'Default'   => '',
				'title'		=> __( 'Verification email resend limit', 'customer-email-verification' ),				
				'class'		=> 'redirect_page',
				'show' => true,	
				'options'   => $limited_count, 				
			),
			'cev_resend_limit_message' => array(
				'type'		=> 'textarea',
				'title'		=> __( 'Resend limit message', 'customer-email-verification' ),				
				'show'		=> true,
				'tooltip'	=>__('limit the amount of times the user can resend the verification email. This option allows you to avoid multiple bounced emails in case the user tries to send verifications multiple times to email that does not exist', 'customer-email-verification'),
				'Default'   => '',
				'placeholder' => __( 'Too many attempts, please contact us</a> for further assistance', 'customer-email-verification' ),
				'desc_tip'      => __('you can use HTML tags &lt;a&gt;,&lt;p&gt;, etc', 'customer-email-verification'),
				'class'     => 'cev_text_design top',				
			),		
			'cev_verification_success_message' => array(
				'type'		=> 'textarea',
				'title'		=> __( 'Email verification success message', 'customer-email-verification' ),				
				'show'		=> true,
				'tooltip'	=> __('the message that will appear on the top of the my-account or checkout page after successful email verification', 'customer-email-verification'),
				'Default'   => __('Your email is verified!', 'customer-email-verification' ),
				'id'        => '',
				'placeholder' => __('Your email is verified!', 'customer-email-verification' ),	
				'desc_tip'      => '',
				'class'     => '',
			),	
			'cev_skip_verification_for_selected_roles' => array(
				'type'		=> 'multiple_select',
				'title'		=> __( 'Skip email verification for the selected user roles:', 'customer-email-verification' ),
				'options'   => $all_roles_array,				
				'show' => true,
				'Default'   => '',				
				'class'     => '',
			),
					
		);
		
		return $form_data;
	}

	public function cev_settings_form_update_fun() {
		if ( ! empty( $_POST ) && check_admin_referer( 'cev_settings_form_nonce', 'cev_settings_form_nonce' ) ) {
			
			$data = $this->get_cev_settings_data();
			$data_2 = $this->get_cev_settings_data_new();
			$data_3 = $this->get_cev_settings_data_checkout();
			$data_4 = $this->get_cev_settings_data_login_otp();

			

			if ( isset( $_POST[ 'cev_enable_email_verification' ] ) ) {
				update_option( 'cev_enable_email_verification', wc_clean( $_POST[ 'cev_enable_email_verification' ] ) );
			}

			if ( isset( $_POST[ 'cev_enable_email_verification_checkout' ] ) ) {
				update_option( 'cev_enable_email_verification_checkout', wc_clean( $_POST[ 'cev_enable_email_verification_checkout' ] ) );
			}

			if ( isset( $_POST[ 'cev_enable_login_authentication' ] ) ) {
				update_option( 'cev_enable_login_authentication', wc_clean( $_POST[ 'cev_enable_login_authentication' ] ) );
			}
			if ( isset( $_POST[ 'cev_verification_type' ] ) ) {
				update_option( 'cev_verification_type', wc_clean( $_POST[ 'cev_verification_type' ] ) );
			}
			foreach ( $data as $key => $val ) {	
				if ( isset( $_POST[ $key ] ) ) {
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}
				
				if ( isset($val['type']) && 'inline_checkbox' == $val['type'] ) {
					foreach ( ( array ) $val['checkbox_array'] as $key1 => $val1 ) {
						if ( isset($_POST[ $key1 ]) ) {
							update_option( $key1, wc_clean($_POST[ $key1 ]) );
						}
					}					
				}
			}
			
			foreach ( $data_3 as $key => $val ) {
			
				if ( isset($_POST[ $key ]) ) {						
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}								
			}

			foreach ( $data_4 as $key => $val ) {
				
				if ( isset($_POST[ $key ]) ) {						
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}	
				
				if ( isset( $val['type'] ) && 'checkbox_select' == $val['type'] ) {					
					if ( isset( $_POST[ $val['select']['id'] ] ) ) {
						update_option( $val['select']['id'], wc_clean( $_POST[ $val['select']['id'] ] ) );
					}					
				}
			}
			if ( isset( $_POST[ 'cev_verification_type' ] ) ) {
				if ( 'otp' === $_POST[ 'cev_verification_type' ] ) {
					$body_contet =__( 'Thank you for signing up for {site_title}, to activate your account, we need to verify your email address.<p>Your verification code: <strong>{cev_user_verification_pin}</strong></p>', 'customer-email-verification' );
				} else if ( 'both'  === $_POST[ 'cev_verification_type' ] ) {
					$body_contet =  __( 'Thank you for signing up for {site_title}, to activate your account, we need to verify your email address.<p>Your verification code: <strong>{cev_user_verification_pin}</strong></p><p>Or, verify your account by clicking on the button below: {cev_user_verification_link}</p>', 'customer-email-verification' );
				} else {
					$body_contet =  __( 'Thank you for signing up for {site_title}, to activate your account, we need to verify your email address.<p> verify your account by clicking on the button below: {cev_user_verification_link}</p>', 'customer-email-verification' );
				}
				update_option( 'cev_verification_email_body', $body_contet );
			}
			
			foreach ( $data_2 as $key => $val ) {				
				
				if ( isset($_POST[ $key ]) ) {						
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}								
				
				if ( isset( $val['type'] ) && 'multiple_select' == $val['type'] ) {					
	
					if ( isset( $_POST[ $key ] ) ) {
						$roles = array();
						foreach ( $val['options'] as $op_status => $op_data ) {
							$roles[ $key ][$op_status] = 0;
						}
											
						foreach ( wc_clean( $_POST[ $key ] ) as $key1 => $val ) {
							$roles[ $key ][$val] = 1;									
						}	
						update_option( $key, wc_clean($roles[ $key ]) );
					} else {
						update_option( $key, '' );
					}				
				}
			}
		}
	}

	/* 
	* Return verification pin code length placeholder text
	*/
	public function cev_verification_code_length_callback( $codelength ) {

		$code_length_text = get_option('cev_verification_code_length', $codelength);

		if ( '1' == $code_length_text ) {
			$code_length_text = '4-digits code';
		}
		if ( '2' == $code_length_text ) {
			$code_length_text = '6-digits code';
		}
		if ( '3' == $code_length_text ) {
			$code_length_text = '9-digits code';
		}
		return $code_length_text;
	}

	/**
	* This function adds custom columns in user listing screen in wp-admin area.
	*/
	public function add_column_users_list( $column ) {
		$column['cev_verified'] = __( 'Email verification', 'customer-email-verification' );
		$column['cev_action'] = __( 'Actions', 'customer-email-verification' );
		return $column;
	}
	
	/**
	* This function adds custom values to custom columns in user listing screen in wp-admin area.
	*/	
	public function add_details_in_custom_users_list( $val, $column_name, $user_id ) {
		
		wp_enqueue_script( 'jquery-blockui' );
		
		wp_enqueue_style( 'customer_email_verification_user_admin_styles', cev_pro()->plugin_dir_url() . 'assets/css/user-admin.css', array(), time() );
				
		wp_enqueue_script( 'customer_email_verification_user_admin_script', cev_pro()->plugin_dir_url() . 'assets/js/user-admin.js', array( 'jquery','wp-util' ), time() , true);
		
		$user_role = get_userdata( $user_id );
		$verified  = get_user_meta( $user_id, 'customer_email_verified', true );
		
		if ( 'cev_verified' === $column_name ) {
			if ( !$this->is_admin_user( $user_id ) ) {
				
				if ( !$this->is_verification_skip_for_user( $user_id ) ) {
					
					$verified_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverified_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
					
					$html = '<span style="' . $unverified_btn_css . '" class="dashicons dashicons-yes cev_5 cev_verified_admin_user_action" title="Verified"></span>';
					$html .= '<span style="' . $verified_btn_css . '" class="dashicons dashicons-no no-border cev_unverified_admin_user_action cev_5" title="Unverify"></span>';
					return $html;
				} 
			} 
			return '-';	
		}
		if ( 'cev_action' === $column_name ) {			
			if ( !$this->is_admin_user( $user_id ) ) {
				if ( !$this->is_verification_skip_for_user( $user_id ) ) {	
					
					$verify_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverify_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
					
					$html = '<span style="' . $unverify_btn_css . '" class="dashicons dashicons-no cev_dashicons_icon_unverify_user" id="' . $user_id . '" wp_nonce="' . wp_create_nonce( 'wc_cev_email' ) . ' "></span>';
					$html .= '<span style="' . $verify_btn_css . '" class="dashicons dashicons-yes small-yes cev_dashicons_icon_verify_user cev_10" id="' . $user_id . '" wp_nonce="' . wp_create_nonce( 'wc_cev_email' ) . ' "></span>';
					$html .= '<span style="' . $verify_btn_css . '" class="dashicons dashicons-image-rotate cev_dashicons_icon_resend_email" id="' . $user_id . '" wp_nonce="' . wp_create_nonce( 'wc_cev_email' ) . ' "></span></span>';
					return $html;
				}
			}	
		}	
		return $val;
	}
	public function cev_manualy_user_verify_in_user_menu() {				
		
		if ( isset( $_POST['wp_nonce'] ) && wp_verify_nonce( wc_clean( $_POST['wp_nonce'] ), 'wc_cev_email' ) ) { 
		
			$user_id = isset( $_POST['id'] ) ? wc_clean( $_POST['id'] ) : '';
			$action_type = isset( $_POST['actin_type'] ) ? wc_clean( $_POST['actin_type'] ) : '';
			
			if ( 'unverify_user' == $action_type ) {
				delete_user_meta( $user_id, 'customer_email_verified' ); 
			}
			
			if ( 'verify_user' == $action_type ) {
				update_user_meta( $user_id, 'customer_email_verified', 'true' );
				$this->trigger_delay_new_account_email( $user_id );	
			}
			
			if ( 'resend_email' == $action_type ) {
				$current_user           = get_user_by( 'id', $user_id );
				$is_secret_code_present = get_user_meta( $user_id, 'customer_email_verification_code', true );
	
				if ( '' === $is_secret_code_present ) {
					$secret_code = md5( $user_id . time() );
					update_user_meta( $user_id, 'customer_email_verification_code', $secret_code );
				}					
				
				cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id = $user_id; // WPCS: input var ok, CSRF ok.
				cev_pro()->WC_customer_email_verification_email_Common->wuev_myaccount_page_id = $this->my_account_id;
				cev_pro()->WC_customer_email_verification_email_Common->code_mail_sender( $current_user->user_email );
			}
		}
		exit;
	}
	
	/**
	 * This function manually verifies a user from wp-admin area.
	 */
	public function cev_manual_verify_user() {
		
		if ( isset( $_GET['user_id'] ) && isset( $_GET['wp_nonce'] ) && wp_verify_nonce( wc_clean( $_GET['wp_nonce'] ), 'wc_cev_email' ) ) { 			
			
			if ( isset( $_GET['wc_cev_confirm'] ) && 'true' === $_GET['wc_cev_confirm'] ) { 
				update_user_meta( wc_clean( $_GET['user_id'] ), 'customer_email_verified', 'true' );
				$this->trigger_delay_new_account_email( wc_clean( $_GET['user_id'] ) );	
				add_action( 'admin_notices', array( $this, 'manual_cev_verify_email_success_admin' ) );				
			} else {
				delete_user_meta( wc_clean( $_GET['user_id'] ), 'customer_email_verified' ); 
				add_action( 'admin_notices', array( $this, 'manual_cev_verify_email_unverify_admin' ) );				
			}
		}
		
		if ( isset( $_GET['user_id'] ) && isset( $_GET['wp_nonce'] ) && wp_verify_nonce( wc_clean( $_GET['wp_nonce'] ), 'wc_cev_email_confirmation' ) ) {			
			$current_user           = get_user_by( 'id', wc_clean( $_GET['user_id'] ) );
			$is_secret_code_present = get_user_meta( wc_clean( $_GET['user_id'] ), 'customer_email_verification_code', true );

			if ( '' === $is_secret_code_present ) {
				$secret_code = md5( wc_clean( $_GET['user_id'] ) . time() );
				update_user_meta( wc_clean( $_GET['user_id'] ), 'customer_email_verification_code', $secret_code );
			}					
			
			cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id = wc_clean( $_GET['user_id'] ); // WPCS: input var ok, CSRF ok.
			cev_pro()->WC_customer_email_verification_email_Common->wuev_myaccount_page_id = $this->my_account_id;
			
			cev_pro()->WC_customer_email_verification_email_Common->code_mail_sender( $current_user->user_email );
			add_action( 'admin_notices', array( $this, 'manual_confirmation_email_success_admin' ) );
		}		
	}
	
	public function manual_confirmation_email_success_admin() {
		?>
		<div class="updated notice">
			<p><?php esc_html_e( 'Verification email successfully sent.', 'customer-email-verification' ); ?></p>
		</div>
		<?php
	}
	
	public function manual_cev_verify_email_success_admin() {
		?>
		<div class="updated notice">
			<p><?php esc_html_e( 'User verified successfully.', 'customer-email-verification' ); ?></p>
		</div>
		<?php
	}
	
	public function manual_cev_verify_email_unverify_admin() {
		?>
		<div class="updated notice">
			<p><?php esc_html_e( 'User unverified.', 'customer-email-verification' ); ?></p>
		</div>
		<?php
	}

	// define the woocommerce_login_form_end callback 
	public function action_woocommerce_login_form_end() { 
		?>
		<p class="woocommerce-LostPassword lost_password">
			<a href="<?php echo wp_kses_post( get_home_url() ); ?>?p=reset-verification-email"><?php esc_html_e( 'Resend verification email', 'customer-email-verification' ); ?></a>
		</p>
	<?php
	}
	
	public function trigger_delay_new_account_email( $user_id ) {
		if ( get_option('delay_new_account_email_customer') == '1' ) {
			$emails = WC()->mailer()->emails;
			$new_customer_data = get_userdata( $user_id );			
			$user_pass = isset( $new_customer_data->user_pass ) ? $new_customer_data->user_pass : '';		
			$email = $emails['WC_Email_Customer_New_Account'];
			$email->trigger( $user_id, $user_pass, false );
		}
	}

	public function show_cev_fields_in_single_user( $user ) { 
	
		wp_enqueue_style( 'customer_email_verification_user_admin_styles', cev_pro()->plugin_dir_url() . 'assets/css/user-admin.css', array(), cev_pro()->version );
		wp_enqueue_script( 'customer_email_verification_user_admin_script', cev_pro()->plugin_dir_url() . 'assets/js/user-admin.js', array( 'jquery','wp-util' ), cev_pro()->version , true);
		
		$user_id = $user->ID;
		$verified  = get_user_meta( $user_id, 'customer_email_verified', true );		
		?>

		<table class="form-table cev-admin-menu">
			<tr>
				<th colspan="2">
					<h4 class="cev_admin_user">
					<?php esc_html_e( 'Customer verification', 'customer-email-verification' ); ?>
					</h4>
				</th>
			</tr>
			<tr>
				<th class="cev-admin-padding">
					<label><?php esc_html_e( 'Email verification status:', 'customer-email-verification' ); ?></label>
				</th>
				<td>
				<?php 
				if ( !$this->is_admin_user( $user_id )  && !$this->is_verification_skip_for_user( $user_id ) ) {
					$verified_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverified_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
					?>
					<span style="<?php esc_html_e( $unverified_btn_css ); ?>" class="dashicons dashicons-yes cev_5 cev_verified_admin_user_action_single" title="Verified"></span>
					<span style="<?php esc_html_e( $verified_btn_css ); ?>" class="dashicons dashicons-no no-border cev_unverified_admin_user_action_single cev_5" title="Unverify"></span>
					<?php				
				} else {
					echo 'Admin';
				} 
				?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<?php
					
				if ( !$this->is_admin_user( $user_id )  && !$this->is_verification_skip_for_user( $user_id ) ) {
						
					$verify_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverify_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
					?>
					
					<a style="<?php esc_html_e( $verify_btn_css ); ?>" class="button-primary cev-admin-verify-button cev_dashicons_icon_verify_user" id="<?php esc_html_e( $user_id ); ?>" wp_nonce="<?php esc_html_e( wp_create_nonce( 'wc_cev_email' ) ); ?>"><span class="dashicons dashicons-yes cev-admin-dashicons" style="color:#ffffff; margin-right: 2px;"></span><span><?php esc_html_e('Verify email manually', 'customer-email-verification'); ?></span></a>
					
					<a style="<?php esc_html_e( $verify_btn_css ); ?>" class="button-primary cev-admin-resend-button cev_dashicons_icon_resend_email" id="<?php esc_html_e( $user_id ); ?>" wp_nonce="<?php esc_html_e( wp_create_nonce( 'wc_cev_email' ) ); ?>"><span class="dashicons dashicons-image-rotate cev-admin-dashicons cev-rotate"></span><span><?php esc_html_e('Resend verification email', 'customer-email-verification'); ?></span></a>
					
					<a style="<?php esc_html_e( $unverify_btn_css ); ?>" class="button-primary cev-admin-unverify-button cev_dashicons_icon_unverify_user" id="<?php esc_html_e( $user_id ); ?>" wp_nonce="<?php esc_html_e( wp_create_nonce( 'wc_cev_email' ) ); ?>"><span class="dashicons dashicons-no cev-admin-dashicons"></span><span><?php esc_html_e('Un-verify email', 'customer-email-verification'); ?></span></a>
					
					<?php }	?>
				</td>
			</tr>
		</table>
	<?php 
	}

	public function filter_user_by_verified( $which ) {
		if ( 'top' === $which ) {
			$top = ( isset($_GET['customer_email_verified_top']) ) ? wc_clean( $_GET['customer_email_verified_top'] ) : null;
			$bottom = ( isset($_GET['customer_email_verified_bottom']) ) ? wc_clean( $_GET['customer_email_verified_bottom'] ) : null;	
			
			$true_selected = '';
			$false_selected = '';
			
			if (!empty($top) || !empty($bottom)) {
				
				$section = !empty($top) ? $top : $bottom;
				if ( 'true' == $section ) {
					$true_selected = 'selected';	
				}
				if ( 'false' == $section ) {
					$false_selected = 'selected';	
				}
			}

			?>
			<select name="customer_email_verified_<?php esc_html_e( $which ); ?>" style="float:none;margin-left:10px;">
				<option value=''><?php esc_html_e( 'User verification', 'customer-email-verification-pro' ); ?></option>
				<option <?php esc_html_e( $true_selected ); ?> value='true'><?php esc_html_e( 'Verified', 'customer-email-verification-pro' ); ?></option>
				<option <?php esc_html_e( $false_selected ); ?> value='false'><?php esc_html_e( 'Non verified', 'customer-email-verification-pro' ); ?></option>
			</select>
			<?php
			submit_button( __( 'Filter' ), null, $which, false );
		
		}
		
			
	}
	
	public function filter_users_by_user_by_verified_section( $query ) {
		
		global $pagenow;
		
		if ( is_admin() && 'users.php' == $pagenow ) {
			
			
			$top = ( isset($_GET['customer_email_verified_top']) ) ? wc_clean( $_GET['customer_email_verified_top'] ) : null;
			$bottom = ( isset($_GET['customer_email_verified_bottom']) ) ? wc_clean( $_GET['customer_email_verified_bottom'] ) : null;		
			
			if ( !empty( $top ) || !empty( $bottom ) ) {
				
				$section = !empty($top) ? $top : $bottom;
				
				if ( 'true' == $section ) {
					// change the meta query based on which option was chosen
					$meta_query = array (array (
						'key' => 'customer_email_verified',
						'value' => $section,
						'compare' => 'LIKE'
					));
				} else {
					$meta_query = array (
						'relation' => 'AND',
						array (
							'key' => 'cev_email_verification_pin',							
							'compare' => 'EXISTS'
						),
						array (
							'key' => 'customer_email_verified',
							'value' => $section,
							'compare' => 'NOT EXISTS'
						),	
					);
				}
				$query->set('meta_query', $meta_query);				
			}
		}	
	}
 
	public function add_custom_bulk_actions_for_user( $bulk_array ) {
	 
		$bulk_array['verify_users_email'] = __('Verify users email', 'customer-email-verification');
		$bulk_array['send_verification_email'] = __('Send verification email', 'customer-email-verification');
		return $bulk_array;
	 
	}

	public function users_bulk_action_handler( $redirect, $doaction, $object_ids ) {
	 
		$redirect = remove_query_arg( array( 'user_id', 'wc_cev_confirm', 'wp_nonce', 'wc_cev_confirmation', 'verify_users_emails', 'send_verification_emails' ), $redirect );

		if ( 'verify_users_email' == $doaction ) {
	 
			foreach ( $object_ids as $user_id ) {
				update_user_meta( $user_id, 'customer_email_verified', 'true' );
			}
	 
			$redirect = add_query_arg( 'verify_users_emails', count( $object_ids ), $redirect );
		}
	 
		if ( 'send_verification_email' == $doaction ) {
			foreach ( $object_ids as $user_id ) {
				$current_user = get_user_by( 'id', $user_id );
				$this->user_id                         = $current_user->ID;
				$this->email_id                        = $current_user->user_email;
				$this->user_login                      = $current_user->user_login;
				$this->user_email                      = $current_user->user_email;
				cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id  = $current_user->ID;
				cev_pro()->WC_customer_email_verification_email_Common->wuev_myaccount_page_id = $this->my_account_id;
				$this->is_user_created                 = true;		
				$is_secret_code_present                = get_user_meta( $this->user_id, 'customer_email_verification_code', true );
		
				if ( '' === $is_secret_code_present ) {
					$secret_code = md5( $this->user_id . time() );
					update_user_meta( $user_id, 'customer_email_verification_code', $secret_code );
				}
				
					cev_pro()->WC_customer_email_verification_email_Common->code_mail_sender( $current_user->user_email );
			}
			$redirect = add_query_arg( 'send_verification_emails', count( $object_ids ), $redirect );
		}
	 
		return $redirect;
	 
	}
 
	public function user_bulk_action_notices() {
		
		if ( ! empty( $_REQUEST['verify_users_emails'] ) ) {
			$verify_users_emails = wc_clean( intval( $_REQUEST['verify_users_emails'] ) );
			?>
			<div id="message" class="updated notice is-dismissible">		
				<p>
				<?php 
				/* translators: %s: search and replace with verify_users_emails */
				$notice = sprintf( _n( 'Verification Status updated for %s user.', 'Verification Status updated for %s users.', $verify_users_emails, 'customer-email-verification' ), $verify_users_emails );
				esc_html_e( $notice );
				?>
				</p>
			</div>
			<?php 			
		}
		
		if ( ! empty( $_REQUEST['send_verification_emails'] ) ) {
			$send_verification_emails = wc_clean( intval( $_REQUEST['send_verification_emails'] ) );
			?>
			<div id="message" class="updated notice is-dismissible">		
				<p>
				<?php 
				/* translators: %s: search and replace with send_verification_emails */
				$notice = sprintf( _n( 'Verification email sent to %s user.', 'Verification email sent to %s users.', $send_verification_emails, 'customer-email-verification' ), $send_verification_emails );
				esc_html_e( $notice );
				?>
				</p>
			</div>
			<?php 			
		}	 		
	}
	/**
	* Check if user is administrator
	*
	* @param int $user_id
	*
	* @return bool
	*/
	public function is_admin_user( $user_id ) {
		
		$user = get_user_by( 'id', $user_id );
		if ( !$user ) {
			return false;
		}
		$roles = $user->roles;
		
		if ( in_array( 'administrator', (array) $roles ) ) {
			return true;	
		}
		return false;
	}
	
	public function is_verification_skip_for_user( $user_id ) {
		
		$user = get_user_by( 'id', $user_id );
		if ( !$user ) {
			return false;
		}
		$roles = $user->roles;
		$cev_skip_verification_for_selected_roles = get_option('cev_skip_verification_for_selected_roles');		
		
		foreach ( ( array ) $cev_skip_verification_for_selected_roles as $role => $val ) {
			if ( in_array( $role, (array) $roles ) && 1 == $val ) {
				return true;
			}
		}
		return false;
	}
}
