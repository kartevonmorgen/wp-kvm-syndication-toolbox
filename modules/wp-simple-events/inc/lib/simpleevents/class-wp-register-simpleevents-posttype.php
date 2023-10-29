<?php

class WPRegisterSimpleEventsPosttype
      extends WPAbstractModuleProvider
      implements WPModuleStarterIF
{

  public function setup($loader)
  {
  }

  public function start()
  {
     $this->create_post_type();
  }
 
  function create_post_type()
  {
    $module = $this->get_current_module();
    $posttype = $module->get_posttype();
    $postname = $module->get_postname();
    $postname_p = $module->get_postname() . 's';
    $labels = array(
      'name'               => $postname_p,
      'singular_name'      => $postname,
      'menu_name'          => $postname_p,
      'name_admin_bar'     => $postname,
      'add_new'            => 'New',
      'add_new_item'       => 'New event',
      'new_item'           => 'New simple event',
      'edit_item'          => 'Edit simple event',
      'view_item'          => 'View simple event',
      'all_items'          => 'All simple events',
      'search_items'       => 'Search simple events',
      'parent_item_colon'  => 'Parent simple event',
      'not_found'          => 'No simple events found',
      'not_found_in_trash' => 'No simple events found in trash'
      );

    $args = array(
      'labels'              => $labels,
      'public'              => true,
      'exclude_from_search' => true,
      'publicly_queryable'  => false,
      'show_ui'             => true,
      'show_in_nav_menus'   => true,
      'show_in_menu'        => true,
      'show_in_admin_bar'   => true,
      'menu_icon'           => 'dashicons-admin-appearance',
      'hierarchical'        => false,
      'show_in_rest'       => false,
      'supports'            => array( 'title', 'editor', 'author'),
      'has_archive'         => false,
      'rewrite'             => array( 'slug' => $posttype . 's' ),
      'query_var'           => false);

    $ui_metabox = new UIMetabox($posttype . '_dates_metabox',
                                'Dates',
                                $posttype);
    $ui_metabox->set_prefix($posttype);
    $field = $ui_metabox->add_datefield('_start_date', 'Start date'); 
    $field = $ui_metabox->add_datefield('_end_date', 'End date'); 
    $field = $ui_metabox->add_checkbox('_allday', 'All Day'); 
    $ui_metabox->register();

    $ui_metabox = new UIMetabox($posttype . '_contact_metabox',
                                'Contact',
                                $posttype);
    $ui_metabox->set_prefix($posttype);
    $field = $ui_metabox->add_textfield('_contact_name', 'Name'); 
    $field = $ui_metabox->add_textfield('_contact_website', 'Website'); 
    $field = $ui_metabox->add_textfield('_contact_email', 'Email'); 
    $field = $ui_metabox->add_textfield('_contact_phone', 'Phone'); 
    $ui_metabox->register();

    $ui_metabox = new UIMetabox($posttype . '_location_metabox',
                                'Location',
                                $posttype);
    $ui_metabox->set_prefix($posttype);
    $field = $ui_metabox->add_textfield('_location_name', 'Name'); 
    $field = $ui_metabox->add_textfield('_location_address', 'Address'); 
    $field = $ui_metabox->add_textfield('_location_zip', 'Zip'); 
    $field = $ui_metabox->add_textfield('_location_city', 'City'); 
    $field = $ui_metabox->add_textfield('_location_country', 'Country'); 
    $field = $ui_metabox->add_textfield('_location_lon', 'Longitude'); 
    $field = $ui_metabox->add_textfield('_location_lat', 'Latitude'); 
    $ui_metabox->register();

    register_post_type( $posttype, $args );  
  }
}

