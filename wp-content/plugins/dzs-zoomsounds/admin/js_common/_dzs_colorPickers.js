export function init_dzsColorPickers() {
  var $ = jQuery;
  $(".with_colorpicker").each(function () {
    var _t = $(this);
    if (_t.hasClass("treated")) {
      return;
    }
    if ($.fn.farbtastic) {

      _t.next().find(".picker").farbtastic(_t);

    }
    ;
    _t.addClass("treated");

    _t.bind("change", function () {

      jQuery("#customstyle_body").html("body{ background-color:" + $("input[name=color_bg]").val() + "} .dzsportfolio, .dzsportfolio a{ color:" + $("input[name=color_main]").val() + "} .dzsportfolio .portitem:hover .the-title, .dzsportfolio .selector-con .categories .a-category.active { color:" + $("input[name=color_high]").val() + " }");
    });
    _t.trigger("change");
    _t.bind("click", function () {
      if (_t.next().hasClass("picker-con")) {
        _t.next().find(".the-icon").eq(0).trigger("click");
      }
    })
  })

}