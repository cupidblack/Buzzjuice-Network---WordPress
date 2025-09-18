<?php
/** @var DzsAudioPlayer $dzsap */

// -- used for gutenberg item options
$arr_off_on = array(
  array(
    'label' => esc_html__("Off"),
    'value' => 'off',
  ),
  array(
    'label' => esc_html__("On"),
    'value' => 'on',
  ),
);

$arr_ap_configs = array(
  array(
    'label' => esc_html__("Default"),
    'value' => 'default',
  ),
);


$metaTypeChoices = array(
  array(
    'label' => esc_html__("Detect"),
    'value' => 'detect',
  ),
  array(
    'label' => esc_html__("Self Hosted"),
    'value' => 'audio',
  ),
  array(
    'label' => esc_html__("shoutcast"),
    'value' => 'shoutcast',
  ),
);
$metaTypeChoicesHtml = array(

  '<span class="option-con"><img src="' . DZSAP_BASE_URL . 'admin//img/type_audio.png"/><span class="option-label">' . esc_html__("Auto detect") . '</span></span>',
  '<span class="option-con"><img src="' . DZSAP_BASE_URL . 'admin//img/type_audio.png"/><span class="option-label">' . esc_html__("Self Hosted") . '</span></span>',
  '<span class="option-con"><img src="' . DZSAP_BASE_URL . 'admin//img/type_radio.png"/><span class="option-label">' . esc_html__("Radio Station") . '</span></span>',

);

if(!isset($dzsap)){
  $dzsap = $this;
}

if ($dzsap->mainoptions && isset($dzsap->mainoptions['soundcloud_api_key']) && $dzsap->mainoptions['soundcloud_api_key']) {

  array_push($metaTypeChoices,
    array(
      'label' => esc_html__("soundcloud"),
      'value' => 'soundcloud',
    ));
  array_push($metaTypeChoicesHtml,

    '<span class="option-con"><img src="' . DZSAP_BASE_URL . 'admin/img/type_soundcloud.png"/><span class="option-label">' . esc_html__("SoundCloud") . '</span></span>');

}


if (isset($dzsap->mainitems_configs)) {
  if (is_array($dzsap->mainitems_configs)) {
    foreach ($dzsap->mainitems_configs as $mainItemConfig) {
      if (isset($mainItemConfig['settings']['id'])) {
        $aux = array(
          'label' => $mainItemConfig['settings']['id'],
          'value' => $mainItemConfig['settings']['id'],
        );
        array_push($arr_ap_configs, $aux);
      } else {
        error_log(DZSAP_PHP_LOG_LABEL . ' something wrong with $mainItemConfig in options-item-meta.php - ' . print_r($mainItemConfig, true));
      }

    }
  }
}


$itemMetaArray = array(


  'artistname'=>array(
    'name' => 'artistname',
    'type' => 'text',
    'title' => esc_html__("Artist Title", 'dzsap') . ' ' . esc_html__("( line 1 )", 'dzsap'),
    'sidenote' => esc_html__("title to appear on the left top"),

    'context' => 'content',
    'default' => 'default',
    'category' => 'main',

  ),


  'the_post_title'=>array(
    'name' => 'the_post_title',
    'type' => 'text',
    'title' => esc_html__("Song Title"),
    'only_for' => array('sliders_admin'),
    'sidenote' => esc_html__("the title of the song"),
    'category' => 'main',
  ),

  'dzsap_meta_config'=>array(
    'name' => 'dzsap_meta_config',
    'type' => 'select',
    'category' => 'main',
    'title' => esc_html__("Audio Player Configuration", DZSAP_ID),
    'sidenote' => sprintf(esc_html__("the audio player configuration , can be edited in %s > Player Configurations"), 'ZoomSounds'),
    'choices' => $arr_ap_configs,
    'default' => 'default',
    'it_is_for' => array('shortcode_generator','elementor'),
  ),


  'dzsap_meta_type'=>array(
    'name' => 'dzsap_meta_type',
    'type' => 'select',
    'select_type' => 'opener-listbuttons',
    'title' => esc_html__("Type"),
    'sidenote' => esc_html__("select the type of media"),
    'default' => '',
    'choices' => $metaTypeChoices,
    'choices_html' => $metaTypeChoicesHtml,
    'category' => 'main',


  ),

  'dzsap_meta_item_source'=>array(
      'name' => 'dzsap_meta_item_source',
    'type' => 'attach',
    'upload_type' => 'audio',
    'no_preview' => 'on',
    'title' => esc_html__("Source"),
    'upload_btn_extra_classes' => 'main-source-upload',
    'sidenote' => esc_html__("link to mp3 or soundcloud link"),
    'category' => 'main',
  ),

  'dzsap_meta_item_thumb'=>array(
    'name' => 'dzsap_meta_item_thumb',
    'type' => 'attach',
    'upload_type' => 'image',
    'title' => esc_html__("Thumbnail"),
    'sidenote' => esc_html__("select a thumbnail for the song", 'dzsap'),
    'sidenote-2' => sprintf(esc_html__("input %snone%s to force no thumbnail", 'dzsap'), '<strong>', '</strong>'),
    'category' => 'main',
  ),



  'post_content'=>array(
    'name' => 'post_content',
    'type' => 'textarea',
    'extra_type' => 'WYSIWYG',
    'upload_type' => 'image',
    'title' => esc_html__("Description"),
    'sidenote' => sprintf(esc_html__("this description will appear if the %sInfo button%s is enabled in the Player Configuration", 'dzsap'), '<strong>', '</strong>'),
    'category' => 'main',
  ),



  'misc-layouter-1'=>array(
    'type' => 'dzs_row',
    'category' => 'misc',
    'it_is_for' => 'shortcode_generator',
  ),



  'misc-layouter-2'=>array(
    'type' => 'dzs_col_md_6',
    'category' => 'misc',
    'it_is_for' => 'shortcode_generator',
  ),


  'dzsap_meta_playfrom'=>array(
    'name' => 'dzsap_meta_playfrom',
    'type' => 'text',
    'category' => 'misc',
    'title' => esc_html__("Play from"),
    'sidenote' => esc_html__("choose a number of seconds from which the track to play from ( for example if set \"70\" then the track will start to play from 1 minute and 10 seconds ) or input \"last\" for the track to play at the last position where it was.", DZSAP_ID),
  ),



  'dzsap_meta_type_normal_stream_type'=>array(
    'name' => 'dzsap_meta_type_normal_stream_type',
    'type' => 'select',
    'title' => esc_html__('Stream type', 'dzsap'),
    'sidenote' => esc_html__("it can be shoutcast or icecast if you select it"),

    'category' => 'misc',
    'options' => array(
      array(
        'label' => esc_html__("Normal", 'dzsap'),
        'value' => "",
      ),
      array(
        'label' => esc_html__("Icecast", 'dzsap'),
        'value' => "icecast",
      ),
      array(
        'label' => esc_html__("Shoutcast", 'dzsap'),
        'value' => "shoutcast",
      ),
    ),
    'default' => 'off',
    'react_type' => 'string',
  ),

  'dzsap_meta_play_in_footer_player'=>array(
    'name' => 'dzsap_meta_play_in_footer_player',
    'type' => 'select',
    'category' => 'misc',
    'title' => esc_html__("Play in footer player", 'dzsap'),
    'sidenote' => esc_html__("optional - play this track in the footer player ( footer player must be setuped on the page )", 'dzsap'),
    'choices' => array(
      array(
        'label' => esc_html__("Default"),
        'value' => 'default',
      ),
      array(
        'label' => esc_html__("Play normally"),
        'value' => 'off',
      ),
      array(
        'label' => esc_html__("In footer"),
        'value' => 'on',
      ),
    ),
  ),



  'misc-layouter-3'=>array(
    'type' => 'dzs_col_md_6_end',
    'category' => 'misc',
    'it_is_for' => 'shortcode_generator',
  ),



  'misc-layouter-4'=>array(
    'type' => 'dzs_col_md_6',
    'category' => 'misc',
    'it_is_for' => 'shortcode_generator',
  ),

  'dzsap_meta_enable_download_button'=>array(
    'name' => 'dzsap_meta_enable_download_button',
    'type' => 'select',
    'class' => ' dzs-dependency-field',
    'category' => 'misc',
    'title' => esc_html__("Enable Download Button", 'dzsap'),
    'sidenote' => esc_html__("optional - Enable Download Button for this track", 'dzsap'),
    'choices' => array(
      array(
        'label' => esc_html__("Disable"),
        'value' => 'off',
      ),
      array(
        'label' => esc_html__("Enable"),
        'value' => 'on',
      ),
    ),
  ),

  'enable_downloads_counter'=>array(
    'name' => 'enable_downloads_counter',
    'type' => 'select',
    'default' => 'off',
    'class' => ' dzs-dependency-field',
    'category' => 'misc',
    'title' => esc_html__("Enable Download Counter", 'dzsap'),
    'sidenote' => esc_html__("optional - Enable Download Counter for this track", 'dzsap'),
    'choices' => array(
      array(
        'label' => esc_html__("Disable"),
        'value' => 'off',
      ),
      array(
        'label' => esc_html__("Enable"),
        'value' => 'on',
      ),
    ),
  ),

  'dzsap_meta_download_custom_link_enable'=>array(
    'name' => 'dzsap_meta_download_custom_link_enable',
    'type' => 'select',
    'category' => 'misc',
    'class' => 'dzs-dependency-field',
    'title' => esc_html__("Enable custom download link", 'dzsap'),
    'sidenote' => esc_html__("enable a custom download link for the button ( input below ) ", 'dzsap'),
    'choices' => array(
      array(
        'label' => esc_html__("Disable"),
        'value' => 'off',
      ),
      array(
        'label' => esc_html__("Enable"),
        'value' => 'on',
      ),
    ),
  ),

  'dzsap_meta_download_custom_link'=>array(
    'name' => 'dzsap_meta_download_custom_link',
    'type' => 'text',
    'category' => 'misc',
    'title' => esc_html__("Custom Link Download", 'dzsap'),
    'sidenote' => esc_html__("a custom link for the download button - clicknig it will go to this link if set, if it is not set then it will just download the track", 'dzsap'),

    'dependency' => array(

      array(
        'element' => 'dzsap_meta_download_custom_link_enable',
        'value' => array('on'),
      ),
      array(
        'element' => 'download_custom_link_enable',
        'value' => array('on'),
      ),
      'relation' => 'OR'
    )
  ,
  ),


  'misc-layouter-5'=>array(
    'type' => 'dzs_col_md_6_end',
    'category' => 'misc',
    'it_is_for' => 'shortcode_generator',
  ),


  'misc-layouter-6'=>array(
    'type' => 'dzs_row_end',
    'category' => 'misc',
    'it_is_for' => 'shortcode_generator',
  ),



  'dzsap_meta_item_extra_classes_player'=>array(
    'name' => 'dzsap_meta_item_extra_classes_player',
    'type' => 'text',
    'category' => 'extra_html',
    'title' => esc_html__("Extra Classes"),
    'sidenote' => esc_html__("extra html classes applied to the player"),
  ),



  'dzsap_meta_replace_menu_songname'=>array(
    'name' => 'dzsap_meta_replace_menu_songname',
    'type' => 'text',
    'category' => 'extra_html',
    'title' => esc_html__("Menu song name"),
    'sidenote' => esc_html__("Leave nothing in the field for default artist name ( your author name ); input ", 'dzsap') . "<strong>none</strong> " . esc_html__("for nothing in the field, or input a custom name", 'dzsap'),
  ),


  'dzsap_meta_replace_menu_artistname'=>array(
    'name' => 'dzsap_meta_replace_menu_artistname',
    'type' => 'text',
    'category' => 'extra_html',
    'title' => esc_html__("Menu artist name"),
    'sidenote' => esc_html__("Leave nothing in the field for default artist name ( your author name ); input ", 'dzsap') . "<strong>none</strong> " . esc_html__("for nothing in the field, or input a custom name", 'dzsap'),
  ),
  'dzsap_meta_replace_menu_in_float_right'=>array(
    'name' => 'dzsap_meta_replace_menu_in_float_right',
    'type' => 'textarea',
    'category' => 'extra_html',
    'title' => esc_html__("Extra html in Right Area"),
    'sidenote' => esc_html__("Leave nothing in the field for inputing nothing  ", 'dzsap') . "<strong>- </strong> " . esc_html__("you can use this field for any social media links for example.", 'dzsap'),
  ),







  'misc-layouter-7'=>array(
    'type' => 'dzs_row',
    'category' => 'extra_html',
    'it_is_for' => 'shortcode_generator',
  ),



  'misc-layouter-8'=>array(
    'type' => 'dzs_col_md_6',
    'category' => 'extra_html',
    'it_is_for' => 'shortcode_generator',
  ),



  'extrahtml_in_bottom_controls_from_player'=>array(
    'name' => 'extrahtml_in_bottom_controls_from_player',
    'type' => 'textarea',
    'category' => 'extra_html',
    'extraattr' => ' style="width: 100%; "',
    'title' => esc_html__("Extra HTML in Bottom Controls"),
    'sidenote' => esc_html__("extra html on the in the bottom controls, leave nothing here and the content will come from the player configuration, if you have set anything here", 'dzsap') . '<br>'
      . '<strong>' . esc_html__("demo content: ", 'dzsap') . '</strong>' . '[player_button style="btn-zoomsounds" background_color="#86ccb6" color="#ffffff" label="Twitter" icon="fa-twitter" link="#"]'
  ,
  ),


  'misc-layouter-9'=>array(
    'type' => 'dzs_col_md_6_end',
    'category' => 'extra_html',
    'it_is_for' => 'shortcode_generator',
  ),




  'misc-layouter-10'=>array(
    'type' => 'dzs_col_md_6',
    'category' => 'extra_html',
    'it_is_for' => 'shortcode_generator',
  ),


  'extrahtml_in_float_right_from_player'=>array(
    'name' => 'extrahtml_in_float_right_from_player',
    'type' => 'textarea',
    'category' => 'extra_html',
    'title' => esc_html__("Extra HTML in Right Controls"),
    'sidenote' => esc_html__("extra html on the in the bottom controls, leave nothing here and the content will come from the player configuration, if you have set anything here", 'dzsap') . '<br>'
      . '<strong>' . esc_html__("demo content: ", 'dzsap') . '</strong>' . '[player_button style="player-but" label="Twitter Profile" icon="fa-twitter" link="#"] '
  ),



  'misc-layouter-11'=>array(
    'type' => 'dzs_col_md_6_end',
    'category' => 'extra_html',
    'it_is_for' => 'shortcode_generator',
  ),



  'misc-layouter-12'=>array(
    'type' => 'dzs_row_end',
    'category' => 'extra_html',
    'it_is_for' => 'shortcode_generator',
  ),






  'dzsap_meta_cover'=>array(
    'name' => 'dzsap_meta_cover',
    'type' => 'image',
    'title' => esc_html__("Cover"),
    'sidenote' => esc_html__("cover image to show before video play"),

    'context' => 'content',
    'default' => '',
    'category' => '',
    'it_is_for' => 'shortcode_generator',
  ),



  'dzsap_meta_autoplay'=>array(
    'name' => 'dzsap_meta_autoplay',
    'type' => 'select',
    'extra_type' => 'switcher',
    'title' => esc_html__("Autoplay"),
    'sidenote' => esc_html__("autoplay the song"),

    'context' => 'content',
    'options' => $arr_off_on,
    'default' => 'off',
    'category' => 'main',
    'it_is_for' => 'shortcode_generator',
  ),

  'dzsap_meta_loop'=>array(
    'name' => 'dzsap_meta_loop',
    'type' => 'select',
    'title' => esc_html__("Loop", DZSAP_ID),
    'extra_type' => 'switcher',
    'sidenote' => esc_html__("loop the song on end", DZSAP_ID),

    'context' => 'content',
    'options' => $arr_off_on,
    'default' => 'off',
    'category' => 'main',
    'it_is_for' => 'shortcode_generator',
  ),



  'dzsap_meta_open_in_ultibox'=>array(
    'name' => 'dzsap_meta_open_in_ultibox',
    'type' => 'select',
    'class' => 'dzs-dependency-field',
    'title' => esc_html__("Open in Ultibox?"),
    'sidenote' => esc_html__("open the current player in a lightbox"),

    'context' => 'content',
    'options' => $arr_off_on,
    'default' => 'off',
    'category' => 'misc',
    'it_is_for' => 'shortcode_generator',
  ),



  'dzsap_meta_enable_likes'=>array(
    'name' => 'dzsap_meta_enable_likes',
    'type' => 'select',
    'title' => esc_html__("Enable Likes ? ", DZSAP_ID),
    'extra_type' => 'switcher',
    'sidenote' => esc_html__("enable like count and button"),
    'sidenote-2' => esc_html__("you need to have a id to link the player to in the database for the views, likes, etc to be recorded"),
    'sidenote-2-class' => 'notice-for-playerid warning',

    'context' => 'content',
    'options' => $arr_off_on,
    'default' => 'off',
    'category' => 'counters',
    'it_is_for' => array('shortcode_generator', 'elementor'),
  ),



  'dzsap_meta_enable_views'=>array(
    'name' => 'dzsap_meta_enable_views',
    'type' => 'select',
    'title' => esc_html__("Enable Play Count ? "),
    'extra_type' => 'switcher',
    'sidenote' => esc_html__("enable play count "),
    'sidenote-2' => esc_html__("you need to have a id to link the player to in the database for the views, likes, etc to be recorded"),
    'sidenote-2-class' => 'notice-for-playerid warning',

    'context' => 'content',
    'options' => $arr_off_on,
    'default' => 'off',
    'category' => 'counters',
    'it_is_for' => array('shortcode_generator', 'elementor'),
  ),

  'dzsap_meta_enable_rates'=>array(
    'name' => 'dzsap_meta_enable_rates',
    'type' => 'select',
    'extra_type' => 'switcher',
    'title' => esc_html__("Enable Ratings ? ", 'dzsap'),
    'sidenote' => esc_html__("enable ratings counter "),
    'sidenote-2' => esc_html__("you need to have a id to link the player to in the database for the views, likes, etc to be recorded"),
    'sidenote-2-class' => 'notice-for-playerid warning',

    'context' => 'content',
    'options' => $arr_off_on,
    'default' => 'off',
    'category' => 'counters',
    'it_is_for' => array('shortcode_generator', 'elementor'),
  ),



  'dzsap_meta_itunes_link'=>array(
    'name' => 'dzsap_meta_itunes_link',
    'type' => 'text',
    'title' => esc_html__("iTunes Link", 'dzsap'),
    'sidenote' => esc_html__("input an optional link to the itunes track page", 'dzsap'),

    'context' => 'content',
    'default' => '',
    'category' => 'counters',
    'it_is_for' => array('shortcode_generator', 'elementor'),
  ),



  'dzsap_meta_playerid'=>array(
    'name' => 'dzsap_meta_playerid',
    'type' => 'text',
    'title' => esc_html__("Link to ID", DZSAP_ID),
    'sidenote' => esc_html__("you need to link to a player id", DZSAP_ID),
    'class' => 'dzs-dependency-field',

    'context' => 'content',
    'default' => '',
    'category' => '',
    'it_is_for' => 'shortcode_generator',
  ),



  'dzsap_meta_replace_songname'=>array(
    'name' => 'dzsap_meta_replace_songname',
    'type' => 'text',
    'category' => 'extra_html',
    'title' => esc_html__("Song name", DZSAP_ID),
    'sidenote' => esc_html__("replace song name or input ", 'dzsap') . "<strong>none</strong> " . esc_html__("for nothing in the field, or input a ", 'dzsap') . '<strong>{{id3}}</strong> ' . esc_html__("for trying to get id3 tags", 'dzsap'),
  ),


  'dzsap_meta_replace_artistname'=>array(
    'name' => 'dzsap_meta_replace_artistname',
    'type' => 'text',
    'it_is_for' => 'for_item_meta_only',
    'title' => esc_html__("Artist name"),
    'sidenote' => esc_html__("Leave nothing in the field for default artist name ( your author name ); input ", 'dzsap') . "<strong>none</strong> " . esc_html__("for nothing in the field, or input a custom name", 'dzsap'),
  ),



  'dzsap_meta_wrapper_image'=>array(
    'name' => 'dzsap_meta_wrapper_image',
    'type' => 'attach',
    'library_type' => 'image',
    'upload_type' => 'image',
    'class' => '',
    'input_extra_classes' => ' dzs-dependency-field',
    'title' => esc_html__("Wrapper Image"),
    'sidenote' => esc_html__("The source, input a mp4 or a youtube link or a youtube id or a vimeo link or a vimeo id"),

    'context' => 'content',
    'category' => 'misc',
    'default' => '',
    'prefer_id' => 'on',
  ),



  'dzsap_meta_wrapper_image_type'=>array(
    'name' => 'dzsap_meta_wrapper_image_type',
    'type' => 'select',
    'title' => esc_html__("Wrapper Image Type", 'dzsap'),

    'context' => 'content',
    'category' => 'misc',
    'options' => array(
      array(
        'label' => esc_html__("Wide Image Wrapper"),
        'value' => 'zoomsounds-wrapper-bg-center',
      ),
      array(
        'label' => esc_html__("Rectangle Image Wrapper"),
        'value' => 'zoomsounds-wrapper-bg-bellow',
      ),
      array(
        'label' => esc_html__("No wrapper"),
        'value' => 'zoomsounds-no-wrapper',
      ),
    ),
    'default' => 'zoomsounds-no-wrapper',
    'dependency' => array(

      array(
        'element' => 'dzsap_meta_wrapper_image',
        'value' => array('anything_but_blank'),
      ),
      array(
        'element' => 'wrapper_image',
        'value' => array('anything_but_blank'),
      ),
      'relation' => 'or',
    )
  ,
  ),



  'dzsap_meta_productid'=>array(
    'name' => 'dzsap_meta_productid',
    'type' => 'text',
    'category' => 'misc',
    'title' => esc_html__("Linked product id", 'dzsap'),
    'sidenote' => esc_html__("link a product id to the zoomsounds item", 'dzsap'),
  ),


  'artistname_link'=>array(
    'name' => 'artistname_link',
    'type' => 'text',
    'category' => 'misc',
    'title' => esc_html__("Link artist name", 'dzsap'),
    'sidenote' => esc_html__("artist name can link to a custom uri", 'dzsap'),
  ),

);

if (isset($dzsap->mainoptions['aws_key']) && $dzsap->mainoptions['aws_key']) {
  array_push($itemMetaArray,
    array(
      'name' => 'dzsap_meta_is_amazon_s3',
      'type' => 'select',
      'title' => esc_html__("Is amazon s3 file", 'dzsap'),
      'sidenote' => esc_html__("it's a amazon s3 private file"),

      'category' => 'misc',
      'options' => array(
        array(
          'label' => esc_html__("no", 'dzsap'),
          'value' => "off",
        ),
        array(
          'label' => esc_html__("yes", 'dzsap'),
          'value' => "on",
        ),
      ),
      'default' => 'off',
      'react_type' => 'string',
    ));
}

return $itemMetaArray;