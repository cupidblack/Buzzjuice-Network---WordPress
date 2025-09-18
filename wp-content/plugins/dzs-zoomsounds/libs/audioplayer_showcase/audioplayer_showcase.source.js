"use strict";


import {load_statistics} from "./inc_js/_view_trackAnalytics";
import {init_statsBtn} from "./inc_js/_view_statsBtn";

class AudioPlayerShowcase {


  init(){
    this.load_statistics = load_statistics;


    init_statsBtn(this);
  }

}
jQuery(document).ready(function ($) {



  const audioPlayerShowcase = new AudioPlayerShowcase();



  audioPlayerShowcase.init();


})

