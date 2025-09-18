export function postAjax(url, data, success) {
  var params = typeof data == 'string' ? data : Object.keys(data).map(
    function (k) {
      return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
    }
  ).join('&');

  var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
  xhr.open('POST', url);
  xhr.onreadystatechange = function () {
    if (xhr.readyState > 3 && xhr.status == 200) {
      success(xhr.responseText);
    }
  };
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.send(params);
  return xhr;
}

String.prototype.plusDecode = function() {
  return decodeURIComponent(this.replace(/\+/gm,"%20"));
}

export function get_query_arg(purl, key, isGoingToDecode = false){
  if(purl.indexOf(key+'=')>-1){
    //faconsole.log('testtt');
    var regexS = "[?&]"+key + "=.+";
    var regex = new RegExp(regexS);
    var regtest = regex.exec(purl);


    if(regtest != null){
      var splitterS = regtest[0];
      if(splitterS.indexOf('&')>-1){
        var aux = splitterS.split('&');
        splitterS = aux[1];
      }
      var splitter = splitterS.split('=');

      if(isGoingToDecode){

        return String((splitter[1])).plusDecode();
      }
      return String((splitter[1]));

    }
  }
}

export function get_query_arg_all(purl){



  var arr = {};
  if(purl.indexOf('=')>-1){

    var regex = /[?|&](.*?)=(.*?)(?=&|$)/g;

    var aux = null;
    while(aux = regex.exec(purl)){




      arr[aux[1]] = aux[2];

    }
  }

  return arr;
}

export function decode_json(arg) {
  var fout = {};

  if (arg) {

    try {

      fout = JSON.parse(arg);
    } catch (err) {
      console.log(err, arg);
    }
  }

  return fout;
}

export function add_query_arg(purl, key, value) {
  key = encodeURIComponent(key);
  value = encodeURIComponent(value);


  var s = purl;
  var pair = key + "=" + value;

  var r = new RegExp("(&|\\?)" + key + "=[^\&]*");



  s = s.replace(r, "$1" + pair);
  var addition = '';
  if (s.indexOf(key + '=') > -1) {


  } else {
    if (s.indexOf('?') > -1) {
      addition = '&' + pair;
    } else {
      addition = '?' + pair;
    }
    s += addition;
  }

  // -- if value NaN we remove this field from the url
  if (value == 'NaN') {
    var regex_attr = new RegExp('[\?|\&]' + key + '=' + value);
    s = s.replace(regex_attr, '');
  }



  return s;
}

export function convert_for_gutenberg_build_options(argarr){


  return argarr;
}
