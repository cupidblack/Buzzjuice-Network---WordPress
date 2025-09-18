<?php

if (!function_exists('dzs_hexInvert')) {

  /**
   * @param $color string color in hex color
   * @return string inverse hex
   */
  function dzs_hexInvert($color) {
    $color = trim($color);
    $prependHash = false;
    // -- remember and remove hash
    if (strpos($color, '#') !== false) {
      $prependHash = true;
      $color = str_replace('#', '', $color);
    }

    $len = strlen($color);
    if ($len == 3 || $len == 6) {
      if ($len == 3) $color = preg_replace('/(.)(.)(.)/', "\\1\\1\\2\\2\\3\\3", $color);
    } else {
      return '';
    }
    if (!preg_match('/^[a-f0-9]{6}$/i', $color)) {
      return '';
    }

    $r = dechex(255 - hexdec(substr($color, 0, 2)));
    $r = (strlen($r) > 1) ? $r : '0' . $r;
    $g = dechex(255 - hexdec(substr($color, 2, 2)));
    $g = (strlen($g) > 1) ? $g : '0' . $g;
    $b = dechex(255 - hexdec(substr($color, 4, 2)));
    $b = (strlen($b) > 1) ? $b : '0' . $b;

    return ($prependHash ? '#' : '') . $r . $g . $b;
  }

}
