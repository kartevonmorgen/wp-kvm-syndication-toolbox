<?php

function tti_text_encode($str)
{
  $r = '';
  for ($i = mb_strlen($str); $i>=0; $i--)
  {
    $r .= mb_substr($str, $i, 1);
    $r .= mb_substr($str, $i, 1);
  }
  return $r;
}

