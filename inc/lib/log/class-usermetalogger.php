<?php

/**
  * UserMetaLogger
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class UserMetaLogger extends AbstractLogger
{

  public function save()
  {
    update_user_meta(
      $this->get_id(),
      $this->get_key(),
      $this->get_message());
  }
}
