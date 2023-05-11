<?php

class OrganisationPosttype 
  extends EntryPosttype
{ 
  public function create_labels()
  {
    $labels = array(
		  'name' => _x( 'Organisations', 'post type general name', 'organisation' ),
		  'singular_name'      => _x( 'Organisation', 
                                  'post type singular name', 
                                  'organisation' ),
      'menu_name'          => _x( 'Organisationen', 'admin menu', 'organisation' ),
      'name_admin_bar'     => _x( 'Organisation', 'add new on admin bar', 'organisation' ),
      'add_new'            => _x( 'Erstellen', 'organisation', 'organisation' ),
      'add_new_item'       => __( 'Organisation erstellen', 'organisation' ),
      'new_item'           => __( 'Neue Organisation', 'organisation' ),
      'edit_item'          => __( 'Organisation bearbeiten', 'organisation' ),
      'view_item'          => __( 'Organisation Anschauen', 'organisation' ),
      'all_items'          => __( 'Alle Organisationen', 'organisation' ),
      'search_items'       => __( 'Organisationen Suche', 'organisation' ),
      'parent_item_colon'  => __( 'Parent Organisationen:', 'organisation' ),
      'not_found'          => __( 'Keine Organisationen gefunden.', 'organisation' ),
      'not_found_in_trash' => __( 'Keine Organisationen gefunden im Papierkorb.', 
                                  'organisation' ));
    return $labels;
  }

  protected function create_post_type_addons($args)
  {
    $args['capabilities'] = array('create_posts' => false);
    return $args;
  }

  protected function create_post_type_metabox1_addons($ui_metabox)
  {
    $field = $ui_metabox->add_dropdownfield('organisation_type', 
                                            'Organisationstype');
    $module = $this->get_current_module();
    foreach($module->get_entry_type_types() as $type)
    {
      $field->add_value($type->get_id(), $type->get_name());
    }
    $field->set_defaultvalue(WPEntryTypeType::INITIATIVE);
  }
}
