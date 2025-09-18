/**
 * rework google analytics outside of player
 * @param o
 * @param dataSrc
 */


window.dzsap_player_play = function(o, dataSrc){

  if (o.google_analytics_send_play_event === 'on' && window._gaq && google_analytics_sent_play_event === false) {
    window._gaq.push(['_trackEvent', 'ZoomSounds Play', 'Play', 'zoomsounds play - ' + dataSrc]);
    google_analytics_sent_play_event = true;
  }

  if (!window.ga) {
    if (window.__gaTracker) {
      window.ga = window.__gaTracker;
    }
  }

  if (o.google_analytics_send_play_event === 'on' && window.ga && google_analytics_sent_play_event === false) {
    google_analytics_sent_play_event = true;
    window.ga('send', {
      hitType: 'event',
      eventCategory: 'zoomsounds play - ' + dataSrc,
      eventAction: 'play',
      eventLabel: 'zoomsounds play - ' + dataSrc
    });
  }
}
