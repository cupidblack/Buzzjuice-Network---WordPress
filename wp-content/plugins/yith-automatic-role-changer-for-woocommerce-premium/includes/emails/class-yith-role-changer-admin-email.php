<?php
/**
 * Class that manages the Email.
 *
 * @class      YITH_Role_Changer_Admin_Email
 * @since      Version 1.0.0
 * @author     YITH <plugins@yithemes.com>
 * @package    YITH\AutomaticRoleChanger\Classes
 */

if ( ! defined( 'YITH_WCARC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_Role_Changer_Admin_Email' ) ) {
	/**
	 * Class YITH_Role_Changer_Admin_Email
	 *
	 */
	class YITH_Role_Changer_Admin_Email extends WC_Email {
		/**
		 * User_id
		 *
		 * @var Current user ID
		 */
		public $user_id = null;
		/**
		 * Order_id
		 *
		 * @var Current order ID
		 */
		public $order_id = null;

		/** __construct function */
		public function __construct() {

			$this->id = 'yith_ywarc_admin_email';

			$this->title       = esc_html__( 'Automatic Role Changer email for Admin', 'yith-automatic-role-changer-for-woocommerce' );
			$this->description = esc_html__(
				'The administrator will receive an email when a order with roles to be 
				granted passes to "Completed" or "Processing" status.',
				'yith-automatic-role-changer-for-woocommerce'
			);

			$this->heading = esc_html__( 'New roles will be added to user', 'yith-automatic-role-changer-for-woocommerce' );
			$this->subject = esc_html__( 'New roles will be added to user', 'yith-automatic-role-changer-for-woocommerce' );

			$this->template_html = 'emails/role-changer-admin.php';

			add_action( 'send_email_to_admin', array( $this, 'trigger' ), 10, 3 );
			add_filter( 'woocommerce_email_styles', array( $this, 'style' ) );

			parent::__construct();
			$this->recipient  = $this->get_option( 'recipient', get_option( 'admin_email' ) );
			$this->email_type = 'html';
		}

		/**
		 * The trigger to change all class variables.
		 *
		 * @param  mixed $valid_rules Valid Rules.
		 * @param  mixed $user_id ID of affected user.
		 * @param  mixed $order_id ID of order.
		 */
		public function trigger( $valid_rules, $user_id, $order_id ) {
			if ( ! $this->is_enabled() ) {
				return;
			}
			$this->object   = $valid_rules;
			$this->user_id  = $user_id;
			$this->order_id = $order_id;

			$this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}

		/**
		 * Return custom style
		 *
		 * @param  mixed $style Custom and class-related style.
		 * @return mixed
		 */
		public function style( $style ) {
			$style = $style .
				'.ywarc_metabox_gained_role {
				border: #dcdada solid 1px;
				padding: 15px;
				text-align: center;
				margin: 10px auto;
				width: 270px;
				}
				
				.ywarc_metabox_role_name {
				font-size: 24px;
				color: grey;
				}
				.ywarc_metabox_dates {
				font-size: 12px;
				margin-top: 10px;
				}';
			return $style;
		}

		/**
		 * Return HTML content
		 *
		 * @return mixed
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				),
				'',
				YITH_WCARC_TEMPLATE_PATH
			);
		}

		/** Create form fields. */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'   => array(
					'title'   => esc_html__( 'Enable/Disable', 'yith-automatic-role-changer-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => esc_html__( 'Enable this email notification', 'yith-automatic-role-changer-for-woocommerce' ),
					'default' => 'yes',
				),
				'recipient' => array(
					'title'       => esc_html__( 'Recipient(s)', 'yith-automatic-role-changer-for-woocommerce' ),
					'type'        => 'text',
					/* translators: %s is replaced with default recipients. */
					'description' => sprintf( esc_html__( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'yith-automatic-role-changer-for-woocommerce' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'subject'   => array(
					'title'       => esc_html__( 'Subject', 'yith-automatic-role-changer-for-woocommerce' ),
					'type'        => 'text',
					/* translators: %s is replaced with default subject */
					'description' => sprintf( esc_html__( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'yith-automatic-role-changer-for-woocommerce' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'heading'   => array(
					'title'       => esc_html__( 'Email Heading', 'yith-automatic-role-changer-for-woocommerce' ),
					'type'        => 'text',
					/* translators: %s is replaced with default heading */
					'description' => sprintf( esc_html__( 'This controls the main heading included in the email notification. Leave blank to use the default heading: <code>%s</code>.', 'yith-automatic-role-changer-for-woocommerce' ), $this->heading ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
			);
		}

	}

}
return new YITH_Role_Changer_Admin_Email();
