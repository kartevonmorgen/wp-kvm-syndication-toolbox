<?php

/**
  * PostMetaLogger
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class LogResult
{
  private $_result;
  private $_message;
  
  public function __construct($result, $message = '') 
  {
    $this->_result = $result;
    $this->_message = $message;
  }

  public function is_true()
  {
    return $this->_result;
  }

  public function is_false()
  {
    return !$this->_result;
  }

  public function set_message($message)
  {
    $this->_message = $message;
  }

  public function get_message()
  {
    return $this->_message;
  }

  public static function true_result($message)
  {
    return new LogResult(true, $message);
  }

  public static function false_result($message)
  {
    return new LogResult(false, $message);
  }

  public static function check($value1, $value2, $prefix)
  {
    if(empty($value1))
    {
      $result = new LogResult(empty($value2));
    }
    else
    {
      if(is_string($value1) && is_string($value2))
      {
        $result = new LogResult(strcmp(trim($value1), trim($value2)) === 0);
      }
      else
      {
        $result = new LogResult($value1 === $value2);
      }
    }
    if($result->is_true())
    {
      $result->set_message($prefix . ' is equal(' . $value1 
                             . ', ' . $value2 . ')');
    }
    else
    {
      $result->set_message($prefix . ' is not equal(' . $value1 . ', ' . $value2 . ')');
    }
    return $result;
  }
}
