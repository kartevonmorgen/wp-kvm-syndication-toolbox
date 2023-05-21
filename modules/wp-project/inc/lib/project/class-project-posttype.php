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


  protected function metabox1_addfields($ui_metabox)
  {
    $ui_metabox->add_field(
      new UIMetaboxFieldWithDefaultValue('project_address', 
                                         'Strasse und Nr.'));
    $ui_metabox->add_field(
      new UIMetaboxFieldWithDefaultValue('project_zipcode', 
                                         'Postleitzahl'));
    $ui_metabox->add_field(
      new UIMetaboxFieldWithDefaultValue('project_city', 
                                         'Ort'));
    $field = $ui_metabox->add_textfield('project_lat', 'Latitude');
    $field->set_disabled(true);
    $field = $ui_metabox->add_textfield('project_lng', 'Longitude');
    $field->set_disabled(true);
  }

  protected function metabox2_addfields($ui_metabox)
  {
    $ui_metabox->add_field(
      new UIMetaboxFieldWithDefaultValue('project_firstname', 'Vorname'));
    $ui_metabox->add_field(
      new UIMetaboxFieldWithDefaultValue('project_lastname', 'Nachname'));
    $ui_metabox->add_field(
      new UIMetaboxFieldWithDefaultValue('project_phone', 'Telefon'));
    $ui_metabox->add_field(
      new UIMetaboxFieldWithDefaultValue('project_email', 'Email'));
    $ui_metabox->add_field(
      new UIMetaboxFieldWithDefaultValue('project_website', 'Webseite'));
  }

  protected function before_metaboxes_added()
  {
    $ui_metabox = new UIMetabox('project_expirator_metabox',
                                'Ablaufdatum',
                                'project');
    $field = $ui_metabox->add_checkbox('project_expiration_enabled', 'Ablaufdatum aktiv');
    $field = $ui_metabox->add_datefield('project_expiration_date', 'Ablaufdatum');
    $ui_metabox->register();
  }
}

  class UIMetaboxFieldWithDefaultValue extends UIMetaboxField
  {
    public function get_defaultvalue($post)
    {
      $id = $this->get_id();
      $id = str_replace('project_', 'organisation_', $id);

      $args = array(
        'numberposts'   =>  1,
        'post_type'     =>  WPEntryType::ORGANISATION,
        'author'        =>  $post->post_author);
      $orgs = get_posts( $args );
      $org = reset($orgs);
      if(empty($org))
      {
        return null;
      }
      return get_post_meta( $org->ID, $id, true );
    }
  }
