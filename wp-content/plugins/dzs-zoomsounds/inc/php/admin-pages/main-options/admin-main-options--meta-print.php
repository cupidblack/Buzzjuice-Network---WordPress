<?php

function dzsap_admin_main_options__page_meta_print(){

  /** @type DZSAudioPlayer $dzsap */
  global $dzsap;

  ?>


<div class="setting">
  <h4 class="setting-label"><?php echo esc_html__('Enable Meta Options for ... ', DZSAP_ID); ?></h4>
  <?php
  $mainPostTypesKey = 'dzsap_meta_post_types';


  $args = array(
    'public' => true,
    '_builtin' => false
  );

  $output = 'names'; // names or objects, note names is the default
  $operator = 'and'; // 'and' or 'or'

  $post_types = get_post_types($args, $output, $operator);



  echo DZSHelpers::generate_input_text($mainPostTypesKey . '[]', array('class' => 'styleme', 'input_type' => 'hidden', 'seekval' => '', 'val' => ''));
  echo '<label>';
  echo DZSHelpers::generate_input_checkbox($mainPostTypesKey . '[]', array('class' => 'styleme', 'def_value' => '', 'seekval' => $dzsap->mainoptions[$mainPostTypesKey], 'val' => 'post'));
  echo esc_html__(' post', DZSAP_ID);
  echo '</label>';
  echo '<br/>';
  echo '<label>';
  echo DZSHelpers::generate_input_checkbox($mainPostTypesKey . '[]', array('class' => 'styleme', 'def_value' => '', 'seekval' => $dzsap->mainoptions[$mainPostTypesKey], 'val' => 'page'));
  echo esc_html__(' page', DZSAP_ID);
  echo '</label>';
  echo '<br/>';
  foreach ($post_types as $key => $post_type) {

    $val = '';

    if (isset($dzsap->mainoptions[$mainPostTypesKey])) {
      $val = $dzsap->mainoptions[$mainPostTypesKey];
    }
    echo '<label>';
    echo DZSHelpers::generate_input_checkbox($mainPostTypesKey . '[]', array('class' => 'styleme', 'def_value' => '', 'seekval' => $val, 'val' => $post_type));
    echo esc_html__(' ' . $post_type, DZSAP_ID);
    echo '</label>';
    echo '<br/>';
  }
  ?>
  <div class="clear"></div>
  <div
    class='sidenote'><?php echo sprintf(esc_html__('allows for %s meta options for these post types', DZSAP_ID), 'ZoomSounds'); ?></div>
  <div class="clear"></div>
</div>


<?php

$nr = 1;
$lab = 'extra_meta_label_' . $nr;

$val = '';

if (isset($dzsap->mainoptions[$lab])) {
  $val = $dzsap->mainoptions[$lab];
}


?>
<div class="setting">


  <h4
    class="setting-label"><?php echo sprintf(esc_html__('Optional Meta Box %s Label', DZSAP_ID), '<strong>' . $nr . '</strong>'); ?></h4>
  <?php

  echo DZSHelpers::generate_input_text($lab, array('class' => ' ', 'seekval' => $val));

  ?>

  <div
    class="sidenote"><?php echo esc_html__("place a optional meta box label - that can be replaced with in the zoomsounds extra html"); ?></div>
</div>


<?php

$nr = 2;
$lab = 'extra_meta_label_' . $nr;

$val = '';

if (isset($dzsap->mainoptions[$lab])) {
  $val = $dzsap->mainoptions[$lab];
}


?>
<div class="setting">


  <h4
    class="setting-label"><?php echo sprintf(esc_html__('Optional Meta Box %s Label', DZSAP_ID), '<strong>' . $nr . '</strong>'); ?></h4>
  <?php

  echo DZSHelpers::generate_input_text($lab, array('class' => ' ', 'seekval' => $val));

  ?>

  <div
    class="sidenote"><?php echo esc_html__("place a optional meta box label - that can be replaced with in the zoomsounds extra html"); ?></div>
</div>


<?php

$nr = 3;
$lab = 'extra_meta_label_' . $nr;

$val = '';

if (isset($dzsap->mainoptions[$lab])) {
  $val = $dzsap->mainoptions[$lab];
}


?>
<div class="setting">


  <h4
    class="setting-label"><?php echo sprintf(esc_html__('Optional Meta Box %s Label', DZSAP_ID), '<strong>' . $nr . '</strong>'); ?></h4>
  <?php

  echo DZSHelpers::generate_input_text($lab, array('class' => ' ', 'seekval' => $val));

  ?>

  <div
    class="sidenote"><?php echo esc_html__("place a optional meta box label - that can be replaced with in the zoomsounds extra html"); ?></div>
</div>

<?php
}