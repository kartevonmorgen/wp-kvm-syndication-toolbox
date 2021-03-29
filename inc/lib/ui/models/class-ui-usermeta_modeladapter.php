<?php

class UIUserMetaModelAdapter extends UIModelAdapter
{
  public function save_value()
  {
    $user_id = $this->get_property(UIModel::USER_ID);
    if(empty($user_id))
    {
      return;
    }
    $value = $this->get_value();
    update_user_meta( $user_id, 
                      $this->get_id(),
                      $value);
  }

  public function load_value()
  {
    $value = '';
    $user_id = $this->get_property(UIModel::USER_ID);
    if( empty($user_id) )
    {
      if( !empty( $_POST[$this->get_id()] ) )
      {
        $value = sanitize_text_field( 
                   $_POST[$this->get_id()] );
      }
    }
    else
    {
      if ( metadata_exists( 'user', $user_id, $this->get_id() ) ) 
      {
        $value = get_the_author_meta( $this->get_id(), 
                                      $user_id );

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
