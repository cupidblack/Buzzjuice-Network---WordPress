<?php


function dzsap_shortcode_woo_grid__getItems($margs) {

  $wpqargs = array(
    'post_type' => $margs['type'],
    'posts_per_page' => '-1',
  );

  if ($margs['count']) {
    $wpqargs['posts_per_page'] = $margs['count'];
  }


  $args_wpqargs = array();
  $margs['settings_wpqargs'] = html_entity_decode($margs['settings_wpqargs']);
  parse_str($margs['settings_wpqargs'], $args_wpqargs);


  if (!isset($args_wpqargs) || !$args_wpqargs || is_array($args_wpqargs) == false) {
    $args_wpqargs = array();
  }

  $taxonomy = DZSAP_REGISTER_POST_TYPE_CATEGORY;


  if ($wpqargs['post_type'] == 'audio_items' || $wpqargs['post_type'] == DZSAP_REGISTER_POST_TYPE_NAME) {
    $taxonomy = DZSAP_REGISTER_POST_TYPE_CATEGORY;
  }
  if ($wpqargs['post_type'] == 'product') {
    $taxonomy = 'product_cat';
  }

  if ($margs['type'] == 'attachment') {
    $wpqargs['post_mime_type'] = 'audio/mpeg';


    $wpqargs['post_parent'] = null;
    $wpqargs['post_status'] = 'inherit';
  }

  $paged = '1';

  if ($wpqargs['posts_per_page'] != '-1') {
    if (isset($_GET['dzsapp_paged'])) {
      $paged = $_GET['dzsapp_paged'];
    }
    $wpqargs['paged'] = $paged;
  }

  if ($margs['cats']) {


    $thecustomcats = array();
    $thecustomcats = explode(',', $margs['cats']);
    $thecustomcats = array_values($thecustomcats);

    foreach ($thecustomcats as $lab => $val) {
      $thecustomcats[$lab] = DZSZoomSoundsHelper::sanitize_term_slug_to_id($val, $taxonomy);
    }

    if ($wpqargs['post_type'] == 'product' || $wpqargs['post_type'] == 'audio_items' || $wpqargs['post_type'] == DZSAP_REGISTER_POST_TYPE_NAME) {
      $wpqargs['tax_query'] = array(
        array(
          'taxonomy' => $taxonomy,
          'field' => 'id',
          'terms' => $thecustomcats,
        )
      );
    }


    if ($wpqargs['post_type'] == 'attachment') {
    }


  }


  if (isset($_GET[DZSAP_VIEW_QUERY_PARAMS_TAGS_FILTER]) && $_GET[DZSAP_VIEW_QUERY_PARAMS_TAGS_FILTER]) {
    $taxonomy = DZSAP_REGISTER_POST_TYPE_TAGS;
    $tax_query = array(

      'taxonomy' => $taxonomy,
      'field' => 'slug',
      'terms' => $_GET[DZSAP_VIEW_QUERY_PARAMS_TAGS_FILTER],
    );
    if (isset($wpqargs['tax_query']) && count($wpqargs['tax_query'])) {

      array_push($wpqargs['tax_query'], $tax_query);
    } else {
      $wpqargs['tax_query'] = array(
        $tax_query
      );
    }
  }


  if ($margs['ids']) {

    $aux_arr = explode(',', $margs['ids']);

    $wpqargs['post__in'] = $aux_arr;


  }


  $wpqargs = array_merge($wpqargs, $args_wpqargs);


  if ($margs['author_id']) {
    $wpqargs['author'] = $margs['author_id'];
  }


  // -- wp query here
  $query = new WP_Query($wpqargs);


  $its = $query->posts;

  return array(
    'its' => $its,
    'query' => $query,
    'wpqargs' => $wpqargs,
    'paged' => $paged,
  );
}

/**
 * [dzsap_woo_grid]
 * @param $atts
 * @param null $content
 * @return string
 */
function dzsap_shortcode_woo_grid($atts, $content = null) {


  global $current_user, $dzsap;


  $dzsap->sliders__player_index++;

  $fout = '';


  DZSZoomSoundsHelper::enqueueMainScrips();
  wp_enqueue_style('dzs.zoomsounds-grid', DZSAP_BASE_URL . 'audioplayer/audioportal-grid.css');

  $margs = array(
    'style' => 'under', // -- "under", "noir", "style1", "style2", "style3", "style4"
    'vpconfig' => '', // -- the player configuration
    'faketarget' => '', // -- ".dzsap_footer" will play tracks in footer
    'type' => DZSAP_REGISTER_POST_TYPE_NAME, // -- audio_items or product
    'title_is_permalink' => 'off', // -- title links to post
    'cats' => '', // -- the category id
    'count' => '10', // -- posts per page
    'ids' => '', // -- manual select ids
    'author_id' => '', // -- author id
    'extra_classes' => '', // -- extra classes
    'layout' => '4-cols', // -- the layout "3-cols"
    'pagination' => 'off', // -- the layout "3-cols"
    'settings_wpqargs' => '', // -- input options like "posts_per_page=7"
  );

  if ($atts) {

    $margs = array_merge($margs, $atts);
  }


  $itsData = dzsap_shortcode_woo_grid__getItems($margs);
  $its = $itsData['its'];

  $paged = $itsData['paged'];

  $str_layout = '';

  $str_layout .= 'dzs-layout--' . $margs['layout'];

  if (intval($itsData['query']->found_posts) > intval($itsData['wpqargs']['posts_per_page'])) {
    if ($margs['pagination'] == 'auto') {
      $margs['pagination'] = 'on';
    }
  }


  // -- start output

  if ($margs['style'] == 'noir' || $margs['style'] == 'style1' || $margs['style'] == 'style2') {
    $fout .= '<div class="dzsap-grid ' . $margs['extra_classes'] . ' ' . $str_layout . ' style-' . $margs['style'] . '">';
  } else {
    $fout .= '<div class="dzsap-woo-grid ' . $margs['extra_classes'] . ' style-' . $margs['style'] . '">';

  }


  if ($margs['style'] == 'style4') {
    $fout .= '<ul class="style-nova">';
  }
  if ($margs['style'] == 'style3') {
    $fout .= '<div class="dzsap-header-tr">
  <div class="column-for-player">' . $dzsap->mainoptions['i18n_play'] . '</div>
  <div class="column-for-title">' . $dzsap->mainoptions['i18n_title'] . '</div>
  <div class="column-for-buy">' . $dzsap->mainoptions['i18n_buy'] . '</div>
</div>';


  }


  // -- start the loop
  foreach ($its as $itemPost) {

    $audioData = DzsapView::getAudioItemMeta($itemPost, $margs);


    $audioSource = $audioData['audioSource'];
    $type = $audioData['type'];
    $buy_link = $audioData['buy_link'];
    $price = $audioData['price'];
    $thumb_url = $audioData['thumb_url'];


    $cue = 'on';
    $thumb_url = '';
    $title = '';

    $shortdesc = '';
    $longdesc = '';


    $html_meta_artist = '';

    $title = $itemPost->post_title;
    $shortdesc = get_post_meta($itemPost->ID, 'dzsap_woo_subtitle', true);
    $longdesc = $itemPost->post_excerpt;

    $user_info = get_userdata($itemPost->post_author);



    $author_name = $audioData['artistName'];

    $wavebg = get_post_meta($itemPost->ID, 'dzsap_woo_product_track_waveformbg', true);
    $waveprog = get_post_meta($itemPost->ID, 'dzsap_woo_product_track_waveformprog', true);
    if (!$audioSource) {
      continue;
    }



    // -- start output

    if ($margs['style'] == 'noir' || $margs['style'] == 'style1' || $margs['style'] == 'style2') {
      $fout .= '<div class="dzs-layout-item "';
      $fout .= '>';
      $fout .= '<div class="grid-object ';


      $fout .= '"';
      $fout .= '>';
    } else {

      if ($margs['style'] != 'style4') {
        $fout .= '<div class="grid-object ';

        if ($audioSource) {
          $fout .= ' zoomsounds-woo-grid-item';
        }
        $fout .= '">';
      }
    }



    if ($title) {
      $html_meta_artist = '<div class="meta-artist"><span class="the-artist">' . $author_name . '</span><span class="the-name">' . $title . '</span></div>';
    }


    $str_pcm = '';

    if ($dzsap->mainoptions['skinwave_wave_mode'] == 'canvas') {


      $argsForPcm = array(
        'source' => $audioSource,
        'linktomediafile' => $itemPost->ID,
        'playerid' => $itemPost->ID,
      );

      $str_pcm .= $dzsap->classView->generate_pcm($argsForPcm);
    } else {

    }



    // -- under here
    if ($margs['style'] == 'under') {



      if ($margs['faketarget']) {


      }

      $argsForPcm = array(

        'source' => $audioSource,
        'cue' => $cue,
        'config' => $margs['vpconfig'],
        'autoplay' => 'off',
        'show_tags' => 'on',
        'enable_likes' => 'off',
        'artistname' => $author_name,
        'title_is_permalink' => $margs['title_is_permalink'],
        'type' => $type,
        'faketarget' => $margs['faketarget'],
        'sample_time_start' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_start', true),
        'sample_time_end' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_end', true),
        'sample_time_total' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_total', true),
        'playerid' => $itemPost->ID,
        'thumb' => DZSZoomSoundsHelper::view_getComputedDzsapThumbnail($itemPost->ID),
        'thumbnail' => DZSZoomSoundsHelper::view_getComputedDzsapThumbnail($itemPost->ID),
        'called_from' => 'woogrid under',
      );


      $taxonomy = DZSAP_REGISTER_POST_TYPE_TAGS;
      $term_list = wp_get_post_terms($itemPost->ID, $taxonomy, array("fields" => "all"));


      $fout .= $dzsap->classView->shortcode_player($argsForPcm);

    }


    if ($margs['style'] == 'style4') {


      $fout .= '<li><div class="li-thumb" style="background-image: url(' . $thumb_url . ')">';


      $argsForShortcode = array(

        'source' => $audioSource,
        'cue' => $cue,
        'extra_classes_player' => 'center-it',
        'config' => array(
          'skin_ap' => 'skin-customcontrols'
        ),
        'inner_html' => ' <div class="custom-play-btn playbtn-darkround" data-border-radius="5px" data-size="30px"></div>
        <div class="custom-pause-btn pausebtn-darkround" data-border-radius="5px" data-size="30px"></div>

        <div class="meta-artist-con">

            <span class="the-artist">' . $title . '</span>
            <span class="the-name">' . $shortdesc . '</span>
        </div>',
        'autoplay' => 'off',
        'type' => $type,
        'faketarget' => $margs['faketarget'],
        'sample_time_start' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_start', true),
        'sample_time_end' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_end', true),
        'sample_time_total' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_total', true),
        'playerid' => $itemPost->ID,
        'called_from' => 'woogrid style4',
      );


      $fout .= $dzsap->classView->shortcode_player($argsForShortcode);

      $fout .= '</div><div class="li-meta"> <a rel="nofollow" class="ajax-link track-title" href="' . $itemPost->ID . '">' . $title . '</a><div class=" track-by">' . esc_html__("by", 'dzsap') . ' ' . $author_name . '</div><div class="the-price">' . esc_html__("Free", 'dzsap') . '</div></div></li>';


    }


    if ($margs['style'] == 'noir') {


      if ($margs['faketarget']) {


      }

      $argsForShortcode = array(

        'source' => $audioSource,
        'cue' => $cue,
        'config' => $margs['vpconfig'],
        'autoplay' => 'off',
        'type' => $type,
        'faketarget' => $margs['faketarget'],
        'sample_time_start' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_start', true),
        'sample_time_end' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_end', true),
        'sample_time_total' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_total', true),
        'playerid' => $itemPost->ID,
        'called_from' => 'woogrid noir',
      );


      $fout .= $dzsap->classView->shortcode_player($argsForShortcode);

      $fout .= '

                        <h4 class="the-title">' . $title . '</h4>
                        <div class="the-price">' . $price . '</div>

                         <a rel="nofollow"  href="' . $buy_link . '" class="dzs-button-dzsap padding-small"><span class="the-bg"></span><span class="the-text">' . $dzsap->mainoptions['i18n_buy'] . '</span></a>';
    }


    if ($margs['style'] == 'style1') {


      if ($margs['type'] == 'attachment') {
        $shortdesc = $itemPost->post_content;
      }


      $buystring = ' <a rel="nofollow" href="' . $buy_link . '" class="button-buy" style="font-size: 16px;">' . $dzsap->mainoptions['i18n_buy'] . '</a>&nbsp;';


      $waveformbg_str = '';
      $waveformprog_str = '';

      if ($margs['type'] == 'attachment') {
        $buystring = '';


        if (get_post_meta($itemPost->ID, '_waveformbg', true)) {
          $wavebg .= get_post_meta($itemPost->ID, '_waveformbg', true);
        }

        if (get_post_meta($itemPost->ID, '_waveformprog', true)) {
          $waveprog = get_post_meta($itemPost->ID, '_waveformprog', true);
        }

      }

      if ($thumb_url) {

        $fout .= '<img src="' . $thumb_url . '" class="fullwidth"/>';
      }


      $fout .= '<div class="label-artist"> <a rel="nofollow" href="' . get_permalink($itemPost->ID) . '">' . $title . '</a></div>
<div class="label-song">' . $shortdesc . '</div>
<div class="dzsap-grid-meta-buy" style="margin-top: 15px;">
' . $buystring;
      if ($audioSource) {
        $fout .= '
<span href="#" class="button-buy audioplayer-song-changer from-style-1" style="font-size: 16px; background-color: #a861c6" data-fakeplayer="' . $margs['faketarget'] . '"  style="" data-thumb="' . $thumb_url . '"  data-bgimage="img/bg.jpg"';


        $fout .= $str_pcm;


        $fout .= ' data-type="' . $type . '" data-playerid="' . $itemPost->ID . '" data-source="' . $audioSource . '" >' . $dzsap->mainoptions['i18n_play'] . '
' . $html_meta_artist . '
</span>';

        wp_enqueue_script('dzsap-player-' . 'change-media', DZSAP_BASE_URL . 'audioplayer/parts/player/change-media/change-media.js');
      }
      $fout .= '
</div>';


    }


    if ($margs['style'] == 'style2') {


      if ($margs['faketarget']) {


      }



      $title = $itemPost->post_title;
      $shortdesc = get_post_meta($itemPost->ID, 'dzsap_woo_subtitle', true);
      $longdesc = $itemPost->post_excerpt;


      if ($margs['type'] == 'attachment') {
        $shortdesc = $itemPost->post_content;
      }


      $buystring = ' <a rel="nofollow" href="' . $buy_link . '" class="button-buy" style="font-size: 16px;">' . $dzsap->mainoptions['i18n_buy'] . '</a>&nbsp;';

      if ($margs['type'] == 'attachment') {
        $buystring = '';
      }


      $fout .= '<div class="dzsap-grid-style2-item">';

      if (isset($margs['style2_hover']) && $margs['style2_hover'] == 'play') {

        $fout .= '<div class="divimage" style="width: 100%; padding-top: 100%; background-image:url(' . $thumb_url . ');"></div>';
      } else {

        if ($thumb_url) {

          $fout .= '<img src="' . $thumb_url . '" class="fullwidth"/>';
        }

      }
      $fout .= '<div class="centered-content-con"><div class="centered-content">';


      if (isset($margs['style2_hover']) && $margs['style2_hover'] == 'play') {


        if ($audioSource) {


          $argsForShortcode = array(

            'source' => $audioSource,
            'cue' => $cue,
            'extra_classes_player' => 'center-it',
            'config' => array(
              'skin_ap' => 'skin-customcontrols'
            ),
            'inner_html' => ' <div class="custom-play-btn playbtn-darkround" data-border-radius="5px" data-size="30px"></div>
        <div class="custom-pause-btn pausebtn-darkround" data-border-radius="5px" data-size="30px"></div>

        <div class="meta-artist-con">

            <span class="the-artist">' . $title . '</span>
            <span class="the-name">' . $shortdesc . '</span>
        </div>',
            'autoplay' => 'off',
            'type' => $type,
            'faketarget' => $margs['faketarget'],
            'sample_time_start' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_start', true),
            'sample_time_end' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_end', true),
            'sample_time_total' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_total', true),
            'playerid' => $itemPost->ID,
            'called_from' => 'woogrid style2 grid play',
          );
          $fout .= $dzsap->classView->shortcode_player($argsForShortcode);


        }

      } else {
        $fout .= '<div class="label-artist">' . $title . '</div>
<div class="label-song">' . $shortdesc . '</div>
<div class="dzsap-grid-meta-buy" style="margin-top: 15px;">
' . $buystring;


        if ($audioSource) {

          $fout .= '
<span href="#" class="button-buy audioplayer-song-changer" style="font-size: 16px; background-color: #a861c6" data-fakeplayer="' . $margs['faketarget'] . '"  style="" data-thumb="' . $thumb_url . '"  data-bgimage="img/bg.jpg" data-scrubbg="' . $wavebg . '" data-scrubprog="' . $waveprog . '"  data-playerid="' . $itemPost->ID . '" data-type="' . $type . '" data-source="' . $audioSource . '" >' . $dzsap->mainoptions['i18n_play'] . '
' . $longdesc . '
</span>';
          wp_enqueue_script('dzsap-player-' . 'change-media', DZSAP_BASE_URL . 'audioplayer/parts/player/change-media/change-media.js');
        }
        $fout .= '</div>';
      }


      $fout .= '
</div>
</div>';


      $fout .= '
</div>';


      if (isset($margs['style2_hover']) && $margs['style2_hover'] == 'play') {
        $fout .= '<h3>';
        $fout .= $title;
        $fout .= '</h3>';
      }


    }


    if ($margs['style'] == 'style3') {


      if ($margs['faketarget']) {


      }

      $thumb_url = wp_get_attachment_image_src(get_post_thumbnail_id($itemPost->ID), 'large');
      if (is_array($thumb_url) && isset($thumb_url[0])) {
        $thumb_url = $thumb_url[0];
      }


      if ($margs['type'] == 'attachment') {
        $thumb_url = get_post_meta($itemPost->ID, '_dzsap-thumb', true);
      }


      $title = $itemPost->post_title;
      $shortdesc = get_post_meta($itemPost->ID, 'dzsap_woo_subtitle', true);
      $longdesc = $itemPost->post_excerpt;


      if (get_permalink($itemPost->ID)) {
        $title = ' <a rel="nofollow" href="' . get_permalink($itemPost->ID) . '">' . $title . '</a>';
      }


      if ($margs['type'] == 'attachment') {
        $shortdesc = $itemPost->post_content;
      }


      $buystring = ' <a rel="nofollow" href="' . $buy_link . '" class="button-buy grid-buy-btn" style="font-size: 16px;">' . $dzsap->mainoptions['i18n_buy'] . '</a>';

      if ($margs['type'] == 'attachment') {
        $buystring = '';
      }


      $argsForPcm = array(

        'source' => $audioSource,
        'cue' => $cue,
        'height' => '',
        'extra_classes_player' => 'position-relative',
        'config' => array(

          'skin_ap' => 'skin-customcontrols'
        ),
        'autoplay' => 'off',
        'type' => $type,
        'faketarget' => $margs['faketarget'],
        'inner_html' => ' <div class="custom-play-btn position-relative playbtn-darkround" data-border-radius="5px" data-size="30px"></div>
        <div class="custom-pause-btn position-relative pausebtn-darkround" data-border-radius="5px" data-size="30px"></div>

        <div class="meta-artist-con">

            <span class="the-artist">' . $title . '</span>
            <span class="the-name">' . $shortdesc . '</span>
        </div>',
        'sample_time_start' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_start', true),
        'sample_time_end' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_end', true),
        'sample_time_total' => get_post_meta($itemPost->ID, 'dzsap_woo_sample_time_total', true),
        'playerid' => $itemPost->ID,
        'called_from' => ' woo_grid',
      );


      $fout .= '<div class="dzsap-product-tr">
<div class="column-for-player">
';
      $fout .= $dzsap->classView->shortcode_player($argsForPcm);

      $fout .= '

</div>';

      $fout .= '
<div class="column-for-title">';


      $fout .= '';


      $fout .= $title;

      $fout .= ' - ' . $author_name;


      $fout .= '</div>';


      $fout .= '<div class="column-for-buy">' . $buystring . '</div>
</div>';


    }


    if ($margs['style'] == 'noir' || $margs['style'] == 'style1' || $margs['style'] == 'style2') {
      $fout .= '</div>';
      $fout .= '</div>';
    } else {

      if ($margs['style'] != 'style4') {
        $fout .= '</div>';
      }
    }
  }


  if ($margs['style'] == 'style4') {
    $fout .= '</ul>';
  }


  if ($margs['pagination'] === 'on') {
    $fout .= '<div class="dzs-pagination-con">';
    global $wp;

    $currentUri = home_url($wp->request);
    $fout .= dzs_pagination($itsData['query']->max_num_pages, 3, array(

      'container_class' => 'dzs-pagination ',
      'include_raquo' => true,
      'style' => 'div',
      'currentUri' => $currentUri,
      'paged' => $paged,
      'a_class' => 'pagination-item',
      'wrap_before_text' => '',
      'wrap_after_text' => '',
      'link_style' => 'dzsapp_paged',
    ));

    $fout .= '</div>';
  }
  $fout .= '</div>';


  return $fout;
}
