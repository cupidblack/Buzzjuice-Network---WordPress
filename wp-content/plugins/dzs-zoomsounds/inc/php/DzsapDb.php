<?php
include_once(DZSAP_BASE_PATH . 'inc/php/view-functions/view-embed-functions.php');
include_once(DZSAP_BASE_PATH . 'inc/php/shortcodes/shortcode-player.php');

class DzsapDb {
  /** @var DzsAudioPlayer */
  public $dzsap;

  /**
   * DzsapView constructor.
   * @param DzsAudioPlayer $dzsap
   */
  function __construct($dzsap) {

    $this->dzsap = $dzsap;


  }

  static function isColumnExists($tableNameType = 'analytics', $columnName = 'country') {

    global $wpdb;
    $table_name = $wpdb->prefix . DZSAP_ANALYTICS_TABLE_NAME;


    $query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=\'' . DB_NAME . '\' AND TABLE_NAME=\'' . $table_name . '\' AND column_name=%s';


    try {

      $val = $wpdb->query($wpdb->prepare($query, $columnName));


      if ($val !== FALSE) {
        if ($val === 1 || (!is_numeric($val) && $val->num_rows > 0)) {
          return true;
        }
      }
    } catch (Exception $exception) {
      error_log('[dzsap] [warning] ' . print_r($exception, true));
    }

    return false;

  }

  function repair_tableAnalytics($tableNameType = 'analytics') {


    global $wpdb;
    $table_name = $wpdb->prefix . DZSAP_ANALYTICS_TABLE_NAME;


    $isNeededRepair = false;


    if (!DzsapDb::isColumnExists('analytics', 'country')) {

      $query = 'ALTER TABLE ' . $table_name . ' ADD `country` mediumtext NULL ;';
      $val = $wpdb->query($query);
      $isNeededRepair = true;
    }
    if (!DzsapDb::isColumnExists('analytics', 'val')) {

      $query = 'ALTER TABLE `' . $table_name . '` ADD `val` int(255) NULL ;';
      $val = $wpdb->query($query);
      $isNeededRepair = true;
    }

    // -- v1.1
    if (!DzsapDb::isColumnExists('analytics', 'metadata')) {

      $query = 'ALTER TABLE `' . $table_name . '` ADD `metadata` json NULL ;';
      $val = $wpdb->query($query);
      $isNeededRepair = true;
    }


    if ($isNeededRepair) {
      echo 'table repaired!';
      update_option(DZSAP_DBNAME_ANALYTICS_VERSION, DZSAP_ANALYTICS_TABLE_CURRENT_VERSION);
    } else {
      echo 'table was already okay';
    }
  }
  function get_tableVersion($tableNameType = 'analytics') {

    $v = floatval(get_option(DZSAP_DBNAME_ANALYTICS_VERSION));

    if (!$v) {
      $v = 1;
    }

    return $v;

  }

  function get_tableFromDb($tableNameType = 'analytics') {

    global $wpdb;

    $table_name = $wpdb->prefix . DZSAP_ANALYTICS_TABLE_NAME;


    $var = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");

    return $var;
  }


  /**
   * table not in database. Create new table
   * @return void
   */
  function analytics_table_create() {

    global $wpdb, $dzsap;

    $table_name = $wpdb->prefix . DZSAP_ANALYTICS_TABLE_NAME;
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $charset_collate = $wpdb->get_charset_collate();

      $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          type varchar(100) NOT NULL,
          country varchar(100) NULL,
          id_user int(10) NOT NULL,
          val int(255) NOT NULL,
          ip varchar(255) NOT NULL,
          id_video int(10) NOT NULL,
          date datetime NOT NULL,
          `metadata` json NOT NULL
          UNIQUE KEY id (id)
     ) $charset_collate;";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);


      $dzsap->mainoptions['analytics_table_created'] = 'on';;
      update_option(DZSAP_DBNAME_OPTIONS, $dzsap->mainoptions);
      update_option(DZSAP_DBNAME_ANALYTICS_VERSION, DZSAP_ANALYTICS_TABLE_CURRENT_VERSION);

    } else {
    }

  }


}