var dzsap_singleton_statistics = null;

function init_singleton_statistics() {
  if (dzsap_singleton_statistics === null) {
    dzsap_singleton_statistics = this;


    jQuery(document).on('click', '.stats-container--bg', handle_mouse);


    function handle_mouse(e) {

      var $t = jQuery(this);

      if ($t.hasClass('stats-container--bg')) {

        statistics_view__remove_containers($t.parent().parent());

      }
    }
  }

}

export function statistics_view__remove_containers(_con) {


  _con.find('.stats-btn').removeClass('active');
  window.$dzsap_currentStats.each(function () {
    var _t2 = jQuery(this);
    _t2.addClass('transitioning-out').removeClass('loaded');


    setTimeout(function () {
      _con.find('.stats-container.transitioning-out').remove()
    }, 400)
  })
}

function statistics_view__construct_structure(response) {

  let struct = '<div class="stats-container"><div class="stats-container--bg"></div><div class="stats-container--inner"><div class="stats-container--inner--content">{{response}}</div></div></div>';


  struct = struct.replace('{{response}}', response);

  return struct;

}

/**
 *
 * @param _con
 */
export function load_statistics(_con) {

  var $ = jQuery;

  init_singleton_statistics();


  if (window.google && window.google.charts) {
    if (window.google.visualization) {
      const _statsBtn = _con.find('.stats-btn').eq(0);
      console.info("NOW APPLYING", _statsBtn.eq(0).attr('data-playerid'));

      const data = {
        action: 'ajax_get_statistics_html',
        url: _statsBtn.attr('data-url'),
        postdata: _statsBtn.attr('data-playerid')
      };

      if (_statsBtn.attr('data-sanitized_source')) {
        data.sanitized_source = _statsBtn.attr('data-sanitized_source');
      }


      $.ajax({
        type: "POST",
        url: window.dzsap_settings.dzsap_site_url + '/?dzsap_action=dzsap_load_charts_html',
        data: data,
        success: function (response) {
          if (typeof window.console != "undefined") {
            console.groupCollapsed('Submit message Got this from the server:');
            console.log(' ' + response);
            console.groupEnd();
          }


          $('body').append(statistics_view__construct_structure(response));

          window.$dzsap_currentStats = $('body').children('.stats-container');
          console.log(window.$dzsap_currentStats);

          setTimeout(function () {

            const _c = window.$dzsap_currentStats;


            _c.addClass('loaded');


            var auxr = /<div class="hidden-data">(.*?)<\/div>/g;
            var aux = auxr.exec(response);


            var aux_resp = '';
            if (aux[1]) {
              aux_resp = aux[1];
            }


            var resp_arr = [];


            try {
              resp_arr = JSON.parse(aux_resp);
            } catch (err) {

            }


            var arr = [];


            arr[0] = [];
            for (var i in resp_arr['labels']) {


              arr[0].push(resp_arr['labels'][i]);
            }
            for (var i in resp_arr['lastdays']) {


              i = parseInt(i, 10);

              arr[i + 1] = [];
              for (var j in resp_arr['lastdays'][i]) {

                j = parseInt(j, 10);


                var val4 = (resp_arr['lastdays'][i][j]);

                if (j != 0) {

                  val4 = parseFloat(val4);
                }


                if (isNaN(val4) == false) {
                  resp_arr['lastdays'][i][j] = val4;
                }
                arr[i + 1].push(resp_arr['lastdays'][i][j]);
              }

            }


            var data = google.visualization.arrayToDataTable(arr);

            var options = {

              backgroundColor: '#444444'
              , height: '300'
              , legend: {position: 'top', maxLines: 1}
              , chart: {
                title: 'Track Performance'
                , backgroundColor: '#444444'
              }
              , chartArea: {
                backgroundColor: '#444444'
              }
              , tooltip: {isHtml: true}
            };


            var chart = new google.visualization.AreaChart(_c.find('.trackchart').get(0));
            chart.draw(data, options);


            auxr = /<div class="hidden-data-time-watched">(.*?)<\/div>/g;

            aux = auxr.exec(response);


            aux_resp = '';
            if (aux[1]) {
              aux_resp = aux[1];
            }


            resp_arr = [];


            try {
              resp_arr = JSON.parse(aux_resp);
            } catch (err) {

            }


            arr = [];


            arr[0] = [];
            for (var i in resp_arr['labels']) {


              arr[0].push(resp_arr['labels'][i]);
            }
            for (var i in resp_arr['lastdays']) {


              i = parseInt(i, 10);

              arr[i + 1] = [];
              for (var j in resp_arr['lastdays'][i]) {

                j = parseInt(j, 10);


                var val4 = (resp_arr['lastdays'][i][j]);

                if (j != 0) {

                  val4 = parseInt((parseFloat(val4) / 60), 10);
                }


                if (isNaN(val4) == false) {
                  resp_arr['lastdays'][i][j] = val4;
                }
                arr[i + 1].push(resp_arr['lastdays'][i][j]);
              }

            }


            data = google.visualization.arrayToDataTable(arr);

            options = {

              color: '#bcb36b'
              , colors: ['#e0d365', '#e6693e', '#ec8f6e', '#f3b49f', '#f6c7b6']
              , backgroundColor: '#444444'
              , height: '300'
              , legend: {position: 'top', maxLines: 3}
              , bar: {groupWidth: "70%"}
              , chart: {
                title: 'Track Performance'
                , backgroundColor: '#444444'
              }
              , chartArea: {
                backgroundColor: '#444444'
              }
              , tooltip: {isHtml: true}
            };


            var chart2 = new google.visualization.ColumnChart(_c.find('.trackchart-time-watched').get(0));
            chart2.draw(data, options);


            auxr = /<div class="hidden-data-month-viewed">(.*?)<\/div>/g;

            aux = auxr.exec(response);


            aux_resp = '';
            if (aux[1]) {
              aux_resp = aux[1];
            }


            resp_arr = [];


            try {
              resp_arr = JSON.parse(aux_resp);
            } catch (err) {

            }


            arr = [];


            arr[0] = [];
            for (var i in resp_arr['labels']) {


              arr[0].push(resp_arr['labels'][i]);
            }
            for (var i in resp_arr['lastdays']) {


              i = parseInt(i, 10);

              arr[i + 1] = [];
              for (var j in resp_arr['lastdays'][i]) {

                j = parseInt(j, 10);


                var val4 = (resp_arr['lastdays'][i][j]);

                if (j != 0) {

                  val4 = parseFloat(val4);
                }


                if (isNaN(val4) == false) {
                  resp_arr['lastdays'][i][j] = val4;
                }
                arr[i + 1].push(resp_arr['lastdays'][i][j]);
              }

            }


            data = google.visualization.arrayToDataTable(arr);

            options = {

              color: '#bcb36b'
              , colors: ['#66a4e0', '#e6693e', '#ec8f6e', '#f3b49f', '#f6c7b6']
              , backgroundColor: '#444444'
              , height: '300'
              , legend: {position: 'top', maxLines: 3}
              , bar: {groupWidth: "70%"}
              , chart: {
                title: 'Track Performance'
                , backgroundColor: '#444444'
              }
              , chartArea: {
                backgroundColor: '#444444'
              }
              , tooltip: {isHtml: true}
            };


            var chart3 = new google.visualization.ColumnChart(_c.find('.trackchart-month-viewed').get(0));
            chart3.draw(data, options);


            _c.slideDown("fast");

            setTimeout(function () {

              $(this).css('height', 'auto');
            }, 400);


          }, 100);


        },
        error: function (arg) {
          if (typeof window.console != "undefined") {
            console.log('Got this from the server: ' + arg, arg);
          }
          ;

        }
      });


    } else {
      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(function () {
        load_statistics(_con);
      });
    }


  } else {

    if (window.dzsvg_loading_google_charts) {


    } else {


      var url = 'https://www.gstatic.com/charts/loader.js';


      $.ajax({
        url: url,
        dataType: "script",
        success: function (arg) {


          console.info('loaded charts');


        }
      });


      window.dzsvg_loading_google_charts = true;
    }

    setTimeout(function () {
      load_statistics(_con)
    }, 1000);
  }

}
