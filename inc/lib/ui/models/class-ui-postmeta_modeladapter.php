<?php

class UIPostMetaModelAdapter extends UIModelAdapter
{
  public function save_value()
  {
    $post_id = $this->get_property(UIModel::POST_ID);
    if(empty($post_id))
    {
      return;
    }
    $value = $this->get_value();
    update_post_meta( $post_id, 
                      $this->get_id(),
                      $value);
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
      if ( metadata_exists( 'post', $post_id, $this->get_id() ) ) 
      {
        $value = get_post_meta( $post_id, $this->get_id(), true);

      }
      else
      {
        $value = $this->get_default_value();
        // This forces that the default Value comes into the DB
        $this->set_value_changed(true);
      }
    }

    $this->set_loaded_value($value);
  }
}
