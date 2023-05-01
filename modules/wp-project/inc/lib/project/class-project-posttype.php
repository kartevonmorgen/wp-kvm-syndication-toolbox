<?php

class ProjectPosttype 
  extends EntryPosttype
{ 
  public function create_labels()
  {
    $labels = array(
      'name'               => _x( 'Projekte', 
                                  'post type general name', 
                                  'project' ),
		  'singular_name'      => _x( 'Projekt', 
                                  'post type singular name', 
                                  'project' ),
      'menu_name'          => _x( 'Projekte', 'admin menu', 'project' ),
      'name_admin_bar'     => _x( 'Projekt', 
                                  'add new on admin bar', 
                                  'project' ),
      'add_new'            => _x( 'Erstellen', 'projekt', 'project' ),
      'add_new_item'       => __( 'Projekt erstellen', 'project' ),
      'new_item'           => __( 'Neues Projekt', 'project' ),
      'edit_item'          => __( 'Projekt bearbeiten', 'project' ),
      'view_item'          => __( 'Projekt Anschauen', 'project' ),
      'all_items'          => __( 'Alle Projekte', 'project' ),
      'search_items'       => __( 'Projekte Suche', 'project' ),
      'parent_item_colon'  => __( 'Parent Projekte:', 'project' ),
      'not_found'          => __( 'Keine Projekte gefunden.', 'project' ),
      'not_found_in_trash' => __( 'Keine Projekte gefunden im Papierkorb.', 
                                  'project' ));
    return $labels;
  }


  protected function create_post_type_metabox1_addons($ui_metabox)
  {
  }
}
