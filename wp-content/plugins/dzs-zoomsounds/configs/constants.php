<?php


const DZSAP_ID = 'dzsap';
const DZSAP_PREFIX_LOWERCASE = 'dzsap';
const DZSAP_REGISTER_POST_TYPE_NAME = 'dzsap_items';
const DZSAP_REGISTER_POST_TYPE_CATEGORY = 'dzsap_category';
const DZSAP_REGISTER_POST_TYPE_TAGS = 'dzsap_tags';
const DZSAP_DBNAME_OPTIONS = 'dzsap_options';
const DZSAP_TAXONOMY_NAME_SLIDERS = 'dzsap_sliders';
const DZSAP_PERMISSION_ULTIMATE = 'manage_options';
const DZSAP_DBNAME_MAINITEMS = 'dzsap_items';
const DZSAP_DBNAME_LEGACY_DBS = 'dzsap_dbs';
const DZSAP_DBNAME_AUDIO_PLAYERS_CONFIGS = 'dzsap_vpconfigs';
const DZSAP_DBNAME_MAINOPTIONS = 'dzsap_options';
const DZSAP_DBNAME_SAMPLEDATA = 'dzsap_sample_data';
const DZSAP_DBNAME_CACHE_TOTAL_TIME = '_dzsap_total_time';
const DZSAP_DBNAME_PCM_LINKS = 'dzsap_pcm_to_id_links'; // -- pcm bindings
const DZSAP_META_OPTION_PREFIX = 'dzsap_meta_';
const DZSAP_META_ITEM_OPTION_PREFIX = 'dzsap_meta_item_';
const DZSAP_ADMIN_UPDATE_LATEST_VERSION_URI = 'https://zoomthe.me/cronjobs/cache/dzsap_get_version.static.html';
const DZSAP_PHP_LOG_LABEL = '[dzsap]';
const DZSAP_PHP_LOG_AJAX_LABEL = '[ajax]';
const DZSAP_ADMIN_PREVIEW_PLAYER_PARAM = 'dzsap_preview_player';

const DZSAP_ADMIN_PAGENAME_PARENT = 'dzsap_menu';
const DZSAP_ADMIN_PAGENAME_AUTOUPDATER = 'dzsap-autoupdater';
const DZSAP_ADMIN_PAGENAME_LEGACY_SLIDERS_ADMIN_VPCONFIGS = 'dzsap_configs';
const DZSAP_ADMIN_PAGENAME_LEGACY_SLIDERS_ADMIN_SLIDERS = 'dzsap_menu';
const DZSAP_ADMIN_PAGENAME_DESIGNER_CENTER = 'dzsap-dc';
const DZSAP_ADMIN_PAGENAME_MAINOPTIONS = 'dzsap-mo';
const DZSAP_ADMIN_PAGENAME_MAINOPTIONS_WAVE_GENERATOR = 'dzsap_wave_regenerate';
const DZSAP_ADMIN_PAGENAME_MAINOPTIONS_WAVE_GENERATOR_AUTO_GENERATE_PARAM = 'dzsap_wave_generate_auto';
const DZSAP_ADMIN_PAGENAME_ABOUT = 'dzsap-about';
const DZSAP_ADMIN_PAGENAME_HIDDEN_SYSTEM_CHECK_WAVES = 'dzsap-system-check-waves';

const DZSAP_VPCONFIGS_DEFAULT_SETTINGS_NAME = 'default-settings-for-zoomsounds';
const DZSAP_VPCONFIGS_PREVIEW_IFRAME_VPCONFIG_NAME = 'called_from_vpconfig_admin_preview';
const DZSAP_VPCONFIGS_PREVIEW_IFRAME_VPCONFIG_NAME_IN_DB = 'dzsap_temp_vpconfig';
const DZSAP_ZOOMSOUNDS_ACRONYM = 'zoomsounds';

const DZSAP_DB_VIEWS_META_NAME = '_dzsap_views';
const DZSAP_DB_DOWNLOADS_META_NAME = '_dzsap_downloads';
const DZSAP_DB_LIKES_META_NAME = '_dzsap_likes';
const DZSAP_DB_PCM_DATA = 'dzsap_pcm_data';

const DZSAP_META_NAME_FOOTER_ENABLE = 'dzsap_footer_enable';
const DZSAP_META_NAME_FOOTER_FEED_TYPE = 'dzsap_footer_feed_type';
const DZSAP_META_NAME_FOOTER_VPCONFIG = 'dzsap_footer_vpconfig';
const DZSAP_META_NAME_FOOTER_FEATURED_MEDIA = 'dzsap_footer_featured_media';
const DZSAP_META_NAME_FOOTER_SONG_NAME = 'dzsap_footer_song_name';
const DZSAP_META_NAME_FOOTER_TYPE = 'dzsap_footer_type';
const DZSAP_META_PAGE_SECTION = 'dzsap_footer_player_options';

const DZSAP_VIEW_STICKY_PLAYER_ID = 'dzsap_footer';
const DZSAP_VIEW_PLAYER_FEATURE_RATINGS_METANAME_RATEINDEX = '_dzsap_rate_index';

const DZSAP_DEFAULT_ZOOMSOUNDS_CONFIG = 'default-settings-for-zoomsounds';
const DZSAP_COOKIENAME_SYSTEM_CHECK_WAVES = 'dzsap_is_admin_systemCheck_waves';
const DZSAP_VIEW_SHOWCASE_PLAYLIST_ID = 'playlist_gallery';
const DZSAP_VIEW_GET_TRACK_SOURCE = 'get_track_source';
const DZSAP_VIEW_NONCE_IDENTIFIER = 'generatenonce';
const DZSAP_VIEW_EMBED_IFRAME_HEIGHT = '180';
const DZSAP_VIEW_APCONFIG_PREFIX = '.apconfig-';

/** called from AUDIT WAVEFORMS */
const DZSAP_AJAX_DELETE_TRACK = 'delete_track';
/** called from AUDIT WAVEFORMS */
const DZSAP_AJAX_DELETE_WAVEFORM = 'delete_waveforms';
const DZSAP_AJAX_DELETE_CACHE_WAVEFORM_DATA = 'dzsap_delete_waveforms';
const DZSAP_AJAX_LOAD_CHARTS_HTML = 'dzsap_load_charts_html';
const DZSAP_AJAX_DELETE_CACHE_TOTAL_TIMES = 'dzsap_delete_times';
const DZSAP_WOOCOMMERCE_OVERLAY_CENTER_CLASS = '.dzsap--go-to-thumboverlay--container';

const DZSAP_LEGACY_SLIDERS__GET_CURRSLIDER = 'currslider';
const DZSAP_LEGACY_SLIDERS__GET_CURRSLIDER_FINDER = 'find_slider_by_slug';
const DZSAP_ADMIN_SHORTCODE_PLAYER_GENERATOR_KEY = 'dzsap_shortcode_player_builder';

const DZSAP_GET_KEY_DOWNLOAD = 'dzsap_download';

const DZSAP_WOOCOMMERCE_PLAYLIST_IN_PRODUCT_PREFIX = 'zoomsounds-product-playlist-';

if (defined('DZSAP_DEBUG_LOCAL_SCRIPTS') && DZSAP_DEBUG_LOCAL_SCRIPTS === true) {
  define('DZSAP_URL_AUDIOPLAYER', 'http://devsite/zoomsounds/source/audioplayer/');
} else {

  define('DZSAP_URL_AUDIOPLAYER', DZSAP_BASE_URL . "audioplayer/");
}

const DZSAP_PREFIX_FOR_VPCONFIGS = '0-settings-';

const DZSAP_URL_FONTAWESOME_EXTERNAL = 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css';

const DZSAP_ITEMS__META_FILE_SOURCE = array(
  'FROM_FOLDER_IMPORT' => 'FROM_FOLDER_IMPORT',
  'FROM_MANUAL_ADD' => 'FROM_MANUAL_ADD',
);
const DZSAP_META_WOOCOMMERCE_ZOOMSOUNDS_MP3 = 'dzsap_woo_product_track';

/** some pages might want to query this */
const DZSAP_ANALYTICS_TABLE_CURRENT_VERSION = 1.1;
const DZSAP_ANALYTICS_SUBMIT_VIEW_NAME = 'submit_views';
const DZSAP_COOKIENAME_ANALYTICS_VIEW_SUBMITTED = 'dzsap_viewsubmitted';
const DZSAP_ANALYTICS_TABLE_NAME = 'dzsap_activity';
const DZSAP_DBNAME_ANALYTICS_VERSION = 'dzsap_analytics_table_version';
const DZSAP_ANALYTICS_QUERY_REPAIR = 'analytics_table_repair';
const DZSAP_PLUGINS_AMAZON_S3_INSTALL_QUERY_ARG = 'install_aws';
const DZSAP_PLUGINS_AMAZON_S3_INSTALL_URI = 'https://zoomthe.me/updater_dzsap/plugins/aws.zip';

const DZSAP_VIEW_QUERY_PARAMS_TAGS_FILTER = 'query_song_tag';

const DZSAP_GUTENBERG_PLAYER_ID_DEPRECATED = 'dzsap-gutenberg-player';
const DZSAP_GUTENBERG_PLAYER_ID = 'dzsap/the-gutenberg-player';
const DZSAP_GUTENBERG_PLAYLIST_BLOCK_ID = 'dzsap/gutenberg-playlist';
const DZSAP_GUTENBERG_PLAYLIST_ID = 'dzsap-gutenberg-playlist';

const DZSAP_ANALYTICS_TABLE_ACTION_TYPE = array(
  'LIKE' => 'like',
  'VIEW' => 'view',
);

const DZSAP_PERMISSION = array(
  'MANAGE_VPCONFIGS' => 'dzsap_manage_vpconfigs',
  'PORTAL_EDIT_OWN_TRACKS' => 'dzsaap_enable_allow_users_to_edit_own_tracks',
  'PORTAL_ENABLE_UNREGISTERED_SUBMIT' => 'dzsaap_enable_unregistered_submit',
  'MANAGE_OPTIONS' => 'dzsap_manage_options',
);

const DZSAP_VIEW_DEFAULT_SHORTCODE_PLAYER_ATTS = array(
  'width' => '100%',
  'config' => 'default-settings-for-zoomsounds',
  'height' => '300',
  'source' => '',
  'sourceogg' => '',
  'coverimage' => '',

  'cue' => 'auto',
  'loop' => 'off',
  'autoplay' => 'off',
  'show_tags' => 'off',
  'type' => 'audio',
  'player' => '',
  'itunes_link' => '',
  'playerid' => '', // -- if player id okay
  'wpPlayerPostId' => '', // -- if player id okay
  'thumb' => '',
  'thumb_for_parent' => '',
  'mp4' => '',
  'openinzoombox' => 'off',
  'enable_likes' => 'off',
  'enable_downloads_counter' => 'off',
  'enable_views' => 'off',
  'enable_rates' => 'off',
  'title_is_permalink' => 'off',
  'playfrom' => 'off',
  'artistname' => 'default',
  'songname' => 'default',
  'is_single' => 'on',
  'embedded' => 'off', // -- in case it is embedded, we might remove embed button from conifg
  'divinsteadofscript' => 'off',
  'init_player' => 'on',
  'faketarget' => '',
  'sample_time_start' => '',
  'sample_time_end' => '',
  'sample_time_total' => '',
  'feed_type' => '',
  'extra_init_settings' => array(),
  'player_index' => '',
  'inner_html' => '',
  'extraattr' => '',
  'extra_classes' => '',
  'content_inner' => '', // -- will replace content inner
  'extra_html' => '',
  'extra_html_in_controls_right' => '',
  'js_settings_extrahtml_in_float_right' => '', // -- js settings extra html in float right .. configs will go into extra_html_in_controls_right
  'play_target' => 'default', // -- "default" or "footer"
  'dzsap_meta_source_attachment_id' => '',
  'outer_comments_field' => '',
  'extra_classes_player' => '',
  'called_from' => 'player',
);

const DZSAP_PLAYER_ATTRIBUTES = array(
  'artistname',
  'config',
  'source_attachment_id',
  'playfrom',
  'autoplay',
  'type_normal_stream_type',
  'play_in_footer_player',
  'enable_download_button',
  'enable_downloads_counter',
  'download_custom_link_enable',
  'download_custom_link',
  'item_extra_classes_player',
  'replace_menu_songname',
  'replace_menu_artistname',
  'extrahtml_in_bottom_controls_from_player',
  'extrahtml_in_float_right_from_player',
  'loop',
  'open_in_ultibox',
  'enable_likes',
  'enable_views',
  'enable_rates',
  'itunes_link',
  'playerid',
  'replace_songname',
  'replace_artistname',
  'wrapper_image',
  'wrapper_image_type',
  'productid',
  'artistname_link',
  'is_amazon_s3',
  'extra_meta_label_1',
);

const DZSAP_VIEW_SVG_ASSETS = array(
  'PLAY_BUTTON' => '<svg class="svg-icon" version="1.1" id="Layer_2" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="11.161px" height="12.817px" viewBox="0 0 11.161 12.817" enable-background="new 0 0 11.161 12.817" xml:space="preserve"> <g> <g> <g> <path fill="#D2D6DB" d="M8.233,4.589c1.401,0.871,2.662,1.77,2.801,1.998c0.139,0.228-1.456,1.371-2.896,2.177l-4.408,2.465 c-1.44,0.805-2.835,1.474-3.101,1.484c-0.266,0.012-0.483-1.938-0.483-3.588V3.666c0-1.65,0.095-3.19,0.212-3.422 c0.116-0.232,1.875,0.613,3.276,1.484L8.233,4.589z"/> </g> </g> </g> </svg>'
);

const DZSAP_SHOWCASE_STYLE_TYPES = array(
  'SCROLLER' => 'scroller',
  'PLAYER' => 'player',
  'WIDGET_PLAYER' => 'widget_player',
  'SLIDER' => 'slider',
  'PLAYLIST' => 'playlist',
  'FEATURED_SLIDER' => 'featured_slider',
);

const DZSAP_EXCLUDED_KEYS_FROM_GENERATOR_PLAYER = array(
  'source', 'type', 'config', 'thumb', 'cover', 'autoplay', 'loop', 'extra_classes', 'extra_classes_player', 'songname', 'artistname', 'open_in_ultibox', 'enable_likes',
  'enable_views', 'enable_downloads_counter', 'enable_download_button', 'playerid', 'itunes_link', 'wrapper_image', 'play_target', 'download_custom_link'
, 'download_link_label', 'type_normal_stream_type', 'is_amazon_s3'
);

$isSecureCookies = !!((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'));
if ($isSecureCookies) {
  define('DZSAP_COOKIES_SECURE', true, false);
} else {
  define('DZSAP_COOKIES_SECURE', false, false);
}

const DZSAP_ALLOWED_EXTENSIONS = array(
  'mp3',
  'wav',
  'm4a',
);
