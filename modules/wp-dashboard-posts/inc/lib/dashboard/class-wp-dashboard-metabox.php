<?php

class WPDashboardMetabox 
{
  private $_dpost;

  public function __construct($dpost)
  {
    $this->_dpost = $dpost;
  }

  public function get_dpost()
  {
    return $this->_dpost;
  }

  public function get_id()
  {
    $post = $this->get_dpost();
    if(empty($post))
    {
      return null;
    }
    return $post->post_name;
  }

  public function get_title()
  {
    $post = $this->get_dpost();
    if(empty($post))
    {
      return '';
    }
    return $post->post_title;
  }

  public function get_position()
  {
    $post = $this->get_dpost();
    if(empty($post))
    {
      return 'normal';
    }
    $position = get_post_meta($post->ID, 'dpost_position', true);
    if(empty($position))
    {
      return 'normal';
    }
    return $position;
  }

  public function setup($loader)
  {
    $loader->add_action('wp_dashboard_setup', $this, 'do_add');
  }

  public function do_add()
  {
    $id = $this->get_id();
    $title = $this->get_title();
    $position = $this->get_position();

    add_meta_box( $id, $title, array($this, 'content'), 'dashboard', $position);
  }
  
  function content()
  {
    $post = $this->get_dpost();
    if(empty($post))
    {
      echo 'haha';
    }
    echo $post->post_content;
  }
}
