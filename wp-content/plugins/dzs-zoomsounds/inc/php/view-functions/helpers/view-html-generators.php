<?php


function dzs_view_generateHtmlForAnchorOrStatic($innerHtml, $anchorHref = '', $otherArgs=array()){

  $viewArgs = array(
    'viewClass' => 'first-line-label'
  );

  $viewArgs = array_merge($viewArgs, $otherArgs);

  $outputHtml = '';

  $viewTag = 'span';
  $viewExtraAttr = '';

  if($anchorHref){
    $viewTag = 'a';
    $viewExtraAttr.=' href="'.$anchorHref.'"';
  }

  $outputHtml.='<'.$viewTag.' class="'.$viewArgs['viewClass'].'" '.$viewExtraAttr.'>';


  $outputHtml.=$innerHtml;

  $outputHtml.='</'.$viewTag.'>';





  return $outputHtml;
}