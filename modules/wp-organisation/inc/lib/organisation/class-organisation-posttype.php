<?php

class OrganisationPosttype implements WPModuleStarterIF
{ 
  public function setup_actions($loader)
  {
  }

  public function start()
  {
    $this->create_post_type();
  }

  function create_post_type() 
  {
    $labels = array(
		  'name' => _x( 'Organisations', 'post type general name', 'organisation' ),
		  'singular_name'      => _x( 'Organisation', 
                                  'post type singular name', 
                                  'organisation' ),
      'menu_name'          => _x( 'Organisations', 'admin menu', 'organisation' ),
      'name_admin_bar'     => _x( 'Organisation', 'add new on admin bar', 'organisation' ),
      'add_new'            => _x( 'Erstellen', 'organisation', 'organisation' ),
      'add_new_item'       => __( 'Organisation erstellen', 'organisation' ),
      'new_item'           => __( 'Neue Organisation', 'organisation' ),
      'edit_item'          => __( 'Organisation bearbeiten', 'organisation' ),
      'view_item'          => __( 'Organisation Anschauen', 'organisation' ),
      'all_items'          => __( 'Alle Organisationn', 'organisation' ),
      'search_items'       => __( 'Organisationn Suche', 'organisation' ),
      'parent_item_colon'  => __( 'Parent Organisations:', 'organisation' ),
      'not_found'          => __( 'Keine Organisation gefunden.', 'organisation' ),
      'not_found_in_trash' => __( 'Keine Organisation gefunden im Papierkorb.', 
                                  'organisation' ));
    $args = array(
      'labels'             => $labels,
      'description'        => __( 'Description.', 'organisation' ),
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'organisation' ),
      //'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'show_in_rest'       => true,
      'taxonomies'         => array( 'category', 'post_tag' ),
      'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
      'map_meta_cap'       => true );

    $mc = WPModuleConfiguration::get_instance();
    $module = $mc->get_module('wp-organisation');
    if(!$module->is_multiple_organisation_pro_user_allowed())
    {
      $args['capabilities'] = array('create_posts' => false);
    }

    $ui_metabox = new UIMetabox('organisation_contact_metabox',
                                'Kontaktdaten',
                                'organisation');
    $field = $ui_metabox->add_dropdownfield('organisation_type', 
                                            'Organisationstype');
    $mc = WPModuleConfiguration::get_instance();
    $module = $mc->get_module('wp-organisation');
    foreach($module->get_organisation_types() as $type)
    {
      $field->add_value($type->get_id(), $type->get_name());
    }
    $field->set_defaultvalue(WPOrganisationType::INITIATIVE);

    $ui_metabox->add_textfield('organisation_address', 'Strasse und Nr.');
    $ui_metabox->add_textfield('organisation_zipcode', 'Postleitzahl');
    $ui_metabox->add_textfield('organisation_city', 'Ort');
    $field = $ui_metabox->add_textfield('organisation_lat', 'Latitude');
    $field->set_disabled(true);
    $field = $ui_metabox->add_textfield('organisation_lng', 'Longitude');
    $field->set_disabled(true);
    $ui_metabox->register();

    $ui_metabox = new UIMetabox('organisation_contactperson_metabox',
                                'Kontaktperson',
                                'organisation');
    $ui_metabox->add_textfield('organisation_firstname', 'Vorname');
    $ui_metabox->add_textfield('organisation_lastname', 'Nachname');
    $ui_metabox->add_textfield('organisation_phone', 'Telefon');
    $ui_metabox->add_textfield('organisation_email', 'Email');
    $ui_metabox->add_textfield('organisation_website', 'Webseite');
    $ui_metabox->register();

    // Karte von morgen Meldungen
    $ui_metabox = new UIMetabox('organisation_kvm_log_metabox',
                                'Karte von morgen Logging',
                                'organisation');
    $field = $ui_metabox->add_textarea('organisation_kvm_log', 
                                        'Meldung');

    // Field should be disabled, otherwise the UI
    // updates the field after the KVM has updated it.
    $field->set_disabled(true);
    $field = $ui_metabox->add_textfield('organisation_kvm_id', 'Karte von morgen Id');
    $field->set_disabled(true);
    $ui_metabox->register();

    register_post_type( 'organisation', $args );
  }

}
