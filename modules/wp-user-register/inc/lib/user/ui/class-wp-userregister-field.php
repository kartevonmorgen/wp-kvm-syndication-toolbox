<?php

class WPUserRegisterField
{
  private $_post_id;

  private $_title;
  private $_description;

  private $_id;
  private $_type_id;
  private $_position = 0;
  private $_bgcolor_id;

  public function __construct($urpost = null)
  {
    $this->set_position( 0 ); 
    if(empty($urpost))
    {
      $this->set_bgcolor_id( 'white' ); 
      $this->set_type_id( 'field' ); 
      return;
    }

    $this->set_post_id( $urpost->ID);

    $this->set_title( $urpost->post_title);
    $this->set_description( $urpost->post_content);

    $this->set_id( get_post_meta($urpost->ID, 'urpost_fieldid', true) ); 
    $this->set_type_id( get_post_meta($urpost->ID, 'urpost_typeid', true) ); 
    
    $position = get_post_meta($urpost->ID, 'urpost_position', true); 
    if(!empty($position))
    {
      $this->set_position( $position );
    }
   
    $this->set_bgcolor_id( get_post_meta($urpost->ID, 'urpost_bgcolorid', true) ); 
  }

  public function set_post_id($postid)
  {
    $this->_post_id = $postid;
  }

  public function get_post_id()
  {
    return $this->_post_id;
  }

  public function set_id($id)
  {
    $this->_id = $id;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function set_title($title)
  {
    $this->_title = $title;
  }

  public function get_title()
  {
    return $this->_title;
  }

  public function set_description($description)
  {
    $this->_description = $description;
  }

  public function has_description()
  {
    return !empty($this->_description);
  }

  public function get_description()
  {
    return $this->_description;
  }

  public function set_type_id($type_id)
  {
    $this->_type_id = $type_id;
  }

  public function get_type_id()
  {
    return $this->_type_id;
  }

  public function is_field()
  {
    return $this->get_type_id() == 'field';
  }

  public function is_text()
  {
    return $this->get_type_id() == 'text';
  }

  public function set_position($position)
  {
    $this->_position = $position;
  }

  public function get_position()
  {
    return $this->_position;
  }

  public function set_bgcolor_id($bgcolor_id)
  {
    $this->_bgcolor_id = $bgcolor_id;
  }

  public function get_bgcolor_id()
  {
    return $this->_bgcolor_id;
  }

  public function insert_post()
  {
    if(!empty($this->get_post_id()))
    {
      echo '<p>We can not insert a urpost, because it is already there</p>';
      return;
    }

    $post_id = wp_insert_post( array(
        'post_title'    => $this->get_title(),
        'post_content'    => $this->has_description() ? $this->get_description() : '',
        'post_type'     => 'urpost',
        'post_status'     => 'publish',
        'meta_input' => array(
          'urpost_fieldid' => $this->get_id(),
          'urpost_typeid' => $this->get_type_id(),
          'urpost_position' => $this->get_position(),
          'urpost_bgcolorid' => $this->get_bgcolor_id()
         )));

    if(is_wp_error($post_id))
    {
      echo '<p>Error by doing insert urpost: ' . $post_id->get_error_message() . '</p>';
      return;
    }
    $this->set_post_id($post_id);
  }

}
