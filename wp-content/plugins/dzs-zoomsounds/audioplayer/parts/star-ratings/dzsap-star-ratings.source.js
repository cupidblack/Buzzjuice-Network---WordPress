window.dzsap_part_starRatings_loaded = 'on';


class dzsapStarRatingsSource {
  constructor(dzsapClass) {

    this.$ratingCon = null;
    this.inputStarSize = 20;
    this.inputStarNumber = 5;
    this.inputStarFormat = '<span style="width: 20px;">&#9733;</span>';
    this.dzsap = dzsapClass;

    this.init();


  }

  viewUpdateStarSizes(){


    const selfStarInstance = this;
    const dzsapClass = this.dzsap;

    const indexToPerc = Number(selfStarInstance.$ratingCon.eq(0).attr('data-initial-rating-index'));
    let auxnr = Number(selfStarInstance.$ratingCon.eq(0).attr('data-initial-rating-index')) * 100;



    if (dzsapClass.starrating_alreadyrated > -1 && dzsapClass.starrating_alreadyrated > 0) {
      auxnr = dzsapClass.starrating_alreadyrated * 100 / selfStarInstance.inputStarNumber;
    }

    dzsapClass.cthis.find('.rating-prog').css({
      'width': auxnr + '%'
    })


    dzsapClass.cthis.find('.star-rating-set-clip').css({
      'opacity': 1
    })
    dzsapClass.cthis.find('.star-rating-set-clip').css({
      'width': selfStarInstance.inputStarSize * selfStarInstance.inputStarNumber * indexToPerc
    })
  }

  init() {

    const selfStarInstance = this;
    const dzsapClass = this.dzsap;
    const $ = jQuery;
    var starrating_index = 0;

    this.$ratingCon = dzsapClass.cthis.find('.star-rating-con');

    this.$ratingCon.get(0).selfStarInstance = selfStarInstance;


    initVisual();


    function initVisual() {

      for (let i = 0; i < selfStarInstance.inputStarNumber; i++) {
        selfStarInstance.$ratingCon.find('.star-rating-bg').append(selfStarInstance.inputStarFormat);
        selfStarInstance.$ratingCon.find('.star-rating-prog').append(selfStarInstance.inputStarFormat);
      }
    }

    function mouse_starrating(e) {
      var $t = $(this);


      if (dzsapClass.cthis.has($t).length === 0) {
        return;
      }

      if (e.type === 'mouseleave') {
        selfStarInstance.viewUpdateStarSizes()

      }
      if (e.type === 'mousemove') {
        var mx = e.pageX - $t.offset().left;
        var my = e.pageX - $t.offset().left;


        starrating_index = Math.round(mx / ($t.outerWidth() / 5));


        if (starrating_index > 4) {
          starrating_index = 5;
        } else {
          starrating_index = Math.round(starrating_index);
        }

        if (starrating_index < 1) {
          starrating_index = 1;
        }


        dzsapClass.cthis.find('.star-rating-prog-clip').css({
          'width': (starrating_index / 5 * 100) + '%'
        })

        dzsapClass.starrating_alreadyrated = -1;


        dzsapClass.cthis.find('.star-rating-set-clip').css({
          'opacity': 0
        })
      }
      if (e.type === 'click') {


        dzsapClass.starrating_alreadyrated = -1;
        if (dzsapClass.starrating_alreadyrated > -1 && dzsapClass.starrating_alreadyrated > 0) {
          return;
        }

        ajax_submit_rating(dzsapClass, starrating_index);
      }


    }


    $(document).on('mousemove', '.star-rating-con', mouse_starrating);
    $(document).on('mouseleave', '.star-rating-con', mouse_starrating);
    $(document).on('click', '.star-rating-con', mouse_starrating);
  }
}

/**
 * only handles ajax call + result
 * @param dzsapInstance
 * @param starrating_index
 */
function ajax_submit_rating(dzsapInstance, starrating_index) {


  var $ = jQuery;


  var data = {
    action: 'dzsap_submit_rate',
    postdata: starrating_index,
    playerid: dzsapInstance.the_player_id
  };


  const onSuccess = (response) => {



    let responseArr = {};

    try {
      responseArr = JSON.parse(response);
    } catch (e) {
    }

    var percentSetRating = dzsapInstance.cthis.find('.star-rating-set-clip').outerWidth() / dzsapInstance.cthis.find('.star-rating-bg').outerWidth();
    let nrOfRates = parseInt(dzsapInstance.cthis.find('.counter-rates .the-number').html(), 10);

    nrOfRates++;

    var percentFinalRating = ((nrOfRates - 1) * (percentSetRating * 5) + starrating_index) / (nrOfRates)


    setTimeout(function () {

      dzsapInstance.cthis.find('.star-rating-con').removeClass('just-rated');
    }, 100);
    dzsapInstance.cthis.find('.counter-rates .the-number').html(responseArr.rate_nr);

    dzsapInstance.cthis.find('.star-rating-con').attr('data-initial-rating-index', Number(responseArr.rate_index) / 5);
    dzsapInstance.cthis.find('.star-rating-con .rating-prog').css('width', (Number(responseArr.rate_index) / 5 * 100) + '%');

    if (dzsapInstance.initOptions.parentgallery && $(dzsapInstance.initOptions.parentgallery).get(0) !== undefined && $(dzsapInstance.initOptions.parentgallery).get(0).api_player_rateSubmitted) {
      $(dzsapInstance.initOptions.parentgallery).get(0).api_player_rateSubmitted();
    }
  }




  if (dzsapInstance.starrating_alreadyrated > -1) {
    return;
  }

  dzsapInstance.cthis.find('.star-rating-con').addClass('just-rated');
  let totalWidth = parseInt(dzsapInstance.cthis.find('.star-rating-bg').width(), 10);
  if (dzsapInstance.urlToAjaxHandler) {
    jQuery.ajax({
      type: "POST",
      url: dzsapInstance.urlToAjaxHandler,
      data: data,
      success: onSuccess,
      error: function (arg) {


        var widthStarRatingClip = dzsapInstance.selfClass.cthis.find('.star-rating-set-clip').outerWidth() / dzsapInstance.cthis.find('.star-rating-bg').outerWidth();
        var nrrates = parseInt(dzsapInstance.cthis.find('.counter-rates .the-number').html(), 10);

        nrrates++;

        var aux2 = ((nrrates - 1) * (widthStarRatingClip * 5) + starrating_index) / (nrrates)


        dzsapInstance.cthis.find('.star-rating-set-clip').width(aux2 * (totalWidth / 5));
        dzsapInstance.cthis.find('.counter-rates .the-number').html(nrrates);

        if (dzsapInstance.initOptions.parentgallery && $(dzsapInstance.initOptions.parentgallery).get(0) && $(dzsapInstance.initOptions.parentgallery).get(0).api_player_rateSubmitted) {
          $(dzsapInstance.initOptions.parentgallery).get(0).api_player_rateSubmitted();
        }

      }
    });
  }
}


window.dzsap_init_starRatings_from_dzsap = function (dzsapClass) {
  var dzsapStarRatingsSourceInstance = new dzsapStarRatingsSource(dzsapClass);
}
