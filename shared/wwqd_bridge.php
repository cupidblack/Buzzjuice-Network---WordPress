<?php
/**
 * Unified WoWonder <-> QuickDate <-> WordPress bridge
 *
 * This file merges functionality from:
 *  - requests/wp_user_bridge.php (QuickDate <> WordPress)
 *  - assets/wp_user_bridge.php      (WoWonder <> WordPress)
 *
 * Place this file in the WordPress root "shared" folder together with:
 *  - shared/db_helpers.php        (moved from data/db_helpers.php)
 *  - shared/DotEnv.php            (moved from data/DotEnv.php)
 *  - shared/buzz_metadata.json    (moved from data/user_field_metadata.json)
 *
 * Goals:
 *  - Keep original function names where possible (backwards compatibility).
 *  - Reduce redundancy by centralizing DB connection helpers and utilities.
 *  - Provide a simple authenticated HTTP endpoint interface for other platforms
 *    (QuickDate / WoWonder) to request sync actions using BUZZ_SSO_SECRET.
 *
 * Authentication / SSO:
 *  - The endpoint expects a BUZZ_SSO_SECRET value (from .env loaded in db_helpers).
 *  - Provide the secret via POST param `sso_secret` OR Authorization: Bearer <secret>.
 *
 * Usage examples (HTTP):
 *  - Single QuickDate user update (QuickDate -> WordPress/WoWonder):
 *      POST /shared/wwqd_bridge.php?action=sync_quickdate_to_wordpress
 *      Headers: Authorization: Bearer <BUZZ_SSO_SECRET>
 *      Body (JSON):
 *        { "quickdate_user": { "email":"joe@example.com", "username":"joe", "first_name":"Joe", ... } }
 *
 *  - Bulk QuickDate user updates:
 *      POST /shared/wwqd_bridge.php?action=bulk_sync_quickdate
 *      Body (JSON):
 *        { "users": [ { ... }, { ... } ] }
 *
 * NOTE: db_helpers.php (and DotEnv) should be present in same shared folder. db_helpers
 *       will load DotEnv and constants like WP_DB_HOST, QUICKDATE_DB_HOST, WOWONDER_DB_HOST, BUZZ_SSO_SECRET.
 */

/* --------------------------------------------------------------------------
 * Basic includes
 * -------------------------------------------------------------------------- */

if (!file_exists(__DIR__ . '/db_helpers.php')) {
    // Friendly error - missing shared helpers
    error_log("[wwqd_bridge] Missing shared/db_helpers.php. Please add the file.");
    return;
}
require_once __DIR__ . '/db_helpers.php'; // This will also load DotEnv.php (as before)

/* --------------------------------------------------------------------------
 * Utilities and compatibility wrappers
 * -------------------------------------------------------------------------- */

/**
 * Return WordPress mysqli connection (alias, backward compatible).
 * Original projects used get_wp_db() / get_wp_db_conn(); keep both.
 */
if (!function_exists('get_wp_db_conn')) {
    function get_wp_db_conn() {
        // db_helpers provides this function already; call it if exists
        if (function_exists('get_wp_db_conn')) {
            return \get_wp_db_conn();
        }
        // Fallback: create new connection from constants
        static $conn = null;
        if ($conn) return $conn;
        if (!defined('WP_DB_HOST')) return false;
        $conn = new mysqli(WP_DB_HOST, WP_DB_USER, WP_DB_PASS, WP_DB_NAME);
        if ($conn->connect_errno) return false;
        $conn->set_charset('utf8mb4');
        return $conn;
    }
}

if (!function_exists('get_wp_db')) {
    function get_wp_db() {
        // Some older code expects a mysqli object in $wpDb global
        // Use get_wp_db_conn() and return same
        return get_wp_db_conn();
    }
}

/**
 * QuickDate DB connection (keeps get_qd_db_conn name used across code)
 */
if (!function_exists('get_qd_db_conn')) {
    function get_qd_db_conn() {
        // db_helpers likely already defines it; call existing if present
        if (function_exists('get_qd_db_conn')) {
            return \get_qd_db_conn();
        }
        // else build from constants
        static $conn = null;
        if ($conn) return $conn;
        if (!defined('QD_DB_HOST')) return false;
        $conn = new mysqli(QD_DB_HOST, QD_DB_USER, QD_DB_PASS, QD_DB_NAME);
        if ($conn->connect_errno) return false;
        $conn->set_charset('utf8mb4');
        return $conn;
    }
}

/**
 * WoWonder DB connection (keeps get_wowonder_db name used across code)
 */
if (!function_exists('get_wowonder_db')) {
    function get_wowonder_db() {
        if (function_exists('get_wowonder_db')) {
            return \get_wowonder_db();
        }
        static $conn = null;
        if ($conn) return $conn;
        if (!defined('WOWONDER_DB_HOST')) return false;
        $conn = new mysqli(WOWONDER_DB_HOST, WOWONDER_DB_USER, WOWONDER_DB_PASS, WOWONDER_DB_NAME);
        if ($conn->connect_errno) return false;
        $conn->set_charset('utf8mb4');
        return $conn;
    }
}

/* Serialization helpers (kept for compatibility) */
if (!function_exists('is_serialized')) {
    function is_serialized($data, $strict = true) {
        if (!is_string($data)) return false;
        $data = trim($data);
        if ('N;' === $data) return true;
        if (strlen($data) < 4) return false;
        if (':' !== $data[1]) return false;
        $lastc = substr($data, -1);
        if (';' !== $lastc && '}' !== $lastc) return false;
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) return false;
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
                break;
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $rest = substr($data, 2);
                return (bool) preg_match("/^([0-9.E-]+);$/", $rest);
        }
        return false;
    }
}

if (!function_exists('maybe_unserialize')) {
    function maybe_unserialize($original) {
        if (is_serialized($original)) return @unserialize($original);
        return $original;
    }
}

/* Table helpers (compatibility) */
if (!function_exists('wp_table')) {
    function wp_table($table) {
        if (defined('WP_TABLE_PREFIX') && defined('WP_DB_NAME')) {
            return '`' . WP_DB_NAME . '`.`' . WP_TABLE_PREFIX . $table . '`';
        }
        return '`' . $table . '`';
    }
}
if (!function_exists('bp_table')) {
    function bp_table($table) {
        if (defined('BP_TABLE_PREFIX') && defined('WP_DB_NAME')) {
            return '`' . WP_DB_NAME . '`.`' . BP_TABLE_PREFIX . $table . '`';
        }
        return '`' . $table . '`';
    }
}

/* --------------------------------------------------------------------------
 * Metadata loader
 * - Reads shared/buzz_metadata.json (preferred local file), falls back to remote URL
 * -------------------------------------------------------------------------- */
function get_user_field_metadata() {
    static $metadata = null;
    if ($metadata !== null) return $metadata;

    $local = __DIR__ . '/buzz_metadata.json';
    if (file_exists($local)) {
        $json = @file_get_contents($local);
    } else {
        // fallback to original remote location (same behavior as earlier)
        $json = @file_get_contents('https://buzzjuice.net/data/user_field_metadata.json');
    }

    $metadata = $json ? json_decode($json, true) : ['private_secure_fields' => [], 'public_open_fields' => []];
    // Ensure keys exist
    if (!isset($metadata['private_secure_fields'])) $metadata['private_secure_fields'] = [];
    if (!isset($metadata['public_open_fields'])) $metadata['public_open_fields'] = [];
    return $metadata;
}

/* --------------------------------------------------------------------------
 * WordPress utility functions (kept names for backward compatibility)
 * Many functions optionally accept a DB connection parameter to be flexible.
 * -------------------------------------------------------------------------- */

/**
 * Fetch WP user by login or email
 * Signature maintained: wp_get_user_by_login_or_email($sqlConnect, $login_or_email)
 * also accept call with only ($login_or_email).
 */
if (!function_exists('wp_get_user_by_login_or_email')) {
    function wp_get_user_by_login_or_email($sqlConnectOrLogin, $maybeLogin = null) {
        if ($maybeLogin === null) {
            // called as wp_get_user_by_login_or_email($login)
            $login_or_email = $sqlConnectOrLogin;
            $sqlConnect = get_wp_db_conn();
        } else {
            $sqlConnect = $sqlConnectOrLogin;
            $login_or_email = $maybeLogin;
        }
        if (!$sqlConnect) return false;
        $login_or_email = mysqli_real_escape_string($sqlConnect, $login_or_email);
        $query = "SELECT * FROM " . wp_table('users') . " WHERE user_login = '$login_or_email' OR user_email = '$login_or_email' LIMIT 1";
        $result = mysqli_query($sqlConnect, $query);
        if (!$result || mysqli_num_rows($result) == 0) return false;
        return mysqli_fetch_assoc($result);
    }
}

/**
 * Check WP password using phpass
 * Keep same signature.
 */
if (!function_exists('wp_check_password')) {
    function wp_check_password($password, $hash) {
        if (!class_exists('PasswordHash')) {
            // try to include a local class-phpass.php if available in shared folder
            $phpass_path = __DIR__ . '/class-phpass.php';
            if (file_exists($phpass_path)) require_once $phpass_path;
        }
        $wp_hasher = new PasswordHash(8, true);
        return $wp_hasher->CheckPassword($password, $hash);
    }
}

/**
 * Get WP usermeta (compat)
 * Signature: wp_get_usermeta($sqlConnect, $user_id, $meta_key) or wp_get_usermeta($user_id, $meta_key)
 */
if (!function_exists('wp_get_usermeta')) {
    function wp_get_usermeta($sqlConnectOrUserId, $maybeUserId = null, $maybeMetaKey = null) {
        if ($maybeMetaKey === null) {
            // called as wp_get_usermeta($user_id, $meta_key)
            $sqlConnect = get_wp_db_conn();
            $user_id = intval($sqlConnectOrUserId);
            $meta_key = $maybeUserId;
        } else {
            $sqlConnect = $sqlConnectOrUserId;
            $user_id = intval($maybeUserId);
            $meta_key = $maybeMetaKey;
        }
        if (!$sqlConnect) return null;
        $meta_key = mysqli_real_escape_string($sqlConnect, $meta_key);
        $query = "SELECT meta_value FROM " . wp_table('usermeta') . " WHERE user_id = $user_id AND meta_key = '$meta_key' LIMIT 1";
        $result = mysqli_query($sqlConnect, $query);
        if (!$result || mysqli_num_rows($result) == 0) return null;
        $row = mysqli_fetch_assoc($result);
        return maybe_unserialize($row['meta_value']);
    }
}

/**
 * Get BuddyBoss xProfile field
 * Signature kept: wp_get_xprofile_data($sqlConnect, $user_id, $field_name) or wp_get_xprofile_data($user_id, $field_name)
 */
if (!function_exists('wp_get_xprofile_data')) {
    function wp_get_xprofile_data($sqlConnectOrUserId, $maybeUserId = null, $maybeField = null) {
        if ($maybeField === null) {
            $sqlConnect = get_wp_db_conn();
            $user_id = intval($sqlConnectOrUserId);
            $field_name = $maybeUserId;
        } else {
            $sqlConnect = $sqlConnectOrUserId;
            $user_id = intval($maybeUserId);
            $field_name = $maybeField;
        }
        if (!$sqlConnect) return null;
        $field_name = mysqli_real_escape_string($sqlConnect, $field_name);
        $field_id_query = "SELECT id FROM " . bp_table('xprofile_fields') . " WHERE name = '$field_name' LIMIT 1";
        $field_id_result = mysqli_query($sqlConnect, $field_id_query);
        if (!$field_id_result || mysqli_num_rows($field_id_result) == 0) return null;
        $field_id_row = mysqli_fetch_assoc($field_id_result);
        $field_id = intval($field_id_row['id']);
        $value_query = "SELECT value FROM " . bp_table('xprofile_data') . " WHERE user_id = $user_id AND field_id = $field_id LIMIT 1";
        $value_result = mysqli_query($sqlConnect, $value_query);
        if (!$value_result || mysqli_num_rows($value_result) == 0) return null;
        $value_row = mysqli_fetch_assoc($value_result);
        return $value_row['value'];
    }
}

/**
 * Create WP user (basic, no meta)
 * Signature: wp_create_user($sqlConnect, $username, $password, $email) or wp_create_user($username, $password, $email)
 */
if (!function_exists('wp_create_user')) {
    function wp_create_user($sqlConnOrUser, $maybePass = null, $maybeEmail = null, $maybeUnused = null) {
        if ($maybeEmail === null) {
            // called as wp_create_user($username, $password, $email)
            $wp_db_conn = get_wp_db_conn();
            $username = $sqlConnOrUser;
            $password = $maybePass;
            $email = $maybeEmail; // actually null, but keep signature consistent: expecting 3 args
            // If user passed exactly 3 args, shift
            if ($maybePass !== null && $maybeEmail === null) {
                // actually arguments were (user, pass, email) but we saw maybeEmail null due to signature;
                // try retrieving global func args
                $args = func_get_args();
                if (count($args) >= 3) {
                    $username = $args[0];
                    $password = $args[1];
                    $email = $args[2];
                }
            }
        } else {
            $wp_db_conn = $sqlConnOrUser;
            $username = $maybePass;
            $password = $maybeEmail;
            $email = $maybeUnused;
        }

        if (!$wp_db_conn) return false;
        if (!class_exists('PasswordHash')) {
            $phpass_path = __DIR__ . '/class-phpass.php';
            if (file_exists($phpass_path)) require_once $phpass_path;
        }
        $wp_hasher = new PasswordHash(8, true);
        $user_login = mysqli_real_escape_string($wp_db_conn, $username);
        $user_pass = $wp_hasher->HashPassword($password);
        $user_email = mysqli_real_escape_string($wp_db_conn, $email);
        $user_registered = date('Y-m-d H:i:s');
        $check_query = "SELECT ID FROM " . wp_table('users') . " WHERE user_login = '$user_login' OR user_email = '$user_email' LIMIT 1";
        $check_result = mysqli_query($wp_db_conn, $check_query);
        if ($check_result && mysqli_num_rows($check_result) > 0) return false;
        $insert_query = "INSERT INTO " . wp_table('users') . " (user_login, user_pass, user_email, user_registered, user_status) VALUES ('$user_login', '$user_pass', '$user_email', '$user_registered', 0)";
        $insert_result = mysqli_query($wp_db_conn, $insert_query);
        if (!$insert_result) return false;
        return mysqli_insert_id($wp_db_conn);
    }
}

/**
 * Get full WP user data (user + meta + xprofile)
 */
if (!function_exists('wp_get_full_user_data')) {
    function wp_get_full_user_data($sqlConnectOrUserId, $maybeUserId = null) {
        if ($maybeUserId === null) {
            $sqlConnect = get_wp_db_conn();
            $user_id = intval($sqlConnectOrUserId);
        } else {
            $sqlConnect = $sqlConnectOrUserId;
            $user_id = intval($maybeUserId);
        }
        if (!$sqlConnect || $user_id <= 0) return false;
        $user_query = "SELECT * FROM " . wp_table('users') . " WHERE ID = $user_id LIMIT 1";
        $user_result = mysqli_query($sqlConnect, $user_query);
        if (!$user_result || mysqli_num_rows($user_result) == 0) return false;
        $user = mysqli_fetch_assoc($user_result);

        $meta = [];
        $meta_query = "SELECT meta_key, meta_value FROM " . wp_table('usermeta') . " WHERE user_id = $user_id";
        $meta_result = mysqli_query($sqlConnect, $meta_query);
        if ($meta_result) {
            while ($row = mysqli_fetch_assoc($meta_result)) {
                $meta[$row['meta_key']] = maybe_unserialize($row['meta_value']);
            }
        }

        $xprofile = [];
        $field_query = "SELECT f.id, f.name FROM " . bp_table('xprofile_fields') . " AS f";
        $field_result = mysqli_query($sqlConnect, $field_query);
        if ($field_result) {
            while ($field = mysqli_fetch_assoc($field_result)) {
                $field_id = intval($field['id']);
                $field_name = $field['name'];
                $data_query = "SELECT value FROM " . bp_table('xprofile_data') . " WHERE user_id = $user_id AND field_id = $field_id LIMIT 1";
                $data_result = mysqli_query($sqlConnect, $data_query);
                if ($data_result && mysqli_num_rows($data_result)) {
                    $data_row = mysqli_fetch_assoc($data_result);
                    $xprofile[$field_name] = $data_row['value'];
                }
            }
        }

        return [
            'ID' => $user['ID'],
            'user_login' => $user['user_login'],
            'user_email' => $user['user_email'],
            'user_pass' => $user['user_pass'],
            'user_registered' => $user['user_registered'],
            'display_name' => $user['display_name'] ?? '',
            'meta' => $meta,
            'xprofile' => $xprofile
        ];
    }
}

/* --------------------------------------------------------------------------
 * Update/Insert WP usermeta and WoWonder mapping
 * This is a consolidated wp_update_usermeta signature to support previous callers.
 *
 * There are two historical signatures in the repo:
 *  - wp_update_usermeta($sqlConnect, $user_id, $meta_data, $user = null)
 *    (where $meta_data may be array of keys => values OR single key then extra value)
 *  - wp_update_usermeta($wp_db_conn, $user_id, $meta_key, $meta_value) (assets version)
 *
 * To keep compatibility, detect usage and normalize input.
 * -------------------------------------------------------------------------- */
if (!function_exists('wp_update_usermeta')) {
    function wp_update_usermeta() {
        $args = func_get_args();
        // Determine form:
        // Form A: ($sqlConnect, $user_id, $meta_data (array OR single key), $user_obj_or_value = null)
        // Form B: ($wp_db_conn, $user_id, $meta_key, $meta_value)  [assets version]
        // Form C: ($user_id, $meta_key, $meta_value)  [some callers used this]
        // We'll normalize to: $wp_db_conn, $user_id, $meta_data_array, $user_obj
        $wp_db_conn = null; $user_id = 0; $meta_data = []; $user_obj = null;

        if (count($args) === 0) return false;

        // If first arg is mysqli, treat as form A/B
        if ($args[0] instanceof mysqli) {
            $wp_db_conn = $args[0];
            if (isset($args[1])) $user_id = intval($args[1]);
            if (isset($args[2])) $meta_data = $args[2];
            if (isset($args[3])) $user_obj = $args[3];
        } else {
            // first arg is not mysqli, maybe called as (user_id, key, value)
            if (count($args) >= 3) {
                $wp_db_conn = get_wp_db_conn();
                $user_id = intval($args[0]);
                $meta_data = [$args[1] => $args[2]];
            } elseif (count($args) === 2) {
                // ($user_id, $meta_data_array)
                $wp_db_conn = get_wp_db_conn();
                $user_id = intval($args[0]);
                $meta_data = $args[1];
            } else {
                return false;
            }
        }

        if (!$wp_db_conn || $user_id <= 0 || empty($meta_data)) {
            error_log("[wp_update_usermeta] Invalid parameters.");
            return false;
        }

        // Normalize meta_data: if not array but a string key with a passed value in $user_obj (old form),
        // the previous implementation used func_get_arg(3). We attempted to capture that above.
        if (!is_array($meta_data)) {
            error_log("[wp_update_usermeta] Converting single meta key to array.");
            // Attempt to recover value from user_obj if provided
            if ($user_obj !== null) {
                $meta_data = [$meta_data => $user_obj];
            } else {
                error_log("[wp_update_usermeta] No value provided for meta key.");
                return false;
            }
        }

        // Prepare escaping/serialization
        $prepare_value = function($val) use ($wp_db_conn) {
            if (is_array($val) || is_object($val)) $val = serialize($val);
            return mysqli_real_escape_string($wp_db_conn, $val);
        };

        // Optional: Extract qd_user_id if present (older logic)
        $qd_user_id = null;
        if ($user_obj && is_object($user_obj) && isset($user_obj->id)) {
            // attempt lookup
            $qd_user_id = get_quickdate_user_id_by_wp_email($user_id);
            error_log("[wp_update_usermeta] Found qd_user_id from user object: $qd_user_id");
        } elseif (isset($meta_data['qd_user_id']) && is_numeric($meta_data['qd_user_id'])) {
            $qd_user_id = (int)$meta_data['qd_user_id'];
            error_log("[wp_update_usermeta] Found qd_user_id from meta_data: $qd_user_id");
        }

        if ($qd_user_id) {
            $safe_qd_value = $prepare_value($qd_user_id);
            $check_qd = "SELECT umeta_id FROM " . wp_table('usermeta') . " WHERE user_id = $user_id AND meta_key = 'qd_user_id' LIMIT 1";
            $result_qd = mysqli_query($wp_db_conn, $check_qd);
            if ($result_qd && mysqli_num_rows($result_qd) > 0) {
                $row_qd = mysqli_fetch_assoc($result_qd);
                $umeta_id_qd = intval($row_qd['umeta_id']);
                $update_qd = "UPDATE " . wp_table('usermeta') . " SET meta_value = '$safe_qd_value' WHERE umeta_id = $umeta_id_qd";
                if (mysqli_query($wp_db_conn, $update_qd)) {
                    error_log("[wp_update_usermeta] Updated qd_user_id for user_id $user_id");
                } else {
                    error_log("[wp_update_usermeta] Failed to update qd_user_id: " . mysqli_error($wp_db_conn));
                }
            } else {
                $insert_qd = "INSERT INTO " . wp_table('usermeta') . " (user_id, meta_key, meta_value) VALUES ($user_id, 'qd_user_id', '$safe_qd_value')";
                if (mysqli_query($wp_db_conn, $insert_qd)) {
                    error_log("[wp_update_usermeta] Inserted qd_user_id for user_id $user_id");
                } else {
                    error_log("[wp_update_usermeta] Failed to insert qd_user_id: " . mysqli_error($wp_db_conn));
                }
            }
            unset($meta_data['qd_user_id']);
        } else {
            error_log("[wp_update_usermeta] No valid qd_user_id found for user_id $user_id");
        }

        // Process other meta keys
        foreach ($meta_data as $meta_key => $meta_value) {
            $safe_key = mysqli_real_escape_string($wp_db_conn, $meta_key);
            $safe_value = $prepare_value($meta_value);

            $check = "SELECT umeta_id FROM " . wp_table('usermeta') . " WHERE user_id = $user_id AND meta_key = '$safe_key' LIMIT 1";
            $result = mysqli_query($wp_db_conn, $check);

            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $umeta_id = intval($row['umeta_id']);
                $update = "UPDATE " . wp_table('usermeta') . " SET meta_value = '$safe_value' WHERE umeta_id = $umeta_id";
                if (mysqli_query($wp_db_conn, $update)) {
                    error_log("[wp_update_usermeta] Updated meta '$meta_key' for user_id $user_id");
                } else {
                    error_log("[wp_update_usermeta] Failed to update '$meta_key': " . mysqli_error($wp_db_conn));
                }
            } else {
                $insert = "INSERT INTO " . wp_table('usermeta') . " (user_id, meta_key, meta_value) VALUES ($user_id, '$safe_key', '$safe_value')";
                if (mysqli_query($wp_db_conn, $insert)) {
                    error_log("[wp_update_usermeta] Inserted meta '$meta_key' for user_id $user_id");
                } else {
                    error_log("[wp_update_usermeta] Failed to insert '$meta_key': " . mysqli_error($wp_db_conn));
                }
            }
        }

        // Also ensure wo_user_id mapping is created/updated (similar to assets logic)
        try {
            // Avoid recursion if meta key already was wo_user_id
            // Attempt to get the WoWonder ID by looking up Wo_Users via user's email
            $q = "SELECT user_email FROM " . wp_table('users') . " WHERE ID = $user_id LIMIT 1";
            $res = mysqli_query($wp_db_conn, $q);
            if ($res && $row = mysqli_fetch_assoc($res)) {
                $user_email = $row['user_email'];
                $ww_db_conn = get_wowonder_db();
                if ($ww_db_conn) {
                    $email_esc = mysqli_real_escape_string($ww_db_conn, $user_email);
                    $ww_query = "SELECT user_id FROM Wo_Users WHERE email='$email_esc' LIMIT 1";
                    $ww_result = mysqli_query($ww_db_conn, $ww_query);
                    if ($ww_result && mysqli_num_rows($ww_result) > 0) {
                        $ww_row = mysqli_fetch_assoc($ww_result);
                        $wowonder_user_id = intval($ww_row['user_id']);
                        $check_q = "SELECT umeta_id, meta_value FROM " . wp_table('usermeta') . " WHERE user_id = $user_id AND meta_key = 'wo_user_id' LIMIT 1";
                        $check_result = mysqli_query($wp_db_conn, $check_q);
                        if ($check_result && mysqli_num_rows($check_result) > 0) {
                            $existing = mysqli_fetch_assoc($check_result);
                            if ($existing['meta_value'] != $wowonder_user_id) {
                                $update = "UPDATE " . wp_table('usermeta') . " SET meta_value = '$wowonder_user_id' WHERE umeta_id = {$existing['umeta_id']}";
                                mysqli_query($wp_db_conn, $update);
                            }
                        } else {
                            $insert = "INSERT INTO " . wp_table('usermeta') . " (user_id, meta_key, meta_value) VALUES ($user_id, 'wo_user_id', '$wowonder_user_id')";
                            mysqli_query($wp_db_conn, $insert);
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            error_log("[wp_update_usermeta] WoWonder mapping check failed: " . $e->getMessage());
        }

        return true;
    }
}

/* --------------------------------------------------------------------------
 * xProfile update (insert or update)
 * -------------------------------------------------------------------------- */
if (!function_exists('wp_update_xprofile_field')) {
    function wp_update_xprofile_field($sqlConnectOrUserId, $maybeUserId = null, $maybeField = null, $maybeValue = null) {
        // Support both signatures:
        // - wp_update_xprofile_field($sqlConnect, $user_id, $field_name, $field_value)
        // - wp_update_xprofile_field($user_id, $field_name, $field_value)   (uses get_wp_db_conn)
        if ($maybeValue === null) {
            // either called with (user_id, field, value) or (sqlConnect, user_id, field)
            if ($maybeField === null) {
                return false; // too few args
            }
            if ($sqlConnectOrUserId instanceof mysqli) {
                $sqlConnect = $sqlConnectOrUserId;
                $user_id = intval($maybeUserId);
                $field_name = $maybeField;
                $field_value = $maybeValue;
            } else {
                // ($user_id, $field_name, $field_value)
                $sqlConnect = get_wp_db_conn();
                $user_id = intval($sqlConnectOrUserId);
                $field_name = $maybeUserId;
                $field_value = $maybeField;
            }
        } else {
            $sqlConnect = $sqlConnectOrUserId;
            $user_id = intval($maybeUserId);
            $field_name = $maybeField;
            $field_value = $maybeValue;
        }

        if (!$sqlConnect || $user_id <= 0) return false;
        $field_name = mysqli_real_escape_string($sqlConnect, $field_name);
        $field_id_query = "SELECT id FROM " . bp_table('xprofile_fields') . " WHERE name = '$field_name' LIMIT 1";
        $field_id_result = mysqli_query($sqlConnect, $field_id_query);
        if (!$field_id_result || mysqli_num_rows($field_id_result) == 0) return false;
        $field_id_row = mysqli_fetch_assoc($field_id_result);
        $field_id = intval($field_id_row['id']);
        $exists_query = "SELECT id FROM " . bp_table('xprofile_data') . " WHERE user_id = $user_id AND field_id = $field_id LIMIT 1";
        $exists_result = mysqli_query($sqlConnect, $exists_query);
        $field_value_esc = mysqli_real_escape_string($sqlConnect, $field_value);

        if ($exists_result && mysqli_num_rows($exists_result) > 0) {
            $row = mysqli_fetch_assoc($exists_result);
            $update = "UPDATE " . bp_table('xprofile_data') . " SET value = '$field_value_esc' WHERE id = {$row['id']}";
            return mysqli_query($sqlConnect, $update);
        } else {
            $insert = "INSERT INTO " . bp_table('xprofile_data') . " (field_id, user_id, value, last_updated) VALUES ($field_id, $user_id, '$field_value_esc', NOW())";
            return mysqli_query($sqlConnect, $insert);
        }
    }
}

/* --------------------------------------------------------------------------
 * QuickDate helpers
 * -------------------------------------------------------------------------- */

/**
 * Get QuickDate user ID by email (compat: qd_get_user_id_by_email / get_quickdate_id_by_email)
 */
if (!function_exists('qd_get_user_id_by_email')) {
    function qd_get_user_id_by_email($email) {
        $conn = get_qd_db_conn();
        if (!$conn) return false;
        $email = $conn->real_escape_string($email);
        $sql = "SELECT id FROM " . QD_USERS_TABLE . " WHERE email = '$email' LIMIT 1";
        $result = $conn->query($sql);
        if (!$result || $result->num_rows == 0) return false;
        $row = $result->fetch_assoc();
        return (int) $row['id'];
    }
}
if (!function_exists('get_quickdate_id_by_email')) {
    function get_quickdate_id_by_email($email) { return qd_get_user_id_by_email($email); }
}

/**
 * Get QuickDate table columns (cached)
 */
if (!function_exists('qd_get_columns')) {
    function qd_get_columns($conn, $table) {
        static $cache = [];
        if (isset($cache[$table])) return $cache[$table];
        $columns = [];
        $result = $conn->query("SHOW COLUMNS FROM `$table`");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            $cache[$table] = $columns;
        }
        return $columns;
    }
}

/**
 * Update QuickDate user by email (fields must be valid columns)
 * Signature kept: qd_update_user($email, $data)
 */
if (!function_exists('qd_update_user')) {
    function qd_update_user($email, $data) {
        $conn = get_qd_db_conn();
        if (!$conn) return false;
        $user_id = qd_get_user_id_by_email($email);
        if (!$user_id) return false;
        $columns = qd_get_columns($conn, QD_USERS_TABLE);
        $set = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $columns)) {
                error_log("[qd_update_user] Skipping unknown field: $key");
                continue;
            }
            $escaped_value = $conn->real_escape_string($value);
            $set[] = "`$key` = '$escaped_value'";
        }
        if (empty($set)) {
            error_log("[qd_update_user] No valid fields to update for user $user_id");
            return false;
        }
        $sql = "UPDATE `" . QD_USERS_TABLE . "` SET " . implode(',', $set) . " WHERE id = $user_id";
        $result = $conn->query($sql);
        if ($result) return true;
        error_log("[qd_update_user] Update failed: " . $conn->error);
        return false;
    }
}

/* --------------------------------------------------------------------------
 * WoWonder helpers
 * -------------------------------------------------------------------------- */

/**
 * Get WoWonder user id by email
 */
if (!function_exists('ww_get_user_id_by_email')) {
    function ww_get_user_id_by_email($email) {
        $conn = get_wowonder_db();
        if (!$conn) return false;
        $email = mysqli_real_escape_string($conn, $email);
        $sql = "SELECT user_id FROM Wo_Users WHERE email = '$email' LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['user_id'];
        }
        return false;
    }
}

/* --------------------------------------------------------------------------
 * Cross-platform sync functions
 * -------------------------------------------------------------------------- */

/**
 * Sync QuickDate user array to WordPress (usermeta + xprofile) and WoWonder.
 * Signature preserved: sync_quickdate_to_wordpress($quickdate_user)
 */
if (!function_exists('sync_quickdate_to_wordpress')) {
    function sync_quickdate_to_wordpress($quickdate_user) {
        $wpDb = get_wp_db_conn();
        if (!$wpDb) return false;
        // Load metadata
        $metadata = get_user_field_metadata();
        $wp_usermeta_fields = array_keys($metadata['private_secure_fields']);
        $wp_xprofile_fields = array_keys($metadata['public_open_fields']);

        // Determine target WP user
        $wp_user_id = isset($quickdate_user['wp_user_id']) ? intval($quickdate_user['wp_user_id']) : 0;
        if (!$wp_user_id && !empty($quickdate_user['email'])) {
            $wp_user = wp_get_user_by_login_or_email($wpDb, $quickdate_user['email']);
            if ($wp_user && isset($wp_user['ID'])) $wp_user_id = intval($wp_user['ID']);
        }
        if (!$wp_user_id) return false;

        // Update usermeta
        foreach ($wp_usermeta_fields as $meta_key) {
            if (isset($quickdate_user[$meta_key])) {
                $meta_value = $quickdate_user[$meta_key];
                wp_update_usermeta($wpDb, $wp_user_id, $meta_key, $meta_value);
            }
        }

        // Update xprofile
        foreach ($wp_xprofile_fields as $xprofile_field) {
            if (isset($quickdate_user[$xprofile_field])) {
                $field_value = $quickdate_user[$xprofile_field];
                wp_update_xprofile_field($wpDb, $wp_user_id, $xprofile_field, $field_value);
            }
        }

        // Also sync to WoWonder
        sync_quickdate_to_wowonder($quickdate_user);
        return true;
    }
}

/**
 * Sync QuickDate user array to WoWonder DB
 */
if (!function_exists('sync_quickdate_to_wowonder')) {
    function sync_quickdate_to_wowonder($quickdate_user) {
        $wwDb = get_wowonder_db();
        if (!$wwDb) return false;
        $metadata = get_user_field_metadata();
        $ww_private_fields = $metadata['private_secure_fields'];
        $ww_public_fields = $metadata['public_open_fields'];

        $ww_user_id = isset($quickdate_user['wo_user_id']) ? (int)$quickdate_user['wo_user_id'] : ww_get_user_id_by_email($quickdate_user['email'] ?? '');
        if (!$ww_user_id) {
            error_log("[sync_quickdate_to_wowonder] Could not find WoWonder user for QuickDate email: " . ($quickdate_user['email'] ?? ''));
            return false;
        }

        $set_clause = [];
        foreach ($ww_private_fields as $qd_key => $ww_key) {
            if (isset($quickdate_user[$qd_key])) {
                $value = $wwDb->real_escape_string($quickdate_user[$qd_key]);
                $set_clause[] = "`$ww_key` = '$value'";
            }
        }
        foreach ($ww_public_fields as $qd_key => $ww_key) {
            if (isset($quickdate_user[$qd_key])) {
                $value = $wwDb->real_escape_string($quickdate_user[$qd_key]);
                $set_clause[] = "`$ww_key` = '$value'";
            }
        }
        if (empty($set_clause)) {
            error_log("[sync_quickdate_to_wowonder] No fields to update for user_id $ww_user_id");
            return false;
        }
        $set_clause_str = implode(', ', $set_clause);
        $sql = "UPDATE Wo_Users SET $set_clause_str WHERE user_id = $ww_user_id LIMIT 1";
        $result = $wwDb->query($sql);
        if ($result) {
            error_log("[sync_quickdate_to_wowonder] Updated WoWonder user_id $ww_user_id");
            return true;
        } else {
            error_log("[sync_quickdate_to_wowonder] Failed to update WoWonder user_id $ww_user_id: " . $wwDb->error . " SQL: $sql");
            return false;
        }
    }
}

/**
 * Called by WordPress after updating usermeta/xprofile -> will sync to QuickDate
 * Signature: sync_user_to_quickdate($wp_user_email, $usermeta_data = [], $xprofile_data = [])
 */
if (!function_exists('sync_user_to_quickdate')) {
    function sync_user_to_quickdate($wp_user_email, $usermeta_data = [], $xprofile_data = []) {
        $qd_data = [];
        // Prefer xprofile values for profile details
        foreach ($xprofile_data as $key => $value) {
            if ($value !== null && $value !== '') {
                if (in_array($key, ['avatar', 'cover']) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    if (strpos($value, '../streams/') !== 0) {
                        $value = '../streams/' . ltrim($value, '/');
                    }
                }
                $qd_data[$key] = $value;
            }
        }
        // Include usermeta values only if not already set
        foreach ($usermeta_data as $key => $value) {
            if (!isset($qd_data[$key]) && $value !== null && $value !== '') {
                if (in_array($key, ['avatar', 'cover']) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    if (strpos($value, '../streams/') !== 0) {
                        $value = '../streams/' . ltrim($value, '/');
                    }
                }
                $qd_data[$key] = $value;
            }
        }
        if (empty($qd_data)) {
            error_log("[sync_user_to_quickdate] No data to sync for $wp_user_email");
            return false;
        }
        return qd_update_user($wp_user_email, $qd_data);
    }
}

/* --------------------------------------------------------------------------
 * Helper: get_quickdate_user_id_by_wp_email (compat)
 * -------------------------------------------------------------------------- */
if (!function_exists('get_quickdate_user_id_by_wp_email')) {
    function get_quickdate_user_id_by_wp_email($wp_user_id) {
        $wp_db = get_wp_db_conn();
        $wp_user_id = (int) $wp_user_id;
        if (!$wp_db || $wp_user_id <= 0) {
            error_log("[get_quickdate_user_id_by_wp_email] Invalid parameters.");
            return false;
        }
        $query = "SELECT user_email FROM " . wp_table('users') . " WHERE ID = $wp_user_id LIMIT 1";
        $result = mysqli_query($wp_db, $query);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $wp_user_email = mysqli_real_escape_string($wp_db, $row['user_email']);
        } else {
            error_log("[get_quickdate_user_id_by_wp_email] Failed to retrieve WP email for user_id $wp_user_id");
            return false;
        }
        $qd_db = get_qd_db_conn();
        if (!$qd_db) {
            error_log("[get_quickdate_user_id_by_wp_email] Failed to connect to QuickDate DB");
            return false;
        }
        $query_qd = "SELECT id FROM users WHERE email = '$wp_user_email' LIMIT 1";
        $result_qd = mysqli_query($qd_db, $query_qd);
        if ($result_qd && $row_qd = mysqli_fetch_assoc($result_qd)) {
            return (int)$row_qd['id'];
        } else {
            error_log("[get_quickdate_user_id_by_wp_email] No QuickDate user found with email $wp_user_email");
            return false;
        }
    }
}

/* --------------------------------------------------------------------------
 * Endpoint / simple router for remote systems (QuickDate, WoWonder) to call
 * - Authenticated by BUZZ_SSO_SECRET (from environment via db_helpers)
 * - Supports single and bulk operations
 *
 * Actions:
 *  - sync_quickdate_to_wordpress      (POST) body: { quickdate_user: { ... } }
 *  - bulk_sync_quickdate              (POST) body: { users: [ { ... }, ... ] }
 *  - qd_update_user                   (POST) body: { email: "...", data: { ... } }
 *  - qd_bulk_update                   (POST) body: { updates: [ { email: "...", data: { ... } }, ... ] }
 *
 * NOTE: This endpoint is optional. If you prefer to wire your own endpoints or call functions directly,
 *       you can skip using the HTTP interface and call the functions above from other code.
 * -------------------------------------------------------------------------- */

function _wwqd_get_request_secret() {
    // Accept secret in POST 'sso_secret' or Authorization: Bearer <secret>
    $secret = null;
    if (!empty($_POST['sso_secret'])) $secret = $_POST['sso_secret'];
    if (!$secret && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $secret = trim($matches[1]);
        }
    }
    // Also accept JSON body request with sso_secret when content-type is application/json
    if (!$secret && in_array($_SERVER['CONTENT_TYPE'] ?? '', ['application/json', 'application/json; charset=utf-8'])) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (is_array($data) && isset($data['sso_secret'])) $secret = $data['sso_secret'];
    }
    return $secret;
}

function _wwqd_validate_secret($provided) {
    if (!defined('BUZZ_SSO_SECRET')) return false;
    if (!$provided) return false;
    return hash_equals((string)BUZZ_SSO_SECRET, (string)$provided);
}

// Only run HTTP router when accessed directly (not included) and request has action
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action = $_REQUEST['action'] ?? ($_GET['action'] ?? null);
    if ($action) {
        // Validate secret
        $provided = _wwqd_get_request_secret();
        if (!_wwqd_validate_secret($provided)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: $_POST;

        switch ($action) {
            case 'sync_quickdate_to_wordpress':
                if (empty($payload['quickdate_user'])) {
                    echo json_encode(['success' => false, 'error' => 'Missing quickdate_user']);
                    exit;
                }
                $ok = sync_quickdate_to_wordpress($payload['quickdate_user']);
                echo json_encode(['success' => (bool)$ok]);
                exit;

            case 'bulk_sync_quickdate':
                if (empty($payload['users']) || !is_array($payload['users'])) {
                    echo json_encode(['success' => false, 'error' => 'Missing users array']);
                    exit;
                }
                $results = [];
                foreach ($payload['users'] as $u) {
                    $results[] = (bool) sync_quickdate_to_wordpress($u);
                }
                echo json_encode(['success' => true, 'results' => $results]);
                exit;

            case 'qd_update_user':
                if (empty($payload['email']) || empty($payload['data'])) {
                    echo json_encode(['success' => false, 'error' => 'Missing email or data']);
                    exit;
                }
                $ok = qd_update_user($payload['email'], $payload['data']);
                echo json_encode(['success' => (bool)$ok]);
                exit;

            case 'qd_bulk_update':
                if (empty($payload['updates']) || !is_array($payload['updates'])) {
                    echo json_encode(['success' => false, 'error' => 'Missing updates array']);
                    exit;
                }
                $results = [];
                foreach ($payload['updates'] as $u) {
                    if (empty($u['email']) || empty($u['data'])) { $results[] = false; continue; }
                    $results[] = (bool) qd_update_user($u['email'], $u['data']);
                }
                echo json_encode(['success' => true, 'results' => $results]);
                exit;

            default:
                echo json_encode(['success' => false, 'error' => 'Unknown action']);
                exit;
        }
    }
}

/* --------------------------------------------------------------------------
 * End of file
 * -------------------------------------------------------------------------- */