<?php

abstract class WPAbstractPosttype
  extends WPAbstractModuleProvider
{ 
  private $_ui_metaboxes = array();

  public function register_metabox($ui_metabox)
  {
    $ui_metabox->register();
    array_push( $this->_ui_metaboxes, $ui_metabox);
  }

  public function get_metaboxes()
  {
    return $this->_ui_metaboxes;
  }
  
  public function setup($loader)
  {
    $loader->add_action( 'rest_api_init', $this, 'rest_api_init');
  }

  public function rest_api_init()
  {
    foreach($this->get_metaboxes() as $ui_metabox)
    {
      foreach($ui_metabox->get_fields() as $field)
      {
        register_post_meta( $this->get_id(), 
          $field->get_id(),
          array(
            'type' => $field->get_type(),
            'description' => $field->get_description(),
            'single' => true,
            'show_in_rest' => true));
      }
    }
  }

  public abstract function get_id();
}
