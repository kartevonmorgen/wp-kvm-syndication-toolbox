<?php

class UserTableColumns extends WPAbstractModuleProvider 
{
  public function setup($loader)
  {
    $loader->add_filter( 'manage_users_columns', $this, 'modify_user_table' );
    $loader->add_filter( 'manage_users_custom_column', $this, 'modify_user_table_row', 10, 3 );
  }

  function modify_user_table( $column )
  {
    unset($column['posts']);
    $column['organisation'] = 'Organisation';
    $column['approved'] = 'Bestätigt';
    return $column;
  }

  function modify_user_table_row( $val, 
                                  $column_name, 
                                  $user_id ) 
  {
    if ('organisation' == $column_name) 
    {
      $module = $this->get_current_module();
      $organisation = $module->get_organisation_by_user($user_id);
      if(empty($organisation))
      {
        return null;
      }
    
      return '<a href="'.admin_url('post.php?post='.$organisation->ID.'&action=edit').'">'.$organisation->post_title.'</a>';
    }

    if ('approved' == $column_name) 
    {
      $user_meta = get_userdata($user_id);
      $user_roles = $user_meta->roles;
      if ( in_array( 'subscriber', $user_roles, true ) ) 
      {
        return '<b>NEIN</b>';
      }
      else
      {
        return 'JA';
      }
    }

    return $val;
  }
}
