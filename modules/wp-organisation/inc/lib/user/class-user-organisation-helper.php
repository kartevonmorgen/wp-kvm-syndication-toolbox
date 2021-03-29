<?php

class UserOrganisationHelper
{
  public function get_organisations_by_user($user_id)
  {
    $args = array(
      'post_status' => 'any',
      'post_type' => 'organisation',
      'numberposts' => -1,
      'author' => $user_id);
    return get_posts($args);
  }

  public function get_organisation_by_user($user_id)
  {
    $organisations = $this->get_organisations_by_user($user_id);
    if(empty($organisations))
    {
      return null;
    }
    return reset($organisations);
  }
}
