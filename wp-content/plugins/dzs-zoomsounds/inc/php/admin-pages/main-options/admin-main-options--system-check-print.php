<?php

function dzsap_admin_main_options__page_system_check_print() {

  global $dzsap;


  ?>


  <div class="setting">
    <h4 class="setting-label"><?php echo esc_html__("Audit Waveforms"); ?></h4>
    <?php


    ?>
    <a class="button-secondary" href="<?php
    echo admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_HIDDEN_SYSTEM_CHECK_WAVES); ?>"><?php echo esc_html__("Audit Waveforms"); ?></a>
  </div>


  <div class="setting">
    <h4 class="setting-label"><strong>GetText</strong> <?php echo esc_html__("Support"); ?></h4>
    <?php
    DZSZoomSoundsHelper::adminSystemCheckSupportedOrNotEcho(function_exists("gettext"));
    ?>
    <div class="sidenote"><?php echo esc_html__('translation support'); ?></div>
  </div>


  <div class="setting">
    <h4 class="setting-label">ZipArchive <?php echo esc_html__("Support"); ?></h4>
    <?php
    DZSZoomSoundsHelper::adminSystemCheckSupportedOrNotEcho(class_exists("ZipArchive"));
    ?>
    <div class="sidenote"><?php echo esc_html__('zip making for album download support'); ?></div>
  </div>

  <div class="setting">
    <h4 class="setting-label">Curl <?php echo esc_html__("Support"); ?></h4>
    <?php
    DZSZoomSoundsHelper::adminSystemCheckSupportedOrNotEcho(function_exists('curl_version'));
    ?>
    <div class="sidenote"><?php echo esc_html__('for making youtube / vimeo api calls'); ?></div>
  </div>

  <div class="setting">
    <h4 class="setting-label">allow_url_fopen <?php echo esc_html__("Support"); ?></h4>
    <?php
    DZSZoomSoundsHelper::adminSystemCheckSupportedOrNotEcho(ini_get('allow_url_fopen'));
    ?>

    <div class="sidenote"><?php echo esc_html__('for making youtube / vimeo api calls'); ?></div>
  </div>


  <div class="setting">

    <h4 class="setting-label"><?php echo esc_html__("PHP Version"); ?></h4>

    <div class="setting-text-ok">
      <?php
      echo phpversion();
      ?>
    </div>

    <div
      class="sidenote"><?php echo esc_html__('the install php version, 5.4 or greater required for facebook api'); ?></div>
  </div>


  <div class="setting">
    <h4 class="setting-label"><?php echo esc_html__("Server Information", DZSAP_ID); ?></h4>

    <table>
      <tbody>
      <tr>
        <td><?php echo esc_html__("Server Information", DZSAP_ID) ?></td>
        <td><?php echo print_r($_SERVER['SERVER_ADDR']) ?></td>
      </tr>
      <tr>
        <td><?php echo esc_html__("getenv(\"HOME\")", DZSAP_ID) ?></td>
        <td><?php echo print_r(getenv("HOME", true)) ?></td>
      </tr>
      <tr>
        <td><?php echo esc_html__("ABSPATH", DZSAP_ID) ?></td>
        <td><?php echo print_r(ABSPATH, true) ?></td>
      </tr>
      <tr>
        <td><?php echo esc_html__("site_url()", DZSAP_ID) ?></td>
        <td><?php echo print_r(site_url(), true) ?></td>
      </tr>
      </tbody>
    </table>

    <div class="sidenote"><?php echo esc_html__('server info'); ?></div>
  </div>


  <div class="setting">
    <h4 class="setting-label"><?php echo esc_html__("Permissions check"); ?></h4>


    <?php
    $role = get_role('administrator');
    $role->add_cap(DZSAP_TAXONOMY_NAME_SLIDERS . '_manage_categories');
    $role->add_cap('dzsap_manage_vpconfigs');
    ?>

    <?php
    $cap = DZSAP_TAXONOMY_NAME_SLIDERS . '_manage_categories';
    ?>
    <div class="permission-check-div">
      <strong class="permission"><?php
        echo $cap;
        ?>
      </strong> -
      <span class="label">

                            <?php
                            if (current_user_can($cap) || current_user_can('manage_options')) {
                              echo '<span class="setting-text-ok"><i class="fa fa-check"></i> ' . '' . esc_html__("allowed", DZSAP_ID) . '</span>';
                            } else {

                              echo '<span class="setting-text-notok"><i class="fa fa-times"></i> ' . '' . esc_html__("not allowed", DZSAP_ID) . '</span>';
                            }
                            ?>
                                </span>
    </div>

    <?php
    $cap = 'dzsap_manage_options';
    ?>
    <div class="permission-check-div">
      <strong class="permission"><?php
        echo $cap;
        ?>
      </strong> -
      <span class="label">

                            <?php
                            if (current_user_can('manage_options')) {
                              $role->add_cap($cap);
                            }
                            if (current_user_can($cap) || current_user_can('manage_options')) {
                              echo '<span class="setting-text-ok"><i class="fa fa-check"></i> ' . '' . esc_html__("allowed", DZSAP_ID) . '</span>';
                            } else {

                              echo '<span class="setting-text-notok"><i class="fa fa-times"></i> ' . '' . esc_html__("not allowed", DZSAP_ID) . '</span>';
                            }
                            ?>
                                </span>
    </div>

    <?php
    $cap = 'dzsap_make_shortcode';
    ?>
    <div class="permission-check-div">
      <strong class="permission"><?php
        echo $cap;
        ?>
      </strong> -
      <span class="label">

                            <?php
                            if (current_user_can('manage_options')) {
                              $role->add_cap($cap);
                            }
                            if (current_user_can($cap) || current_user_can('manage_options')) {
                              echo '<span class="setting-text-ok"><i class="fa fa-check"></i> ' . '' . esc_html__("allowed", DZSAP_ID) . '</span>';
                            } else {

                              echo '<span class="setting-text-notok"><i class="fa fa-times"></i> ' . '' . esc_html__("not allowed", DZSAP_ID) . '</span>';
                            }
                            ?>
                                </span>
    </div>

    <?php
    $cap = 'dzsap_manage_vpconfigs';
    ?>
    <div class="permission-check-div">
      <strong class="permission"><?php
        echo $cap;
        ?>
      </strong> -
      <span class="label">

                            <?php
                            if (current_user_can($cap) || current_user_can('manage_options')) {
                              echo '<span class="setting-text-ok"><i class="fa fa-check"></i> ' . '' . esc_html__("allowed", DZSAP_ID) . '</span>';
                            } else {

                              echo '<span class="setting-text-notok"><i class="fa fa-times"></i> ' . '' . esc_html__("not allowed", DZSAP_ID) . '</span>';
                            }
                            ?>
                </span>
    </div>


  </div>


  <div class="setting">

    <h4 class="setting-label"><?php echo esc_html__("Analytics table status"); ?></h4>
    <?php


    global $wpdb;

    $table_name = $wpdb->prefix . DZSAP_ANALYTICS_TABLE_NAME;


    $mainOptions = $dzsap->mainoptions;
    $isAnalyticsEnabled = isset($mainOptions['analytics_enable']) && $mainOptions['analytics_enable'] == 'on';

    if ($isAnalyticsEnabled && $dzsap->instanceDb->get_tableFromDb() != $table_name) {

      echo '<div class="setting-text-notok error">' . '' . esc_html__("analytics table not installed, go to System Check > Repair Table", DZSAP_ID) . '</div>';
    } else {
      echo '<div class="setting-text-ok"><i class="fa fa-check"></i> ' . '' . esc_html__("table ok", DZSAP_ID) . '</div>';

      $currentV = $dzsap->instanceDb->get_tableVersion();

      if ($currentV >= DZSAP_ANALYTICS_TABLE_CURRENT_VERSION) {

        echo '<div class="setting-text-ok"><strong>' . '' . esc_html__("version", DZSAP_ID) . '</strong>: ' . $dzsap->instanceDb->get_tableVersion() . '</div>';
      } else {

        echo '<div class="notice notice-warning inline"><strong>' . '' . esc_html__("version", DZSAP_ID) . '</strong>: ' . $dzsap->instanceDb->get_tableVersion() . ' / latest version: ' . DZSAP_ANALYTICS_TABLE_CURRENT_VERSION . ' - ' . esc_html__("please repair", DZSAP_ID) . '</div>';
      }


      echo '<p class=""><a class="button-secondary repair-table" href="' . admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_MAINOPTIONS . '&tab=14&' . DZSAP_ANALYTICS_QUERY_REPAIR . '=on') . '">' . esc_html__("repair table") . '</a></p>';


      echo '<p class=""><a class="button-secondary" href="' . admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_MAINOPTIONS . '&tab=14&show_analytics_table_last_10_rows=on') . '">' . esc_html__("check last 10 rows") . '</a></p>';


      if (isset($_GET['show_analytics_table_last_10_rows']) && $_GET['show_analytics_table_last_10_rows'] == 'on') {

        $query = 'SELECT * FROM ' . $table_name . ' ORDER BY id DESC LIMIT 10';
        $results = $GLOBALS['wpdb']->get_results($query, OBJECT);

        print_rr($results);
      }

    }
    ?>

    <?php
    if (ini_get('allow_url_fopen')) {
    } else {

    }
    ?>

    <div class="sidenote"><?php echo esc_html__('check if the analytics table exists'); ?></div>
  </div>


  <div class="setting">

    <h4 class="setting-label"><?php echo esc_html__("Wp_options table status"); ?></h4>
    <?php


    global $wpdb;

    $table_name = $wpdb->prefix . 'options';


    echo '<p class=""><a class="button-secondary" href="' . admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_MAINOPTIONS . '&tab=14&show_wp_options_table_last_10_rows=on') . '">' . esc_html__("check last 10 rows") . '</a></p>';


    if (isset($_GET['show_wp_options_table_last_10_rows']) && $_GET['show_wp_options_table_last_10_rows'] == 'on') {

      $query = 'SELECT * FROM ' . $table_name . ' WHERE option_name LIKE "dzsap_%" ORDER BY option_id DESC LIMIT 10';
      $results = $GLOBALS['wpdb']->get_results($query, OBJECT);

      print_rr($results);
    }
    ?>

    <?php
    if (ini_get('allow_url_fopen')) {
    } else {

    }
    ?>

    <div class="sidenote"><?php echo esc_html__('check if the analytics table exists'); ?></div>
  </div>


  <div class="setting">

    <h4 class="setting-label"><?php echo esc_html__("Backup log"); ?></h4>

    <pre><?php
      $logged_backups = array();
      try {

        $logged_backups = json_decode(get_option('dzsap_backuplog'), true);
      } catch (Exception $err) {

      }

      if (!is_array($logged_backups)) {
        $logged_backups = array();
      }


      foreach ($logged_backups as $lb) {
        echo date("F j, Y, g:i a", $lb) . '<br>';
      }
      ?></pre>
  </div>


  <?php
}