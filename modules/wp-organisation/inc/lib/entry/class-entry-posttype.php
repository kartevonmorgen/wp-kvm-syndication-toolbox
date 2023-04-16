<?php

abstract class EntryPosttype 
  extends WPAbstractModuleProvider
  implements WPModuleStarterIF
{ 
  public function setup_actions($loader)
  {
  }

  public function start()
  {
    $this->create_post_type();
  }

  public function get_type()
  {
    return $this->get_current_module()->get_type();
  }

  public abstract function create_labels();

  protected abstract function create_post_type_addons($args);

  protected abstract function create_post_type_metabox1_addons($ui_metabox);

  function create_post_type() 
  {
    $slug = $this->get_type()->get_id();

    $args = array(
      'labels'             => $this->create_labels(),
      'description'        => __( 'Description.', $slug ),
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => $slug ),
      //'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'show_in_rest'       => true,
      'taxonomies'         => array( 'category', 'post_tag' ),
      'supports'           => array( 'title', 
                                     'editor', 
                                     'author', 
                                     'thumbnail', 
                                     'excerpt', 
                                     'revisions' ),
      'map_meta_cap'       => true );
   
    $this->create_post_type_addons($args);

    $ui_metabox = new UIMetabox($slug . '_contact_metabox',
                                'Kontaktdaten',
                                $slug);

    $this->create_post_type_metabox1_addons($ui_metabox);

    $ui_metabox->add_textfield($slug . '_address', 'Strasse und Nr.');
    $ui_metabox->add_textfield($slug . '_zipcode', 'Postleitzahl');
    $ui_metabox->add_textfield($slug . '_city', 'Ort');
    $field = $ui_metabox->add_textfield($slug . '_lat', 'Latitude');
    $field->set_disabled(true);
    $field = $ui_metabox->add_textfield($slug . '_lng', 'Longitude');
    $field->set_disabled(true);
    $ui_metabox->register();

    $ui_metabox = new UIMetabox($slug . '_contactperson_metabox',
                                'Kontaktperson',
                                $slug);
    $ui_metabox->add_textfield($slug . '_firstname', 'Vorname');
    $ui_metabox->add_textfield($slug . '_lastname', 'Nachname');
    $ui_metabox->add_textfield($slug . '_phone', 'Telefon');
    $ui_metabox->add_textfield($slug . '_email', 'Email');
    $ui_metabox->add_textfield($slug . '_website', 'Webseite');
    $ui_metabox->register();

    if ($this->is_module_enabled('wp-kvm-interface')) 
    { 
      // Karte von morgen Meldungen
      $ui_metabox = new UIMetabox($slug . '_kvm_log_metabox',
                                  'Karte von morgen',
                                  $slug);

      $field = $ui_metabox->add_textarea($slug . '_kvm_log', 
                                        'Meldung');

      // Field should be disabled, otherwise the UI
      // updates the field after the KVM has updated it.
      $field->set_disabled(true);

      $field = $ui_metabox->add_textfield($slug . '_kvm_id', 
                                          'Karte von morgen Id');
      $field->set_disabled(true);

      $field = $ui_metabox->add_checkbox($slug . '_kvm_do_not_upload', 
                                         'Nicht hochladen zu der Karte von morgen');
      $field->set_defaultvalue(false);

      $ui_metabox->register();
    }


    register_post_type( $slug, $args );
  }

}
