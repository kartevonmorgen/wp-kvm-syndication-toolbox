<?php

class UIPostModelAdapter extends UIModelAdapter
{
  public function save_value()
  {
    $post_id = $this->get_property(UIModel::POST_ID);
    if(empty($post_id))
    {
      return;
    }
    $value = $this->get_value();
    $post = array(
      'ID'            => $post_id,
      $this->get_id() => $value);
 
    // Update the post into the database
    wp_update_post( $post );
  }

  public function load_value()
  {
    $value = '';
    $post_id = $this->get_property(UIModel::POST_ID);
    if( empty($post_id) )
    {
      if( !empty( $_POST[$this->get_id()] ) )
      {
        $value = sanitize_text_field( 
                   $_POST[$this->get_id()] );
      }
    }
    else
    {
      $post = get_post($post_id);
      if ( empty($post) ) 
      {
        $value = $this->get_default_value();
        // This forces that the default Value comes into the DB
        $this->set_value_changed(true);
      }
      else
      {
        $value = $post->post_title;
      }
    }

    $this->set_loaded_value($value);
  }
}
