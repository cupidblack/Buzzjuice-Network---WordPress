<?php

$items = $this->mainitems_configs;


$id_for_preview_player = 'newconfig';




// -- if NOT then it is
if (isset($items[$this->currSlider]['settings']['id'])) {
  $id_for_preview_player = ($items[$this->currSlider]['settings']['id']);
}

$this->videoplayerconfig = '<div class="slider-con" style="display:none;">
        <div class="dzs-container" style="max-width: none;"><div class="settings-con vpconfigs-settings-con dzs-row">';


$this->videoplayerconfig .= '
        <div class="dzs-tabs auto-init-from-vpconfig">

                <div class="dzs-tab-tobe"><div class="tab-menu with-tooltip">
                        <span class="tab-title-icon"><i class="fa fa-tachometer"></i></span>
                        <span class="tab-title-label">' . esc_html__("General", DZSAP_ID) . '</span>
                    </div><div class="tab-content">
                        <br>

        <div class="setting type_all">
            <div class="setting-label">' . esc_html__('Config ID', DZSAP_ID) . '</div>
            <input type="text" class="textinput mainsetting main-id" name="0-settings-id" value="default"/>
            <div class="sidenote">' . esc_html__('Choose an unique id.', DZSAP_ID) . '</div>
        </div>
        <div class="setting styleme setting-skin_ap">
        
            <div class="setting-label">' . esc_html__('Audio Player Skin', DZSAP_ID) . '</div>
            <select class="dzs-style-me textinput opener-listbuttons dzs-dependency-field mainsetting skin-gamma" name="0-settings-skin_ap">
                <option value="skin-wave"></option>
                <option value="skin-silver"></option>
                <option value="skin-aria"></option>
                <option value="skin-default"></option>
                <option value="skin-pro"></option>
                <option>skin-minimal</option>
                <option>skin-minion</option>
                <option>skin-justthumbandbutton</option>
                <option>skin-steel</option>
                <option>skin-redlights</option>
                <option>skin-customcontrols</option>
                <option>skin-customhtml</option>
            </select>
            <ul class="dzs-style-me-feeder">

                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_wave.svg"><span class="option-label">Wave skin full</span></span>
                </div>
                
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_silver.svg"><span class="option-label">Silver skin</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_aria.svg"><span class="option-label">Aria skin</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_default.svg"><span class="option-label">Thumb skin</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_pro.svg"><span class="option-label">Pro skin</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_minimal.svg"><span class="option-label">' . esc_html__("Minimal", DZSAP_ID) . ' ' . esc_html__("skin", DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_minion.svg"><span class="option-label">' . esc_html__("Minion", DZSAP_ID) . ' ' . esc_html__("skin", DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_justthumbandbutton.svg"><span class="option-label">' . esc_html__("Thumb and button", DZSAP_ID) . ' ' . esc_html__("skin", DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_steel.svg"><span class="option-label">' . esc_html__("Steel", DZSAP_ID) . ' ' . esc_html__("skin", DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_redlights.svg"><span class="option-label">' . esc_html__("Redlights", DZSAP_ID) . ' ' . esc_html__("skin", DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_customcontrols.svg"><span class="option-label">' . esc_html__("Custom controls", DZSAP_ID) . ' ' . esc_html__("skin", DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_customhtml.svg"><span class="option-label">' . esc_html__("Custom html", DZSAP_ID) . ' ' . esc_html__("skin", DZSAP_ID) . '</span></span>
                </div>
            </ul>


        </div>
        
        
        
        
';


$dependency = array(

  array(
    'lab' => 'skin_ap',
    'val' => array('skin-customcontrols', 'skin-customhtml'),
  ),

);


$this->videoplayerconfig .= '<div class="setting styleme" data-dependency=\'' . json_encode($dependency) . '\'>
            <div class="setting-label">' . esc_html__("Extra HTML in Player", DZSAP_ID) . '</div>
            <textarea rows="5" type="text" style="width: 100%;" class="textinput mainsetting" name="0-settings-settings_extrahtml_in_player" ></textarea>
            <div class="sidenote">' . esc_html__('enable a embed button for visitors to be able the embed the player on their sites.', DZSAP_ID) . '</div>
        </div>';

$this->videoplayerconfig .= '<div class="setting styleme" >
            <div class="setting-label">' . esc_html__('Enable Embed Button', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-enable_embed_button">
                <option value="off">' . esc_html__("Disable") . '</option>
                <option value="in_player_controls">' . esc_html__("In player controls") . '</option>
                <option value="in_extra_html">' . esc_html__("Below player") . '</option>
                <option value="in_lightbox">' . esc_html__("In multisharer") . '</option>
            </select>
            <div class="sidenote">' . esc_html__('enable a embed button for visitors to be able the embed the player on their sites. ( for multisharer, enable multisharer button below ) ', DZSAP_ID) . '</div>
        </div>
        
        
        
                    
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Hover to Play', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-preview_on_hover">
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('zoomsounds offers the possibility to play tracks on hover', DZSAP_ID) . '</div>
        </div>
        
        ';


$this->videoplayerconfig .= '<div class="setting styleme">
            <div class="setting-label">' . esc_html__('Loop', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-loop">
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('Loop the track on song end', DZSAP_ID) . '</div>
        </div>
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Preload Method', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-preload_method">
                <option>metadata</option>
                <option>auto</option>
                <option>none</option>
            </select>
            <div class="sidenote">' . esc_html__('none - preload no info / metadata - preload only metadata ( total time and thumbnail ) / auto - preload the whole track ', DZSAP_ID) . '</div>
        </div>
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Cue Media', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-cue_method">
                <option>on</option>
                <option>off</option>
            </select>
            <div class="sidenote">' . esc_html__('settings this to OFF will not load the media at all, not even the metadata', DZSAP_ID) . '</div>
        </div>
        <div class="setting type_all">
            <div class="setting-label">' . esc_html__('Play From', DZSAP_ID) . '</div>
            <input type="text" class="textinput mainsetting" name="0-settings-playfrom" value="off"/>
            <div class="sidenote">' . esc_html__('This is a default setting, it can be changed individually per item ( it will be overwritten if set ) . - choose a number of seconds from which the track to play from ( for example if set "70" then the track will start to play from 1 minute and 10 seconds ) or input "last" for the track to play at the last position where it was.', DZSAP_ID) . '</div>
        </div>
        
        
        <div class="setting type_all">
            <div class="setting-label">' . esc_html__('Default Volume', DZSAP_ID) . '</div>
            <input type="text" class="textinput mainsetting" name="0-settings-default_volume" value="default"/>
            <div class="sidenote">' . esc_html__('number / set the default volume 0-1 or "last" for the last known volume', DZSAP_ID) . '</div>
        </div>
        
        
        
        
        
        ';


$lab = 'menu_right_enable_info_btn';
$this->videoplayerconfig .= '
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Enable Info Button in Player', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-' . $lab . '">
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('deprecated - use universal share instead ', DZSAP_ID) . '</div>
        </div>';


$lab = 'menu_right_enable_multishare';
$this->videoplayerconfig .= '
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Enable Universal Share in Player', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-' . $lab . '">
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('enable a button that brings up a lightbox with all share options', DZSAP_ID) . '</div>
        </div>';


$lab = 'settings_exclude_from_list';
$this->videoplayerconfig .= '
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Pause when other player is playing', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-' . $lab . '">
                <option value="off">' . esc_html__('pause when other is playing', DZSAP_ID) . '</option>
                <option value="on">' . esc_html__('player is not affected', DZSAP_ID) . '</option>
            </select>
            <div class="sidenote">' . esc_html__('normally if another player has started playing, the current playing one will pause, you can disable this option here ', DZSAP_ID) . '</div>
        </div>';


$lab = 'player_navigation';
$this->videoplayerconfig .= '
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Player Navigation', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-' . $lab . '">
                <option value="default">' . esc_html__("Detect") . '</option>
                <option value="off">' . esc_html__("Disable") . '</option>
                <option value="on">' . esc_html__("Force On") . '</option>
            </select>
            <div class="sidenote">' . esc_html__('show or not the left and right arrows alongside the play button - leave default for the player to auto detect if it needs them', DZSAP_ID) . '</div>
        </div>';

$lab = 'footer_btn_playlist';
$this->videoplayerconfig .= '
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Sticky player playlist button', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-' . $lab . '">
                <option value="off">' . esc_html__("Disable") . '</option>
                <option value="on">' . esc_html__("Enable") . '</option>
            </select>
            <div class="sidenote">' . esc_html__('show a playlist button in the footer player', DZSAP_ID) . '</div>
        </div>';


$this->videoplayerconfig .= '</div>
</div>';


$this->videoplayerconfig .= '<div class="dzs-tab-tobe tab-disabled"><div class="tab-menu ">
                        &nbsp;&nbsp;
                    </div><div class="tab-content">

                    </div></div><div class="dzs-tab-tobe"><div class="tab-menu with-tooltip">
                        <span class="tab-title-icon"><i class="fa fa-paint-brush"></i></span>
                        <span class="tab-title-label">' . esc_html__("Colors", DZSAP_ID) . '</span>
                    </div><div class="tab-content">';


$this->videoplayerconfig .= '<h2>' . esc_html__('General Colors', DZSAP_ID) . '</h2>';


$lab = DZSAP_PREFIX_FOR_VPCONFIGS . 'color_ui';

$this->videoplayerconfig .= '<div class="setting type_all"><div class="label">' . esc_html__("Icon Background UI", DZSAP_ID) . '</div><input type="text" name="' . $lab . '" class="textinput mainsetting colorpicker-nohash with_colorpicker" value=""/><div class="picker-con"><div class="the-icon"></div><div class="picker"></div></div><div class="sidenote">' . esc_html__('Leave blank for default. Or select a color for the icons.', DZSAP_ID) . '</div></div>';

$lab = DZSAP_PREFIX_FOR_VPCONFIGS . 'color_ui_alternate';

$this->videoplayerconfig .= '<div class="setting type_all"><div class="label">' . esc_html__("Icon Background UI Alternate", DZSAP_ID) . '</div><input type="text" name="' . $lab . '" class="textinput mainsetting colorpicker-nohash with_colorpicker" value=""/><div class="picker-con"><div class="the-icon"></div><div class="picker"></div></div><div class="sidenote">' . esc_html__('for some button skins like Button Style - there is a need for an alternate color', DZSAP_ID) . '</div></div>';


$lab = DZSAP_PREFIX_FOR_VPCONFIGS . 'color_text';

$this->videoplayerconfig .= '<div class="setting type_all"><div class="label">' . esc_html__("Text color", DZSAP_ID) . '</div><input type="text" name="' . $lab . '" class="textinput mainsetting colorpicker-nohash with_colorpicker" value=""/><div class="picker-con"><div class="the-icon"></div><div class="picker"></div></div><div class="sidenote">' . esc_html__('Leave blank for default. Or select a color for the text.', DZSAP_ID) . '</div></div>';


$lab = DZSAP_PREFIX_FOR_VPCONFIGS . 'colorhighlight';

$this->videoplayerconfig .= '<div class="setting type_all"><div class="label">' . esc_html__("Highlight Color", DZSAP_ID) . '</div><input type="text" name="' . $lab . '" class="textinput mainsetting colorpicker-nohash with_colorpicker" value=""/><div class="picker-con"><div class="the-icon"></div><div class="picker"></div></div><div class="sidenote">' . esc_html__('Leave blank for default. Or select a color.', DZSAP_ID) . '</div></div>';


$this->videoplayerconfig .= '<h2>' . esc_html__('Waveform Colors', DZSAP_ID) . '</h2>';

$val = '';

$lab = DZSAP_PREFIX_FOR_VPCONFIGS . 'design_wave_color_bg';

$this->videoplayerconfig .= ' <div class="setting type_all">
                    <div class="label">' . esc_html__('Waveform BG Color', DZSAP_ID) . '</div>
                    <input type="text" name="' . $lab . '" class="textinput mainsetting colorpicker-nohash with_colorpicker" value=""/><div class="picker-con"><div class="the-icon"></div><div class="picker"></div></div>
                </div>';


$lab = DZSAP_PREFIX_FOR_VPCONFIGS . 'design_wave_color_progress';

$this->videoplayerconfig .= ' <div class="setting type_all">
                    <div class="label">' . esc_html__('Waveform Progress Color', DZSAP_ID) . '</div>
                    <input type="text" name="' . $lab . '" class="textinput mainsetting colorpicker-nohash with_colorpicker" value=""/><div class="picker-con"><div class="the-icon"></div><div class="picker"></div></div>
                </div>';


$this->videoplayerconfig .= '<br><br><br><br><br><br><br>'; // -- make room
$this->videoplayerconfig .= '</div>
</div>';


$this->videoplayerconfig .= '<div class="dzs-tab-tobe tab-disabled"><div class="tab-menu ">
                        &nbsp;&nbsp;
                    </div><div class="tab-content">

                    </div></div><div class="dzs-tab-tobe"><div class="tab-menu with-tooltip">
                        <span class="tab-title-icon"><i class="fa fa-paint-brush"></i></span>
                        <span class="tab-title-label">' . esc_html__("Styling") . '</span>
                    </div><div class="tab-content">
                    
                    
                    
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Animate Play Pause', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-design_animateplaypause">
                <option>default</option>
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('fade animation on play / pause', DZSAP_ID) . '</div>
        </div>
        
        
        
        
        
        ';


$lab = 'enable_footer_close_button';
$this->videoplayerconfig .= '
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Enable Footer Close Button', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-' . $lab . '">
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('only for footer players', DZSAP_ID) . '</div>
        </div>';

$lab = 'disable_scrubbar';
$this->videoplayerconfig .= '
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Disable Scrubbar', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-' . $lab . '">
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('disable the scrubbar / wave', DZSAP_ID) . '</div>
        </div>';


$this->videoplayerconfig .= '
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Disable Volume', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-disable_volume">
                <option value="default">' . esc_html__("Detect") . '</option>
                <option value="off">' . esc_html__("Disable") . '</option>
                <option value="on">' . esc_html__("Force On") . '</option>
            </select>
            <div class="sidenote">' . esc_html__('disable the volume bar if set to "on". set to skin default when "default" is set.', DZSAP_ID) . '</div>
        </div>';


$this->videoplayerconfig .= '
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    </div>
                    
                    </div><div class="dzs-tab-tobe tab-disabled"><div class="tab-menu ">
                        &nbsp;&nbsp;
                    </div><div class="tab-content">

                    </div></div><div class="dzs-tab-tobe"><div class="tab-menu with-tooltip">
                        <span class="tab-title-icon"><i class="fa fa-bar-chart"></i></span>
                        <span class="tab-title-label">' . 'Skin-Wave' . '</span>
                    </div><div class="tab-content">
                    <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Dynamic Waves', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-skinwave_dynamicwaves">
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('*only on skin-wave - dynamic waves that act on volume change', DZSAP_ID) . '</div>
        </div>';


ob_start();

?>
  <div class="setting">
    <h4 class="label"><?php echo esc_html__('Waveform Mode', DZSAP_ID); ?></h4>
    <?php


    $lab = DZSAP_PREFIX_FOR_VPCONFIGS . 'skinwave_wave_mode';

    $opts = array(
      array(
        'lab' => esc_html__("Default", DZSAP_ID),
        'val' => ''
      ),
      array(
        'lab' => esc_html__("Image", DZSAP_ID),
        'val' => 'image'
      ),
      array(
        'lab' => esc_html__("Canvas", DZSAP_ID),
        'val' => 'canvas'
      ),
      array(
        'lab' => esc_html__("Line", DZSAP_ID),
        'val' => 'line'
      ),
    );


    echo DZSHelpers::generate_select($lab, array('class' => 'textinput mainsetting  dzs-dependency-field  styleme', 'options' => $opts, 'seekval' => 'canvas')); ?>


    <div class="sidenote"><?php echo esc_html__("this is the wave style ") . '<br>';
      printf("<strong> %s </strong> - %s <br>", esc_html__("Image"), esc_html__("is just a image png that must be generated from the backend"));
      echo sprintf("<strong> %s </strong> - %s <br>", esc_html__("Canvas"), esc_html__("is a new and more immersive mode to show the waves. you can control color more easily, reflection size and wave bars number")); ?></div>
  </div>


<?php


?>
  <div class="setting">
    <h4 class="label"><?php echo esc_html__('Reflection Size', DZSAP_ID); ?></h4>
    <?php


    $lab = DZSAP_PREFIX_FOR_VPCONFIGS . 'skinwave_wave_mode_canvas_reflection_size';

    $opts = array(
      array(
        'lab' => esc_html__("Default"),
        'val' => ''
      ),
      array(
        'lab' => esc_html__("None"),
        'val' => '0'
      ),
      array(
        'lab' => esc_html__("Normal"),
        'val' => '0.25'
      ),
      array(
        'lab' => esc_html__("Big"),
        'val' => '0.5'
      ),
    );


    echo DZSHelpers::generate_select($lab, array('class' => ' styleme mainsetting', 'options' => $opts, 'seekval' => '0.25')); ?>


    <div class="sidenote"><?php echo esc_html__("the waveform bars size / the number of bars on screen") . ''; ?></div>
  </div>


<?php

$dependency = array(

  array(
    'element' => 'skinwave_wave_mode',
    'value' => array('canvas'),
  ),
);
?>
  <div class="setting">
    <h4 class="label"><?php echo esc_html__('Waves Number', DZSAP_ID); ?></h4>
    <?php


    $lab = 'skinwave_wave_mode_canvas_waves_number';
    $name = DZSAP_PREFIX_FOR_VPCONFIGS . $lab;

    echo DZSHelpers::generate_input_text($name, array(
      'class' => '  mainsetting',
      'type' => 'text',
      'seekval' => ''
    )); ?>


    <div class="sidenote"><?php echo sprintf(esc_html__("%s - %s default global option %s  %s - %s pixel %s  %s - %s pixels %s     %s - %s pixels %s  %s - means that there will be the number of waves that you set - for example 100 means 100 waves                           ")
          , '<strong></strong>'
          , 'leave nothing and option will come from global settings'
          , '<br>'
          , '<strong>1</strong>'
          , '1'
          , '<br>'
          , '<strong>2</strong>'
          , '2'
          , '<br>'
          , '<strong>3</strong>'
          , '3'
          , '<br>'
          , '<strong>any number over 3 - </strong>'

        ) . ''; ?></div>
  </div>
  <div class="setting">
    <h4 class="label"><?php echo esc_html__('Waves Bar Spacing', DZSAP_ID); ?></h4>
    <?php


    $lab = 'skinwave_wave_mode_canvas_waves_padding';
    $name = DZSAP_PREFIX_FOR_VPCONFIGS . $lab;

    echo DZSHelpers::generate_input_text($name, array(
      'class' => ' mainsetting ',
      'type' => 'text',

      'seekval' => ''
    )); ?>


    <div
      class="sidenote"><?php echo esc_html__("spacing between bars - leave nothing here and this will come from global") . ''; ?></div>
  </div>


<?php

;
?>
  <div class="setting">
  <h4 class="label"><?php echo esc_html__('Normalize ', DZSAP_ID); ?></h4>
  <?php


  $thelab = 'skinwave_wave_mode_canvas_normalize';
  $lab = DZSAP_PREFIX_FOR_VPCONFIGS . $thelab;

  $opts = array(
    array(
      'lab' => esc_html__("Default", DZSAP_ID),
      'val' => ''
    ),
    array(
      'lab' => esc_html__("Normalize Waves", DZSAP_ID),
      'val' => 'on'
    ),
    array(
      'lab' => esc_html__("Do not normalize", DZSAP_ID),
      'val' => 'off'
    ),
  );


  $val = 'on';


  echo DZSHelpers::generate_select($lab, array('class' => 'textinput mainsetting  styleme', 'options' => $opts, 'seekval' => '')); ?>


  <div
    class="sidenote"><?php echo esc_html__("normalize the waves to look like they have continuity , or disable normalizing to make the waveforms follow the real sound") . ''; ?></div>
  </div><?php
$this->videoplayerconfig .= str_replace(array("\r", "\n"), '', ob_get_clean());;


$this->videoplayerconfig .= '<div class="setting styleme">
            <div class="setting-label">' . esc_html__('Enable Spectrum', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-skinwave_enablespectrum">
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('only on skin-wave - enable a realtime spectrum analyzer instead of the static generated waveform / the file must be on the same server for security issues', DZSAP_ID) . '<br>
            <strong>' . esc_html__('important', DZSAP_ID) . ': </strong>' . esc_html__('only use audios hosted on your own server', DZSAP_ID) . '</div>
        </div>';

$this->videoplayerconfig .= '<div class="setting styleme">
            <div class="setting-label">' . esc_html__('Timer indicators are static', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-skinwave_timer_static">
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('make the timing indicators static ( not move )', DZSAP_ID) . '</div>
        </div>';


$this->videoplayerconfig .= '
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Enable Reflect', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-skinwave_enablereflect">
                <option>on</option>
                <option>off</option>
            </select>
            <div class="sidenote">' . esc_html__('*only on skin-wave - enable a small reflection of the waves / spectrum', DZSAP_ID) . '</div>
        </div>
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Enable Commenting', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-skinwave_comments_enable">
                <option>off</option>
                <option>on</option>
            </select>
            <div class="sidenote">' . esc_html__('*only on skin-wave - enable time-based commenting', DZSAP_ID) . '</div>
        </div>
        
        
        
        <div class="setting setting-skin-layout styleme">
            <div class="setting-label">' . esc_html__('Skin layout', DZSAP_ID) . '</div>
            <select class=" mainsetting  dzs-style-me textinput opener-listbuttons  mainsetting skin-gamma" name="0-settings-skinwave_mode">
                <option value="normal">' . esc_html__('Normal', DZSAP_ID) . '</option>
                <option value="small">' . esc_html__('Slick', DZSAP_ID) . '</option>
                <option value="alternate">' . esc_html__('Alternate', DZSAP_ID) . '</option>
                <option value="bigwavo">' . esc_html__('Big Wavo', DZSAP_ID) . '</option>
                <option value="nocontrols">' . esc_html__('Just Wave', DZSAP_ID) . '</option>
            </select>
            <ul class="dzs-style-me-feeder">

                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/admin/skin-wave-mode--normal.png"><span class="option-label">' . esc_html__('Mode normal', DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/admin/skin-wave-mode--small.png"><span class="option-label">' . esc_html__('Mode slick', DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/admin/skin-wave-mode--alternate.png"><span class="option-label">' . esc_html__('Mode alternate', DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/admin/skin-wave-mode--bigwavo.png"><span class="option-label">' . esc_html__('Mode big wavo', DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/admin/skin-wave-mode--nocontrols.png"><span class="option-label">' . esc_html__('Just wave', DZSAP_ID) . '</span></span>
                </div>
            </ul>
            <div class="sidenote">' . esc_html__('choose the normal or slick theming', DZSAP_ID) . '</div>
        </div>
        
        
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Wave Mode', DZSAP_ID) . '</div>
            <select class="textinput mainsetting styleme" name="0-settings-skinwave_wave_mode_canvas_mode">
                <option value="normal">' . esc_html__('Bar', DZSAP_ID) . '</option>
                <option value="reflecto">' . esc_html__('Wave ( reflecto )', DZSAP_ID) . '</option>
            </select>
            <div class="sidenote">' . esc_html__('choose a bar type format or a wave for the waveform style', DZSAP_ID) . '</div>
        </div>
        <div class="setting styleme">
            <div class="setting-label">' . esc_html__('Button Style', DZSAP_ID) . '</div>
            <select class="dzs-style-me textinput opener-listbuttons dzs-dependency-field mainsetting skin-gamma" name="0-settings-button_aspect">
                <option value="default">' . esc_html__('Default', DZSAP_ID) . '</option>
                <option value="button-aspect-noir">' . esc_html__('Aspect Noir', DZSAP_ID) . '</option>
                <option value="button-aspect-noir button-aspect-noir--filled">' . esc_html__('Aspect Noir Filled', DZSAP_ID) . '</option>
                <option value="button-aspect-noir button-aspect-noir--stroked">' . esc_html__('Aspect Noir Stroked', DZSAP_ID) . '</option>
            </select>
            <ul class="dzs-style-me-feeder">

                <div class="bigoption">
                    <span class="option-con"><img src="https://i.imgur.com/aVIk654.png"><span class="option-label">' . esc_html__('Wave skin full', DZSAP_ID) . '</span></span>
                </div>
                
                <div class="bigoption">
                    <span class="option-con"><img src="https://i.imgur.com/oVUgjff.png"><span class="option-label">' . esc_html__('Aspect noir', DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_aria.svg"><span class="option-label">' . esc_html__('Aspect noir filled', DZSAP_ID) . '</span></span>
                </div>
                <div class="bigoption">
                    <span class="option-con"><img src="' . DZSAP_BASE_URL . 'assets/svg/skin_default.svg"><span class="option-label">' . esc_html__('Aspect noir stroked', DZSAP_ID) . '</span></span>
                </div>
            </ul>
            <div class="sidenote">' . esc_html__('only for skin-wave', DZSAP_ID) . '</div>
        </div>
        
                    
                    
                    
                    
                    
                    
                    
                    
                    </div>
                    
                    </div><div class="dzs-tab-tobe tab-disabled"><div class="tab-menu ">
                        &nbsp;&nbsp;
                    </div><div class="tab-content">

                    </div></div><div class="dzs-tab-tobe"><div class="tab-menu with-tooltip">
                    
                        <span class="tab-title-icon"><i class="fa fa-puzzle-piece"></i></span>
                        <span class="tab-title-label">' . esc_html__("Misc") . '</span>
                    </div><div class="tab-content">
                    
                    
                    
        
                   
        <div class="setting type_all">
            <div class="setting-label">' . esc_html__('Extra Classes for Player', DZSAP_ID) . '</div>
            <input type="text" class="textinput mainsetting" name="0-settings-extra_classes_player" />
            <div class="sidenote">' . esc_html__('extra classes include  ', DZSAP_ID) . '<strong>disable-all-but-play-btn</strong> <strong>time-total-visible</strong> <strong>disable-scrubbar</strong> <strong>disable-meta-artist-con</strong> <strong>disable-the-thumb-con</strong> <strong>disable-extrahtml</strong> </div>
        </div>
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
        <div class="setting type_all">
            <div class="setting-label">' . esc_html__('Extra HTML After Artist', DZSAP_ID) . '</div>
            <textarea rows="5" type="text" style="width: 100%;" class="textinput mainsetting" name="0-settings-settings_extrahtml_after_artist" ></textarea>
            <div class="sidenote">' . esc_html__('extra html on the rift of the artist field ( first line ) ', DZSAP_ID) . '</div>
        </div>
                    
                    
                    
                    
                    
        <div class="setting type_all">
            <div class="setting-label">' . esc_html__('Extra HTML in Right Controls', DZSAP_ID) . '</div>
            <textarea rows="5" type="text" style="width: 100%;" class="textinput mainsetting" name="0-settings-js_settings_extrahtml_in_float_right_from_config" ></textarea>
            <div class="sidenote">' . esc_html__('extra html on the in the right controls ', DZSAP_ID) . '</div>
        </div>
                    
                    
                    
                    
                    
        <div class="setting type_all">
            <div class="setting-label">' . esc_html__('Extra HTML in Bottom Controls', DZSAP_ID) . '</div>
            <textarea rows="5" type="text" style="width: 100%;" class="textinput mainsetting" name="0-settings-js_settings_extrahtml_in_bottom_controls_from_config" ></textarea>
            <div class="sidenote">' . esc_html__('extra html on the in the Bottom controls ', DZSAP_ID) . '</div>
        </div>
                    
                    
                    
                    
                    
        <div class="setting type_all">
            <div class="setting-label">' . esc_html__('Extra HTML in After Play Button', DZSAP_ID) . '</div>
            <textarea rows="5" type="text" style="width: 100%;" class="textinput mainsetting" name="0-settings-settings_extrahtml_after_playpause_from_config" ></textarea>
            <div class="sidenote">' . esc_html__('Extra HTML in After Play Button', DZSAP_ID) . '</div>
        </div>
                    
                    
        <div class="setting type_all">
            <div class="setting-label">' . esc_html__('Extra HTML after Controls', DZSAP_ID) . '</div>
            <textarea rows="5" type="text" style="width: 100%;" class="textinput mainsetting" name="0-settings-settings_extrahtml_after_con_controls_from_config" ></textarea>
            <div class="sidenote">' . esc_html__('Extra HTML after Controls', DZSAP_ID) . '</div>
        </div>
                    
                    
        <div class="setting type_all">
            <div class="setting-label">' . esc_html__('Extra CSS', DZSAP_ID) . '</div>
            <textarea rows="5" type="text" style="width: 100%;" class="textinput mainsetting" name="0-settings-config_extra_css" ></textarea>
            <div class="sidenote">' . esc_html__('Extra CSS for this player configuration.', DZSAP_ID) . ' ' . esc_html__('Use', DZSAP_ID) . ' <strong>$classmain</strong> ' . esc_html__('for replacing the player configuration selector', DZSAP_ID) . '</div>
        </div>
                    
                    
                    </div>
                    
                    </div>
                    
                    
                    
                    
                    </div><!-- end .tabs-->
        
        
        ';

$this->videoplayerconfig .= '<div class="preview-player-iframe-con">
      <div class="dzs--preloader-spinner  "></div>


      <iframe class="preview-player-iframe" width="100%" height="333%"
              src="' . admin_url('admin.php?page=dzsap-mo&' . DZSAP_ADMIN_PREVIEW_PLAYER_PARAM . '=on&config=' . urlencode($id_for_preview_player) . '') . '"></iframe>
      <div class="button-secondary btn-refresh-preview"><i
          class="fa fa-refresh"></i><span class="dzs-label">' . esc_html__("Refresh preview", DZSAP_ID) . '</span></div>
    </div>';


$this->videoplayerconfig .= '</div>'; // -- end .dzs-container


$this->videoplayerconfig .= '

        
        ';


$val = 'ea8c52';


$this->videoplayerconfig .= '
        </div><!--end settings con-->
        <div class="clearboth"></div>
        </div>';


