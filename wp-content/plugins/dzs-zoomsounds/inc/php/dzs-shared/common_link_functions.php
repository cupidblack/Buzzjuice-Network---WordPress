<?php


if (!function_exists('dzsCommon_isAbsoluteWebLink')) {
  /**
   * @param string $linkUri
   * @return bool
   */
  function dzsCommon_isAbsoluteWebLink($linkUri){
    if(strpos($linkUri, 'http:') === 0 || strpos($linkUri, 'https:') === 0){
      return true;
    }

    return false;
  }
}
if (!function_exists('dzsCommon_getPathFromLink')) {
  /**
   * @param string $fileUrl
   * @param string $base_siteUrl
   * @param string $base_path
   * @return string|null null if no match or file does not exist
   */
  function dzsCommon_getPathFromLink($fileUrl, $base_siteUrl, $base_path) {

    $filePathForDownload = $fileUrl;
    if (strpos($fileUrl, $base_siteUrl) !== false) {
      $filePathForDownload = str_replace($base_siteUrl . '/', $base_path, $fileUrl);
    }

    if (file_exists($filePathForDownload)) {
      return $filePathForDownload;
    }

    return null;
  }
}
