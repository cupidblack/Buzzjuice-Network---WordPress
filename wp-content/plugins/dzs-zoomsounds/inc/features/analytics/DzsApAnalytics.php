<?php

class DzsApAnalytics {
  private $dzsap;

  /**
   * DzsapAdmin constructor.
   * @param DzsAudioPlayer $dzsap
   */
  function __construct($dzsap) {
    $this->dzsap = $dzsap;

    $this->init();
  }

  function init(){
    add_action('admin_enqueue_scripts', array($this, 'handle_admin_enqueue_scripts'), 10);
    if ($this->dzsap->mainoptions['analytics_enable'] == 'on') {
      add_action('wp_dashboard_setup', array($this, 'handle_wp_dashboard_setup'));
    }
  }


  function handle_admin_enqueue_scripts($hook_suffix) {
    global $pagename;


    $dzsap = $this->dzsap;

    // -- dashboard only
    if ($hook_suffix == 'index.php') {

      if ($dzsap->mainoptions['analytics_enable'] == 'on') {

        DzsApAnalytics::enqueueAnalyticsScripts($this->dzsap);
      }


    }
  }

  function handle_admin_init(){

  }


  function handle_wp_dashboard_setup() {

    $dzsap = $this->dzsap;

    if ($dzsap->mainoptions['analytics_enable'] == 'on') {

      wp_add_dashboard_widget('dzsap_dashboard_analytics', // Widget slug.
        'ZoomSounds' . esc_html__('Analytics', DZSAP_ID), // Title.
        'dzsap_analytics_dashboard_content'

      );
    }

  }


  /**
   * DzsapAdmin constructor.
   * @param DzsAudioPlayer $dzsap
   */
  static function enqueueAnalyticsScripts($dzsap){

    wp_enqueue_script('google-charts', 'https://www.gstatic.com/charts/loader.js');
    wp_enqueue_script('dzsap-dashboard-chart', DZSAP_BASE_URL . 'admin/admin-page/dashboard-chart.js');
    wp_enqueue_script('dzsap-shortcode-analytics', DZSAP_BASE_URL . 'inc/features/analytics/zoomsoundsDashboardStats.js');


    if ($dzsap->mainoptions['analytics_enable_location'] == 'on') {
      wp_enqueue_script('google-maps', 'https://www.google.com/jsapi');
    }
  }
}