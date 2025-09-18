import {add_query_arg} from "../../_dzsap_helpers";

/**
 *
 * @param {DzsAudioPlayer} selfClass
 */
export function checkIfNeedsSongNameRefresh(selfClass){

  if (selfClass.audioTypeSelfHosted_streamType === 'icecast' || selfClass.audioTypeSelfHosted_streamType === 'shoutcast') {
    // -- if we have icecast we can update currently playing song


    if (selfClass.audioTypeSelfHosted_streamType === 'icecast' || (selfClass.radio_isGoingToUpdateArtistName || selfClass.radio_isGoingToUpdateSongName)) {

      player_icecastOrShoutcastRefresh(selfClass);
    }
    setInterval(function () {
      player_icecastOrShoutcastRefresh(selfClass);
    }, 10000)
  }
}


export function player_icecastOrShoutcastRefresh(selfClass) {


  let url = selfClass.cthis.attr('data-source');

  if (selfClass.audioTypeSelfHosted_streamType === 'shoutcast') {
    url = add_query_arg(selfClass.urlToAjaxHandler, 'action', 'dzsap_shoutcast_get_streamtitle');
    url = add_query_arg(url, 'source', (selfClass.data_source));
  }


  jQuery.ajax({
    type: "GET",
    url: url,
    crossDomain: true,
    success: function (response) {

      if (response.documentElement && response.documentElement.innerHTML) {
        response = response.documentElement.innerHTML;
      }


      let regex_title = '';
      let regex_creator = '';
      let new_title = '';
      let new_artist = '';

      if (selfClass.audioTypeSelfHosted_streamType === 'icecast') {

        var regex_location = /<location>(.*?)<\/location>/g

        var regexMatches = null;
        if (regexMatches = regex_location.exec(response)) {


          if (regexMatches[1] !== selfClass.data_source) {
            selfClass.data_source = regexMatches[1];
            selfClass.setup_media({
              'called_from': 'icecast setup'
            });
          }
        }
      }

      if (selfClass.radio_isGoingToUpdateSongName) {
        if (selfClass.audioTypeSelfHosted_streamType === 'icecast') {
          regex_title = /<title>(.*?)<\/title>/g

          if (regexMatches = regex_title.exec(response)) {
            new_title = regexMatches[1];
          }
        }
        if (selfClass.audioTypeSelfHosted_streamType === 'shoutcast') {
          new_title = response;
        }

      }
      if (selfClass.radio_isGoingToUpdateArtistName) {
        if (selfClass.audioTypeSelfHosted_streamType === 'icecast') {

          regex_creator = /<creator>(.*?)<\/creator>/g;

          if (regexMatches = regex_creator.exec(response)) {
            new_artist = regexMatches[1];
          }
        }
        if (selfClass.audioTypeSelfHosted_streamType === 'shoutcast') {
        }
      }

      if (selfClass.radio_isGoingToUpdateSongName) {
        selfClass._metaArtistCon.find('.the-name').html(new_title);
      }

      if (selfClass.radio_isGoingToUpdateArtistName) {
        selfClass._metaArtistCon.find('.the-artist').html(new_artist)
      }
    },
    error: function (err) {

    }
  });

}
