<?php

class UIPostTableAction extends UITableAction
{
  private $_posttype;
  private $_listener;

  public function __construct($id, 
                              $title, 
                              $menu_title = null,
                              $posttype, 
                              $entity_title = null) 
  {
    parent::__construct($id, $title, $menu_title, $entity_title);
    $this->_posttype = $posttype;
  }
  
  private function get_posttype()
  {
    return $this->_posttype;
  }

  public function set_postaction_listener($listener)
  {
    $this->_listener = $listener;
  }

  public function get_postaction_listener()
  {
    return $this->_listener;
  }

  public function setup($loader)
  {
    $loader->add_filter('post_row_actions',
                        $this,
                        'setup_tableaction',
                        10,
                        2);
    $loader->add_action('admin_menu',
                        $this, 
                        'menu');
  }

  function setup_tableaction($actions, $post)
  {
    if(!$this->is_allowed($post->post_author))
    {
      return $actions;
    }

    $id = $this->get_id();
    $title = $this->get_title();
    $posttype = $this->get_posttype();

    if($post->post_type === $posttype)
    {
       $url = $this->get_link($post->ID);
       $url .= '&amp;page=' . $this->get_id();
       $actions[$id] = '<a href="'. $url .
                       '" title="' . $title . 
                       '" rel="permalink">' . $title . '</a>';
    }
    return $actions;
  }

  public function get_link($post_id = null)
  {
    $id = $this->get_id();
    $posttype = $this->get_posttype();
    $parent_menu_php_file = $this->get_parent_menu_php_file();
    if(empty($post_id))
    {
      $url = $parent_menu_php_file . '?post_type=' . $posttype;
    }
    else
    {
      $url = $parent_menu_php_file . '?post_id=' . $post_id;
    }
    return admin_url($url); 
  }

  function menu()
  {
    $id = $this->get_id();
    $title = $this->get_title();
    $posttype = $this->get_posttype();
    $parent_menu_id = $this->get_parent_menu_id();
    $parent_menu_php_file = $this->get_parent_menu_php_file();
    if(empty($parent_menu_id))
    {
      $parent_menu_id = $parent_menu_php_file . '?post_type=' . $posttype;
    }

    add_submenu_page($parent_menu_id,
                     $title, $title,
                     'publish_posts', $id,
                     array($this, 'do_action'));

  }

  function do_action()
  {
    ?><h2><?php echo $this->get_title(); ?></h2><?php

    $post_id = null;
    if(array_key_exists('post_id', $_GET))
    {
      $post_id = $_GET['post_id'];
    }
    if( empty($post_id) )
    {
      $this->do_no_post_action();
      return;  
    }

    $post = get_post($post_id);
    ?><p>für <?php echo $this->get_entity_title(); ?>: <b><?php echo $post->post_title; ?></b></p><?php


    if(! $this->do_fields_action($post))
    {
      return;
    }

    $listener = $this->get_postaction_listener();
    if(!empty($listener))
    {
      $listener->action($post_id, $post);
    }
  }

  function do_no_post_action()
  {
    $posttype = $this->get_posttype();
    $posttype_title = $this->get_entity_title();
    $title = $this->get_title();
    $post_status = $this->get_post_status();
    $dropdown = new UIDropdownPosts(array('post_type' => $posttype,
                                          'post_status' => $post_status
                                          ));

    ?>
        <form method="get">
          <p>
            <label><?php echo $posttype_title; ?>:&nbsp;
            <!-- looks like this is not needed, because we have the ID for sure
             input type="hidden" name="post_type" value="<?php echo $posttype; ?>" /-->
            <?php $dropdown->wp_dropdown_posts( ); ?>
          </p>
          <p>
            <button type="submit" 
                    name="page"  
                    value="<?php echo $this->get_id(); ?>">Bestätigen
            </button>
          </p>
        </form>
      <?php
    return;
  }

  private function do_fields_action($post)
  {
    foreach($this->get_fields() as $field)
    {
      $field_value = $_GET[$field->get_id()];
      if(empty($field_value))
      {
        $this->do_fields_not_filled_action($post);
        return false;
      }
      $field->save_value($post->ID, $field_value);

    }
    return true;
  }

  private function do_fields_not_filled_action($post)
  {
    $posttype = $this->get_posttype();
    ?>
        <form method="get">
          <input type="hidden" name="post_type" value="<?php echo $posttype; ?>" />
          <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
      <?php
        foreach($this->get_fields() as $field)
        {
          $field->show_value($post);
        }
      ?>
          <p>
            <button type="submit" 
                    name="page"  
                    value="<?php echo $this->get_id(); ?>">Bestätigen
            </button>
          </p>
        </form>
      <?php

  }
}
