<?php

function dzsap_compat_aws_installAws() {
  global $dzsap;


  $FILE_LOCATION = DZSAP_BASE_PATH . '/aws.zip';
  $res = dzs_get_contents(DZSAP_PLUGINS_AMAZON_S3_INSTALL_URI);


  if (!$res) {
    echo 'server offline';
  } else {
    if (strpos($res, '<div class="error">') === 0) {
      echo $res;


      if (strpos($res, '<div class="error">error: in progress') === 0) {

        $dzsap->mainoptions['dzsap_purchase_code_binded'] = 'on';
        update_option(DZSAP_DBNAME_OPTIONS, $dzsap->mainoptions);
      }
    } else {

      file_put_contents($FILE_LOCATION, $res);
      if (class_exists('ZipArchive')) {
        $zip = new ZipArchive;
        $res = $zip->open($FILE_LOCATION);

        if ($res === TRUE) {

          $zip->extractTo(DZSAP_BASE_PATH.'class_parts');
          $zip->close();


        } else {
          echo 'failed, code:' . $res;
        }
        echo esc_html__('Update installed.');
      } else {

        echo esc_html__('ZipArchive class not found.');
      }

    }
  }
}