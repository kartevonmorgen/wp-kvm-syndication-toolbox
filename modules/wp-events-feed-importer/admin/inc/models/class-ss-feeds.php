<?php

class SSFeeds extends WPAbstractModuleProvider
              implements WPModuleStarterIF
{ 
  public function setup($loader)
  {  
    $loader->add_action('manage_ssfeed_posts_columns',
                        $this,
                        'columns',
                        10,
                        2);

    $loader->add_action('manage_ssfeed_posts_custom_column',
                        $this,
                        'column_data',
                        11,
                        2);
    
    $loader->add_filter('posts_join', 
                        $this, 
                        'join',
                        10,
                        1);
    $loader->add_filter('posts_orderby',
                        $this, 
                        'set_default_sort',
                        20,
                        2);

    $tableAction = new UIPostTableAction('update_feed', 
                                         'Update event feed', 
                                         'Update feed', 
                                         'ssfeed',
                                         'Event feed');
    $tableAction->set_postaction_listener(
      new SSUpdateFeed($this->get_current_module()) );
    $tableAction->setup($loader);
  }
  
  public function start()
  {
    $this->create_post_type();
  }

  function create_post_type() 
  {
    $labels = array(
      'name'               => 'Import event feeds',
      'singular_name'      => 'Import event feed',
      'menu_name'          => 'Import event feeds',
      'name_admin_bar'     => 'Import event feed',
      'add_new'            => 'Neu hinzufügen',
      'add_new_item'       => 'Feed neu hinzufügen',
      'new_item'           => 'New feed',
      'edit_item'          => 'Edit feed',
      'view_item'          => 'View feed',
      'all_items'          => 'Alle feeds',
      'search_items'       => 'Search feeds',
      'parent_item_colon'  => 'Parent feed',
      'not_found'          => 'No feeds found',
      'not_found_in_trash' => 'No feeds found in trash'
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
      'supports'            => array( 'title', 
                                      'author'),
      'has_archive'         => false,
      'rewrite'             => array( 'slug' => 'feeds' ),
      'query_var'           => false);

    // Feed Details
    $ui_metabox = new UIMetabox('ssfeed_metabox',
                                'Feed details',
                                'ssfeed');
    $field = $ui_metabox->add_textfield('ss_feedurl', 
                                        'Feed URL');
//    $field->set_escape_html(false);
    $field = $ui_metabox->add_dropdownfield(
                            'ss_feedurltype',
                            'Feed URL type');

    $factory = $this->get_current_module()->get_importer_factory();
    foreach($factory->get_importtypes()
            as $importtype)
    {
       $field->add_value(
         $importtype->get_id(),
         $importtype->get_name());
    }
    $field = $ui_metabox->add_checkbox('ss_feedupdatedaily', 
                                       'Update daily');
    $field->set_description('Update the feed automatically every day, ' .
                            'for new and changed events '); 

    $field = $ui_metabox->add_checkbox('ss_disable_linkurl_valid_check', 
                              'Disable linkurl check');
    $field->set_description('Sometimes the url of the feed does not match ' . 
                            'with the url of the events that are imported ' .
                            'by this feed, if this behaviour is wanted, ' .
                            'then disable the linkurl check');
    
    $field = $ui_metabox->add_checkbox('ss_define_location_by_geo', 
                                       'Define the address by GEO coordinates from the feed');
    $field->set_description('Define the address (street, ZIP, city, ' .
                            'country) by GEO Coordinates from the feed, ' . 
                            'even if the address is already provided in the feed');
    $field->set_defaultvalue(true);
    
    $field = $ui_metabox->add_dropdownfield(
                            'ss_feed_wplocation_freetextformat_type',
                            'Feed location free text format type');
    $mc = WPModuleConfiguration::get_instance();
    $root = $mc->get_root_module();
    foreach($root->get_wplocation_freetextformat_types()
            as $type)
    {
      $field->add_value(
         $type->get_id(),
         $type->get_name());
    }
    foreach($root->get_wplocation_freetextformat_types()
            as $type)
    {
      if($type->is_default())
      {
        $field->set_defaultvalue($type->get_id());
      }
    }

    $field = $ui_metabox->add_textfield('ss_feed_filtered_tags', 
                                        'Filtered tags');
    $field->set_description('Only events with one of these tags will be imported ' .
                            'You can give a comma seperated list for multiple tags');

    $field = $ui_metabox->add_textfield('ss_feed_filtered_categories', 
                                        'Filtered categories');
    $field->set_description('Only events with one of these categories will be imported ' .
                            'You can give a comma seperated list for multiple categories (slugs)');
    $field = $ui_metabox->add_textfield('ss_feed_include_tags', 
                                        'Include tags');
    $field->set_description('Include this tags for every event ' .
                            'that are imported by this Feed. ' .
                            'You can give a comma seperated ' . 
                            'list for multiple tags');

    $field = $ui_metabox->add_checkbox('ss_feed_extended_graphql', 
                                       'Only GraphQL: Will the GraphQL Query be extended ');
    $field->set_description('The GraphQL Query will be extended with "excategories" ' .
                            'this will NOT work for Queries to Mobilizon ');

    $field = $ui_metabox->add_textfield('ss_feed_filtered_mobgroups', 
                                        'Only GraphQL: Filter mobilizon groups');
    $field->set_description('Only events from one of these groups ' . 
                            'will be imported ' .
                            'You can give a comma seperated list ' .
                            'for multiple groups');


    $field = $ui_metabox->add_checkbox('ss_feed_include_mobgroup', 
                                       'Only GraphQL: Convert group to tag');
    $field->set_description('The Mobilizon group will be added as a tag to ' .
                            'the event ');


    $ui_metabox->register();

    // Feed Info's
    $ui_metabox = new UIMetabox('ssfeed_metabox_info',
                                'Feed informationen',
                                'ssfeed');
    $field = $ui_metabox->add_textfield('ss_feed_title', 
                                        'Feed title');
    $field->set_disabled(true);
    $field = $ui_metabox->add_textfield('ss_feed_uuid', 
                                        'Feed UUID');
    $field->set_disabled(true);
    $field = $ui_metabox->add_textfield('ss_feed_lastupdate', 
                                        'Letzte update');
    $field->set_disabled(true);
    $field = $ui_metabox->add_textfield('ss_feed_eventids', 
                                        'Event ids');
    $field->set_disabled(true);
    $field = $ui_metabox->add_textarea('ss_feed_updatelog', 
                                        'Update log');
    $field->set_disabled(true);

    $ui_metabox->register();

    register_post_type( 'ssfeed', $args );  
  }

  function columns($columns) 
  {
    unset($columns['date']);
    return array_merge(
      $columns,
      array(
        'ss_feed_lastupdate' => 'Letzte update',
        'ss_feedurl' => 'Feed URL',
        'ss_feedurltype' => 'Feed type',
        'ss_feedupdatedaily' => 'Update daily'
      ));
  }

  function column_data($column,$post_id) 
  {
    switch($column) 
    {
      case 'ss_feed_lastupdate' :
        echo get_post_meta($post_id,'ss_feed_lastupdate',1);
        break;
      case 'ss_feedurl' :
        echo get_post_meta($post_id,'ss_feedurl',1);
        break;
      case 'ss_feedurltype' :
        echo get_post_meta($post_id,'ss_feedurltype',1);
        break;
      case 'ss_feedupdatedaily' :
        $value = get_post_meta($post_id,
                               'ss_feedupdatedaily',1);
        if((bool)$value)
        {
          echo 'JA';
        }
        else
        {
          echo 'NEIN';
        }
        break;
     }
  }

  function join($wp_join) 
  {
    return $wp_join;
  }

  function set_default_sort($orderby,$query) 
  {
    return $orderby;
  }

	public function update_feeds_daily()
	{
    $cron_message = '';
    $cron_message .= 'Start update_feeds_daily ' . get_date_from_gmt(date("Y-m-d H:i:s"));
    $cron_message .= PHP_EOL;
    update_option('ss_cron_message', $cron_message );

    $args = array( 'post_type' => 'ssfeed',
                   'numberposts' => -1);
    $feeds = get_posts($args);

    $instance = $this->get_current_module()->get_importer_factory();
		if ( empty( $feeds ))
		{
      $cron_message .= 'No feeds found';
      $cron_message .= PHP_EOL;
      update_option('ss_cron_message', $cron_message );
      return;
    }

    foreach ( $feeds as $feed )
    {
      if ( empty( $feed ))
			{
        $cron_message .= 'Feed is empty';
        $cron_message .= PHP_EOL;
        update_option('ss_cron_message', $cron_message );
        continue;
      }

      $feed_id = $feed->ID;

      $feed_updatedaily = get_post_meta($feed_id,
                                        'ss_feedupdatedaily',
                                        1);
      $feed_url = get_post_meta($feed_id,'ss_feedurl',1);
      $feed_type = get_post_meta($feed_id,'ss_feedurltype',1);
      $feed_user = $feed->post_author;

      $cron_message .= 'Update feed ' . get_date_from_gmt( date("Y-m-d H:i:s"));
      $cron_message .= PHP_EOL;
      $cron_message .= 'Feed (' . $feed_type . '): ' . 
                                  $feed_url; 
      $cron_message .= PHP_EOL;
      $cron_message .= 'Feedowner: ' . $feed_user;
      $cron_message .= PHP_EOL;

      if ( ! ((bool) $feed_updatedaily) )
			{
        $cron_message .= 'Feed update daily is OFF';
        $cron_message .= PHP_EOL;
        update_option('ss_cron_message', $cron_message );
        continue;
      }

      if ( empty($feed_url) || empty($feed_type) )
			{
        $cron_message .= 'Feed URL or Type is empty';
        $cron_message .= PHP_EOL;
        update_option('ss_cron_message', $cron_message );
        continue;
      }

      $importer = $instance->create_importer($feed);
      if(empty($importer))
      {
        $cron_message .= 'Importer could not be created';
        $cron_message .= PHP_EOL;
        update_option('ss_cron_message', $cron_message );
        continue;
      }

      wp_set_current_user($feed_user);
      $importer->import();
      if( $importer->has_error() )
      {
        $cron_message .= $importer->get_error();
        $cron_message .= PHP_EOL;
      }
      else
      {
        $cron_message .= 'Import done sucessfully';
        $cron_message .= PHP_EOL;
      }
      wp_set_current_user(0);
      update_option('ss_cron_message', $cron_message );
		}
    $cron_message .= 'Cronjob finished';
    $cron_message .= PHP_EOL;
    update_option('ss_cron_message', $cron_message );
	}
}
