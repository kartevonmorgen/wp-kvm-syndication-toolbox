<?php

/** 
 * UIMetabox
 * This Class is used to add a Settings Section to an Settings Page
 * it uses the Settings API 
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UIMetabox
{
  private $_title;
  private $_posttype;
  private $_context;
  private $_priority;
  private $_id;
  private $_description = '';

  private $_fieldprefix = '';
  private $_fields = array();

  public function __construct($id, 
                              $title, 
                              $posttype, 
                              $context = 'advanced',
                              $priority = 'default')
  {
    $this->_id = $id;
    $this->_title = $title;
    $this->_posttype = $posttype;
    $this->_context = $context;
    $this->_priority = $priority;
  }

  public function set_prefix($fieldprefix)
  {
    $this->_fieldprefix = $fieldprefix;
  }

  public function register()
  {
    if(is_admin())
    {
      add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
      add_action( 'save_post', array( $this, 'save_metabox' ) );
    }
   // add_action( 'rest_api_init', array( $this, 'register_rest_api_fields' ));
  }

  public function register_now()
  {
    if(is_admin())
    {
      $this->add_metabox();
      add_action( 'save_post', array( $this, 'save_metabox' ) );
    }
  }

  public function add_field($field)
  {
    array_push($this->_fields, $field);
    return $field;
  }

  public function add_textfield($field_id, $field_title)
  {
    return $this->add_field( 
      new UIMetaboxField($this->_fieldprefix . $field_id, 
                         $field_title) );
  }

  public function add_datefield($field_id, $field_title)
  {
    return $this->add_field( 
      new UIMetaboxDateField($this->_fieldprefix . $field_id, 
                             $field_title) );
  }

  public function add_openinghoursfield($field_id, $field_title)
  {
    return $this->add_field( 
      new UIMetaboxOpeningHoursField($this->_fieldprefix . $field_id, 
                                     $field_title) );
  }

  public function add_textarea($field_id, $field_title)
  {
    return $this->add_field( 
      new UIMetaboxTextAreaField($this->_fieldprefix . $field_id, 
                                 $field_title) );
  }

  public function add_dropdownfield($field_id, $field_title)
  {
    return $this->add_field( 
      new UIMetaboxDropDownField($this->_fieldprefix . $field_id, 
                                 $field_title));
  }

  public function add_checkbox($field_id, $field_title)
  {
    return $this->add_field( 
      new UIMetaboxCheckBoxField($this->_fieldprefix . $field_id, 
                                 $field_title));
  }

  public function add_metabox()
  {
    add_meta_box($this->get_id(),
               __( $this->get_title(), 'site'),
               array( $this, 'metabox_callback'),
               $this->get_posttype(),
               $this->get_context(),
               $this->get_priority());
  }

  public function metabox_callback($post)
  {
    wp_nonce_field ( $this->get_nonce_field_id(),
                     $this->get_nonce_field_id());

    foreach($this->get_fields() as $field)
    {
      $field->show_value($post);
    }
  }

  public function save_metabox($post_id)
  {
    if ( ! isset( $_POST[$this->get_nonce_field_id()] ) )
    {
      return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce(
           $_POST[$this->get_nonce_field_id()],
           $this->get_nonce_field_id() ) )
    {
      return;
    }

    // If this is an autosave, our form has 
    // not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    {
      return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) &&
         $this->get_posttype() == $_POST['post_type'] )
    {
      if ( ! current_user_can( 'edit_post', $post_id ) )
      {
        return;
      }
    }
    else
    {
      return;
    }

    /* OK, it's safe for us to save the data now. */
    foreach($this->get_fields() as $field)
    {
      $field->save_value($post_id);
    }
  }

  public function register_rest_api_fields()
  {
    foreach($this->get_fields() as $field)
    {
      register_rest_field( $field->get_id(), 
                           $this->get_id(),
                           array( 'get_callback' => 
                             array($this, 'get_rest_api_value'),
                             null,
                             null));

    }
  }
  
  public function get_rest_api_value($object, $field_id, $request)
  {
    $field = $this->get_field_by_id($field_id);
    return $field->get_value();
  }

  public function set_description($description)
  {
    $this->_description = $description;
  }

  function the_description()
  {
    echo '' . $this->_description;
  }

  public function get_fields()
  {
    return $this->_fields;
  }

  public function get_field_by_id($id)
  {
    foreach($this->get_fields() as $field)
    {
      if($id == $field->get_id())
      {
        return $field;
      }
    }
    return null;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_title()
  {
    return $this->_title;
  }

  public function get_posttype()
  {
    return $this->_posttype;
  }

  public function get_context()
  {
    return $this->_context;
  }

  public function get_priority()
  {
    return $this->_priority;
  }

  public function get_nonce_field_id()
  {
    return $this->get_id() . '_nonce';
  }

}
