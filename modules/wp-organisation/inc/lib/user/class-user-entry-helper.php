<?php

class UserEntryHelper extends WPAbstractModuleProvider 
{
  public function get_entries_by_user($user_id)
  {
    $type = $this->get_current_module()->get_type();

    $args = array(
      'post_status' => 'any',
      'post_type' => $type->get_id(),
      'numberposts' => -1,
      'author' => $user_id);
    return get_posts($args);
  }

  public function get_entry_by_user($user_id)
  {
    $entries = $this->get_entries_by_user($user_id);
    if(empty($entries))
    {
      return null;
    }
    return reset($entries);
  }
}
