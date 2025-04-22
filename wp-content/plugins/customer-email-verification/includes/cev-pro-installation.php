<?php
if (!defined('ABSPATH')) {
	exit;
}

class CEV_Installation {
	public $plugin_file;
	/**
	 * Initialize the main plugin function
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		register_activation_hook($this->plugin_file, array($this, 'on_install_cev'));		
		add_action( 'init', array( $this, 'on_update_cev' ) );
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
	 * @param string $plugin_file
	 * @return CEV_Installation
	 */
	public static function get_instance( $plugin_file ) {
		if (null === self::$instance) {
			self::$instance = new self($plugin_file);
		}

		return self::$instance;
	}
	public function on_install_cev() {
		$this->create_user_log_table();
	
	}

	/**
	 * Create user log table
	 */
	public function create_user_log_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'cev_user_log';		
		if ( !$wpdb->query( $wpdb->prepare( 'show tables like %s', $table_name ) ) ) {	
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id int(11) NOT NULL AUTO_INCREMENT,
				email varchar(100) NOT NULL,
				pin varchar(100) NOT NULL,
				verified varchar(10) NOT NULL,
				secret_code varchar(100) NOT NULL,
				last_updated datetime NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";
	
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta($sql);
		}
	}

	/**
	 * Handle plugin update tasks
	 *
	 * @param WP_Upgrader $upgrader_object
	 * @param array $options
	 */
	public function on_update_cev() {
		if ( is_admin() ) {			
			if ( version_compare( get_option( 'cev_pro_update_version', '1.0' ), '1.1', '<' ) ) {								
				$this->create_user_log_table();	
				update_option( 'cev_pro_update_version', '1.1');
				update_option( 'cev_verification_message', 'We sent verification code. To verify your email address, please check your inbox and enter the code below.');
				update_option( 'cev_verification_code_length', '1');
				update_option( 'cev_verification_email_body', 'Thank you for signing up for {site_title}, to activate your account, we need to verify your email address. <p>Your verification code: <strong>{cev_user_verification_pin}</strong></p>');
					
			}	
		}
	}
}
