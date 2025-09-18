<?php

include_once(DZSAP_BASE_PATH . 'inc/php/view-functions/helpers/view-html-generators.php');
/**
 * generate html for meta-artist
 * @param array $singleItemInstance
 * @param array $singlePlayerOptions
 * @return string
 */
function dzsap_view_player_generateMetaArtist($singleItemInstance, $singlePlayerOptions) {

  $isHavingArtistName = false;

  $meta_artist_html = '';

  if ((isset($singleItemInstance['artistname']) && $singleItemInstance['artistname']) || (isset($singleItemInstance['songname']) && $singleItemInstance['songname']) || $singlePlayerOptions['called_from'] == 'footer_player') {

    $metaArtistLink = '';

    $meta_artist_html .= '<div class="meta-artist track-meta-for-dzsap">';
    $meta_artist_html .= '<span class="the-artist first-line">';
    if ($singleItemInstance['artistname']) {
      $isHavingArtistName = true;


      $artistName_link = '';
      if (isset($singleItemInstance['artistname_link'])) {
        $artistName_link = $singleItemInstance['artistname_link'];
      }
      $meta_artist_html .= dzs_view_generateHtmlForAnchorOrStatic($singleItemInstance['artistname'], $artistName_link, array(
        'viewClass' => 'first-line-label'
      ));
    }
    if (isset($vpConfig['settings_extrahtml_after_artist'])) {
      $meta_artist_html .= dzs_esc__(do_shortcode($vpConfig['settings_extrahtml_after_artist']));
    }
    $meta_artist_html .= '</span>';


    if ($singleItemInstance['songname'] != '' || $singleItemInstance['called_from'] == 'footer_player') {

      $meta_artist_html .= dzs_view_generateHtmlForAnchorOrStatic($singleItemInstance['songname'], false, array(
        'viewClass' => 'the-name the-songname second-line'
      ));
    }

    $meta_artist_html .= '</div>';
  }

  return $meta_artist_html;
}

/**
 * replace output
 * @param $singlePlayerOptions
 * @param $meta_artist_html
 * @param $pcmString
 * @param $fakeplayer_attr
 * @param $thumb_for_parent_attr
 * @param $thumb_link_attr
 * @return string|string[]
 */
function dzsap_view_player_generateCustomHtml($singlePlayerOptions, $meta_artist_html, $pcmString, $fakeplayer_attr, $thumb_for_parent_attr, $thumb_link_attr) {

  $i_fout = DZSZoomSoundsHelper::sanitize_from_meta_textarea($singlePlayerOptions['settings_extrahtml_in_player']);

  $i_fout = str_replace('{{artist_complete_html}}', $meta_artist_html, $i_fout);


  $lab = 'source';

  if (isset($singleItemInstance[$lab])) {
    $i_fout = str_replace('{{' . $lab . '}}', $singleItemInstance[$lab], $i_fout);
  } else {

    $i_fout = str_replace('{{' . $lab . '}}', '', $i_fout);
  }
  $lab = 'type';

  if (isset($singleItemInstance[$lab])) {
    $i_fout = str_replace('{{' . $lab . '}}', $singleItemInstance[$lab], $i_fout);
  } else {

    $i_fout = str_replace('{{' . $lab . '}}', '', $i_fout);
  }

  $lab = 'thumb';

  if (isset($singleItemInstance[$lab])) {
    $i_fout = str_replace('{{' . $lab . '}}', $singleItemInstance[$lab], $i_fout);
  } else {

    $i_fout = str_replace('{{' . $lab . '}}', '', $i_fout);
  }
  $lab = 'pcm';

  $i_fout = str_replace('{{' . $lab . '}}', $pcmString, $i_fout);

  $lab = 'fakeplayer_attr';
  $i_fout = str_replace('{{' . $lab . '}}', $fakeplayer_attr, $i_fout);

  $lab = 'thumb_for_parent_attr';
  $i_fout = str_replace('{{' . $lab . '}}', $thumb_for_parent_attr, $i_fout);

  $lab = 'thumb_link';
  $i_fout = str_replace('{{' . $lab . '}}', $thumb_link_attr, $i_fout);

  return $i_fout;
}


/**
 * @param array $its
 * @param array $singleItemInstance
 * @param string $audioplayerClasses
 * @param $singlePlayerOptions
 * @param $str_tw
 * @param DzsAudioPlayer $dzsap
 * @param $thumb_for_parent_attr
 * @param $thumb_link_attr
 * @param string $pcmString
 * @param string $type
 * @param $fakeplayer_attr
 * @param $meta_artist_html
 * @param array $playlistOptions
 * @param bool $isPlayerIdFake
 * @return string
 */
function dzsap_view_parseItems__generateStructure($its, $singleItemInstance, $audioplayerClasses, $singlePlayerOptions, $str_tw, $dzsap, $thumb_for_parent_attr, $thumb_link_attr, $pcmString, $type, $fakeplayer_attr, &$meta_artist_html, $playlistOptions, $isPlayerIdFake) {

  $dzsapView = $dzsap->classView;


  $postType = '';


  if ($singleItemInstance['wpPlayerPostId']) {
    $linkedPostWpPlayer = get_post($singleItemInstance['wpPlayerPostId']);

    if ($linkedPostWpPlayer) {
      if ($linkedPostWpPlayer->post_type) {
        $postType = $linkedPostWpPlayer->post_type;
      }
    }

    if ($postType) {
      $singleItemInstance['post_type'] = $postType;
    }
  }

  if (isset($singleItemInstance['dzsap_meta_source_attachment_id']) && $singleItemInstance['dzsap_meta_source_attachment_id']) {
  } else {
    // -- try to get dzsap_meta_source_attachment_id if it's a dzsap_item
    if ($singleItemInstance['wpPlayerPostId']) {
      if (get_post_meta($singleItemInstance['wpPlayerPostId'], 'dzsap_meta_source_attachment_id', true)) {
        $singleItemInstance['dzsap_meta_source_attachment_id'] = get_post_meta($singleItemInstance['wpPlayerPostId'], 'dzsap_meta_source_attachment_id', true);
      }
    }
  }


  if ($dzsap->mainoptions['try_to_get_id3_thumb_in_frontend'] == 'on') {
    if (isset($singleItemInstance['dzsap_meta_source_attachment_id']) && $singleItemInstance['dzsap_meta_source_attachment_id']) {

      if (!(isset($singleItemInstance['thumb']) && $singleItemInstance['thumb'])) {

        // -- get base64 data in frontend
        $file = get_attached_file($singleItemInstance['dzsap_meta_source_attachment_id']);

        include_once(ABSPATH . 'wp-admin/includes/media.php');
        $metadata = wp_read_audio_metadata($file);

        if ($metadata && isset($metadata['image']) && isset($metadata['image']['data'])) {
          $singleItemInstance['thumb'] = 'data:image/jpeg;base64,' . base64_encode($metadata['image']['data']);
        }

      }

      if (!(isset($singleItemInstance['artistname']) && $singleItemInstance['artistname'])) {
        $file = get_attached_file($singleItemInstance['dzsap_meta_source_attachment_id']);
        include_once(ABSPATH . 'wp-admin/includes/media.php');
        $metadata = wp_read_audio_metadata($file);
      }
    }
  }

  if (isset($singleItemInstance['thumb_for_parent']) && $singleItemInstance['thumb_for_parent']) {
    $thumb_for_parent_attr .= ' data-thumb_for_parent="' . $singleItemInstance['thumb_for_parent'] . '"';
  };
  $isSourceFake = isset($singlePlayerOptions['source']) && $singlePlayerOptions['source'] == 'fake';


  // -- -----
  // -- -------- start output

  $outputItem = '';


  // -- parse the item
  $outputItem .= '<div class="' . $audioplayerClasses;
  $outputItem .= '" ';
  // -- end class


  if ($singlePlayerOptions['auto_init_player_options']) {
    $outputItem .= ' data-options=\'' . $singlePlayerOptions['auto_init_player_options'] . '\'';
  }

  $outputItem .= ' style="';
  if ($singlePlayerOptions['called_from'] == 'player') {
    $outputItem .= ' opacity: 0; ';
  }
  $outputItem .= '' . $str_tw . '';
  $outputItem .= '"';

  if ($singleItemInstance['wpPlayerPostId']) {
    if ($postType) {
      $outputItem .= ' data-posttype="' . $postType . '"';
    }
  }

  if (isset($singleItemInstance['product_id']) && $singleItemInstance['product_id']) {

    $outputItem .= ' data-product_id="' . $singleItemInstance['product_id'] . '"';
  }

  if (isset($singleItemInstance['type_normal_stream_type']) && $singleItemInstance['type_normal_stream_type']) {
    $outputItem .= ' data-streamtype="' . DZSZoomsoundsHelper::sanitizeForShortcodeAttr($singleItemInstance['type_normal_stream_type']) . '"';
  }


  if ($dzsap->mainoptions['analytics_track_only_once'] == 'on' && dzsap_check_if_user_played_track($singleItemInstance['playerId_computed']) === true) {
    $outputItem .= ' data-viewsubmitted="on"';
  }


  if ($singleItemInstance['playerId_computed'] != '') {


    if ($singlePlayerOptions['called_from'] == 'footer_player') {

      $outputItem .= ' id="' . DZSAP_VIEW_STICKY_PLAYER_ID . '"';
    } else {
      $outputItem .= ' id="ap' . $singleItemInstance['playerId_computed'] . '"';
    }

    $outputItem .= ' data-playerid="' . $singleItemInstance['playerId_computed'] . '"';
    $outputItem .= ' data-computed-playerid="' . $singleItemInstance['playerId_computed'] . '"';

    if (isset($singleItemInstance['wpPlayerPostId']) && $singleItemInstance['wpPlayerPostId'] != '') {

      $outputItem .= ' data-real-playerid="' . $singleItemInstance['wpPlayerPostId'] . '"';
    }
  };

  $outputItem .= ' data-sanitized_source="' . DZSZoomSoundsHelper::sanitize_toKey($singleItemInstance['source']) . '"';


  $outputItem .= $singlePlayerOptions['extraattr'];


  if (isset($singleItemInstance['thumb']) && $singleItemInstance['thumb']) {
    $outputItem .= ' data-thumb="' . $singleItemInstance['thumb'] . '"';
  };
  $outputItem .= $thumb_for_parent_attr;
  $outputItem .= $thumb_link_attr;

  if (isset($singleItemInstance['wrapper_image']) && $singleItemInstance['wrapper_image']) {
    $outputItem .= ' data-wrapper-image="' . DZSZoomSoundsHelper::getImageSourceFromId($singleItemInstance['wrapper_image']) . '" ';
  }

  if (isset($singleItemInstance['publisher']) && $singleItemInstance['publisher']) {
    $outputItem .= ' data-publisher="' . $singleItemInstance['publisher'] . '"';
  };


  if (isset($singleItemInstance['sample_time_start']) && $singleItemInstance['sample_time_start']) {
    // -- not pseudo
    $outputItem .= ' data-sample_time_start="' . $singleItemInstance['sample_time_start'] . '"';
  }

  if (isset($singleItemInstance['sample_time_end']) && $singleItemInstance['sample_time_end']) {
    // -- not pseudo
    $outputItem .= ' data-sample_time_end="' . $singleItemInstance['sample_time_end'] . '"';
  }

  if (isset($singleItemInstance['sample_time_total']) && $singleItemInstance['sample_time_total']) {
    $outputItem .= ' data-sample_time_total="' . $singleItemInstance['sample_time_total'] . '"';
  } else {

    // -- try to set from cache total time

    if ($dzsap->mainoptions['try_to_cache_total_time'] == 'on' && !$isSourceFake && get_post_meta($singleItemInstance['playerId_computed'], DZSAP_DBNAME_CACHE_TOTAL_TIME, true) && intval(get_post_meta($singleItemInstance['playerId_computed'], DZSAP_DBNAME_CACHE_TOTAL_TIME, true)) > 0) {
      $outputItem .= ' data-sample_time_total="' . intval(get_post_meta($singleItemInstance['playerId_computed'], DZSAP_DBNAME_CACHE_TOTAL_TIME, true)) . '"';
    }
  }


  if ($dzsap->mainoptions['skinwave_wave_mode'] == 'canvas') {
    $outputItem .= $pcmString;
  } else {
    if (isset($singleItemInstance['waveformbg']) && $singleItemInstance['waveformbg'] != '') {
      $outputItem .= ' data-scrubbg="' . $singleItemInstance['waveformbg'] . '"';
    };
    if (isset($singleItemInstance['waveformprog']) && $singleItemInstance['waveformprog'] != '') {
      $outputItem .= ' data-scrubprog="' . $singleItemInstance['waveformprog'] . '"';
    };
  }

  if ($type != '') {

    if ($type == 'detect') {
      if ($singleItemInstance['source']) {

        if ($singleItemInstance['source'] != sanitize_youtube_url_to_id($singleItemInstance['source'])) {
          $type = 'youtube';
          $singleItemInstance['source'] = sanitize_youtube_url_to_id($singleItemInstance['source']);
        }
      }
    }
    $outputItem .= ' data-type="' . $type . '"';
  };


  if (($dzsap->mainoptions['developer_check_for_bots_and_dont_reveal_source'] == 'on' && DZSZoomSoundsHelper::isBotScraping() == false) || $dzsap->mainoptions['developer_check_for_bots_and_dont_reveal_source'] != 'on') {

    if (isset($singleItemInstance['source']) && $singleItemInstance['source'] != '') {
      $outputItem .= ' data-source="' . DZSZoomsoundsHelper::sanitizeForShortcodeAttr($singleItemInstance['source']) . '"';
    };
    if (isset($singleItemInstance['sourceogg']) && $singleItemInstance['sourceogg'] != '') {
      $outputItem .= ' data-sourceogg="' . $singleItemInstance['sourceogg'] . '"';
    };
  }

  if (isset($singleItemInstance['bgimage']) && $singleItemInstance['bgimage'] != '') {
    $outputItem .= ' data-bgimage="' . $singleItemInstance['bgimage'] . '"';
    $outputItem .= ' data-wrapper-image="' . $singleItemInstance['bgimage'] . '"';
  };


  if ($singleItemInstance['playfrom']) {
    $outputItem .= ' data-playfrom="' . $singleItemInstance['playfrom'] . '"';
  };


  $outputItem .= $fakeplayer_attr;

  $outputItem .= '>';


  if ($singleItemInstance['artistname']) {
    $outputItem .= '<div hidden class="feed-dzsap feed-artist-name">' . $singleItemInstance['artistname'] . '</div>';
  }
  if ($singleItemInstance['songname']) {
    $outputItem .= '<div hidden class="feed-dzsap feed-song-name">' . $singleItemInstance['songname'] . '</div>';
  }

  $meta_artist_html .= dzsap_view_player_generateMetaArtist($singleItemInstance, $singlePlayerOptions);

  $outputItem .= $meta_artist_html;


  if (isset($singleItemInstance['wrapper_image_type']) && $singleItemInstance['wrapper_image_type']) {
    if ($singleItemInstance['wrapper_image_type'] == 'zoomsounds-wrapper-bg-bellow') {

      $dzsap->isEnableMultisharer = true;
      $outputItem .= '<div href="#" class=" dzsap-wrapper-but dzsap-multisharer-but "><span class="the-icon">{{svg_share_icon}}</span> </div>';

      $outputItem .= '<div href="#" class=" dzsap-wrapper-but btn-like zoomsounds-player-btn-like"><span class="the-icon">{{heart_svg}}</span> </div>';
    }

  }


  if ($singleItemInstance['menu_artistname'] != '' || $singleItemInstance['menu_songname'] != '' || (isset($singleItemInstance['thumb']) && $singleItemInstance['thumb'] != '')) {
    $outputItem .= '<div class="menu-description">';
    if (isset($singleItemInstance['thumb']) && $singleItemInstance['thumb']) {
      $outputItem .= '<div class="menu-item-thumb-con"><div class="menu-item-thumb" style="background-image: url(' . $singleItemInstance['thumb'] . ')"></div></div>';
    }


    if ($playlistOptions && $playlistOptions['galleryskin'] == 'skin-aura') {
      $outputItem .= '<div class="menu-artist-info">';
    }


    $outputItem .= '<span class="the-artist">' . $singleItemInstance['menu_artistname'] . '</span>';
    $outputItem .= '<span class="the-name">' . $singleItemInstance['menu_songname'] . '</span>';


    if ($playlistOptions && $playlistOptions['galleryskin'] == 'skin-aura') {
      $outputItem .= '</div>';
    }


    if ($playlistOptions && $playlistOptions['galleryskin'] == 'skin-aura') {
      $outputItem .= '<div class="menu-item-views">' . DZSAP_VIEW_SVG_ASSETS['PLAY_BUTTON'] . ' <span class="the-count">' . get_post_meta($singleItemInstance['playerId_computed'], DZSAP_DB_VIEWS_META_NAME, true) . '</span></div>';


      if ($singlePlayerOptions['menu_facebook_share'] == 'auto' || $singlePlayerOptions['menu_facebook_share'] == 'on' || $singlePlayerOptions['menu_like_button'] == 'auto' || $singlePlayerOptions['menu_like_button'] == 'on' || (isset($singleItemInstance['replace_menu_in_float_right']) && $singleItemInstance['replace_menu_in_float_right'])) {

        $outputItem .= '<div class="float-right">';
        if ($singlePlayerOptions['menu_facebook_share'] == 'auto' || $singlePlayerOptions['menu_facebook_share'] == 'on') {
          $outputItem .= ' <a rel="nofollow" class="btn-zoomsounds-menu menu-facebook-share"  onclick=\'window.dzs_open_social_link("https://www.facebook.com/sharer.php?u={{shareurl}}",this); return false;\'><i class="fa fa-share" aria-hidden="true"></i></a>';
        }
        if ($singlePlayerOptions['menu_like_button'] == 'auto' || $singlePlayerOptions['menu_like_button'] == 'on') {

          $outputItem .= ' <a rel="nofollow" class="btn-zoomsounds-menu menu-btn-like "><i class="fa fa-thumbs-up" aria-hidden="true"></i></a>';

        }
        if (isset($singleItemInstance['replace_menu_in_float_right']) && $singleItemInstance['replace_menu_in_float_right']) {

          $outputItem .= do_shortcode($singleItemInstance['replace_menu_in_float_right']);

        }


        $outputItem .= '</div>';
      }
    }


    $outputItem .= stripslashes($singleItemInstance['menu_extrahtml']);
    $outputItem .= '</div>';
  }


  if (isset($its['settings']['skinwave_comments_enable']) && $its['settings']['skinwave_comments_enable'] == 'on') {

    if ($singleItemInstance['playerId_computed'] != '') {

      $outputItem .= '<div class="the-comments">';
      $commentsArr = get_comments(array('post_id' => $singleItemInstance['playerId_computed']));

      foreach ($commentsArr as $comm) {
        $outputItem .= '<div class="dzstooltip-con dzsap--comment" style="left:' . dzsap_sanitize_to_css_perc($comm->comment_author_url) . '"><span class="dzstooltip arrow-from-start transition-slidein  arrow-bottom talign-start style-rounded color-dark-light  " style="width: 250px;"><span class="dzstooltip--inner"><span class="the-comment-author">@' . $comm->comment_author . '</span> says:<br>' . $comm->comment_content . '</span></span><fig class="the-avatar" style="background-image: url(https://secure.gravatar.com/avatar/' . md5($comm->comment_author_email) . '?s=20)"></fig></div>';


      }
      $outputItem .= '</div>';


      wp_enqueue_style('dzs.tooltip', DZSAP_BASE_URL . 'libs/dzstooltip/dzstooltip.css');
      wp_enqueue_script('dzsap-part-player-comments', DZSAP_URL_AUDIOPLAYER . 'parts/player/player-comments/player-comments.js');
      wp_enqueue_style('dzsap-part-player-comments', DZSAP_URL_AUDIOPLAYER . 'parts/player/player-comments/player-comments.css');
    }
  }

  if (isset($vpConfig) && $vpConfig['skin_ap'] && ($vpConfig['skin_ap'] == 'skin-customcontrols' || $vpConfig['skin_ap'] == 'skin-customhtml')) {

    $customContent = '';

    if ($singlePlayerOptions['the_content']) {
      $customContent = do_shortcode($singlePlayerOptions['the_content']);
    } else {
      if (isset($singlePlayerOptions['settings_extrahtml_in_player']) && $singlePlayerOptions['settings_extrahtml_in_player']) {
        $customContent = DZSZoomSoundsHelper::sanitize_from_meta_textarea($singlePlayerOptions['settings_extrahtml_in_player']);;
      }
    }
    $outputItem .= '<div hidden aria-hidden="true" class="feed-dzsap feed-dzsap--custom-controls">' . $customContent . '</div>';
  }
  // --- extra html meta


  $che_post = null;
  $singleItemInstance_post = null;
  if ($singleItemInstance['playerId_computed'] && $isPlayerIdFake === false) {
    $che_post = get_post($singleItemInstance['playerId_computed']);
  }


  if ($singlePlayerOptions['called_from'] == 'single_product_summary') {
    if (isset($singlePlayerOptions['product_id'])) {
      if ($dzsap->mainoptions['wc_product_play_in_footer'] == 'on') {


        $vpset = $dzsap->classView->get_zoomsounds_player_config_settings($dzsap->mainoptions['enable_global_footer_player']);


        $price = '';

        if (get_post_meta($singlePlayerOptions['product_id'], '_regular_price', true)) {
          if (function_exists('get_woocommerce_currency_symbol')) {
            $price .= get_woocommerce_currency_symbol();
          }
          if (get_post_meta($singlePlayerOptions['product_id'], '_sale_price', true)) {

            $price .= get_post_meta($singlePlayerOptions['product_id'], '_sale_price', true);
          } else {

            $price .= get_post_meta($singlePlayerOptions['product_id'], '_regular_price', true);
          }
        }


        if (isset($vpset['settings']['extra_classes_player']) && strpos($vpset['settings']['extra_classes_player'], 'skinvariation-wave-righter') !== false) {
          $outputItem .= '<div hidden class="feed-dzsap-for-extra-html-right"><form method="post" style="margin: 0!important; "><button class="zoomsounds-add-tocart-btn" name="add-to-cart" value="' . $singlePlayerOptions['product_id'] . '"><i class="fa fa-shopping-cart"></i>&nbsp;&nbsp;&nbsp;<span class="the-price">' . $price . '</span></button></form></div>';


          wp_enqueue_style('zoomsounds-woocommerce', DZSAP_BASE_URL . 'audioplayer/parts/player/zoomsounds-woocommerce.css');
        }


      }
    }

  }


  if (
    DZSZoomSoundsHelper::isPlayerHasExtrahtml($singleItemInstance, $its['settings'], $its['settings'], $its['playerConfigSettings'])
  ) {


    $extraHtmlAreas = $dzsapView->parseItems_determineExtraHtml($singleItemInstance, $its['settings'], $singlePlayerOptions, $its['playerConfigSettings'], $singleItemInstance['playerId_computed'], $che_post);


    $outputItem .= $dzsapView->generatePlayerExtraHtml($extraHtmlAreas, $singleItemInstance);
  }


  if (isset($singleItemInstance['inner_html']) && $singleItemInstance['inner_html']) {
    $outputItem .= $singleItemInstance['inner_html'];
  }


  if (isset($singlePlayerOptions['feed_embed_code']) && $singlePlayerOptions['feed_embed_code']) {
    $outputItem .= '<div aria-hidden="true" hidden class="feed-dzsap feed-dzsap--embed-code" >' . $singlePlayerOptions['feed_embed_code'] . '</div>';
  }

  $outputItem .= '</div>';// -- <!-- end .audioplayer-->


  return $outputItem;
}