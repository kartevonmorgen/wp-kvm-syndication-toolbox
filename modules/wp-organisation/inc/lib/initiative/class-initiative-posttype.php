<?php

class InitiativePosttype implements WPModuleStarterIF
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
		  'name' => _x( 'Initiativen', 'post type general name', 'initiative' ),
		  'singular_name'      => _x( 'Initiative', 
                                  'post type singular name', 
                                  'initiative' ),
      'menu_name'          => _x( 'Initiativen', 'admin menu', 'initiative' ),
      'name_admin_bar'     => _x( 'Initiative', 'add new on admin bar', 'initiative' ),
      'add_new'            => _x( 'Erstellen', 'initiative', 'initiative' ),
      'add_new_item'       => __( 'Initiative erstellen', 'initiative' ),
      'new_item'           => __( 'Neue Initiative', 'initiative' ),
      'edit_item'          => __( 'Initiative bearbeiten', 'initiative' ),
      'view_item'          => __( 'Initiative Anschauen', 'initiative' ),
      'all_items'          => __( 'Alle Initiativen', 'initiative' ),
      'search_items'       => __( 'Initiativen Suche', 'initiative' ),
      'parent_item_colon'  => __( 'Parent Initiativen:', 'initiative' ),
      'not_found'          => __( 'Keine initiatives gefunden.', 'initiative' ),
      'not_found_in_trash' => __( 'Keine initiatives gefunden im Papierkorb.', 
                                  'initiative' ));
    $args = array(
      'labels'             => $labels,
      'description'        => __( 'Description.', 'initiative' ),
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'initiative' ),
      //'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'show_in_rest'       => true,
      'taxonomies'         => array( 'category', 'post_tag' ),
      'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
      'map_meta_cap'       => true );

    $args['capabilities'] = array('create_posts' => false);

    // Karte von morgen Meldungen
    $ui_metabox = new UIMetabox('initiative_kvm_log_metabox',
                                'Karte von morgen Logging',
                                'initiative');
    $field = $ui_metabox->add_textarea('initiative_kvm_log', 
                                        'Meldung');

    // Field should be disabled, otherwise the UI
    // updates the field after the KVM has updated it.
    $field->set_disabled(true);

    register_post_type( 'initiative', $args );
  }

}
