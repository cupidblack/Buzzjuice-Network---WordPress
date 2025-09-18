<?php





/**
 * init from ?dzsap_wave_regenerate=on
 */
function dzsap_adminPageSystemCheckWaves__generateWaveformTool($systemCheckTrackId) {


  ?>

  <div class="setting">

    <h4 class="setting-label"><?php echo esc_html__("Flash"); ?><?php echo esc_html__("Tool"); ?></h4>


    <?php


    global $dzsap;


    $lab = 'track_id';
    $val = '';

    if (isset($systemCheckTrackId)) {
      $val = $systemCheckTrackId;
    }


    $uriQueryId = DZSZoomSoundsHelper::sanitize_toKey($systemCheckTrackId);


    $src = wp_get_attachment_url($uriQueryId);


    if (isset($_GET['track_source'])) {
      $flash_src = $_GET['track_source'];
    } else {
      $flash_src = $src;
    }

    $aux = '';

    $urlIframe = admin_url() . 'admin.php';

    // -- admin.php?page=dzsap-mo&dzsap_wave_regenerate=on&disable_isShowTrackInfo=off&track_id=265&track_url=http%3A%2F%2F192.168.0.107%3A81%2Fwp2%2Fwp-content%2Fuploads%2F2020%2F12%2Fpreview.mp3
    $urlIframe = add_query_arg('page', DZSAP_ADMIN_PAGENAME_MAINOPTIONS, $urlIframe);
    $urlIframe = add_query_arg(DZSAP_ADMIN_PAGENAME_MAINOPTIONS_WAVE_GENERATOR, 'on', $urlIframe);
    $urlIframe = add_query_arg('disable_isShowTrackInfo', 'off', $urlIframe);
    $urlIframe = add_query_arg('track_id', $uriQueryId, $urlIframe);

    $aux .= '<iframe class="regenerate-waveform-iframe" src="
' . $urlIframe . '" width="100%" height="530"></iframe>';


    echo $aux;

    ?>
  </div>


  <div
    class="sidenote"><?php echo esc_html__("copy the text from above in the box below to overwrite pcm data"); ?>
    - <?php echo sprintf(esc_html__("in order to save the pcm data fro mthe flash tool, click the text above and press %s ( %s ) "), "ctrl + a", esc_html__("Select All")); ?></div>

  <div class="setting">

    <h4 class="setting-label"><?php echo esc_html__("PCM"); ?><?php echo esc_html__("Data"); ?></h4>
    <?php

    $lab = DZSAP_DB_PCM_DATA;


    $val = get_option($lab . '_' . $uriQueryId);
    echo DZSHelpers::generate_input_textarea($lab, array(
      'seekval' => $val,
      'extraattr' => ' data-id="' . $uriQueryId . '" style="width: 100%;" rows="5" ',
    ));
    ?>

    <button name="dzsap_save_pcm" value="on"
            class="button-secondary"><?php echo esc_html__('Save PCM Data From Textarea', DZSAP_ID); ?></button>
  </div>
  <?php
  // -- end analyzing
}

/**
 * init from ?dzsap_wave_regenerate=on
 */
function dzsap_adminPageSystemCheckWaves() {
  ?>
  <div class="wrap"><h3><?php
    echo esc_html__("Audit Waveforms");
    ?></h3>


  <a class="button-secondary" href="<?php
  echo admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_MAINOPTIONS); ?>">← <?php echo esc_html__("Back to Main Options"); ?></a>

  <?php

  include_once(DZSAP_BASE_PATH . 'inc/php/admin/admin-systemCheck-wave-check-for-every-file.php');
  dzsap_admin_systemCheck_wavesCheckEachFileInit();


  $searchedVal = '';
  if (isset($_GET['track_search']) && $_GET['track_search']) {
    $searchedVal = $_GET['track_search'];
  }
  $temp = '';
  $temp = '<h4 style="margin-bottom: 5px;">' . esc_html__('Search a track for waves check') . '</h4>';
  $temp .= '<form class="dzs-big-search--con" action="' . admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_HIDDEN_SYSTEM_CHECK_WAVES) . '">';
  $temp .= '<input type="hidden" name="page" value="' . DZSAP_ADMIN_PAGENAME_HIDDEN_SYSTEM_CHECK_WAVES . '"/>';
  $temp .= '<input type="hidden" name="tab" value="14"/>';
  $temp .= '<input class="dzs-big-input" id="' . DZSAP_COOKIENAME_SYSTEM_CHECK_WAVES . '--search" name="track_search" type="search" value="' . $searchedVal . '" placeholder="' . esc_html__('Search tracks', DZSAP_ID) . '"/><button class="dzs--settings-search--search-icon">
          ' . dzs_read_from_file_ob(DZSAP_BASE_PATH . 'assets/svg/search.svg') . '        </button>';
  $temp .= '</form>';
  echo $temp;
  ?>









<div class="dzstoggle toggle1<?php

$systemCheckTrackId = '';
$systemCheckTrackSource = '';

if (isset($_GET['track_id']) && $_GET['track_id']) {
  $systemCheckTrackId = $_GET['track_id'];
}

?>" rel="">
  <div class="toggle-title" style=""><?php echo esc_html__('Analyze track data', DZSAP_ID); ?></div>
  <div class="toggle-content" style="<?php

  if ($systemCheckTrackId) {
    echo 'height: auto;';
  }

  ?>">


    <div
      class="sidenote"><?php echo esc_html__("Analyze wave data or generate wave data for a single track."); ?></div>

    <form action="<?php echo admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_HIDDEN_SYSTEM_CHECK_WAVES); ?>"
          method="get">
      <div class="setting">

        <h4 class="setting-label"><?php echo esc_html__("Track"); ?><?php echo esc_html__("Id"); ?></h4>

        <?php


        $lab = 'page';
        echo DZSHelpers::generate_input_text($lab, array(
          'seekval' => DZSAP_ADMIN_PAGENAME_HIDDEN_SYSTEM_CHECK_WAVES,
          'input_type' => 'hidden',
        ));

        $lab = 'track_id';
        $val = '';

        if ($systemCheckTrackId) {
          $val = $systemCheckTrackId;
        }
        echo DZSHelpers::generate_input_text($lab, array('seekval' => $val));
        ?>
        <div class="sidenote"><?php echo esc_html__("get track by id or source"); ?></div>
      </div>
      <div class="setting">

        <h4 class="setting-label"><?php echo esc_html__("Get pcm from url"); ?></h4>

        <?php


        $lab = 'track_source';
        $val = '';

        if (isset($_GET[$lab])) {
          $val = $_GET[$lab];
        }
        echo DZSHelpers::generate_input_text($lab, array('seekval' => $val));
        ?>
        <div
          class="sidenote"><?php echo esc_html__("( donor mp3 ) ( optional ) get pcm data from another mp3 url"); ?></div>
      </div>
      <button class="button-secondary" name="dzsap_action"
              value="generate_wave"><?php echo esc_html__("Get Track Data"); ?></button>

    </form>

    <?php


    if ($systemCheckTrackId) {
      dzsap_adminPageSystemCheckWaves__generateWaveformTool($systemCheckTrackId);
    }

    ?>

  </div><!-- end toggle content -->

  </div>
  <!-- end analyze track data -->


  <div class="feedbacker" style=""><img alt="" style="" id="save-ajax-loading2"
                                        src="<?php echo site_url(); ?>/wp-admin/images/wpspin_light.gif"/>
  </div>


  <?php
  wp_enqueue_script('wavesurfer', DZSAP_BASE_URL . 'audioplayer/wavesurfer.js');
  $js_url = DZSAP_BASE_URL . 'audioplayer/dzsap-wave-generator.js';
  if (defined('DZSAP_DEBUG_LOCAL_SCRIPTS') && DZSAP_DEBUG_LOCAL_SCRIPTS === true) {
    $js_url = 'http://devsite/zoomsounds/source/audioplayer/dzsap-wave-generator.js';
  }
  wp_enqueue_script('dzsap-regenerate-waveform', $js_url);

}

