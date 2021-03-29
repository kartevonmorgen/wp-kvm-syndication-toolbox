<?php
/**
  * PHPArrayUtil
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class PHPArrayUtil 
{
  public function __construct() 
  {
  }

  public function remove_empty_entries($array)
  {
    $returned = array();
    if(empty($array))
    {
      return $returned;
    }
   
    foreach($array as $element)
    {
      if(empty(trim($element)))
      {
        continue;
      }
      array_push($returned, $element);
    } 
    return $returned;
  }
}
