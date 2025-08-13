<?php
/**
* Plugin Name: PWA Pro Extension Manager
* Plugin URI: https://pwa-for-wp.com/
* Description: This is PWA for wp Extension Manger Plugin.
* Version: 1.9.2.2
* Author: PWAforWP Team
* Author URI: https://pwa-for-wp.com/
**/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
define('PWAFORWPPRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('PWAFORWPPRO_PLUGIN_DIR_URI', plugin_dir_url(__FILE__));
define('PWAFORWPPRO_ITEM_NAME', 'PWA Pro Extension Manager');
define('PWAFORWPPRO_VERSION','1.9.2.2');
define( 'PWAFORWPPRO_ITEM_FOLDER_NAME',basename(__DIR__));
class PWAFORWPPROExtensionManager{
    var $store_url = 'https://pwa-for-wp.com/';
    private $bundle_item_name = "Pro Membership Bundle";
    var $license = '';
    var $download_id = '';
    var $plugin_active_path = '';
    var $message = '';
    var $license_info = '';
    var $product_name = '';
    var $all_extension_list = array();
    var $all_plugin_list = array();
    private $pwa_plugin_count = 0;
    private $plugin_count = 0;
    public $main_documentation_link = 'https://pwa-for-wp.com/docs/';
    private $support_link = 'https://pwa-for-wp.com/contact-us/';

    /*
    * For menu related contents
    */
    private $prefix = 'pwawp';
    private $pro_name = 'PWA for WP';
    private $page_title = 'PWA Extension Manager';
    // private $menu_title = 'Extension Manager';
    private $capability = 'manage_options';
    private $menu_slug  = 'pwawp-extension-manager';
    private $function   = 'settings_page';
    private $position   = 4;

    /** */
    private $nonce_name = 'pro_nonce';

    public function __construct() {
        $this->nonce_name = $this->prefix.'pro_nonce';
        add_action( 'init', array($this, 'manager_init') );
        $this->plugin_loaded();
    }
    public function manager_init(){
       if(!function_exists('pwaforwp_init_plugin')){
         add_action("admin_notices", array($this, 'notice_parent_plugin_message'));
       }else if(is_admin()){  
        add_action( 'admin_menu', array($this, 'admin_menu_add') );
        add_action( 'admin_enqueue_scripts',  array($this, 'enqueue_scripts') );
        //BUNDLE
        add_action( 'wp_ajax_'.$this->prefix.'_pro_validate_licence', array( $this, 'activate_bundle_licence' ) );

        //CHILD
        add_action( 'wp_ajax_'.$this->prefix.'_pro_activate_licence', array( $this, 'activate_child_licence' ) );

        add_action( 'wp_ajax_'.$this->prefix.'_pro_remove_licence', array( $this, 'pro_remove_licence' ) );

        add_action( 'wp_ajax_'.$this->prefix.'_pro_refresh_bundle', array( $this, 'refresh_bundle_request' ) );

        add_action( 'wp_ajax_nopriv_'.$this->prefix.'set_T', array( $this, 'pwaforwp_set_T' ) );

        add_action( 'wp_ajax_'.$this->prefix.'set_T', array( $this, 'pwaforwp_set_T' ) );

        add_action( 'wp_ajax_nopriv_'.$this->prefix.'set_T_f7', array( $this, 'pwaforwp_set_T_f7' ) );

        add_action( 'wp_ajax_'.$this->prefix.'set_T_f7', array( $this, 'pwaforwp_set_T_f7' ) );

        add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 12, 2 );
        add_filter( "views_plugins", array( $this, 'plugin_page_plugin_heading' ), 50 );

        add_action('current_screen', array( $this, 'load_plugin_list'));

        /*add_action('activate_plugin', array( $this, 'reset_transient' ));
        add_action('deactivate_plugin', array( $this, 'reset_transient' ));*/
        $this->plugin_updater();

        //Plugins.php page
        //add_filter( "all_plugins", array( $this, 'plugin_extesion_list' ),30 );
        
      }
    }
    public function load_plugin_list($screen){
        if($screen->base=='plugins'){
            //if ( !isset( $_REQUEST['plugin_status'] ) || ($_REQUEST['plugin_status']=="pwa-pro-ext" || $_REQUEST['plugin_status']=="all")) {
                add_filter( "all_plugins", array( $this, 'plugin_extesion_list' ),30 );
            //}
        }
    }

    public function notice_parent_plugin_message(){
        $class = 'notice notice-error is-dismissible';
        $message = esc_html__( 'PWA PRO Extension manager is requires PWA parent plugin.', 'pwa-for-wp' );
        $message .= ' <a href="https://wordpress.org/plugins/pwa-for-wp/" class="button button-primary" target="__blank">'.esc_html__('Click here', 'pwa-for-wp').'</a>';
     
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
    }

    public function reset_transient(){
        delete_transient($this->prefix.'_extensions_list_trans');
    }
    
    public function plugin_loaded(){
        
    }
    public function plugin_settings_link( $actions, $plugin_file ) {
        $plugin = plugin_basename(__FILE__);
        if ( $plugin === $plugin_file ) {
            $amp_activate = '';
            $settings = array( 'settings' => '<a href="admin.php?page='.$this->prefix.'-extension-manager">Settings</a>');
            $actions = array_merge( $actions, $settings );
        }
        return $actions;
    }
    public function enqueue_scripts($hook){
        if($hook!=('pwa_page_pwawp-extension-manager' || 'admin_page_pwawp-extension-manager' || 'pwaforwp' )){ return false; }
        wp_register_style( $this->prefix.'-pro-ext_css', untrailingslashit(PWAFORWPPRO_PLUGIN_DIR_URI) . '/css/style.css', false, PWAFORWPPRO_VERSION );
        wp_register_script( $this->prefix.'-pro-ext_script',  untrailingslashit(PWAFORWPPRO_PLUGIN_DIR_URI) .'/js/script.js', array(), PWAFORWPPRO_VERSION, true );

        $localizeData = array('prefix'=> $this->prefix);
        wp_localize_script($this->prefix.'-pro-ext_script', $this->prefix.'_vars', $localizeData);

        wp_enqueue_style( $this->prefix.'-pro-ext_css' );
        wp_enqueue_script( $this->prefix.'-pro-ext_script' );

        if(isset($_GET["page"]) && !empty($_GET)){
            if($_GET['page'] == 'pwaforwp' ){
                wp_enqueue_style( 'pwafwp-admin-ss', PWAFORWPPRO_PLUGIN_DIR_URI . 'inc/css/pwafwp-ext-manager-admin-css.css', false , PWAFORWPPRO_VERSION );
            }
        }
    }
    public function admin_menu_add(){
        $days = '';
        $license_alert = '';
        $get_license_info = get_option( 'pwawppro_license_info');
        if($get_license_info){
            $pwawp_pro_expires = date('Y-m-d', strtotime($get_license_info->expires));
            $license_info_lifetime = $get_license_info->expires;
                    $today = date('Y-m-d');
        $exp_date = $pwawp_pro_expires;
        $date1 = date_create($today);
        $date2 = date_create($exp_date);
        $diff = date_diff($date1,$date2);
        $days = $diff->format("%a");
        if( $license_info_lifetime == 'lifetime' ){
                $days = 'Lifetime';
            }

            else if($today > $exp_date){
                $days = -$days;
            }
    $license_alert = $days<=30 && $days!=='Lifetime' ? "<span class='pwaforwp_pro_icon dashicons dashicons-warning pwaforwp_pro_alert'></span>": "" ;
}
      $page_title = $this->page_title;
      $menu_title = 'Extension Manager'.$license_alert.' ';
      $capability = $this->capability;
      $menu_slug  = $this->menu_slug;
      $function   = array($this, $this->function);
      $position   = $this->position;
      add_submenu_page('pwaforwp', $page_title, $menu_title, $capability, $menu_slug, $function, $position );
    }
    public function settings_page(){
        require_once dirname( __FILE__ ).'/pwawp-extension-manager-settings.php';
    }
    /**
     * Activate bundle licenses
     */
    public function activate_bundle_licence(){
        $res = array();
        if ( !isset( $_POST['verify_nonce'] ) || !wp_verify_nonce( $_POST['verify_nonce'],  $this->nonce_name )) {
            $res['success'] = 400;
            $res['message'] = 'Sorry, your nonce did not verify.';
        } else {
            if(isset($_POST['license']) && !empty($_POST['license'])){
                $license_k = sanitize_text_field($_POST['license']);
                $this->activate_bundle_license($license_k);
            }else{
                $res['success'] = 500;
                $res['message'] = "Please enter a valid license key";
            }
        }
        echo json_encode($res);
        die;
    }
    public function activate_bundle_license($license_k){
        $res = array();
        if ( ! isset( $_POST['verify_nonce'] ) || ! wp_verify_nonce( $_POST['verify_nonce'], $this->nonce_name )) {
            $res['success'] = 2;
            $res['message'] = 'Sorry, your nonce did not verify.';
        }else{
            $item_name = $this->bundle_item_name;
            $api_params = array(
                'edd_action' => 'activate_license',
                'license'    => $license_k,
                'item_name'  => urlencode( $item_name ), // the name of our product in EDD
                'url'        => home_url(),
                'referer'    => 'extension_manager',
                'activation_type'    => 'bundle',
            );
            $response = wp_remote_post( $this->store_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
            $response = wp_remote_retrieve_body( $response );
            $license_data = json_decode( $response );
            if (!$license_data->success ) {
                switch( $license_data->license ) {
                    case 'expired' :
                        $this->message = sprintf(
                            esc_html__( 'Your license key expired on %s.', 'accelerated-mobile-pages' ),
                            date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                        );
                        break;

                    case 'revoked' :

                        $this->message = esc_html__( 'Your license key has been disabled.', 'accelerated-mobile-pages' );
                        break;

                    case 'missing' :
                        $this->message = esc_html__( 'Invalid license.', 'accelerated-mobile-pages' );
                        break;

                    case 'invalid' :
                    if ($license_data->error == 'expired' ) {
                     $this->message = sprintf( 
                            esc_html__( ' This appears to be an Expired license key for %s.', 'accelerated-mobile-pages' ),
                            $item_name
                        );
                     update_option('pwawppro_license_info',$license_data);
                    }
                    else {
                      $this->message = sprintf( 
                            esc_html__( 'This appears to be an invalid license key for %s.', 'accelerated-mobile-pages' ),
                            $item_name
                        );
                  }
                        break;

                    case 'site_inactive' :

                        $this->message = esc_html__( 'Your license is not active for this URL.', 'accelerated-mobile-pages' );
                        break;

                    case 'item_name_mismatch' :

                        $this->message = sprintf( 
                            esc_html__( 'This appears to be an invalid license key for %s.', 'accelerated-mobile-pages' ),
                            $item_name
                        );
                        break;

                    case 'no_activations_left':

                        $this->message = esc_html__( 'Your license key has reached its activation limit.', 'accelerated-mobile-pages' );
                        break;

                    default :

                        $this->message = esc_html__( 'An error occurred, please try again.', 'accelerated-mobile-pages' );
                        $res['other'] = $response;
                        break;
                }
                if ($license_data->error == 'expired' ) {
                $res['success'] = 3;
                $t = date('Y-m-d', strtotime($license_data->expires));
                $today = date('Y-m-d');
                $exp_date = $t;
                $date1 = date_create($today);
                $date2 = date_create($exp_date);
                $diff = date_diff($date1,$date2);
                $days = $diff->format("%a");
                if($today > $exp_date){
                $days = -$days;
                }
                $res['expires'] = $days;
                }
                else{
                    $res['success'] = 2;
                }
                $res['message'] = $this->message;
                echo json_encode($res);
                exit;
            }else{
                // delete_option('ampforwppro_license_info');
                $license_data->license_key = $license_k;                
                // update_option($this->prefix.'pro_license_info',$license_data);
                update_option('pwawppro_license_info',$license_data);
                $license_info = $license_data;
                global $all_extensions_data;
                $renew = "no";
                $license_exp = "";                
                $t = date('Y-m-d', strtotime($license_info->expires));
                $today = date('Y-m-d');
                $exp_date =$t;
                $date1 = date_create($today);
                $date2 = date_create($exp_date);
                $diff = date_diff($date1,$date2);
                $days = $diff->format("%a");
                $t = $license_info->expires;
                $res['success'] = 1;
                $res['message'] = "Activated";
                $res['license_exp__'] = $days;
                echo json_encode($res);
                 exit;
            }  
        }
        // echo json_encode($res);die;
    }
    public function activate_child_licence(){
        if ( ! isset( $_POST['verify_nonce'] ) || ! wp_verify_nonce( $_POST['verify_nonce'], $this->nonce_name )) {
           $res['success'] = 2;
           $res['message'] = 'Sorry, your nonce did not verify.';
        } else {
            $this->pwaforwppro_reset_transient();
            $is_active = $_POST['is_active'];
            $key = $_POST['id'];
            $c_status = $_POST['c_status'];
            if(empty($is_active) || $is_active=="1") {
                $this->license_info = get_option( $this->prefix.'pro_license_info');
                $response = $this->license_info->afwpp_response;
                $this->product_name = $response[$key]->post_title;
                $this->license = $response[$key]->license_key;
                $this->download_id = $response[$key]->download_id;
                $edd_action = 'activate_license';
                if($c_status=="Deactivate" || $c_status=="Revoke"){
                    $edd_action = 'deactivate_license';
                }
                $api_params = array(
                    'edd_action' => $edd_action,
                    'license'    => $this->license,
                    'item_name'  => urlencode( $this->product_name ), // the name of our product in EDD
                    'referer'    => 'extension_manager',
                    'activation_type'    => 'individual',
                );
                $response = wp_remote_post( $this->store_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
                $response = wp_remote_retrieve_body( $response );
                $license_data = json_decode( $response );
                if (!$license_data->success ) {
                    switch( $license_data->license ) {
                        case 'expired' :
                            $this->message = sprintf(
                                esc_html__( 'Your license key expired on %s.', 'accelerated-mobile-pages' ),
                                date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                            );
                            break;

                        case 'revoked' :

                            $this->message = esc_html__( 'Your license key has been disabled.', 'accelerated-mobile-pages' );
                            break;

                        case 'missing' :
                            $this->message = esc_html__( 'Invalid license.', 'accelerated-mobile-pages' );
                            break;

                        case 'invalid' :
                          $this->message = sprintf( 
                                esc_html__( 'This appears to be an invalid license key for %s.', 'accelerated-mobile-pages' ),
                                $this->product_name
                            );
                            break;

                        case 'site_inactive' :

                            $this->message = esc_html__( 'Your license is not active for this URL.', 'accelerated-mobile-pages' );
                            break;

                        case 'item_name_mismatch' :

                            $this->message = sprintf( 
                                esc_html__( 'This appears to be an invalid license key for %s.', 'accelerated-mobile-pages' ),
                                $this->product_name
                            );
                            break;

                        case 'no_activations_left':

                            $this->message = esc_html__( 'Your license key has reached its activation limit.', 'accelerated-mobile-pages' );
                            break;

                        default :
                            $this->message = esc_html__( 'An error occurred, please try again.', 'accelerated-mobile-pages' );
                            break;
                    }
                    $res['success'] = 2;
                    $res['message'] = $this->message;
                }else{
                    if(isset($license_data->afwpp_response[0])){
                        $this->license_info->afwpp_response[$key]->status = $license_data->afwpp_response[0]->status;
                    }
                    if($c_status=="Activate" || $c_status=="Activate Plugin" || $c_status=="Activate License"){
                        $this->pro_process_plugin_activation($this->license_info,$key);
                    }else if($c_status=="Deactivate" || $c_status=="Revoke"){
                        $this->pro_process_plugin_deactivation($this->license_info,$key);
                    }
                    $res['success'] = 1;
                    $res['message'] = "Successful";
                }
            }else{
                $plugin_path = $_POST['plugin_path'];
                if($is_active==0){
                    activate_plugin($plugin_path);
                    $res['success'] = 1;
                    $res['message'] = "Successful";
                }else{
                    deactivate_plugins($plugin_path);
                    $res['success'] = 1;
                    $res['message'] = "Successful";
                }
            }
        }
        echo json_encode($res);
        exit;
    }
    public function pro_process_plugin_activation($license_data,$key){
        $activation_reps = $license_data->afwpp_response;
        $this->license_info->afwpp_response = $activation_reps;
        $request = $this->pro_get_the_version_info();

        if(isset($request->download_link)){
            $url  = $request->download_link;
            $slug = $request->slug;
            $name = $request->name;
            WP_Filesystem();
            
            $download   = download_url($url,300,false);
            $permfile   = PWAFORWPPRO_PLUGIN_DIR.'/../'.$slug.'.zip';
            $upload_to  = PWAFORWPPRO_PLUGIN_DIR.'/../';
            copy( $download, $permfile );
            unlink( $download );
            $file_result = unzip_file($permfile, $upload_to );
            unlink( $permfile );
            
            //Activate the plugin
            $plugin_path = $slug."/".$slug.".php";
            $this->plugin_active_path = $plugin_path;
            activate_plugin($plugin_path);

            $this->license_info->afwpp_response[$key]->slug = $slug;
            update_option($this->prefix.'pro_license_info',$this->license_info);
            $this->pro_license_update_core($license_data,$slug,"activate");
            $res['success'] = 1;
            $res['message'] = "Successful";
            echo json_encode($res);
            exit;
        }else{
            $res['success'] = 2;
            $res['message'] = $request->msg;
            echo json_encode($res);
            exit;
        }
    }
    public function pro_process_plugin_deactivation($license_data,$key){
        
        $activation_reps = $license_data->afwpp_response;
        $this->license_info->afwpp_response = $activation_reps;
         $request = $this->pro_get_the_version_info();
        if(isset($request->download_link)){
            $url  = $request->download_link;
            $slug = $request->slug;
            $name = $request->name;
            $this->license_info->afwpp_response[$key]->slug = $slug;
            update_option($this->prefix.'pro_license_info',$this->license_info);
            
            $plugin_path = $slug."/".$slug.".php";
            $this->plugin_active_path = $plugin_path;
            deactivate_plugins($plugin_path);
            $this->pro_license_update_core($license_data,$slug,"deactivate");
            $res['success'] = 1;
            $res['message'] = "Successful";
            echo json_encode($res);
            exit;
        }else{
            $res['success'] = 2;
            $res['message'] = $request->msg;
            echo json_encode($res);
            exit;
        }
    }
    public function pro_get_the_version_info(){
        $api_params = array(
          'edd_action' => 'get_version',
          'license'    => $this->license,
          'item_name'  => $this->product_name,
          'referer'    => 'extension_manager',
        );
        $request    = wp_remote_post( $this->store_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
        $response = wp_remote_retrieve_body( $request );
        $version_info = json_decode( $response);
        return $version_info;
    }

    public function get_all_plugins_addon_list(){
        $product_item_name = pwaforwp_list_addons();
        $product_item_name = apply_filters('pwaforwp_pro_extension_lists', $product_item_name);
        return $product_item_name;
    }

    public function pro_license_update_core($license_data,$slug,$type){
        $product_item_name = $this->get_all_plugins_addon_list();
        
        $license_response = $license_data->afwpp_response;
        
        $current_plugin_activating = array();
        foreach($license_response as $license){
            if($license->slug==$slug){
                $current_plugin_activating = $license;
            break;
            }
        }
        if($current_plugin_activating){
            $saswp_addon_name = '';
            foreach($product_item_name as $item_index=> $item_name){
                if(strtolower($item_name['p-title'])==strtolower($current_plugin_activating->post_title)){
                    $saswp_addon_name = $item_index;
                    break;
                }
                
            }
            
            if($type=="activate"){
                $license_addon = array();
                $license_addon[strtolower($saswp_addon_name).'_addon_license_key_status']  = 'active';
                $license_addon[strtolower($saswp_addon_name).'_addon_license_key']         = $current_plugin_activating->license_key;
                $license_addon[strtolower($saswp_addon_name).'_addon_license_key_message'] = 'active';
                
                $get_options   = get_option('pwaforwp_settings');
                if(!$get_options){ $get_options = array(); }
                $merge_options = array_merge($get_options, $license_addon);
                update_option('pwaforwp_settings', $merge_options); 
            }else if($type=="deactivate"){
                $get_options   = get_option('pwaforwp_settings');
                unset($get_options[strtolower($saswp_addon_name).'_addon_license_key_status']);
                unset($get_options[strtolower($saswp_addon_name).'_addon_license_key']);
                unset($get_options[strtolower($saswp_addon_name).'_addon_license_key_message']);
                update_option('pwaforwp_settings', $get_options); 
            }
        }
    }
    public function get_plugin_prefix($name,$slug, $request){
        $ind = strtolower($slug);
        $plugin_path = $slug."/".$slug.".php";
        if(!file_exists(PWAFORWPPRO_PLUGIN_DIR."/../".$plugin_path)){
            $url = $request->url;
            $plugin_url = parse_url($url);
            $path = explode("/", $plugin_url['path']);
            $path = end($path);
            $plugin_path = $path."/".$path.".php";
        }
        if(isset($this->all_extension_list[$ind])){
            $plugin_path = $this->all_extension_list[$ind]['plugin_active_path'];
        }
        return $plugin_path;
    }
    
    public function get_plugin_update( $file, $plugin_data ) {
        $current = get_site_transient( 'update_plugins' );
        if ( ! isset( $current->response[ $file ] ) ) {
            return false;
        }
        $response = $current->response[ $file ];
        $plugins_allowedtags = array(
            'a'       => array(
                'href'  => array(),
                'title' => array(),
            ),
            'abbr'    => array( 'title' => array() ),
            'acronym' => array( 'title' => array() ),
            'code'    => array(),
            'em'      => array(),
            'strong'  => array(),
        );
        if(isset($plugin_data['Name'])){
        $plugin_name = wp_kses( $plugin_data['Name'], $plugins_allowedtags );
         }
        $details_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $response->slug . '&section=changelog&TB_iframe=true&width=600&height=800' );
        $update_info = '';
        $update_info = '<a href="'.esc_url( $details_url ).'" title="Update Plugin" class="afwpp-link" target="_blank"> <i class="dashicons dashicons-update-alt ext-update"></i> <span class="ext-update">Update</span></a>';
        if ( is_network_admin() || ! is_multisite() ) {
            if ( is_network_admin() ) {
                $active_class = is_plugin_active_for_network( $file ) ? ' active' : '';
            } else {
                $active_class = is_plugin_active( $file ) ? ' active' : '';
            }

            $requires_php   = isset( $response->requires_php ) ? $response->requires_php : null;
            $compatible_php = is_php_version_compatible( $requires_php );
            $notice_type    = $compatible_php ? 'notice-warning' : 'notice-error';

            if ( ! current_user_can( 'update_plugins' ) ) {
                $update_info = '<a href="'.esc_url( $details_url ).'" title="Update Plugin" class="afwpp-link" target="_blank"> <i class="dashicons dashicons-update-alt ext-update"></i> <span class="ext-update">Update</span></a>';
           
            } elseif ( empty( $response->package ) ) {
                $update_info = '<a href="'.esc_url( $details_url ).'" title="Update Plugin" class="afwpp-link" target="_blank"> <i class="dashicons dashicons-update-alt ext-update"></i> <span class="ext-update">Update</span></a>';
                
            } else {
                if ( $compatible_php ) {
                    $update_info = '<a href="'.wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file, 'upgrade-plugin_' . $file ).'" title="Update Plugin" class="afwpp-link"> <i class="dashicons dashicons-update-alt ext-update"></i> <span class="ext-update">Update</span></a>';
                
                } else {
                    $update_info = '<a href="'.esc_url( $details_url ).'" title="Update Plugin" class="afwpp-link" target="_blank"> <i class="dashicons dashicons-update-alt ext-update"></i> <span class="ext-update">Update</span></a>';
                
                }
            }
        }
        return $update_info;
    }
    public function get_documentation($name){
        $docs = array(
            'BuddyPress for PWA for WP' => 'https://pwa-for-wp.com/docs/article/how-to-use-buddypress-for-pwa-for-wp/',
            'Call to Action for PWA' => 'https://pwa-for-wp.com/docs/article/how-to-use-call-to-action-cta-in-pwa/',
            'Data Analytics for PWA' => 'https://pwa-for-wp.com/docs/article/how-to-use-data-analytics-for-pwa/',
            'Loading Icon Library for PWA' => 'https://pwa-for-wp.com/docs/article/add-preloader-on-your-website-with-pwa/',
            'Multilingual Compatibility for PWA' => 'https://pwa-for-wp.com/docs/article/how-to-use-multilingual-compatibility-for-pwa/',
            'Navigation Bar for PWA' => 'https://pwa-for-wp.com/docs/article/how-to-use-navigation-bar-for-pwa-addon/',
            'Offline Forms for PWA for WP'=>'https://pwa-for-wp.com/docs/article/how-to-use-offline-forms/',
            'Pull to Refresh for PWA'=>'https://pwa-for-wp.com/docs/article/pull-to-refresh-for-pwa/',
            'PWA to APK Plugin' => 'https://pwa-for-wp.com/docs/article/how-to-use-pwa-to-apk-plugin/',
            'Quick Action for PWA' => 'https://pwa-for-wp.com/docs/article/how-to-use-quick-action-for-pwa-for-wp/',
            'Rewards on PWA install'=>  'https://pwa-for-wp.com/docs/article/how-to-use-rewards-on-pwa-install/',
            'Scroll Progress Bar for PWA'=> 'https://pwa-for-wp.com/docs/article/scroll-progress-bar-for-pwa/',
        );
        $doc = '';
        if(isset($docs[$name])){
            $doc ='<a href="'.esc_url($docs[$name]).'" title="Documentation" class="afwpp-link" target="_blank"><button class="button btn-setting">Documentation</button></a>';
          
        }
        return $doc;
    }
    public function pro_remove_licence(){
        if ( ! isset( $_POST['verify_nonce'] ) || ! wp_verify_nonce( $_POST['verify_nonce'],  $this->nonce_name )) {
            $res['success'] = 2;
            $res['message'] = 'Sorry, your nonce did not verify.';
            
        }elseif(isset($_POST['action']) && $_POST['action'] == $this->prefix.'_pro_remove_licence'){
            $license_info =  get_option($this->prefix.'pro_license_info');
            $license_k = $license_info->license_key;
            $item_name = $this->bundle_item_name;
            $api_params = array(
                'edd_action' => 'deactivate_license',
                'license'    => $license_k,
                'item_name'  => urlencode( $item_name ), // the name of our product in EDD
                'url'        => home_url(),
                'referer'    => 'extension_manager',
                'activation_type' => 'bundle',
            );
            $response = wp_remote_post( $this->store_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
            $response = wp_remote_retrieve_body( $response );
            $license_data = json_decode( $response );
            if($license_data->success){

                if($license_data->license=='deactivated'){
                    delete_option($this->prefix.'pro_license_info');
                    $this->pwaforwppro_reset_transient();
                    $res['success'] = 1;
                    $res['message'] = "Successful";
                    echo json_encode($res);
                    exit;
                }else{
                    $res['success'] = 2;
                    $res['message'] = "Opps! something went wrong please try again.";
                    echo json_encode($res);
                    exit;
                }
            }
            elseif(isset($license_info->error) && $license_info->error == 'expired'){
                delete_option($this->prefix.'pro_license_info');
                $this->pwaforwppro_reset_transient();
                $res['success'] = 1;
                $res['message'] = "Successful";
                echo json_encode($res);
            }
            else{
                if(isset($license_data->license) && $license_data->license == 'failed'){
                delete_option($this->prefix.'pro_license_info');
                $this->pwaforwppro_reset_transient();
                $res['success'] = 1;
                $res['message'] = "Successfuldsfsfsdfsdfsdfs";
                echo json_encode($res);
                }
                else{
                $res['success'] = 2;
                $res['message'] = "Placed license key is Invalid please contact our support team to deactivate.";
                echo json_encode($res);
                exit;
            }
            }
        }
    }
    public function refresh_bundle_request(){
        if ( ! isset( $_POST['verify_nonce'] ) || ! wp_verify_nonce( $_POST['verify_nonce'],  $this->nonce_name )) {
            $res['success'] = 2;
            $res['message'] = 'Sorry, your nonce did not verify.';
            echo json_encode($res);
        }else{
            $renew_status = $_POST['renew_status'];
            $license_info =  get_option($this->prefix.'pro_license_info');
            $payment_id = $license_info->payment_id;
            $download_id = $license_info->download_id;
            $api_params = array(
                'action'        => 'afwpp_refresh_lincense',
                'verify_nonce'  => 'refresh_bundle_list',
                'payment_id'    => $payment_id, 
                'download_id'   => $download_id,
                'referer'       => 'extension_manager',
            );
            $response = wp_remote_post( $this->store_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
            $response = wp_remote_retrieve_body( $response );
            $resp_data = json_decode( $response );
            if($resp_data->success==1){
                $license_k = $license_info->license_key;
                $this->activate_bundle_license($license_k);
            }else if($renew_status=="yes" || $resp_data->success==2){
                $license_k = $license_info->license_key;
                $this->activate_bundle_license($license_k);
            }else{
                $res['success'] = 2;
                $res['message'] = "Opps! something went wrong.";
                echo json_encode($res);
            }
        }
    }
    
    public function ampforwppro_popular_plugin_list($extension){
        $popular = array();
        $extension = strtolower($extension);
        if(in_array($extension, $popular)){
            return true;
        }
        return false;
    }

    public function pwaforwppro_reset_transient(){
        delete_transient('ampforwp_extensions_list_trans');
    }

    public function plugin_page_plugin_heading($views){
        $class="";
        $current="";
        //$this->pwa_plugin_count = $this->plugin_count
        if ( isset( $_REQUEST['plugin_status'] ) && $_REQUEST['plugin_status']=="pwa-pro-ext") {
            $class="current";
            $current= 'aria-current="page"';
            $views['all'] = str_replace('class="current" aria-current="page"', "", $views['all']);
        }

        if($this->plugin_count>0){
            $views['all'] = preg_replace("/(\d+)/", $this->plugin_count, $views['all']);
        }


        $views['pwa-pro-ext'] = '<a href="plugins.php?plugin_status=pwa-pro-ext" class="'.$class.'" '.$current.'>PWA <span class="count">('.$this->pwa_plugin_count.')</span></a>';
        
        return $views;
    }

    public function pwaforwp_set_T( $value )
    {
            $transient =  'pwaforwp_set_trans_once';
            $value =  'pwaforwp_set_trans_once_v';
            $expiration =  '' ;
            set_transient( $transient, $value, $expiration );
    }

    public function pwaforwp_set_T_f7( $value )
    {
            $transient_load =  'pwaforwp_set_trans';
            $value_load =  'pwaforwp_set_trans_v';
            $expiration_load =  86400 ;
            set_transient( $transient_load, $value_load, $expiration_load );
    }

    public function plugin_extesion_list($all_plugins){
        $get_li_info = get_option($this->prefix.'pro_license_info');
        if(is_object($get_li_info)){
            $response = $get_li_info->afwpp_response;
            $extension_arr = array();

            foreach ($response as $key => $value) {
                $status = ucfirst($response[$key]->status);
                $title = $response[$key]->post_title;
                $tit = $title;
                $ind = trim(strtolower(str_replace(' ', '-', $tit)));
                $extension_arr[] = $ind;
            }
            $this->pwa_plugin_count = $this->plugin_count = 0;

            foreach ($all_plugins as $key => $value) {
               $p_key = strtolower(str_replace(' ', '-', $value['Title']));
               //if(isset($_REQUEST['plugin_status']) && $_REQUEST['plugin_status']=="pwa-pro-ext" && (!in_array($p_key, $extension_arr))){
               if( (!in_array($p_key, $extension_arr)) ){
                    $this->plugin_count += 1; 
                    if(isset($_REQUEST['plugin_status']) && $_REQUEST['plugin_status']=="pwa-pro-ext"){
                        unset($all_plugins[$key]);
                    }
               }else if( in_array($p_key, $extension_arr) ){
                    $this->pwa_plugin_count += 1;
                    if(isset($_REQUEST['plugin_status']) && $_REQUEST['plugin_status']=="all"){
                        unset($all_plugins[$key]);
                    }
               }else{

               }
            }
        }
        
        return $all_plugins;
    }
    public function ampforwppro_recommended_plugin_list($extension){
        $recommended = array();
        if(in_array($extension, $recommended)){
            return true;
        }
        return false;
    }

    // Check for updates
    public function plugin_updater() {
        require_once dirname( __FILE__ ) . '/updater/EDD_SL_Plugin_Updater.php';
        // setup the updater
        $edd_updater = new PWAFORWPPRO_EXTENSION_MANAGER_EDD_SL_Plugin_Updater( $this->store_url, __FILE__, array(
                'version'   => PWAFORWPPRO_VERSION,
                'license'   => '37ec7b87b29b5ff90f0473a4a491c876',  
                'license_status'=>'active',
                'item_name' => PWAFORWPPRO_ITEM_NAME,
                'author'    => 'PWAforWP Team',
                'beta'      => false,
            )
        );
        $path = plugin_basename( __FILE__ );
        add_action("after_plugin_row_{$path}", function( $plugin_file, $plugin_data, $status ) {
            if(! defined('PWAFORWPPRO_ITEM_FOLDER_NAME')){
                $folderName = basename(__DIR__);
                define( 'PWAFORWPPRO_ITEM_FOLDER_NAME', $folderName );
            }
            $update_cache = get_site_transient( 'update_plugins' );
            $update_cache = is_object( $update_cache ) ? $update_cache : new stdClass();
            if(isset($update_cache->response[ PWAFORWPPRO_ITEM_FOLDER_NAME ]) 
                && empty($update_cache->response[ PWAFORWPPRO_ITEM_FOLDER_NAME ]->download_link) 
              ){
               unset($update_cache->response[ PWAFORWPPRO_ITEM_FOLDER_NAME ]);
                set_site_transient( 'update_plugins', $update_cache );
            }
            
           
        }, 10, 3 );
    }
// Notice to enter license key once activate the plugin
}
$pwaforwp_pro_plugin = null;
add_action( 'plugins_loaded',  'pwaforwp_pro_plugin_initiate');
function pwaforwp_pro_plugin_initiate(){
    global $pwaforwp_pro_plugin;
    $pwaforwp_pro_plugin = new PWAFORWPPROExtensionManager();
}

