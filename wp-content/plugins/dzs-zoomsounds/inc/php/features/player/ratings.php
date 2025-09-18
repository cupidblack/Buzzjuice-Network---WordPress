<?php
function dzsap_player_generateRatings($playerId){

  $outputRatings = '';


  // -- 1 to 5
  $decInitialRateIndex = get_post_meta($playerId, DZSAP_VIEW_PLAYER_FEATURE_RATINGS_METANAME_RATEINDEX, true);

  // -- 1 to 5
  if ($decInitialRateIndex == '') {
    $decInitialRateIndex = 0;
  } else {
    $decInitialRateIndex = floatval($decInitialRateIndex) / 5;
  }
  if ($decInitialRateIndex > 5) {
    $decInitialRateIndex = 5;
  }

  $percInitialRateIndex = floatval(($decInitialRateIndex) * 100);


  $outputRatings .= '<div class="star-rating-con" data-initial-rating-index="' . $decInitialRateIndex . '">';


  $stringStars = '<span class="star-rating-bg "><span class="rating-inner">{{starssvg}}</span></span>';


  $stringStars .= '<div class=\'star-rating-clip star-rating-set-clip\' style=\'width: ' . $percInitialRateIndex . '%;\'>
                  <div class=\'star-rating-prog\'>{{starssvg}}</div>
                </div>';
  $stringStars .= '<div class=\'star-rating-clip star-rating-prog-clip\' style=\'\'>
                  <div class=\'star-rating-prog\'>{{starssvg}}</div>
                </div>';


  $stringStars = str_replace('{{starssvg}}', '&#9733;&#9733;&#9733;&#9733;&#9733;', $stringStars);

  $outputRatings .= $stringStars;
  $outputRatings .= '</div>';


  wp_enqueue_script('dzsap-part-ratings', DZSAP_BASE_URL . 'audioplayer/parts/star-ratings/dzsap-star-ratings.js', array(), DZSAP_VERSION);
  wp_enqueue_style('dzsap-part-ratings', DZSAP_BASE_URL . 'audioplayer/parts/star-ratings/dzsap-star-ratings.css', array(), DZSAP_VERSION);

  return $outputRatings;
}