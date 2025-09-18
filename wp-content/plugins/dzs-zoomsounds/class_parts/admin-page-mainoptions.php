<?php


include_once DZSAP_BASE_PATH . 'inc/php/dzs-shared/view_layout.php';
include_once DZSAP_BASE_PATH . 'inc/php/admin-pages/main-options/admin-main-options--meta-print.php';
include_once DZSAP_BASE_PATH . 'inc/php/admin-pages/main-options/admin-main-options--system-check-print.php';
global $dzsap;

$ignoreKeys = $dzsap->mainoptions['settings_ignore_keys'];
?>

  <div class="wrap">
    <h2><?php echo esc_html__('ZoomSounds Main Settings', DZSAP_ID); ?></h2>
    <br/>

    <a class="ultibox-item button-secondary" href="<?php echo DZSAP_BASE_URL; ?>readme/index.html"
       data-suggested-width="95vw" data-scaling="fill"
       data-suggested-height="95vh"><?php echo esc_html__("Documentation", DZSAP_ID); ?></a>

    <a
      href="<?php echo admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_MAINOPTIONS . '&dzsap_shortcode_builder=on'); ?>"
      target="_blank"
      class="button-secondary action"><?php echo esc_html__('Gallery Generator', DZSAP_ID); ?></a>

    <a
      href="<?php echo admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_MAINOPTIONS . '&' . DZSAP_ADMIN_SHORTCODE_PLAYER_GENERATOR_KEY . '=on'); ?>"
      target="_blank"
      class="button-secondary action"><?php echo esc_html__('Player Generator', DZSAP_ID); ?></a>

    <a
      href="<?php echo admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_MAINOPTIONS . '&' . DZSAP_ADMIN_PAGENAME_MAINOPTIONS_WAVE_GENERATOR . '=on'); ?>"
      target="_blank"
      class="button-secondary action"><?php echo esc_html__('Wave Generator', DZSAP_ID); ?></a>


    <?php
    do_action('dzsap_mainoptions_before_tabs');
    ?>


    <div class="dzs--main-setings--search-con">
      <br>
      <div>
        <input class="dzs-big-input" id="dzs--settings-search" type="search"
               placeholder="<?php echo esc_html__('Search...', DZSAP_ID); ?>"/>
        <i class="dzs--settings-search--search-icon">
          <?php

          echo dzs_read_from_file_ob(DZSAP_BASE_PATH . 'assets/svg/search.svg');
          ?>
        </i>
      </div>
    </div>
    <?php

    // -- nags
    $currentV = $dzsap->instanceDb->get_tableVersion();

    if (!($currentV >= DZSAP_ANALYTICS_TABLE_CURRENT_VERSION)) {
      echo dzs_view_generateWarning('<strong>' . esc_html__("Analytics Database", DZSAP_ID) . ' ' . esc_html__("version", DZSAP_ID) . '</strong>: ' . $dzsap->instanceDb->get_tableVersion() . ' / latest version: ' . DZSAP_ANALYTICS_TABLE_CURRENT_VERSION . ' - ' . esc_html__("please repair from SYSTEM CHECK menu", DZSAP_ID) . '');
    }


    if (isset($_GET[DZSAP_ANALYTICS_QUERY_REPAIR]) && $_GET[DZSAP_ANALYTICS_QUERY_REPAIR] == 'on') {
      $dzsap->instanceDb->repair_tableAnalytics();
    }
    ?>

    <form class="mainsettings">
      <div id="dzs-tabs--main-options" class="dzs-tabs auto-init" data-options="{ 'design_tabsposition' : 'top'
,design_transition: 'fade'
,design_tabswidth: 'default'
,toggle_breakpoint : '400'
,toggle_type: 'accordion'
,toggle_type: 'accordion'
,settings_enable_linking : 'on'
,settings_appendWholeContent: true
,refresh_tab_height: '1000'
}">

        <div class="dzs-tab-tobe">
          <div class="tab-menu with-tooltip">
            <i class="fa fa-tachometer"></i> <?php echo esc_html__("Settings", DZSAP_ID); ?>
          </div>
          <div class="tab-content">
            <br>


            <!-- general settings tab content -->


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Enable Global Footer Player', DZSAP_ID); ?></h4>
              <?php


              $lab = 'enable_global_footer_player';

              $vpconfigs_arr = array(
                array('lab' => esc_html__("Off", DZSAP_ID), 'val' => 'off')
              );

              $i23 = 0;
              foreach ($dzsap->mainitems_configs as $vpconfig) {


                $auxa = array(
                  'lab' => $vpconfig['settings']['id'],
                  'val' => $vpconfig['settings']['id'],
                  'extraattr' => 'data-sliderlink="' . $i23 . '"',
                );

                array_push($vpconfigs_arr, $auxa);

                $i23++;
              }

              echo DZSHelpers::generate_select($lab, array('class' => 'vpconfig-select styleme', 'options' => $vpconfigs_arr, 'seekval' => $dzsap->mainoptions[$lab])); ?>

              <div class="edit-link-con" style="margin-top: 10px;"></div>

              <div
                class="sidenote"><?php echo esc_html__("this will output the footer player on the whole site.", DZSAP_ID); ?></div>
            </div>


            <?php

            $dependency = array(

              array(
                'element' => 'skinwave_wave_mode',
                'value' => array('canvas'),
              ),
            );
            ?>
            <div class="setting" data-dependency='<?php echo json_encode($dependency); ?>'>
              <h4 class="setting-label"><?php echo esc_html__('Normalize ', DZSAP_ID); ?></h4>
              <?php


              $lab = 'skinwave_wave_mode_canvas_normalize';

              $opts = array(
                array(
                  'lab' => esc_html__("Normalize Waves", DZSAP_ID),
                  'val' => 'on'
                ),
                array(
                  'lab' => esc_html__("Do not normalize", DZSAP_ID),
                  'val' => 'off'
                ),

              );


              echo DZSHelpers::generate_select($lab, array('class' => ' styleme', 'options' => $opts, 'seekval' => $dzsap->mainoptions[$lab])); ?>


              <div
                class="sidenote"><?php echo esc_html__("normalize the waves to look like they have continuity , or disable normalizing to make the waveforms follow the real sound", DZSAP_ID) . ''; ?></div>
            </div>


            <div class="setting">
              <h4
                class="setting-label"><?php echo esc_html__('Allow Download Only for Registered Users ', DZSAP_ID); ?></h4>
              <?php


              $lab = 'allow_download_only_for_registered_users';

              $opts = array(
                array(
                  'lab' => esc_html__("Off", DZSAP_ID),
                  'val' => 'off'
                ),
                array(
                  'lab' => esc_html__("On", DZSAP_ID),
                  'val' => 'on'
                ),

              );


              echo DZSHelpers::generate_select($lab, array(
                'class' => ' styleme dzs-dependency-field',
                'options' => $opts,
                'seekval' => $dzsap->mainoptions[$lab]));
              ?>


              <div
                class="sidenote"><?php echo esc_html__("allow the download tab only for registered users", DZSAP_ID) . ''; ?></div>
            </div>


            <?php
            $lab = 'exclude_from_search';
            $val = 'off';
            if (isset($dzsap->mainoptions[$lab]) && $dzsap->mainoptions[$lab]) {
              $val = $dzsap->mainoptions[$lab];
            }

            include_once DZSAP_BASE_PATH . 'inc/php/admin/admin-echo-registeredUsersCapabilityMainOption.php';
            dzsap_admin_echo_registeredUsersCapabilityMainOption($dzsap);
            ?>
            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Exclude audio items from Search', DZSAP_ID); ?></h4>
              <?php


              $opts = array(
                array(
                  'lab' => esc_html__("Include", DZSAP_ID),
                  'val' => 'off'
                ),
                array(
                  'lab' => esc_html__("Exclude", DZSAP_ID),
                  'val' => 'on'
                ),
              );


              echo DZSHelpers::generate_select($lab, array(
                'class' => ' styleme ',
                'options' => $opts,
                'seekval' => $val
              ));

              ?>


              <div
                class="sidenote"><?php echo esc_html__("select a class to restrict downloads too", DZSAP_ID) . ''; ?></div>
            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('SoundCloud API Key', DZSAP_ID); ?></h4>
              <?php
              $val = '';
              if ($dzsap->mainoptions['soundcloud_api_key']) {
                $val = $dzsap->mainoptions['soundcloud_api_key'];
              }
              echo DZSHelpers::generate_input_text('soundcloud_api_key', array('val' => '', 'seekval' => $val, 'type' => '', 'class' => ''));
              ?>
              <div
                class="sidenote"><?php echo esc_html__('You can get one by going to <a href="https://soundcloud.com/you/apps/new">here</a> and registering a new app. The api key wil lbe the client ID you get at the end.', DZSAP_ID); ?></div>
            </div>

            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Play Remember Time', DZSAP_ID); ?></h4>
              <?php
              $lab = 'play_remember_time';
              $val = '';
              if ($dzsap->mainoptions[$lab]) {
                $val = $dzsap->mainoptions[$lab];
              }
              echo DZSHelpers::generate_input_text($lab, array('val' => '', 'seekval' => $val, 'type' => '', 'class' => ''));
              ?>
              <div
                class="sidenote"><?php echo esc_html__('plays are regitered by ip - you can specify a time ( in minutes ) at which plays are remembers. after this time - a new play can be registered for the same ip', DZSAP_ID); ?></div>
            </div>
            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Like Markup Part 1', DZSAP_ID); ?></h4>
              <?php
              $val = '';
              $lab = 'str_likes_part1';
              if ($dzsap->mainoptions[$lab]) {
                $val = stripslashes($dzsap->mainoptions[$lab]);
              }
              echo dzsap_misc_input_textarea($lab, array('val' => '', 'seekval' => $val, 'type' => '', 'class' => ''));
              ?>
              <div class="sidenote"><?php echo esc_html__('Replace with any html. Default is - ', DZSAP_ID); ?>
                <pre><?php echo esc_html('<span class="btn-zoomsounds btn-like zoomsounds-player-btn-like"><span class="the-icon">{{heart_svg}}</span><span class="the-label hide-on-active">Like</span><span class="the-label show-on-active">Liked</span></span>') ?></pre>
              </div>
            </div>
            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Plays Markup', DZSAP_ID); ?></h4>
              <?php
              $val = '';
              $lab = 'str_views';
              if ($dzsap->mainoptions[$lab]) {
                $val = stripslashes($dzsap->mainoptions[$lab]);
              }
              echo dzsap_misc_input_textarea($lab, array('val' => '', 'seekval' => $val, 'type' => '', 'class' => ''));
              ?>
              <div class="sidenote"><?php echo esc_html__('Replace with any html. Default is - ', DZSAP_ID); ?>
                <pre><?php echo esc_html('<div class="dzsap-counter counter-hits"><i class="fa fa-play"></i><span class="the-number">{{get_plays}}</span></div>') ?></pre>
              </div>
            </div>
            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Like Markup Part 2', DZSAP_ID); ?></h4>
              <?php
              $val = '';
              $lab = 'str_likes_part2';
              if ($dzsap->mainoptions[$lab]) {
                $val = stripslashes($dzsap->mainoptions[$lab]);
              }
              echo dzsap_misc_input_textarea($lab, array('val' => '', 'seekval' => $val, 'type' => '', 'class' => ''));
              ?>
              <div class="sidenote"><?php echo esc_html__('Replace with any html. Default is - ', DZSAP_ID); ?>
                <pre><?php echo esc_html('<div class="dzsap-counter counter-likes"><i class="fa fa-heart"></i><span class="the-number">{{get_likes}}</span></div>') ?></pre>
              </div>
            </div>
            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Rates Markup', DZSAP_ID); ?></h4>
              <?php
              $val = '';
              $lab = 'str_rates';
              if ($dzsap->mainoptions[$lab]) {
                $val = stripslashes($dzsap->mainoptions[$lab]);
              }
              echo dzsap_misc_input_textarea($lab, array('val' => '', 'seekval' => $val, 'type' => '', 'class' => ''));
              ?>
              <div class="sidenote"><?php echo esc_html__('Replace with any html. Default is - ', DZSAP_ID); ?>
                <pre><?php echo esc_html('<div class="counter-rates"><span class="the-number">{{get_rates}}</span> rates</div>', DZSAP_ID) ?></pre>
              </div>
            </div>


            <?php

            $config_main_options = include(DZSAP_BASE_PATH . 'configs/config-main-options.php');



            echo DZSZoomSoundsHelper::generateOptionsFromConfigForMainOptions($config_main_options, 'main_settings', $dzsap, $ignoreKeys);

            ?>


            <!-- end general settings -->


            <div class="setting">
              <h4
                class="setting-label"><?php echo esc_html__('Replace default wordpress audio shortcode', DZSAP_ID); ?></h4>
              <?php


              $lab = 'replace_audio_shortcode';

              $vpconfigs_arr = array(
                array('lab' => esc_html__("Off", DZSAP_ID), 'val' => 'off')
              );

              $i23 = 0;
              foreach ($dzsap->mainitems_configs as $vpconfig) {


                $auxa = array(
                  'lab' => $vpconfig['settings']['id'],
                  'val' => $vpconfig['settings']['id'],
                  'extraattr' => 'data-sliderlink="' . $i23 . '"',
                );

                array_push($vpconfigs_arr, $auxa);

                $i23++;
              }

              echo DZSHelpers::generate_select($lab, array('class' => 'vpconfig-select styleme', 'options' => $vpconfigs_arr, 'seekval' => $dzsap->mainoptions[$lab])); ?>

              <div class="edit-link-con" style="margin-top: 10px;"></div>

              <div
                class="sidenote"><?php echo esc_html__("select a audio player configuration with which to replace the default wordpress player", DZSAP_ID); ?></div>
            </div>


            <?php

            $lab = 'replace_audio_shortcode_extra_args';


            ?>
            <div class="setting">
              <h4
                class="setting-label"><?php echo esc_html__('Extra arguments for default audio shortcode', DZSAP_ID); ?></h4>
              <?php echo dzsap_misc_input_textarea($lab, array('val' => '', 'seekval' => stripslashes($dzsap->mainoptions[$lab]))); ?>

              <div class="sidenote"><?php echo esc_html__("in json format", DZSAP_ID); ?>
                ; <?php echo esc_html__("for example enter: ", DZSAP_ID); ?>
                <code>{"enable_likes":"on"}</code><?php echo esc_html__("for showing likes, or enter: ", DZSAP_ID); ?>
                <code>{"enable_likes":"on","enable_views":"on"}</code><?php echo esc_html__("for enabling likes AND plays", DZSAP_ID); ?>
              </div>
            </div>


            <div class="setting">
              <h4
                class="setting-label"><?php echo esc_html__('Play default shortcode in footer player', DZSAP_ID); ?></h4>
              <?php


              $lab = 'replace_audio_shortcode_play_in_footer';


              echo DZSHelpers::generate_input_text($lab, array('id' => $lab, 'class' => 'fake-input', 'val' => 'off', 'input_type' => 'hidden'));


              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>


              <div
                class="sidenote"><?php echo esc_html__("only if a player configuration is selected for the default player, then this will play in the footer player"); ?></div>
            </div>
          </div>
        </div>

        <div class="dzs-tab-tobe tab-disabled">
          <div class="tab-menu ">
            &nbsp;&nbsp;
          </div>
          <div class="tab-content">

          </div>
        </div>


        <div class="dzs-tab-tobe">
          <div class="tab-menu with-tooltip">
            <i class="fa fa-shopping-cart"></i> <?php echo esc_html__("WooCommerce ", DZSAP_ID) ?>
          </div>
          <div class="tab-content">
            <br>

            <h3><?php echo esc_html__('Single product', DZSAP_ID); ?></h3>

            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Single Product ZoomSounds Preview', DZSAP_ID); ?></h4>
              <?php


              $lab = 'wc_single_product_player';


              echo DZSHelpers::generate_select($lab, array('class' => 'vpconfig-select styleme', 'options' => $vpconfigs_arr, 'seekval' => $dzsap->mainoptions[$lab])); ?>

              <div class="edit-link-con" style="margin-top: 10px;"></div>

              <div
                class="sidenote"><?php echo esc_html__("this will output a preview player in the woocommerce product page if a track is set in the zoomsounds settings of the product.", DZSAP_ID); ?></div>
            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Product Player Position', DZSAP_ID); ?></h4>
              <?php


              $lab = 'wc_single_player_position';


              echo DZSHelpers::generate_select($lab, array('class' => 'vpconfig-select styleme', 'options' => array(
                array(
                  'label' => esc_html__("Top of product"),
                  'value' => 'top',
                ),
                array(
                  'label' => esc_html__("Overlay product image"),
                  'value' => 'overlay',
                ),
                array(
                  'label' => esc_html__("Below product"),
                  'value' => 'bellow',
                ),
              ), 'seekval' => $dzsap->mainoptions[$lab])); ?>

              <div class="edit-link-con" style="margin-top: 10px;"></div>

              <div
                class="sidenote"><?php echo esc_html__("this will output a preview player in the woocommerce single product page if a track is set in the zoomsounds settings of the product."); ?></div>
            </div>

            <h3><?php echo esc_html__('Loop product', DZSAP_ID); ?></h3>

            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Loop Product ZoomSounds Preview', DZSAP_ID); ?></h4>
              <?php


              $lab = 'wc_loop_product_player';


              echo DZSHelpers::generate_select($lab, array('class' => ' styleme', 'options' => $vpconfigs_arr, 'seekval' => $dzsap->mainoptions[$lab])); ?>

              <div class="edit-link-con" style="margin-top: 10px;"></div>

              <div
                class="sidenote"><?php echo esc_html__("this will output a preview player in the woocommerce shop page if a track is set in the zoomsounds settings of the product."); ?></div>
            </div>

            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Product Loop Position', DZSAP_ID); ?></h4>
              <?php


              $lab = 'wc_loop_player_position';


              echo DZSHelpers::generate_select($lab, array('class' => 'dzs-dependency-field vpconfig-select styleme', 'options' => array(
                array(
                  'label' => esc_html__("Top of product"),
                  'value' => 'top',
                ),
                array(
                  'label' => esc_html__("Overlay product image"),
                  'value' => 'overlay',
                ),
                array(
                  'label' => esc_html__("Below product"),
                  'value' => 'bellow',
                ),
              ), 'seekval' => $dzsap->mainoptions[$lab])); ?>

              <div class="edit-link-con" style="margin-top: 10px;"></div>

              <div
                class="sidenote"><?php echo esc_html__("this will output a preview player in the woocommerce shop page if a track is set in the zoomsounds settings of the product."); ?></div>
            </div>

            <?php
            $dependency = array(

              array(
                'element' => 'wc_loop_player_position',
                'value' => array('overlay'),
              ),
            );
            ?>
            <div class="setting" data-dependency='<?php echo json_encode($dependency); ?>'>
              <h4 class="setting-label"><?php echo esc_html__('Custom Wrapper Selector', DZSAP_ID); ?></h4>
              <?php
              $lab = 'wc_loop_player_position__overlay__wrapper_selector';
              $val = '';
              if ($dzsap->mainoptions[$lab]) {
                $val = $dzsap->mainoptions[$lab];
              }
              echo DZSHelpers::generate_input_text($lab, array('val' => '', 'seekval' => $val, 'type' => '', 'class' => ''));
              ?>
              <div
                class="sidenote"><?php echo esc_html__('plays are regitered by ip - you can specify a time ( in minutes ) at which plays are remembers. after this time - a new play can be registered for the same ip', DZSAP_ID); ?></div>
            </div>

            <h3><?php echo esc_html__('General WooCommerce Players', DZSAP_ID); ?></h3>

            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Play in Sticky Player ? ', DZSAP_ID); ?></h4>
              <?php


              $lab = 'wc_product_play_in_footer';


              echo DZSHelpers::generate_select($lab, array('class' => ' styleme', 'options' => array(
                array(
                  'label' => esc_html__("Off"),
                  'value' => 'off',
                ),
                array(
                  'label' => esc_html__("On"),
                  'value' => 'on',
                ),
              ), 'seekval' => $dzsap->mainoptions[$lab])); ?>

              <div class="edit-link-con" style="margin-top: 10px;"></div>

              <div
                class="sidenote"><?php echo esc_html__("this will output a preview player in the woocommerce shop page if a track is set in the zoomsounds settings of the product."); ?></div>
            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Try to hide real url', DZSAP_ID); ?> (beta)</h4>
              <?php


              $lab = 'try_to_hide_url';


              echo DZSHelpers::generate_select($lab, array('class' => 'styleme', 'options' => array(
                array(
                  'label' => esc_html__("Off"),
                  'value' => 'off',
                ),
                array(
                  'label' => esc_html__("On"),
                  'value' => 'on',
                ),
              ), 'seekval' => $dzsap->mainoptions[$lab])); ?>


              <div
                class="sidenote"><?php echo esc_html__("( beta ) try to hide real url and deny access for direct download - will DISABLE seeking the mp3 progress"); ?></div>
            </div>

            <div class="setting">
              <h4
                class="label"><?php echo esc_html__('Single Product ZoomSounds Preview - Optional shortcode', DZSAP_ID); ?></h4>
              <?php


              $lab = 'wc_single_product_player_shortcode';


              echo DZSHelpers::generate_input_textarea($lab, array('class' => ' ', 'seekval' => $dzsap->mainoptions[$lab])); ?>


              <div
                class="sidenote"><?php echo esc_html__("you can input here shortcode to replace the main player in woocommerce product ie -", DZSAP_ID); ?>
                <br>
                <pre style="white-space: pre-line;">[zoomsounds_player type="detect" dzsap_meta_source_attachment_id="{{postid}}" source="{{source}}" thumb="{{thumb}}" config="sample--skin-wave--with-comments" autoplay="off" loop="off" open_in_ultibox="off" enable_likes="off" enable_views="on" play_in_footer_player="on" enable_download_button="off" download_custom_link_enable="off"]</pre>
              </div>
            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Samples Times Reflect', DZSAP_ID); ?></h4>
              <?php


              $lab = 'sample_time_pseudo';


              echo DZSHelpers::generate_select($lab, array('class' => 'styleme', 'options' => array(
                array(
                  'label' => esc_html__("Part of Real Track"),
                  'value' => '',
                ),
                array(
                  'label' => esc_html__("Part of Preview Track"),
                  'value' => 'pseudo',
                ),
              ), 'seekval' => $dzsap->mainoptions[$lab])); ?>


              <div
                class="sidenote"><?php echo esc_html__("this controls wheter the sample time start / end reflect the part of the real track or the preview track"); ?>
                <a
                  href="https://zoomthe.me/knowledge-base/zoomsounds-audio-player/article/how-to-use-woocommerce-sample-preview-times/"></a>
              </div>
            </div>


          </div>

        </div>


        <div class="dzs-tab-tobe tab-disabled">
          <div class="tab-menu ">
            &nbsp;&nbsp;
          </div>
          <div class="tab-content">

          </div>
        </div>

        <div class="dzs-tab-tobe">
          <div class="tab-menu with-tooltip">
            <i class="fa fa-flag"></i> <?php echo esc_html__("Translate") ?>
          </div>
          <div class="tab-content">
            <br>


            <div
              class="sidenote"><?php echo esc_html__("Note that integral translation of the plugin can be done by installing the WPML plugin. Or by using PO Edit and modifying the core wordpress language. We provide next only a few strings to be translated, for convenience:"); ?></div>

            <?php
            $lab = 'i18n_buy';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Translate "Buy"', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                </h4>';

            $lab = 'i18n_title';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Translate "Title"', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                </h4>';


            $lab = 'i18n_play';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Translate "Play"', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                </h4>';
            $lab = 'i18n_free_download';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Translate "Free Download"', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                </h4>';


            $lab = 'i18n_register_to_download';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Translate "Register to download"', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                </h4>';


            ?>


            <div class="setting">
              <h4
                class="setting-label"><?php echo esc_html__('Register to download - opens in new window', DZSAP_ID); ?></h4>
              <?php


              $lab = 'register_to_download_opens_in_new_link';


              echo DZSHelpers::generate_input_text($lab, array('id' => $lab, 'class' => 'fake-input', 'val' => 'off', 'input_type' => 'hidden'));


              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>


            </div>

          </div>
        </div>


        <div class="dzs-tab-tobe tab-disabled">
          <div class="tab-menu ">
            &nbsp;&nbsp;
          </div>
          <div class="tab-content">

          </div>
        </div>

        <div class="dzs-tab-tobe">
          <div class="tab-menu with-tooltip">
            <i class="fa fa-keyboard-o"></i> <?php echo esc_html__("Keyboard") ?>
          </div>
          <div class="tab-content">
            <br>


            <div class="sidenote"><?php echo dzs_esc__(esc_html__("keyboard controls setup: %s %s escape key - %s space key - %s 
                        left key - %s
                        right key - %s
                        up key - %s
                        down key - %s
                        you can input something like this %s also
                        ", DZSAP_ID), array('<br>', '<br>', '<strong>27</strong><br>'
              , '<strong>32</strong><br>'
              , '<strong>37</strong><br>'
              , '<strong>39</strong><br>'
              , '<strong>38</strong><br>'
              , '<strong>40</strong><br>'
              , '<strong>ctrl+39</strong>'
              )); ?></div>

            <?php

            $lab = 'keyboard_pause_play';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Play / pause code', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                </h4>';


            $lab = 'keyboard_step_forward';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Step forward key code', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                </h4>';
            $lab = 'keyboard_step_back';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Step back key code', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                </h4>';


            $lab = 'keyboard_step_back_amount';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Back amount in seconds', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                </h4>';


            $lab = 'keyboard_sync_players_goto_prev';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Previous track', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                    <div class="sidenote">' . esc_html__('either enable Play Single Players One After Another on the Page in Developer Settings, or enable the sticky player playlist in Player configurations - for this to work', DZSAP_ID) . '</div>
                </h4>';
            $lab = 'keyboard_sync_players_goto_next';

            echo '
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Next track', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $dzsap->mainoptions[$lab])) . '
                </h4>';


            ?>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Show tooltips', DZSAP_ID); ?></h4>
              <?php

              $lab = 'keyboard_show_tooltips';


              echo DZSHelpers::generate_input_text($lab, array('id' => $lab, 'class' => 'fake-input', 'val' => 'off', 'input_type' => 'hidden'));


              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>

            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Play triggers step back', DZSAP_ID); ?></h4>
              <?php

              $lab = 'keyboard_play_trigger_step_back';


              echo DZSHelpers::generate_input_text($lab, array('id' => $lab, 'class' => 'fake-input', 'val' => 'off', 'input_type' => 'hidden'));


              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>

            </div>

          </div>
        </div>


        <div class="dzs-tab-tobe tab-disabled">
          <div class="tab-menu ">
            &nbsp;&nbsp;
          </div>
          <div class="tab-content">

          </div>
        </div>

        <div class="dzs-tab-tobe">
          <div class="tab-menu with-tooltip">
            <i class="fa fa-paint-brush"></i> <?php echo esc_html__("Appearance", DZSAP_ID) ?>
          </div>
          <div class="tab-content">
            <br>


            <?php


            echo DZSZoomSoundsHelper::generateOptionsFromConfigForMainOptions($config_main_options, 'settings_appearance', $dzsap, $ignoreKeys);

            $val = '444444';

            $lab = 'design_wave_color_bg';
            if (isset($dzsap->mainoptions[$lab]) && $dzsap->mainoptions[$lab]) {
              $val = $dzsap->mainoptions[$lab];
            }
            echo '<h3>' . esc_html__("Wave Form Options") . '</h3>
                <div class="setting">
                    <h4 class="setting-label">' . esc_html__('Waveform BG Color', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('val' => 'ffffff', 'seekval' => $val, 'type' => 'colorpicker', 'class' => 'colorpicker-nohash')) . '
                    <div class="sidenote">' . sprintf(esc_html__("you can input a gradient by inputing %s with your colors", DZSAP_ID), '<strong>000000,ffffff</strong>') . '</div>
                </h4>';

            $val = 'ef6b13';


            $lab = 'design_wave_color_progress';
            if (isset($dzsap->mainoptions[$lab]) && $dzsap->mainoptions[$lab]) {
              $val = $dzsap->mainoptions[$lab];
            }

            echo '<div class="setting">
                    <h4 class="setting-label">' . esc_html__('Waveform Progress Color', DZSAP_ID) . '</div>
                    ' . DZSHelpers::generate_input_text($lab, array('seekval' => $val, 'type' => 'colorpicker', 'class' => 'colorpicker-nohash')) . '
                </h4>';
            ?>




            <?php

            $dependency = array(

              array(
                'element' => 'skinwave_wave_mode',
                'value' => array('image'),
              ),
            );
            ?>
            <div class="setting" data-dependency='<?php echo json_encode($dependency); ?>'>
              <h4 class="setting-label"><?php echo esc_html__('Multiplier', DZSAP_ID); ?></h4>
              <?php
              $val = 'ffffff';
              $lab = 'waveformgenerator_multiplier';
              if ($dzsap->mainoptions[$lab]) {
                $val = $dzsap->mainoptions[$lab];
              }
              echo DZSHelpers::generate_input_text($lab, array('val' => '1', 'seekval' => $val, 'type' => '', 'class' => ''));
              ?>
              <div
                class="sidenote"><?php echo esc_html__('If your waveformes come out a little flat and need some amplifying, you can increase this value .', DZSAP_ID); ?></div>
            </div>


            <div class="setting" data-dependency='<?php echo json_encode($dependency); ?>'>
              <h4 class="setting-label"><?php echo esc_html__('Waveform Style', DZSAP_ID); ?></h4>
              <?php echo DZSHelpers::generate_select('settings_wavestyle', array('options' => array('reflect', 'normal'), 'seekval' => $dzsap->mainoptions['settings_wavestyle'])); ?>

            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Extra CSS', DZSAP_ID); ?></h4>
              <?php
              echo DZSHelpers::generate_input_textarea('extra_css', array(
                'val' => '',
                'extraattr' => ' rows="5" style="width: 100%;"',
                'seekval' => stripslashes($dzsap->mainoptions['extra_css']),
              ));
              ?>

            </div>


          </div>
        </div>

        <div class="dzs-tab-tobe tab-disabled">
          <div class="tab-menu ">
            &nbsp;&nbsp;
          </div>
          <div class="tab-content">

          </div>
        </div>


        <div class="dzs-tab-tobe">
          <div class="tab-menu with-tooltip">
            <i class="fa fa-bar-chart"></i> <?php echo esc_html__("Analytics", DZSAP_ID) ?>
          </div>
          <div class="tab-content">
            <br>


            <?php
            echo DZSZoomSoundsHelper::generateOptionsFromConfigForMainOptions($config_main_options, 'settings_social', $dzsap, $ignoreKeys);
            ?>


          </div>
        </div>


        <!-- END system check -->
        <div class="dzs-tab-tobe tab-disabled">
          <div class="tab-menu ">
            &nbsp;&nbsp;
          </div>
          <div class="tab-content">

          </div>
        </div>

        <div class="dzs-tab-tobe">
          <div class="tab-menu with-tooltip">
            <i class="fa fa-bookmark"></i> <?php echo esc_html__("Meta"); ?>
          </div>

          <div class="tab-content">
            <br>

            <?php
            dzsap_admin_main_options__page_meta_print();
            ?>

          </div>
        </div>


        <!-- system check -->
        <div class="dzs-tab-tobe tab-disabled">
          <div class="tab-menu ">
            &nbsp;&nbsp;
          </div>
          <div class="tab-content">

          </div>
        </div>

        <div class="dzs-tab-tobe">
          <div class="tab-menu with-tooltip">
            <i class="fa fa-gear"></i> <?php echo esc_html__("System Check"); ?>
          </div>
          <div class="tab-content">


            <?php


            dzsap_admin_main_options__page_system_check_print();
            ?>

          </div>
        </div>
        <!-- system check END -->


        <div class="dzs-tab-tobe tab-disabled">
          <div class="tab-menu ">
            &nbsp;&nbsp;
          </div>
          <div class="tab-content">

          </div>
        </div>

        <div class="dzs-tab-tobe">
          <div class="tab-menu with-tooltip">
            <i class="fa fa-gears"></i> <?php echo esc_html__("Amazon s3") ?>
          </div>
          <div class="tab-content">


            <div class="setting">

              <h3 class="setting-label"><?php echo esc_html__("AWS Support", DZSAP_ID); ?></h3>

              <?php


              if (isset($_GET[DZSAP_PLUGINS_AMAZON_S3_INSTALL_QUERY_ARG]) && $_GET[DZSAP_PLUGINS_AMAZON_S3_INSTALL_QUERY_ARG] == 'on') {

                $FILE_LOCATION = DZSAP_BASE_PATH . '/aws.zip';

                if (file_exists($FILE_LOCATION)) {

                  echo '<div class="setting-text-ok"><i class="fa fa-check"></i> ' . esc_html__('Amazon installed') . '</div>';
                } else {

                  include_once(DZSAP_BASE_PATH . 'inc/php/compatibilities/aws/install-aws.php');
                  dzsap_compat_aws_installAws();
                }
              }

              ?>
            </div>

            <?php
            if (file_exists(dirname(__FILE__) . '/aws/aws-autoloader.php')) {
              ?>



              <div class="setting">
              <h5 class="label"><?php echo esc_html__('Enable AWS Support', DZSAP_ID); ?></h5>
              <?php
              $lab = 'aws_enable_support';
              echo DZSHelpers::generate_input_text($lab, array('id' => $lab, 'class' => 'fake-input', 'val' => 'off', 'input_type' => 'hidden'));
              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>
              <div
                class="sidenote"><?php echo sprintf(esc_html__('enable aws support', DZSAP_ID), '/', 'wp-content/dzsap_backups'); ?></div>
              </div><?php


              $lab = 'aws_key';
              ?>
              <div class="setting">


                <h5 class="label">Amazon S3 <?php echo esc_html__('Key', DZSAP_ID); ?></h5>
                <?php

                echo DZSHelpers::generate_input_text($lab, array('class' => ' ', 'seekval' => $dzsap->mainoptions[$lab]));

                ?>
                <div class="sidenote"><?php echo dzs_esc__(esc_html__("tutorial %shere%s", DZSAP_ID),

                    array('<a target="_blank" href="https://zoomthe.me/knowledge-base/zoomsounds-audio-player/article/how-to-enable-amazon-s3-support-for-reading-files-from-bucket/">',
                      '</a>')
                  ); ?></div>


              </div>
              <?php


              $lab = 'aws_key_secret';
              ?>
              <div class="setting">


                <h5 class="label">Amazon S3 <?php echo esc_html__('Secret', DZSAP_ID); ?></h5>
                <?php

                echo DZSHelpers::generate_input_text($lab, array('class' => ' ', 'seekval' => $dzsap->mainoptions[$lab]));

                ?>


              </div>
              <?php


              $lab = 'aws_region';
              ?>
              <div class="setting">


                <h5 class="label">Amazon S3 <?php echo esc_html__('Region code', DZSAP_ID); ?></h5>
                <?php

                echo DZSHelpers::generate_input_text($lab, array('class' => ' ', 'seekval' => $dzsap->mainoptions[$lab]));

                ?>
                <div
                  class="sidenote"><?php echo dzs_esc__(esc_html__("region code ( ie. %s ) - full list %shere%s", DZSAP_ID), array(
                    '<strong>eu-west</strong>',
                    '<a target="_blank" href="https://docs.aws.amazon.com/general/latest/gr/rande.html">',
                    '</a>'
                  )); ?></div>


              </div>
              <?php


              $lab = 'aws_bucket';
              ?>
              <div class="setting">


                <h5 class="label">Amazon S3 <?php echo esc_html__('Bucket', DZSAP_ID); ?></h5>
                <?php

                echo DZSHelpers::generate_input_text($lab, array('class' => ' ', 'seekval' => $dzsap->mainoptions[$lab]));

                ?>


              </div>
              <?php

            } else {

              echo '<p class=""><a class="button-secondary repair-table" href="' . admin_url('admin.php?page=' . DZSAP_ADMIN_PAGENAME_MAINOPTIONS . '&tab=17&' . DZSAP_PLUGINS_AMAZON_S3_INSTALL_QUERY_ARG . '=on') . '">' . esc_html__("install aws", DZSAP_ID) . '</a></p>';
            } ?>

          </div>
        </div>


        <div class="dzs-tab-tobe tab-disabled">
          <div class="tab-menu ">
            &nbsp;&nbsp;
          </div>
          <div class="tab-content">

          </div>
        </div>

        <div class="dzs-tab-tobe">
          <div class="tab-menu with-tooltip">
            <i class="fa fa-gears"></i> <?php echo esc_html__("Developer") ?>
          </div>
          <div class="tab-content">
            <br>


            <!-- developer tab content -->

            <?php

            echo DZSZoomSoundsHelper::generateOptionsFromConfigForMainOptions($config_main_options, 'developer_settings', $dzsap, $ignoreKeys);
            ?>



            <?php
            $lab = 'pcm_notice';
            ?>
            <div class="setting">
              <?php
              echo DZSHelpers::generate_input_text($lab, array('id' => $lab, 'input_type' => 'hidden', 'class' => 'mainsetting', 'val' => 'off'))
              ?>
              <h4 class="setting-label"><?php echo esc_html__('Wave Generating Notice', DZSAP_ID); ?></h4>
              <?php
              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>
              <div
                class="sidenote"><?php echo esc_html__('display the wave generating notice - or else the notice will not show but the wave forms will still generate', DZSAP_ID); ?></div>
            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Safe Binding?', DZSAP_ID); ?></h4>

              <?php
              $lab = 'is_safebinding';
              echo '<div class="dzscheckbox skin-nova">
' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
 <label for="' . $lab . '"></label>
</div>';
              ?>
              <div
                class="sidenote"><?php echo esc_html__('the galleries admin can use a complex ajax backend to ensure fast editing, but this can cause limitation issues on php servers. Turn this to on if you want a faster editing experience ( and if you have less then 20 videos accross galleries ) ', DZSAP_ID); ?></div>
            </div>
            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Do Not Use Caching', DZSAP_ID); ?></h4>
              <?php
              $lab = 'use_api_caching';
              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'off', 'seekval' => $dzsap->mainoptions[$lab])) . '
    <label for="' . $lab . '"></label>
</div>';
              ?>
              <div
                class="sidenote"><?php echo esc_html__('use caching for vimeo / youtube api ( recommended - on )', DZSAP_ID); ?></div>
            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Force File Get Contents', DZSAP_ID); ?></h4>
              <?php
              $lab = 'force_file_get_contents';
              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>
              <div
                class="sidenote"><?php echo esc_html__('sometimes curl will not work for retrieving youtube user name / playlist - try enabling this option if so...', DZSAP_ID); ?></div>
            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Hide Audio Items from menu', DZSAP_ID); ?></h4>
              <?php
              $lab = 'dzsap_items_hide';


              echo DZSHelpers::generate_input_text($lab, array('id' => $lab, 'input_type' => 'hidden', 'class' => 'mainsetting', 'val' => 'off'));

              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>
              <div
                class="sidenote"><?php echo esc_html__('hide the items', DZSAP_ID); ?></div>
            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Force Refresh Size Every 1000ms', DZSAP_ID); ?></h4>
              <?php
              $lab = 'settings_trigger_resize';
              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>
              <div
                class="sidenote"><?php echo esc_html__('sometimes sizes need to be recalculated ( for example if you use the gallery in tabs )', DZSAP_ID); ?></div>
            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Enable Powerpress Support', DZSAP_ID); ?></h4>
              <?php $lab = 'replace_powerpress_plugin';
              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>
              <div
                class="sidenote"><?php echo esc_html__('replace the current powerpress player with zoomsounds ', DZSAP_ID); ?></div>
            </div>


            <div class="setting">
              <h4 class="setting-label"><?php echo 'Powerpress - ';
                echo esc_html__(' try to read category data ', DZSAP_ID);
                echo 'xml'; ?></h4>
              <?php $lab = 'powerpress_read_category_xml';
              echo '<div class="dzscheckbox skin-nova">
                                        ' . DZSHelpers::generate_input_checkbox($lab, array('id' => $lab, 'class' => 'mainsetting', 'val' => 'on', 'seekval' => $dzsap->mainoptions[$lab])) . '
                                        <label for="' . $lab . '"></label>
                                    </div>';
              ?>
              <div
                class="sidenote"><?php echo esc_html__('replace the current powerpress player with zoomsounds ', DZSAP_ID); ?></div>
            </div>


            <?php
            $lab = 'js_init_timeout';
            ?>
            <div class="setting">


              <h4 class="setting-label"><?php echo esc_html__('Javascript Init Timeout', DZSAP_ID); ?></h4>
              <?php

              echo DZSHelpers::generate_input_text($lab, array('class' => ' ', 'seekval' => $dzsap->mainoptions[$lab]));

              ?>

              <div
                class="sidenote"><?php echo esc_html__("place a timeout for initializing the player ( in ms ) "); ?></div>
            </div>


            <?php

            $lab = 'wavesurfer_pcm_length';


            ?>
            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Precision', DZSAP_ID); ?></h4>
              <?php
              echo DZSHelpers::generate_input_text($lab, array('class' => ' ', 'seekval' => $dzsap->mainoptions[$lab]));
              ?>
              <div class="sidenote"><?php echo esc_html__("higher is more precise, but occupies more storage space", DZSAP_ID); ?></div>
            </div>


            <?php
            $lab = 'extra_js';
            ?>
            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Extra Javascript', DZSAP_ID); ?></h4>
              <?php echo dzsap_misc_input_textarea($lab, array('val' => '', 'seekval' => stripslashes($dzsap->mainoptions[$lab]))); ?>
              <div class="sidenote"><?php echo esc_html__("extra javascript on page load"); ?></div>
            </div>

            <?php
            $lab = 'settings_ignore_keys';
            ?>
            <div class="setting">
              <h4 class="setting-label"><?php echo esc_html__('Ignore settings', DZSAP_ID); ?></h4>
              <?php echo dzsap_misc_input_textarea($lab, array('val' => '', 'seekval' => stripslashes($dzsap->mainoptions[$lab]))); ?>
              <div class="sidenote"><?php echo esc_html__("ignore settings with these keys, separate with commas ( ie. 'always_embed,single_index_seo_disable')"); ?></div>
            </div>


          </div>
        </div>


        <?php
        do_action('dzsap_mainoptions_after_last_tab');
        ?>

      </div>


      <br/>
      <br/>
      <br/>
      <a href='#'
         class="button-primary save-btn dzsap-save-main-options save-mainoptions"><?php echo esc_html__('Save Options', DZSAP_ID); ?></a>
    </form>
    <br/><br/>


    <div class="dzstoggle toggle1">
      <div class="toggle-title" style=""><?php echo esc_html__('Delete Plugin Data', DZSAP_ID); ?></div>
      <div class="toggle-content" style="">
        <br>
        <form class="mainsettings" method="POST">
          <button name="dzsap_delete_plugin_data" value="on"
                  class="button-secondary"><?php echo esc_html__('Delete plugin data', DZSAP_ID); ?></button>
        </form>
        <br>
        <form class="mainsettings" method="POST">
          <?php
          $nonce = wp_create_nonce('dzsap_delete_waveforms_nonce');
          ?>
          <input type="hidden" name="action" value="<?php echo DZSAP_AJAX_DELETE_CACHE_WAVEFORM_DATA ?>"/>
          <input type="hidden" name="nonce" value="<?php echo $nonce; ?>"/>
          <button
            class="button-secondary btn-delete-waveform-data"><?php echo esc_html__('Delete waveform data', DZSAP_ID); ?></button>
        </form>
        <br>
        <form class="mainsettings" method="POST">
          <?php
          $nonce = wp_create_nonce(DZSAP_AJAX_DELETE_CACHE_TOTAL_TIMES . '_nonce');
          ?>
          <input type="hidden" name="action" value="<?php echo DZSAP_AJAX_DELETE_CACHE_TOTAL_TIMES ?>"/>
          <input type="hidden" name="nonce" value="<?php echo $nonce; ?>"/>
          <button
            class="button-secondary btn-delete-cache-times"><?php echo esc_html__('Delete total times', DZSAP_ID); ?></button>
        </form>
        <br>

      </div>

    </div>


    <div class="feedbacker" style=""><img alt="" style="" id="save-ajax-loading2"
                                          src="<?php echo site_url(); ?>/wp-admin/images/wpspin_light.gif"/>
    </div>
  </div>
  <div class="clear"></div><br/>
<?php
