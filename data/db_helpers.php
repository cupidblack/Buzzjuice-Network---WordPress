<?php
// Load DotEnv for environment variables
require_once __DIR__ . '/DotEnv.php';

// Load env only once
if (!defined('DB_HELPERS_ENV_LOADED')) {
    try {
        $dotenv = new DotEnv(dirname(__DIR__, 3) . '/.env');
        $dotenv->load();
        define('DB_HELPERS_ENV_LOADED', true);
    } catch (Exception $e) {
        die('Error - Failed to load environment: ' . $e->getMessage());
    }
}

// WORDPRESS DB
if (!defined('WP_DB_HOST')) define('WP_DB_HOST', getenv('WORDPRESS_DB_HOST'));
if (!defined('WP_DB_USER')) define('WP_DB_USER', getenv('WORDPRESS_DB_USER'));
if (!defined('WP_DB_PASS')) define('WP_DB_PASS', getenv('WORDPRESS_DB_PASS'));
if (!defined('WP_DB_NAME')) define('WP_DB_NAME', getenv('WORDPRESS_DB_NAME'));
if (!defined('WP_TABLE_PREFIX')) define('WP_TABLE_PREFIX', 'wp_');
if (!defined('BP_TABLE_PREFIX')) define('BP_TABLE_PREFIX', 'wp_bp_');

// QUICKDATE DB
if (!defined('QD_DB_HOST')) define('QD_DB_HOST', getenv('QUICKDATE_DB_HOST'));
if (!defined('QD_DB_USER')) define('QD_DB_USER', getenv('QUICKDATE_DB_USER'));
if (!defined('QD_DB_PASS')) define('QD_DB_PASS', getenv('QUICKDATE_DB_PASS'));
if (!defined('QD_DB_NAME')) define('QD_DB_NAME', getenv('QUICKDATE_DB_NAME'));
if (!defined('QD_USERS_TABLE')) define('QD_USERS_TABLE', 'users');

// WOWONDER DB
if (!defined('WOWONDER_DB_HOST')) define('WOWONDER_DB_HOST', getenv('WOWONDER_DB_HOST'));
if (!defined('WOWONDER_DB_USER')) define('WOWONDER_DB_USER', getenv('WOWONDER_DB_USER'));
if (!defined('WOWONDER_DB_PASS')) define('WOWONDER_DB_PASS', getenv('WOWONDER_DB_PASS'));
if (!defined('WOWONDER_DB_NAME')) define('WOWONDER_DB_NAME', getenv('WOWONDER_DB_NAME'));

// Connection helpers
function get_wp_db_conn() {
    static $conn = null;
    if ($conn) return $conn;
    $conn = new mysqli(WP_DB_HOST, WP_DB_USER, WP_DB_PASS, WP_DB_NAME);
    if ($conn->connect_errno) return false;
    $conn->set_charset('utf8mb4');
    return $conn;
}

function get_qd_db_conn() {
    static $conn = null;
    if ($conn) return $conn;
    $conn = new mysqli(QD_DB_HOST, QD_DB_USER, QD_DB_PASS, QD_DB_NAME);
    if ($conn->connect_errno) return false;
    $conn->set_charset('utf8mb4');
    return $conn;
}

function get_wowonder_db() {
    static $conn = null;
    if ($conn) return $conn;
    $conn = new mysqli(WOWONDER_DB_HOST, WOWONDER_DB_USER, WOWONDER_DB_PASS, WOWONDER_DB_NAME);
    if ($conn->connect_errno) return false;
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Helper for table names
function wp_table($table) {
    return '`' . WP_DB_NAME . '`.`' . WP_TABLE_PREFIX . $table . '`';
}
function bp_table($table) {
    return '`' . WP_DB_NAME . '`.`' . BP_TABLE_PREFIX . $table . '`';
}