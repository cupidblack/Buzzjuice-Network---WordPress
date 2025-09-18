<?php


function dzsap_admin_page_vpc() {
  global $dzsap;


  $id_for_preview_player = 'newconfig';

  /** @var number $targetSliderId */
  $targetSliderId = null;
  $targetSliderId = $dzsap->currSlider;


  // -- sliders admin video config
  ?>
  <div class="wrap <?php

  if (isset($_GET[DZSAP_LEGACY_SLIDERS__GET_CURRSLIDER_FINDER])) {
    echo 'legacySliders--found-by-slug';
  }

  ?>">
    <div class="import-export-db-con">
      <div class="the-toggle">
        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
             width="45.973px" height="45.973px" viewBox="0 0 45.973 45.973" style="enable-background:new 0 0 45.973 45.973;"
             xml:space="preserve">
<g>
  <g>
    <path d="M43.454,18.443h-2.437c-0.453-1.766-1.16-3.42-2.082-4.933l1.752-1.756c0.473-0.473,0.733-1.104,0.733-1.774
			c0-0.669-0.262-1.301-0.733-1.773l-2.92-2.917c-0.947-0.948-2.602-0.947-3.545-0.001l-1.826,1.815
			C30.9,6.232,29.296,5.56,27.529,5.128V2.52c0-1.383-1.105-2.52-2.488-2.52h-4.128c-1.383,0-2.471,1.137-2.471,2.52v2.607
			c-1.766,0.431-3.38,1.104-4.878,1.977l-1.825-1.815c-0.946-0.948-2.602-0.947-3.551-0.001L5.27,8.205
			C4.802,8.672,4.535,9.318,4.535,9.978c0,0.669,0.259,1.299,0.733,1.772l1.752,1.76c-0.921,1.513-1.629,3.167-2.081,4.933H2.501
			C1.117,18.443,0,19.555,0,20.935v4.125c0,1.384,1.117,2.471,2.501,2.471h2.438c0.452,1.766,1.159,3.43,2.079,4.943l-1.752,1.763
			c-0.474,0.473-0.734,1.106-0.734,1.776s0.261,1.303,0.734,1.776l2.92,2.919c0.474,0.473,1.103,0.733,1.772,0.733
			s1.299-0.261,1.773-0.733l1.833-1.816c1.498,0.873,3.112,1.545,4.878,1.978v2.604c0,1.383,1.088,2.498,2.471,2.498h4.128
			c1.383,0,2.488-1.115,2.488-2.498v-2.605c1.767-0.432,3.371-1.104,4.869-1.977l1.817,1.812c0.474,0.475,1.104,0.735,1.775,0.735
			c0.67,0,1.301-0.261,1.774-0.733l2.92-2.917c0.473-0.472,0.732-1.103,0.734-1.772c0-0.67-0.262-1.299-0.734-1.773l-1.75-1.77
			c0.92-1.514,1.627-3.179,2.08-4.943h2.438c1.383,0,2.52-1.087,2.52-2.471v-4.125C45.973,19.555,44.837,18.443,43.454,18.443z
			 M22.976,30.85c-4.378,0-7.928-3.517-7.928-7.852c0-4.338,3.55-7.85,7.928-7.85c4.379,0,7.931,3.512,7.931,7.85
			C30.906,27.334,27.355,30.85,22.976,30.85z"/>
  </g>
</g>
</svg>
      </div>
      <div class="the-content-mask" style="">

        <div class="the-content">


          <form enctype="multipart/form-data" action="" method="POST">
            <div class="one_half">
              <h3><?php echo esc_html__('Import config', 'dzsap'); ?></h3>
              <input name="importsliderupload" type="file" size="10"/><br/>
            </div>
            <div class="one_half last alignright">
              <input class="button-secondary" type="submit" name="dzsap_import_config" value="Import"/>
            </div>
            <div class="clear"></div>
          </form>


          <div class="clear"></div>

        </div>
      </div>
    </div>
    <h2>DZS <?php echo esc_html__('ZoomSounds Admin', 'dzsap'); ?> <img alt="" style="visibility: visible;"
                                                                        id="main-ajax-loading"
                                                                        src="<?php bloginfo('wpurl'); ?>/wp-admin/images/wpspin_light.gif"/>
    </h2>
    <noscript><?php echo esc_html__('You need javascript for this.', 'dzsap'); ?></noscript>
    <div class="top-buttons">
      <a rel="nofollow" href="<?php echo DZSAP_BASE_URL; ?>readme/index.html"
         class="button-secondary action"><?php echo esc_html__('Documentation', 'dzsap'); ?></a>

    </div>
    <table cellspacing="0" class="wp-list-table widefat dzs_admin_table main_sliders">
      <thead>
      <tr>
        <th style="" class="manage-column column-name" id="name"
            scope="col"><?php echo esc_html__('ID', 'dzsap'); ?></th>
        <th class="column-edit"><?php echo esc_html__("Edit", 'dzsap'); ?></th>
        <th class="column-edit"><?php echo esc_html__("Embed", 'dzsap'); ?></th>
        <th class="column-edit"><?php echo esc_html__("Export", 'dzsap'); ?></th>
        <th class="column-edit"><?php echo esc_html__("Duplicate", 'dzsap'); ?></th>

        <th class="column-edit">Delete</th>
      </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <?php
    $url_add = '';
    $url_add = '';
    $vpConfigs = $dzsap->mainitems_configs;


    $aux = remove_query_arg('deleteslider', dzs_curr_url());
    $aux = admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_LEGACY_SLIDERS_ADMIN_VPCONFIGS . '&adder=adder');
    $params = array('currslider' => count($vpConfigs));
    $url_add = add_query_arg($params, $aux);


    // -- if NOT then it is
    if (isset($vpConfigs[$targetSliderId]['settings']['id'])) {
      $id_for_preview_player = ($vpConfigs[$targetSliderId]['settings']['id']);
    }
    ?>
    <a rel="nofollow" class="button-secondary add-slider"
       href="<?php echo $url_add; ?>"><?php echo esc_html__('Add Configuration', 'dzsap'); ?></a>
    <form class="master-settings only-settings-con mode_vpconfigs">
    </form>
    <div class="saveconfirmer"><?php echo esc_html__('Loading...', 'dzsap'); ?></div>
    <a rel="nofollow" href="#" class="button-primary master-save-vpc"></a> <img alt=""
                                                                                style="position:fixed; bottom:18px; right:125px; visibility: hidden;"
                                                                                id="save-ajax-loading"
                                                                                src="<?php bloginfo('wpurl'); ?>/wp-admin/images/wpspin_light.gif"/>

    <a rel="nofollow" href="#"
       class="button-primary master-save-vpc"><?php echo esc_html__('Save All Configs', 'dzsap'); ?></a>
    <a rel="nofollow" href="#"
       class="button-primary slider-save-vpc"><?php echo esc_html__('Save Config', 'dzsap'); ?></a>


  </div>
  <?php

  ob_start();


  $aux = str_replace(array("\r", "\r\n", "\n"), '', $dzsap->videoplayerconfig);
  $aux = str_replace(array("'"), '&quot;', $aux);
  echo "var videoplayerconfig = '" . $aux . "';
    ";
  ?>
  'use strict';
  jQuery(document).ready(function ($) {
  sliders_ready($);
  if ($.fn.multiUploader) {
  $('.dzs-multi-upload').multiUploader();
  }
  <?php
  $vpConfigs = $dzsap->mainitems_configs;

  for ($i = 0; $i < count($vpConfigs); $i++) {


    $aux = '';
    if (isset($vpConfigs[$i]) && isset($vpConfigs[$i]['settings']) && isset($vpConfigs[$i]['settings']['id'])) {

      if ($vpConfigs[$i]['settings']['id'] == DZSAP_VPCONFIGS_PREVIEW_IFRAME_VPCONFIG_NAME) {
        continue;
      }
      $vpConfigs[$i]['settings']['id'] = str_replace('"', '', $vpConfigs[$i]['settings']['id']);
      $aux = '{ name: "' . $vpConfigs[$i]['settings']['id'] . '"}';
    }
    echo "dzs_legacy_slidersAddSlider(" . $aux . ");";
  }
  if (count($vpConfigs) > 0)
    echo 'dzs_legacy_slidersShowSlider(0);';
  for ($i = 0; $i < count($vpConfigs); $i++) {
    if (($dzsap->mainoptions['is_safebinding'] != 'on' || $i == $targetSliderId) && isset($vpConfigs[$i]) && is_array($vpConfigs[$i])) {

      //==== jsi is the javascript I, if safebinding is on then the jsi is always 0 ( only one gallery )
      $jsi = $i;
      if ($dzsap->mainoptions['is_safebinding'] == 'on') {
        $jsi = 0;
      }

      for ($j = 0; $j < count($vpConfigs[$i]) - 1; $j++) {
        echo "dzs_legacy_slidersAddItem(" . $jsi . ");";
      }


      foreach ($vpConfigs[$i] as $label => $value) {
        if ($label === 'settings') {
          if (is_array($vpConfigs[$i][$label])) {
            foreach ($vpConfigs[$i][$label] as $sublabel => $subvalue) {
              $subvalue = (string)$subvalue;
              $subvalue = stripslashes($subvalue);
              $subvalue = str_replace(array("\r", "\r\n", "\n", '\\', "\\"), '', $subvalue);
              $subvalue = str_replace(array("'"), '"', $subvalue);
              $subvalue = str_replace(array("</script>"), '{{scriptend}}', $subvalue);
              // -- only settings
              echo 'dzs_legacy_slidersChange(' . $jsi . ', "settings", "' . $sublabel . '", ' . "'" . $subvalue . "'" . ');';
            }
          }
        } else {

          if (is_array($vpConfigs[$i][$label])) {
            foreach ($vpConfigs[$i][$label] as $sublabel => $subvalue) {
              $subvalue = (string)$subvalue;
              $subvalue = stripslashes($subvalue);
              $subvalue = str_replace(array("\r", "\r\n", "\n", '\\', "\\"), '', $subvalue);
              $subvalue = str_replace(array("'"), '"', $subvalue);
              $subvalue = str_replace(array("</script>"), '{{scriptend}}', $subvalue);
              if ($label == '') {
                $label = '0';
              }


              echo 'dzs_legacy_slidersChange(' . $jsi . ', ' . $label . ', "' . $sublabel . '", ' . "'" . $subvalue . "'" . ');';
            }
          }
        }
      }
      if ($dzsap->mainoptions['is_safebinding'] == 'on') {
        break;
      }
    }
  }
  ?>
  jQuery('#main-ajax-loading').css('visibility', 'hidden');
  if (dzsap_settings.is_safebinding == "on") {
  jQuery('.master-save-vpc').remove();
  if (dzsap_settings.addslider == "on") {

  dzs_legacy_slidersAddSlider();
  window.currSlider_nr = -1
  dzs_legacy_slidersShowSlider(0);
  }
  jQuery('.slider-in-table').each(function () {

  });
  }
  dzs_legacy_slidersViewWarningTooMany();
  sliders_allready();
  });

  <?php
  $scriptString = ob_get_clean();


  wp_register_script('dzsap-script-hook', '');
  wp_enqueue_script('dzsap-script-hook');
  wp_add_inline_script('dzsap-script-hook', $scriptString);
}