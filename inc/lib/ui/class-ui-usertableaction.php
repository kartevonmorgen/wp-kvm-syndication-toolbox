<?php

class UIUserTableAction extends UITableAction
{
  private $_listener;

  public function __construct($id, $title, $menu_title = null)
  {
    parent::__construct($id, $title, $menu_title, 'Benutzer');
  }


  public function set_useraction_listener($listener)
  {
    $this->_listener = $listener;
  }

  public function get_useraction_listener()
  {
    return $this->_listener;
  }
  
  public function setup($loader)
  {
    $loader->add_filter('user_row_actions',
                        $this,
                        'setup_tableaction',
                        10,
                        2);
    $loader->add_action('admin_menu',
                        $this, 
                        'menu');
  }

  function setup_tableaction($actions, $user)
  {
    if(!$this->is_allowed($user->ID))
    {
      return $actions;
    }

    $id = $this->get_id();
    $title = $this->get_menu_title();

    $link = $this->get_link($user->ID);
    $actions[$id] = '<a href='. $link . '>' . $title . '</a>';
    return $actions;
  }

  public function get_link($user_id = null)
  {
    $id = $this->get_id();
    $url = 'users.php?page=' . $id;
    if(!empty($user_id))
    {
      $url .= '&amp;author=' . $user_id;
    }
    return admin_url($url); 
  }

  function menu()
  {
    $id = $this->get_id();
    $title = $this->get_menu_title();

    add_users_page($title, 
                   $title, 
                   'manage_options', 
                   $id, 
                   array($this, 'do_action'));

  }

  function do_action()
  {
    ?><h2><?php esc_html_e( $this->get_title() ); ?></h2><?php

    $user_id = $_GET['author'];
    if(empty($user_id))
    {
      $this->do_no_action();
      return;
    }

    $listener = $this->get_useraction_listener();
    if(!empty($listener))
    {
      $user_meta = get_userdata($user_id);
      $listener->action($user_id, $user_meta);
    }
  }

  private function do_no_action()
  {
    $args = array( 'fields' => 'ID' );

    if(!empty($this->get_enabled_roles()))
    {
      $args['role'] = $this->get_enabled_roles();
    }
    $user_ids = get_users( $args );

    if(empty($user_ids))
    {
      ?><p>Keine <?php $this->get_entity_title(); ?> gefunden</p><?php
      return;       
    }
    ?>
        <form action="<?php $this->get_link(); ?>" method="get">
          <p>
            <label><?php $this->get_entity_title(); ?></label>
            <?php wp_dropdown_users( array( 'name' => 'author', 
                                            'show' => 'display_name_with_login', 
                                            'include' => $user_ids) ); ?>
          </p>
          <p>
            <button type="submit" 
                    name="page" 
                    value="<?php echo $this->get_id() ?>">Bestätigen
            </button>
          </p>
        </form>
      <?php
  }
}
