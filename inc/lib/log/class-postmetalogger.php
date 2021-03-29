<?php

/**
  * PostMetaLogger
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class PostMetaLogger extends AbstractLogger
{

  public function save()
  {
    $message = $this->get_message();
    if($this->is_add())
    {
      $message = get_post_meta( $this->get_id(), $this->get_key(), true);
      $this->add_newline();
      $message .= $this->get_message();
    }
    
    update_post_meta(
      $this->get_id(),
      $this->get_key(),
      $message);
  }
}
