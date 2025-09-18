<?php

function dzsap_admin_systemCheck_wavesCheckEachFileInit() {
  // todo: add search + pagination
  global $dzsap;
  $currentPageIndex = 1;
  if (isset($_GET['paged']) && $_GET['paged']) {
    $currentPageIndex = $_GET['paged'];
  }
  $searchedVal = '';
  if (isset($_GET['track_search']) && $_GET['track_search']) {
    $searchedVal = $_GET['track_search'];
  }

  $getAudioAttachmentsArgs = array
  (
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'post_mime_type' => 'audio',
    'paged' => $currentPageIndex,
    'posts_per_page' => '3',
    'cache_results' => true,
  );
  if ($searchedVal) {


    if (is_numeric($searchedVal)) {

      $getAudioAttachmentsArgs['p'] = $searchedVal;
    } else {

      $getAudioAttachmentsArgs['s'] = $searchedVal;
      $getAudioAttachmentsArgs['s_meta_keys'] = array('short_desc', 'post_title', 'post_id', 'post_content', 'ID');
    }
  }
  $audioQuery = new WP_Query($getAudioAttachmentsArgs);


  $fout = '';
  $fout .= '<hr>';


  $audioPosts = $audioQuery->posts;


  if (count($audioPosts)) {
    foreach ($audioPosts as $file) {
      $trackUrl = wp_get_attachment_url($file->ID);

      $fout .= '<h3>' . $file->post_title . '</h3>';

      $fout .= $dzsap->classView->shortcode_player(array(
        'source' => $file->ID
      ));

      $fout .= '<button style="margin-top:10px;" class="button-secondary systemCheck_waves_check-regenerate-waveform" data-file_id="' . $file->ID . '" data-track_url="' . $trackUrl . '">' . esc_html__('Regenerate waveform', DZSAP_ID) . '</button>';


    }

  } else {

    echo esc_html__('no audio posts found', DZSAP_ID);
    $argsForPcm = array(
      'playerid' => $searchedVal,
    );

    echo '<br><iframe class="regenerate-waveform-iframe" src="'.admin_url('admin.php?page='.DZSAP_ADMIN_PAGENAME_MAINOPTIONS.'&'.DZSAP_ADMIN_PAGENAME_MAINOPTIONS_WAVE_GENERATOR.'=on&disable_isShowTrackInfo=on&track_id='.$searchedVal).'" width="100%" height="400"></iframe>';

  }
  $fout .= '<br><br>';
  $fout .= dzs_pagination($audioQuery->max_num_pages, 2, array(
    'paged' => $currentPageIndex,
  ));
  $fout .= '<hr>';

  echo $fout;
}