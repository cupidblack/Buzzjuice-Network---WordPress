export const isLodash = () => {
  let isLodash = false;

  // If _ is defined and the function _.forEach exists then we know underscore OR lodash are in place
  if ( 'undefined' != typeof( _ ) && 'function' == typeof( _.forEach ) ) {

    // A small sample of some of the functions that exist in lodash but not underscore
    const funcs = [ 'get', 'set', 'at', 'cloneDeep' ];

    // Simplest if assume exists to start
    isLodash  = true;

    funcs.forEach( function ( func ) {
      // If just one of the functions do not exist, then not lodash
      isLodash = ( 'function' != typeof( _[ func ] ) ) ? false : isLodash;
    } );
  }

  if ( isLodash ) {
    // We know that lodash is loaded in the _ variable
    return true;
  } else {
    // We know that lodash is NOT loaded
    return false;
  }
};

export function postAjax(url, data, success) {
  var params = typeof data == 'string' ? data : Object.keys(data).map(
    function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
  ).join('&');

  var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
  xhr.open('POST', url);
  xhr.onreadystatechange = function() {

    if (xhr.readyState>3 && xhr.status===200) { success(xhr.responseText); }
  };
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.send(params);
  return xhr;
}
export function decode_json(arg) {
  var fout = {};

  if(arg){

    try{

      fout=JSON.parse(arg);
    }catch(err){
      console.log(err, arg);
    }
  }

  return fout;
}
export function add_query_arg(purl, key,value){
  key = encodeURIComponent(key); value = encodeURIComponent(value);



  var s = purl;
  var pair = key+"="+value;

  var r = new RegExp("(&|\\?)"+key+"=[^\&]*");




  s = s.replace(r,"$1"+pair);

  var addition = '';
  if(s.indexOf(key + '=')>-1){


  }else{
    if(s.indexOf('?')>-1){
      addition = '&'+pair;
    }else{
      addition='?'+pair;
    }
    s+=addition;
  }


  if(value=='NaN'){
    var regex_attr = new RegExp('[\?|\&]'+key+'='+value);
    s=s.replace(regex_attr, '');
  }




  return s;
}

/**
 *
 * @param {array} haystackArray
 * @param {string} needleKey
 * @param {string} needleValue
 */
export function getObjectByKey(haystackArray, needleKey, needleValue){




  let foundEl = null;

  if(haystackArray.forEach){
    haystackArray.forEach((el)=>{

      if(el[needleKey]===needleValue){
        foundEl = el;
      }
    });
  }

  return foundEl;

}

