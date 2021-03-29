<?php

class WPRegisterUserRegisterPosttype implements WPModuleStarterIF
{

  public function setup($loader)
  {
    if(is_admin())
    {
      $loader->add_action('admin_menu', $this, 'modify_admin_menu',11);
    }
  }

  public function start()
  {
     $this->create_post_type();
  }
 
  function create_post_type()
  {
    $labels = array(
      'name'               => 'User register items',
      'singular_name'      => 'User register item',
      'menu_name'          => 'User register items',
      'name_admin_bar'     => 'User register item',
      'add_new'            => 'New',
      'add_new_item'       => 'New Item',
      'new_item'           => 'New User register item',
      'edit_item'          => 'Edit User register item',
      'view_item'          => 'View User register item',
      'all_items'          => 'All User register items',
      'search_items'       => 'Search User register items',
      'parent_item_colon'  => 'Parent User register item',
      'not_found'          => 'No User register items Found',
      'not_found_in_trash' => 'No User register items Found in Trash'
      );

    $args = array(
      'labels'              => $labels,
      'public'              => true,
      'exclude_from_search' => true,
      'publicly_queryable'  => false,
      'show_ui'             => true,
      'show_in_nav_menus'   => true,
      'show_in_menu'        => false,
      'show_in_admin_bar'   => true,
      'menu_icon'           => 'dashicons-admin-appearance',
      'hierarchical'        => false,
      'show_in_rest'       => false,
      'supports'            => array( 'title', 'editor', 'author'),
      'has_archive'         => false,
      'rewrite'             => array( 'slug' => 'urpost' ),
      'query_var'           => false);

    $ui_metabox = new UIMetabox('urpost_metabox',
                                'Form properties',
                                'urpost');
    $model = new InUserModel();
    $model->init();

    $field = $ui_metabox->add_dropdownfield('urpost_fieldid', 'Fields from model'); 
    $field->add_value('none', 'Nicht zugewiesen an ein Feld, nur beschreibend');
    foreach($model->get_modeladapters() as $ma)
    {
      $field->add_value($ma->get_id(), $ma->get_title());
    }
    $field->set_defaultvalue('none');

    $field = $ui_metabox->add_dropdownfield('urpost_typeid', 'Type'); 
    $field->add_value('text', 'Description');
    $field->add_value('field', 'Field');
    $field->set_defaultvalue('field');
    $field = $ui_metabox->add_textfield('urpost_position', 'Position on form'); 

    $field = $ui_metabox->add_dropdownfield('urpost_bgcolorid', 'Backgroundcolor'); 
    foreach($model->get_bgcolors() as $color)
    {
      $field->add_value($color->get_id(), $color->get_name());
    }

    $ui_metabox->register();

    register_post_type( 'urpost', $args );  
  }

  function modify_admin_menu()
  {
    $mc = WPModuleConfiguration::get_instance();
    $root = $mc->get_root_module();
    add_submenu_page($root->get_id() . '-menu', 
                     'User register items', 
                     'User register items',
                     'manage_options', 
                     admin_url('/edit.php?post_type=urpost') );
  }

}
