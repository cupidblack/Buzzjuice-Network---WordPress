'use strict';
jQuery(document).ready(function ($) {

  google.load("visualization", "1.0", {"packages": ["corechart"]});


  google.setOnLoadCallback(dzsap_drawChart);


  function dzsap_drawChart() {

    const $dzsapAdminFeed = $('.dzsap-admin-feed--dashboard-data');
    if ($dzsapAdminFeed.length) {

      let dataArr = JSON.parse($dzsapAdminFeed.eq(0).text());

      var data = new google.visualization.DataTable();
      data.addColumn("string", "Topping");
      data.addColumn("number", "Slices");
      data.addRows(dataArr);
      const options = {
        "title": "Number of Comments",
        "width": "100%",
        "height": 300
      };
      var chart = new google.visualization.PieChart(document.getElementById("dzsap_chart_div"));
      chart.draw(data, options);
    }
  }
})