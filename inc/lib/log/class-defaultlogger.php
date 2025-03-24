<?php

/**
  * DefaultLogger in a option
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class DefaultLogger extends AbstractLogger
{
  public function __construct($add = false) 
  {
    parent::__construct('scw_toolbox_log', 0, $add);
  }


  public function save()
  {
    $message = $this->get_message();
    if($this->is_add())
    {
      $message = get_option( $this->get_key());
      $this->add_newline();
      $message .= $this->get_message();
    }
    
    update_option(
      $this->get_key(),
      $message);
  }
}
