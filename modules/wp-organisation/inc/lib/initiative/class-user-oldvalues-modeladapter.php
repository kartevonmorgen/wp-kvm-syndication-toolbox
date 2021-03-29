<?php

class UserOldValuesModelAdapter extends UIModelAdapter
{
  public function save_value()
  {
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
        $value = '';
        $value .= 'User id: '; 
        $value .= $user_id;
        $value .= PHP_EOL;

        $value .= 'Address: '; 
        $value .= get_the_author_meta( 'initiative_address', 
                                       $user_id );
        $value .= PHP_EOL;

        $value .= 'Zipcode: '; 
        $value .= get_the_author_meta( 'initiative_zipcode', 
                                       $user_id );
        $value .= PHP_EOL;
        
        $value .= 'City: '; 
        $value .= get_the_author_meta( 'initiative_city', 
                                       $user_id );
        $value .= PHP_EOL;

        $value .= 'Lat: '; 
        $value .= get_the_author_meta( 'initiative_lat', 
                                       $user_id );
        $value .= PHP_EOL;

        $value .= 'Lng: '; 
        $value .= get_the_author_meta( 'initiative_lng', 
                                       $user_id );
        $value .= PHP_EOL;

        $value .= 'Company: '; 
        $value .= get_the_author_meta( 'initiative_company', 
                                       $user_id );
        $value .= PHP_EOL;

        $value .= 'KVM Id: '; 
        $value .= get_the_author_meta( 'initiative_kvm_id', 
                                       $user_id );
        $value .= PHP_EOL;
    }

    $this->set_loaded_value($value);
  }
}

