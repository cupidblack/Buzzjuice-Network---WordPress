<?php

/**
 * *deprecated
 * @param $currDb
 * @return void
 */
function dzsap_legacy_sliders__getSliders($currDb){

  global $dzsap;

  // -- LEGACY
  // ---------------- deprecated


  if (isset($_GET[DZSAP_LEGACY_SLIDERS__GET_CURRSLIDER])) {
    $dzsap->currSlider = $_GET[DZSAP_LEGACY_SLIDERS__GET_CURRSLIDER];
  } else {
    $dzsap->currSlider = 0;
  }


  $dzsap->dbs = get_option(DZSAP_DBNAME_LEGACY_DBS);

  if ($dzsap->dbs == '') {
    $dzsap->dbs = array('main');
    update_option(DZSAP_DBNAME_LEGACY_DBS, $dzsap->dbs);
  }
  if (is_array($dzsap->dbs) && !in_array($currDb, $dzsap->dbs) && $currDb != 'main' && $currDb != '') {
    array_push($dzsap->dbs, $currDb);
    update_option(DZSAP_DBNAME_LEGACY_DBS, $dzsap->dbs);
  }


  $dbname_mainitems = DZSAP_DBNAME_MAINITEMS;
  if ($currDb != 'main' && $currDb != '') {
    $dbname_mainitems .= '-' . $currDb;
  }

  $dzsap->mainitems = get_option($dbname_mainitems);


  if (is_array($dzsap->mainitems) == false) {
    $dzsap->mainitems = array();

    update_option(DZSAP_DBNAME_MAINITEMS, $dzsap->mainitems);
  }


}