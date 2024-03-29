<?php

class UITableAction
{
  private $_id;
  private $_title;
  private $_menu_title;
  private $_parent_menu_id = null;
  private $_parent_menu_php_file = 'edit.php';
  private $_entity_title;
  private $_enabled_roles = array();
  private $_disabled_roles = array();
  private $_fields = array();
  private $_post_status = 'publish';

  public function __construct($id, $title, $menu_title, $entity_title)
  {
    $this->_id = $id;
    $this->_title = $title;
    $this->_menu_title = $menu_title;
    if(empty($menu_title))
    {
      $this->_menu_title = $title;
    }
    $this->_entity_title = $entity_title;
  }
  
  public function get_id()
  {
    return $this->_id;
  }

  public function get_title()
  {
    return $this->_title;
  }

  public function get_menu_title()
  {
    return $this->_menu_title;
  }

  public function set_parent_menu($php, $id)
  {
    $this->_parent_menu_php_file = $php;
    $this->_parent_menu_id = $id;
  }

  public function get_parent_menu_php_file()
  {
    return $this->_parent_menu_php_file;
  }

  public function is_edit()
  {
    return $this->get_parent_menu_php_file() == 'edit.php';
  }

  public function get_parent_menu_id()
  {
    return $this->_parent_menu_id;
  }

  public function get_entity_title()
  {
    return $this->_entity_title;
  }

  public function add_enabled_role($role)
  {
    array_push($this->_enabled_roles, $role);
  }

  protected function get_enabled_roles()
  {
    return $this->_enabled_roles;
  }

  public function add_disabled_role($role)
  {
    array_push($this->_disabled_roles, $role);
  }

  protected function get_disabled_roles()
  {
    return $this->_disabled_roles;
  }

  public function is_allowed($user_id)
  {
    $user_meta = get_userdata($user_id);
    $user_roles = $user_meta->roles;

    if(empty($this->get_enabled_roles()) &&
       empty($this->get_disabled_roles()))
    {
      return true;
    }

    foreach($this->get_enabled_roles() as $role)
    {
      if ( in_array( $role, $user_roles, true ) )
      {
        return true;
      }
    }

    foreach($this->get_disabled_roles() as $role)
    {
      if ( !in_array( $role, $user_roles, true ) )
      {
        return true;
      }
    }
    return false;
  }

  public function add_field($field)
  {
    array_push($this->_fields, $field);
    return $field;
  }

  public function get_fields()
  {
    return $this->_fields;
  }

  public function set_post_status($post_status)
  {
    $this->_post_status = $post_status;
  }

  public function get_post_status()
  {
    return $this->_post_status;
  }

}
