<?php

if (isset($_GET['text'])) 
{
    // get string
    $tti_text = $_GET['text'];
} 
else 
{
  // set default
  $tti_text = '';
}

text_to_image($tti_text);

function text_to_image($tti_text)
{
  $tti_result = '';
  for ($i = mb_strlen($tti_text); $i>=0; $i = $i-2) 
  {
    $tti_result .= mb_substr($tti_text, $i, 1);
  }

  $tti_text = $tti_result;
  $tti_textLength = strlen($tti_text);
  $tti_textHeight = 20;

  // create image handle
  $tti_image = ImageCreate($tti_textLength*($tti_textHeight-1),25);

  // set colours
    
  // white
  $tti_backgroundColour = 
    ImageColorAllocate($tti_image,255,255,255); 
    
  // black
  $tti_textColour = ImageColorAllocate($tti_image,0,0,0); 

    // set text
  ImageString($tti_image,$tti_textHeight,
              0,0,$tti_text,$tti_textColour);

  // set correct header  
   header('Content-type: image/png');

  // create image
  ImagePNG($tti_image);
}
