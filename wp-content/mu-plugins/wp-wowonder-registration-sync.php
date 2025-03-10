<?php
/**
 * Plugin Name: WordPress WoWonder User Registration Sync
 * Description: Syncs WordPress user registration with WoWonder user registration. Using authentications to check for an existing account before creating a new one.
 * Version: 1.0001
 * Author: Blue Crown R&D
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include the DotEnv class and load environment variables from the .env file
require_once __DIR__ . '/../plugins/blue-crown-platform/DotEnv.php';
$dotenv = new DotEnv(dirname(__DIR__, 3) . '/.env');
$dotenv->load();

// Hook into WordPress login action
add_action('wp_login', 'wp_wowonder_registration_sync', 10, 2);

function wp_wowonder_registration_sync($user_login, $user) {
/*	
	if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wp-login')) {
		error_log('Invalid nonce, possible CSRF attack.');
		return;
	}
*/
	//WOWONDER AND USER CREDENTIALS
	$wowonder_api_url = getenv('WOWONDER_API_URL');
	$server_key = getenv('WOWONDER_SERVER_KEY');

	$username = $user->user_login;
	$user_email = $user->user_email;
	$user_password = $_POST['pwd'] ?? ''; // Get password from login form

	if (empty($user_password) || empty($username) || !$server_key || !$wowonder_api_url) {
		error_log('Credentials error.');
		return;
	}

//	// Step 1.01: Attempt to authenticate the user on WoWonder.
	error_log("Authenticating Buzzjuice User");
	
	$auth_response = wp_safe_remote_post("$wowonder_api_url/auth", [
		'timeout' => 20,
		'body' => [
			'server_key' => $server_key,
			'username' => $username,
			'password' => $user_password
		],
		'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
	]);

	if (is_wp_error($auth_response)) {
		error_log("User Authentication Error!");
/*			error_log("Auth API is_wp_error, get_error_message, get_error_data: " . json_encode([
			"message" => $auth_response->get_error_message(),
			"data" => $auth_response->get_error_data()
		]));
		error_log("Authentication API Response - is_wp_error print_r: " . print_r($auth_response, true));		*/
	}
	
	$auth_data = json_decode(wp_remote_retrieve_body($auth_response), true);
	
	if (!empty($auth_data['api_status']) && $auth_data['api_status'] == 200) {
	//	error_log("User Authentication Data - print_r: " . print_r($auth_data, true));
		error_log("User Authenticated!");
		
		$access_token = $auth_data['access_token'];
		$wowonder_user_id = $auth_data['user_id'];
		
        update_user_meta($user->ID, 'wowonder_access_token', $access_token);
		
		return;
	}  	
			
//	// Step 1.02: Create a new user account if authentication fails.

	error_log("Authentication Failed. Creating New User Account.");
	
	$create_user_api_response = wp_safe_remote_post("$wowonder_api_url/create-account", [
		'timeout' => 20,
		'body' => [
			'server_key' => $server_key,
			'username' => $username,
			'password' => $user_password,
			'email' => $user_email,
			'confirm_password' => $user_password
		],
		'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
	]);
	
	if (is_wp_error($create_user_api_response)) {
		error_log("The 'Create User' API failed");
		//error_log("The 'Create User' API failed with the following response:" . $create_user_api_response->get_error_message());
		//error_log("Create User API Response - is_wp_error print_r: " . print_r($create_user_api_response, true));
	}
	
	$new_user_data = json_decode(wp_remote_retrieve_body($create_user_api_response), true);

	if (!empty($new_user_data['api_status']) && $new_user_data['api_status'] == 200) {
		error_log("New User Created Successfully!");
		// error_log("New User Created Successfully: " . print_r($new_user_data, true));
		
		$access_token = $new_user_data['access_token'];
		$wowonder_user_id = $new_user_data['user_id'];
		
		update_user_meta($user->ID, 'wowonder_access_token', $access_token);
		
		return;
	}
	
	
}




?>