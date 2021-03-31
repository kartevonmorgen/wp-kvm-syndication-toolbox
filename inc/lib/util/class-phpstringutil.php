<?php
/**
  * PHPStringUtil
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class PHPStringUtil 
{
  public function __construct() 
  {
  }

  public function startsWith( $haystack, $needle ) 
  {
    $length = strlen( $needle );
    return substr( $haystack, 0, $length ) === $needle;
  }

  public function endsWith( $haystack, $needle ) 
  {
    $length = strlen( $needle );
    if( !$length ) 
    {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
  }

  public function findParameters($params)
  {
    $result = array();

    do
    {
      $pos = $this->findPositionWithoutQuotes($params, ';');
      if($pos === false)
      {
        $param = $params;
      }
      else
      {
        $param = substr($params, 0, $pos);
      } 

      $pos2 = $this->findPositionWithoutQuotes($param, '=');
      if($pos2 !== false)
      {
        $paramKey = substr($param, 0, $pos2);
        $paramValue = substr($param, $pos2 + 1);
        $result[$paramKey] = $paramValue;
      }

      if($pos !== false)
      {
        $params = substr($params, $pos + 1);
      }
    }
    while($pos !== false);

    return $result;
  }

  
  public function findPositionWithoutQuotes($string, $searchChar)
  {
    $inQuotes = false;
    //echo 'FIND : ' . $string . ' - ' . $seachChar;
    for($i = 0; $i < strlen($string); $i++)
    {
      //echo 'I . ' . $i . ' = ' . $string[$i] . ' Q=' . $inQuotes;
      if($string[$i] == '"')
      {
        $inQuotes = ! $inQuotes;
      }
      else if($string[$i] == $searchChar)
      {
        if(!$inQuotes)
        {
          return $i;
        }
      }
    }
    return false;
  }

  public function remove_quotes($string)
  {
    return str_replace('"', '', $string);
  }

  public function replace_procent_twenty($string)
  {
    return str_replace('%20', ' ', $string);
  }

  public function str_split_unicode($str, $l = 0) 
  {
    if ($l > 0) 
    {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
  }

  public function contains($string, $contains, $case_insensitive = false)
  {

    if($case_insensitive === false)
    {
      $result = strpos($string, $contains);
    }
    else
    {
      $result = strripos($string, $contains);
    }
    if($result === false)
    {
      return false;
    }
    return true;
  }


}
