<?php

/**
  * AbstractLogger
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
abstract class AbstractLogger
{
  private $prefix;
  private $message;
  private $echomessage;
  private $id;
  private $key;
  private $add;

  public function __construct($key, $id, $add = false) 
  {
    $this->id = $id;
    $this->key = $key;
    $this->add = $add;

    $this->reset();
  }

  public function is_add()
  {
    if($this->add)
    {
      return $this->add;
    }
    else
    {
      $mc = WPModuleConfiguration::get_instance();
      $root = $mc->get_root_module();
      return $root->is_reset_log_manual();
    }
  }

  public function add_date()
  {
    $this->message .= '[' . 
      get_date_from_gmt(date("Y-m-d H:i:s")) . '] ';
    $this->echomessage .= '[' . 
      get_date_from_gmt(date("Y-m-d H:i:s")) . '] ';
  }

  public function add_prefix($prefix)
  {
    $this->prefix = $prefix;
  }

  public function remove_prefix()
  {
    $this->prefix = '';
  }

  public function add_line($line)
  {
    $this->message .= $this->prefix;
    $this->message .= $line;
    $this->echomessage .= $this->prefix;
    $this->echomessage .= $line;
    $this->add_newline();
  }

  public function add_newline()
  {
    $this->message .= PHP_EOL;
    $this->echomessage .= '<br>';
  }

  public abstract function save();

  public function reset()
  {
    $this->prefix = '';
    $this->message = '';
    $this->echomessage = '';
  }

  public function get_message()
  {
    return $this->message;
  }
  
  public function get_echomessage()
  {
    return $this->echomessage;
  }

  public function get_id()
  {
    return $this->id;
  }

  public function get_key()
  {
    return $this->key;
  }

  public function add_stacktrace() 
  {
    $stacktrace = debug_backtrace();
    $this->add_line(str_repeat("=", 50));
    $i = 1;
    foreach($stacktrace as $node) 
    {
      $this->add_line("$i. ".basename($node['file']) .":" .
                      $node['function'] ."(" .$node['line'].")");
      $i++;
    }
    $this->add_newline();
  }

  public function echo_log()
  {
    echo '<p>' . $this->echomessage .'</p>';
    $this->echomessage = '';
  }

}
