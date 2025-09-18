<?php


function dzsap_db_getPcmData($pcmId, $pcmSource) {

  $dbPcm = '';
  $dbPcmId = '';
  $dbPcmSource = '';

  if ($pcmId) {
    $dbPcmId = (DZSZoomSoundsHelper::sanitize_toKey($pcmId));
    $dbPcm = get_option(DZSAP_DB_PCM_DATA . '_' . $dbPcmId);
  }



  if (!$pcmSource) {
    $pcmSource = dzsap_db_pcmFindInPairsFromId($pcmId);
  }

  if ($pcmSource) {
    $dbPcmSource = (DZSZoomSoundsHelper::sanitize_toKey($pcmSource));
    if (!$dbPcm) {
      $dbPcm = get_option(DZSAP_DB_PCM_DATA . '_' . $dbPcmSource);
    }
  }



  return array(
    'dbPcm' => $dbPcm,
    'dbPcmId' => DZSAP_DB_PCM_DATA . '_' . $dbPcmId,
    'dbPcmSource' => DZSAP_DB_PCM_DATA . '_' . $dbPcmSource,
  );


}

function dzsap_db_pcmFindInPairsFromId($trackId){

  $pcmSource = '';
  $arr_pcm_to_id_links = get_option(DZSAP_DBNAME_PCM_LINKS);


  if($arr_pcm_to_id_links && is_array($arr_pcm_to_id_links)){
    foreach ($arr_pcm_to_id_links as $key => $pcmLink) {
      if (DZSZoomSoundsHelper::sanitize_toKey($key) === DZSZoomSoundsHelper::sanitize_toKey($trackId)) {
        $pcmSource = $pcmLink;
        break;
      }
    }
  }
  return $pcmSource;
}

function dzsap_db_savePcmData($pcmData, $pcmId, $forceUrlTrackId, $pcmSource) {

  $dbPcmDataKey = '';
  $isPcmSourceFromArgs = false;

  if ($pcmId) {
    $dbPcmDataKey = DZSAP_DB_PCM_DATA . '_' . (DZSZoomSoundsHelper::sanitize_toKey($pcmId));
    // -- update the id
    update_option($dbPcmDataKey, $pcmData, false);
  }
  if (isset($pcmSource) && $pcmSource) {
    $isPcmSourceFromArgs = true;
  }




  if (!(isset($pcmSource) && $pcmSource)) {
    $pcmSource = dzsap_db_pcmFindInPairsFromId($pcmId);
  }

  if (isset($pcmSource) && $pcmSource) {
    $pcmSourceUrlId = DZSZoomSoundsHelper::sanitize_toKey($pcmSource);

    $dbPcmDataKeySource = DZSAP_DB_PCM_DATA . '_' . $pcmSourceUrlId;
    $pcmData = stripslashes($pcmData);
    update_option($dbPcmDataKeySource, $pcmData, false);


    $arr_pcm_to_id_links = array();
    if (get_option(DZSAP_DBNAME_PCM_LINKS)) {
      $arr_pcm_to_id_links = get_option(DZSAP_DBNAME_PCM_LINKS);
    }

    if ($forceUrlTrackId) {
      $pcmSourceUrlId = $forceUrlTrackId;
    }

    if ($pcmSourceUrlId && $isPcmSourceFromArgs) {
      $arr_pcm_to_id_links[$pcmId] = $pcmSourceUrlId;
      update_option(DZSAP_DBNAME_PCM_LINKS, $arr_pcm_to_id_links, false);
    }


    // -- if we have source then just link to id
  }
}