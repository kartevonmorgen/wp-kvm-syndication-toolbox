<?php

class WPSimpleEventsQueryHelper
      extends WPAbstractModuleProvider
{

  public function get_events($start_date, $end_date, $cat)
  {
    $module = $this->get_current_module();
    $posttype = $module->get_posttype();

    $args = array(
      'post_type' => $posttype,
      'meta_query' => array(
      'relation' => 'AND',  
        array(
          'key' => $posttype . '_start_date',
          'value' => $start_date,
          'compare' => '>='
        ),
        array(
          'key' => $posttype . '_start_date',
          'value' => $end_date,
          'compare' => '<'
        ),
       ),
    );

    return get_posts($args);
  }

  public function get_event_by_slug($slug)
  {
    $module = $this->get_current_module();
    $posttype = $module->get_posttype();

    $args = array(
      'name'        => $slug,
      'post_type'   => $posttype,
      'post_status' => array('draft', 'pending', 'publish'),
      'numberposts' => 1);
    $posts = get_posts($args);
    if( empty( $posts )) 
    {
      return null;
    }
    return reset($posts);
  }
}

