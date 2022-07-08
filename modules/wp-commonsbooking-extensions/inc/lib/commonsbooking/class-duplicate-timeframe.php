<?php

class DuplicateTimeFrame
{
  public function duplicate($cb_timeframe_to_duplicate)
  {
    echo '<p>Zeitrahmen ' . $cb_timeframe_to_duplicate->post_title . 
      ' duplizieren für alle Artikel</p>'; 
    $item_id = get_post_meta($cb_timeframe_to_duplicate->ID, 'item-id', true);
    if(! empty($item_id))
    {
      echo '<p>Man kann nur Zeitrahmen duplizieren die noch kein Artikel haben</p>'; 
      return;
    }
    $duplicate_location_id = get_post_meta($cb_timeframe_to_duplicate->ID, 'location-id', true);
    if(empty($duplicate_location_id))
    {
      echo '<p>Die Zeitrahmen zum duplizieren sollte ein Standort haben</p>'; 
      return;
    }
    $duplicate_location = get_post($duplicate_location_id);
    echo '<p>für Standort: ' . $duplicate_location->post_title . '</p>'; 

    $cb_timeframes = get_posts(array('post_type' => 'cb_timeframe',
                                     'post_status'    => 'any'));

    // First search for the 
    foreach($cb_timeframes as $cb_timeframe)
    {
      $item_id = get_post_meta($cb_timeframe->ID, 'item-id', true);
      if(empty($item_id))
      {
        continue;
      }

      $location_id = get_post_meta($cb_timeframe->ID, 'location-id', true);
      if(empty($location_id))
      {
        continue;
      }

      if($location_id !== $duplicate_location_id)
      {
        continue;
      }

      echo '<p>Entferne Zeitrahmen ' . $cb_timeframe->post_title . 
        ' für Artikel ' . $item_id .'</p>'; 
      wp_delete_post($cb_timeframe->ID, true);
    }

    // Get all the Items and create timeFrames for it.
    $cb_items = get_posts(array('post_type' => 'cb_item'));
    foreach($cb_items as $cb_item)
    {
      echo '<p>Erstelle Zeitrahmen für Artikel ' . $cb_item->post_title .
        ' </p>'; 
      $new_post_id = $this->duplicate_post($cb_timeframe_to_duplicate);
      if(empty($new_post_id))
      {
        continue;
      }
      update_post_meta($new_post_id, 'item-id', $cb_item->ID);
    }
  }

  /**
   * returns new post id
   */
  private function duplicate_post($post)
  {
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;
 
    /*
     * if post data exists, create the post clone
     */
    if (!isset( $post )) 
    {
      return 0;
    }
    
    if($post == null) 
    {
      return 0;
    }

    global $wpdb;

    $post_id = $post->ID;

    /*
     * new post data array
     */
    $args = array(
      'comment_status' => $post->comment_status,
      'ping_status'    => $post->ping_status,
      'post_author'    => $new_post_author,
      'post_content'   => $post->post_content,
      'post_excerpt'   => $post->post_excerpt,
      'post_name'      => $post->post_name,
      'post_parent'    => $post->post_parent,
      'post_password'  => $post->post_password,
      'post_status'    => 'publish',
      'post_title'     => $post->post_title,
      'post_type'      => $post->post_type,
      'to_ping'        => $post->to_ping,
      'menu_order'     => $post->menu_order
    );
 
    /*
     * insert the post by wp_insert_post() function
     */
    $new_post_id = wp_insert_post( $args );
 
    /*
     * get all current post terms ad set them to the new post draft
     */
    $taxonomies = get_object_taxonomies($post->post_type); 
    // returns array of taxonomy names for post type, ex array("category", "post_tag");

    foreach ($taxonomies as $taxonomy) 
    {
      $post_terms = wp_get_object_terms($post_id, 
                                        $taxonomy, 
                                        array('fields' => 'slugs'));
      wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
    }

    /*
     * clone all post meta just in two SQL queries
     */
    $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
    if (count($post_meta_infos)!=0) 
    {
      $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
      foreach ($post_meta_infos as $meta_info) 
      {
        $meta_key = $meta_info->meta_key;
        if( $meta_key == '_wp_old_slug' ) 
        {
          continue;
        }
        $meta_value = addslashes($meta_info->meta_value);
        $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
      }
      $sql_query.= implode(" UNION ALL ", $sql_query_sel);
      $wpdb->query($sql_query);
    }
    return $new_post_id;
  }
}
