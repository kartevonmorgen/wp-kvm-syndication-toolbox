<?php

class WPRegisterNewsletterListPosttype 
      extends WPAbstractModuleProvider
      implements WPModuleStarterIF
{

  public function setup($loader)
  {
    if(is_admin())
    {
      $loader->add_action('admin_menu', 
                          $this, 
                          'modify_admin_menu',
                          11);

      $loader->add_action( 'save_post_newsletterlist', 
                           $this, 
                           'save', 
                           12, 
                           3 );
    }
  }

  public function start()
  {
    $module = $this->get_current_module();
    if($module->has_newsletter_list())
    {
      $this->create_post_type();
    }
  }
 
  function create_post_type()
  {
    $labels = array(
      'name'               => 'Newsletter lists',
      'singular_name'      => 'Newsletter list',
      'menu_name'          => 'Newsletter lists',
      'name_admin_bar'     => 'Newsletter list',
      'add_new'            => 'New',
      'add_new_item'       => 'New list',
      'new_item'           => 'New Newsletter list',
      'edit_item'          => 'Edit Newsletter list',
      'view_item'          => 'View Newsletter list',
      'all_items'          => 'All Newsletter lists',
      'search_items'       => 'Search Newsletter lists',
      'parent_item_colon'  => 'Parent Newsletter list',
      'not_found'          => 'No Newsletter lists found',
      'not_found_in_trash' => 'No Newsletter lists found in trash'
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
      'rewrite'             => array( 'slug' => 'newsletterlists' ),
      'query_var'           => false);
    $ui_metabox = new UIMetabox('newsletterlist_metabox',
                                'Form properties',
                                'newsletterlist');
    $field = $ui_metabox->add_textfield('newsletterlist_position', 'Position on form'); 
    $ui_metabox->register();

    register_post_type( 'newsletterlist', $args );  
  }

  function modify_admin_menu()
  {
    $root = $this->get_root_module();
    add_submenu_page($root->get_id() . '-menu', 
                     'Newsletter lists', 
                     'Newsletter lists',
                     'manage_options', 
                     admin_url('/edit.php?post_type=newsletterlist') );
  }

  function save($newsletterlist_post_id, 
                $newsletterlist_post, 
                $update = false) 
  {
    $module = $this->get_current_module();
    if(!$module->has_newsletter_list())
    {
      return;
    }

    $adapter = $module->get_current_newsletter_adapter();
    if(empty($adapter))
    {
      return;
    }

    $adapter->update_newsletter_list($newsletterlist_post);
  }
}

