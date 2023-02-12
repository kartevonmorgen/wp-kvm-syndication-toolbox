<?php

class DuplicateTimeFrame
{
  public function duplicate_frontend($cb_timeframe_to_duplicate)
  {
    echo '<p>Zeitrahmen ' . $cb_timeframe_to_duplicate->post_title . 
      ' duplizieren für alle Artikel</p>'; 

    $item_id = get_post_meta($cb_timeframe_to_duplicate->ID, 'item-id', true);
    if(! empty($item_id))
    {
      echo '<p>Man kann nur Zeitrahmen duplizieren die noch kein Artikel haben</p>'; 
      return false;
    }

    $duplicate_location_id = get_post_meta($cb_timeframe_to_duplicate->ID, 'location-id', true);
    if(empty($duplicate_location_id))
    {
      echo '<p>Die Zeitrahmen zum duplizieren sollte ein Standort haben</p>'; 
      return false;
    }

    $duplicate_location = get_post($duplicate_location_id);
    echo '<p>für Standort: ' . $duplicate_location->post_title . '</p>'; 

    if(!$this->_delete_frontend( $cb_timeframe_to_duplicate ))
    {
      return false;
    }

    // Get all the Items and store them in the timeframe, so
    // the Cronjob Background process kann start creating
    // duplicates for every Item
    $cb_items = get_posts(array('post_type' => 'cb_item',
                                'post_status' => 'publish',
                                'numberposts'    => -1));
    $ids = null;
    foreach($cb_items as $cb_item)
    {
      if(!empty($ids))
      {
        $ids = $ids . ',';
      }
      $ids = $ids . $cb_item->ID;
    }
    
    echo '<p>Zeitrahmen für ' . $cb_timeframe_to_duplicate->post_title . 
        ' duplizieren lassen. IDS= ' . $ids . '</p>'; 

    $post = array(
      'ID' => $cb_timeframe_to_duplicate->ID,
      'post_content' => '<p>Zeitrahmen werden dupliziert ' . 
                        'für ' . count($cb_items) . ' (' . 
                        $cb_timeframe_to_duplicate->post_title . 
                        ')</p>',
      'post_excerpt' => $ids);

    wp_update_post($post);
    return true;
  }

  public function delete_frontend($cb_timeframe_to_duplicate)
  {
    echo '<p>Duplizierte Zeitrahmen ' . 
         $cb_timeframe_to_duplicate->post_title . 
         ' für alle Artikel entfernen</p>'; 

    $this->_delete_frontend( $cb_timeframe_to_duplicate );
  }
  
  public function _delete_frontend($cb_timeframe_to_duplicate)
  {
    if(!empty($cb_timeframe_to_duplicate->post_excerpt ) && 
       $cb_timeframe_to_duplicate->post_excerpt !== 'READY')
    {
      echo '<p>Für diese Zeitrahmen werden gerade Zeitrahmen dupliziert</p>'; 
      return false;
    }
    

    $cb_timeframes = get_posts(array(
      'post_type' => 'cb_timeframe',
      'post_parent' => $cb_timeframe_to_duplicate->ID,
      'post_status'    => 'any',
      'numberposts'    => -1));

    // First search for the already existing Timeframes
    // and delete them 
    foreach($cb_timeframes as $cb_timeframe)
    {
      $item_id = get_post_meta($cb_timeframe->ID, 'item-id', true);

      echo '<p>Entferne Zeitrahmen ' . $cb_timeframe->post_title . 
        ' für Artikel ' . $item_id .'</p>'; 
      wp_delete_post($cb_timeframe->ID, true);
    }
    return true;
  }

  public function duplicate_backend()
  {
    $cb_timeframes = get_posts(array(
      'post_type' => 'cb_timeframe',
      'post_status'    => 'draft',
      'numberposts'    => -1));

    $count = 5;
    foreach($cb_timeframes as $cb_timeframe)
    {
      if(empty($cb_timeframe->post_excerpt) || 
         $cb_timeframe->post_excerpt == 'READY')
      {
        continue;
      }

      if($count > 0)
      {
        $count = $this->duplicate_pending_timeframe($cb_timeframe, $count);
      }
    }
  }

  private function duplicate_pending_timeframe($cb_timeframe_to_duplicate, $count)
  {
    $postname = sanitize_title($cb_timeframe_to_duplicate->post_title);
    $content = $cb_timeframe_to_duplicate->post_content;

    $item_ids = explode( ',', $cb_timeframe_to_duplicate->post_excerpt );
    $new_item_ids = array();
    foreach($item_ids as $item_id)
    {
      if($count > 0)
      {
        $cb_item = get_post($item_id);

        $content .= '<p>Erstelle Zeitrahmen (' . $postname . 
                    ') für Artikel (ID=' . $item_id .
                    ') ' . $cb_item->post_title .
                    ' </p>'; 
        $new_post_id = $this->duplicate_post($cb_timeframe_to_duplicate, 
                                             $postname, $cb_item->ID);
        if(empty($new_post_id))
        {
          continue;
        }
        update_post_meta($new_post_id, 'item-id', $cb_item->ID);
        $count = $count - 1;
      }
      else
      {
        array_push($new_item_ids, $item_id);
      }
    }

    if(empty($new_item_ids))
    {
      $content .= '<p>Fertig: Alle Zeitrahmen sind erstellt worden</p>';
      $post = array(
        'ID' => $cb_timeframe_to_duplicate->ID,
        'post_content' => $content,
        'post_excerpt' => 'READY',
        'post_status' => 'draft'
        );
    }
    else
    {
      $content .= '<p>Noch ' . count($new_item_ids) . ' zu duplizieren</p>';
      $post = array(
        'ID' => $cb_timeframe_to_duplicate->ID,
        'post_content' => $content,
        'post_excerpt' => implode(',', $new_item_ids),
        'post_status' => 'draft'
        );
    }
    wp_update_post($post);
  }

  /**
   * returns new post id
   */
  private function duplicate_post($post, $postname, $uid)
  {
    global $wpdb;
    //$result = '';
 
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

    $post_id = $post->ID;

    /*
     * new post data array
     */
    $args = array(
      'comment_status' => $post->comment_status,
      'ping_status'    => $post->ping_status,
      'post_author'    => $post->post_author,
      'post_content'   => $post->post_content,
      'post_excerpt'   => $post->post_excerpt,
      'post_name'      => $postname . '_' . $uid,
      'post_parent'    => $post->ID,
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
    //$result .= '<p>Erstelle post ' . $post_id . ' (' . time() . ')</p>'; 

    $new_post_id = wp_insert_post( $args, false, false );
 
    //  $result .= '<p>Erstelle Metadata für post ' . $post_id . ' (' . time() . ')</p>'; 

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
    //$result .= '<p>Ende erstelle Metadata für post ' . $post_id . ' (' . time() . ')</p>'; 
    return $new_post_id;
  }
}
