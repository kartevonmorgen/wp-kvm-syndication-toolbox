<?php

class EntrySearchBehaviour
  extends WPAbstractModuleProvider
{
  public function get_type()
  {
    return $this->get_current_module()->get_type();
  }

  public function setup($loader)
  {
    $loader->add_filter('pre_get_posts', $this, 'query_post_type');
    $loader->add_filter('pre_get_posts', $this, 'posts_for_current_author');
    $loader->add_filter('pre_get_posts', $this, 'entry_type');

    $loader->add_action('pre_get_posts', $this, 'users_own_attachments');
  }

  /**
   * Make Tags and Categorie query's with custom-post-types
   * So Custom Post Types as Posts are shown for the catebories.
   */
  function query_post_type($query) 
  {
    if( is_category() || is_tag()) 
    {
      $post_type = get_query_var('post_type');
      if($post_type)
      {
        $post_type = $post_type;
      }
      else
      {
        // TODO: Add 'project' post_type if the Module 'Projekte'
        //       is enabled
        // don't forget nav_menu_item to allow menus to work!
        $post_type = array('nav_menu_item', 'post', 'organisation'); 
      }
      $query->set('post_type',$post_type);
    }
    return $query;
  }

  /**
   * Do not show Posts of other Authors in Admin
   */
  function posts_for_current_author($query) 
  {
    global $pagenow;
 
    if( 'edit.php' != $pagenow )
    {
      return $query;
    }

    // Wir sind nicht im Admin Bereich
    if( !$query->is_admin )
    {
      return $query;
    }

    // If the user can edit someone else its posts, 
    // we want to show them 
    if( current_user_can( 'edit_others_posts' ) ) 
    {
      return $query;
    }
  
    // If the user can not edit other posts,
    // we do not want to show them, so we 
    // only get the posts of the current user
    global $user_ID;
    $query->set('author', $user_ID );
    return $query;
  }

  function users_own_attachments( $wp_query_obj ) 
  {
    global $current_user, $pagenow;

    if( !is_a( $current_user, 'WP_User') )
    {
      return;
    }

    if( ( 'upload.php' != $pagenow ) &&
        (( 'admin-ajax.php' != $pagenow ) || 
        ( $_REQUEST['action'] != 'query-attachments' ) ) )
    {
      return;
    }
  
    if( !current_user_can('delete_pages') )
    {
      $wp_query_obj->set('author', $current_user->id );
    }
  }



 /**
  * Search for Entries by Type (Company or Organisation)
  */
  function entry_type($query) 
  {
    // Wir sind nicht im Admin Bereich
    if( $query->is_admin )
    {
      return $query;
    }

    if(!$query->is_main_query())
    {
      return $query;
    }

    if( ! $query->is_post_type_archive(array($this->get_type()->get_id())))
    {
      return $query;
    }

    if(array_key_exists('first', $_GET))
    {
      $is_first = $_GET['first'];
    }

    if(isset($is_first))
    {
      $_POST['search_company'] = 1;
      $_POST['search_initiative'] = 1;
    }

    if(array_key_exists('search_company', $_POST))
    {
      $search_company = $_POST['search_company'];
    }
    if(array_key_exists('search_initiative', $_POST))
    {
      $search_initiative = $_POST['search_initiative'];
    }
    if(array_key_exists('search_term', $_POST))
    {
      $search_term = $_POST['search_term'];
    }

    if( isset($search_term))
    {
      $query->set('s', $search_term);
    }

    if( isset( $search_company) &&
        isset( $search_initiative) )
    {
      return $query;
    }

    if( isset( $search_company))
    {
      $query->set('meta_key', 'entry_type'); 
      $query->set('meta_value', WPEntryType::COMPANY); 
    }
    else if( isset($search_initiative))
    {
      $query->set('meta_key', 'entry_type'); 
      $query->set('meta_value', WPEntryType::INITIATIVE); 
      // And Not exist
    }
    else
    {
      $query->set('meta_key', 'entry_type'); 
      $query->set('meta_value', 'nothing to find'); 
    }

    return $query;
  }
}

?>
