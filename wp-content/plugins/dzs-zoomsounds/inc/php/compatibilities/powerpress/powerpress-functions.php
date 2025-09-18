<?php

function dzsap_powerpress_filter_content($fout) {

  global $post, $powerpress_feed;

  global $dzsap;


// PowerPress settings:
  $GeneralSettings = get_option('powerpress_general');


  $feed_slug = 'podcast';


  $EpisodeData = null;
  if (function_exists('powerpress_get_enclosure_data')) {

    $EpisodeData = powerpress_get_enclosure_data($post->ID, $feed_slug);
  }


  if ($EpisodeData && isset($EpisodeData['url'])) {


    $dzsap->sliders__player_index++;


    $src = get_post_meta($post->ID, DZSAP_META_WOOCOMMERCE_ZOOMSOUNDS_MP3, true);


    $dzsap->front_scripts();

    $margs = dzsap_powerpress_generate_margs();


    $args = array();

    $margs['autoplay'] = 'off';
    $aux = $dzsap->classView->shortcode_player($margs);


    return $aux . $fout;


  }

  return $fout;
}


function dzsap_powerpress_get_enclosure_data($feed_slug) {
  global $post, $dzsap;

  $EpisodeData = powerpress_get_enclosure_data($post->ID, $feed_slug);


  if ($EpisodeData && isset($EpisodeData['url'])) {


    $dzsap->sliders__player_index++;


    $src = get_post_meta($post->ID, DZSAP_META_WOOCOMMERCE_ZOOMSOUNDS_MP3, true);


    $dzsap->front_scripts();

    $margs = dzsap_powerpress_generate_margs();


    $enc_margs = json_encode($margs);
    $enc_margs = base64_encode(json_encode($margs));


    $embed_url = site_url() . '?action=embed_zoomsounds&type=player&margs=' . urlencode($enc_margs);
    $embed_code = '<iframe src=\'' . $embed_url . '\' style="overflow:hidden; transition: height 0.3s ease-out;" width="100%" height="180" scrolling="no" frameborder="0"></iframe>';


    ?>
    <meta name="twitter:card" content="player">
    <meta name="twitter:site" content="@youtube">
    <meta name="twitter:url" content="<?php echo get_permalink($post->ID); ?>">
    <meta name="twitter:title" content="<?php echo get_permalink($post->post_title); ?>">
    <meta name="twitter:description" content="<?php echo get_permalink($post->post_content); ?>">
    <meta name="twitter:image" content="">
    <meta name="twitter:app:name:iphone" content="<?php echo get_permalink($post->ID); ?>">
    <meta name="twitter:app:name:googleplay" content="<?php echo get_permalink($post->post_title); ?>">
    <meta name="twitter:player" content="<?php echo $embed_url; ?>">
    <meta name="twitter:player:width" content="1280">
    <meta name="twitter:player:height" content="300"><?php


  }
}
