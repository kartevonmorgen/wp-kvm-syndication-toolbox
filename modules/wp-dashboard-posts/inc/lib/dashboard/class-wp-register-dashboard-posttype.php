<?php

class WPRegisterDashboardPosttype implements WPModuleStarterIF
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
      'name'               => 'Dashboard items',
      'singular_name'      => 'Dashboard item',
      'menu_name'          => 'Dashboard items',
      'name_admin_bar'     => 'Dashboard item',
      'add_new'            => 'New',
      'add_new_item'       => 'New item',
      'new_item'           => 'Neue Dashboard item',
      'edit_item'          => 'Edit Dashboard item',
      'view_item'          => 'View Dashboard item',
      'all_items'          => 'All Dashboard items',
      'search_items'       => 'Search Dashboard items',
      'parent_item_colon'  => 'Parent Dashboard item',
      'not_found'          => 'No Dashboard items found',
      'not_found_in_trash' => 'No Dashboard items found in trash'
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
      'rewrite'             => array( 'slug' => 'dposts' ),
      'query_var'           => false);

    $ui_metabox = new UIMetabox('dpost_metabox',
                                'Position on Dashboard',
                                'dpost');
    $field = $ui_metabox->add_dropdownfield('dpost_position', 'Position'); 
    $field->add_value('normal', 'Normal');
    $field->add_value('side', 'Right Side');
    $ui_metabox->register();

    register_post_type( 'dpost', $args );  
  }

  function modify_admin_menu()
  {
    $mc = WPModuleConfiguration::get_instance();
    $root = $mc->get_root_module();
    add_submenu_page($root->get_id() . '-menu', 
                     'Dashboard items', 
                     'Dashboard items',
                     'manage_options', 
                     admin_url('/edit.php?post_type=dpost') );
  }

}

