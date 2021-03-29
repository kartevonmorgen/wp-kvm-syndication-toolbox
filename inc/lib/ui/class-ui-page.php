<?php

/** 
 * UIPage
 * This Class is used to add a Page to the Wordpress Menu
 * Standard it is added direct into the menu.
 * It is also possible to define it as as submenupage.
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class UIPage
{
  private $_title;
  private $_menutitle;
  private $_submenutitle;
  private $_id;

  private $_is_submenu_page = false;
  private $_parentpage_slug;
  private $_is_options_page = false;

  public function __construct($id, $title)
  {
    $this->_id = $id;
    $this->_title = $title;
  }

  public function get_capability()
  {
    return 'manage_options';;
  }

  public function register()
  {
    if(is_admin())
    {
      add_action( 'admin_init', array( $this, 'admin_init' ) );
      add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }
  }

  abstract function admin_init();

  function admin_menu()
  {
    if( $this->is_options_page() )
    {
      add_options_page( $this->get_title(),
                        $this->get_menutitle(),
                        $this->get_capability(),
                        $this->get_menuid(),
                        array ($this, 'show_page') );
      return;
    }

    if( $this->is_submenu_page() )
    {
      add_submenu_page( $this->get_parentpage_slug(),
                        $this->get_title(),
                        $this->get_menutitle(),
                        $this->get_capability(),
                        $this->get_menuid(), 
                        array( $this, 'show_page' ));
      return;
    }

    if($this->get_menutitle() !== $this->get_submenutitle())
    {
      add_menu_page( $this->get_title(),
                     $this->get_menutitle(),
                     $this->get_capability(),
                     $this->get_menuid(),
                     false); 
      add_submenu_page( $this->get_menuid(),
                        $this->get_title(),
                        $this->get_submenutitle(),
                        $this->get_capability(),
                        $this->get_menuid(),
                        array( $this, 'show_page' ));

      return;
    }

    add_menu_page( $this->get_title(),
                   $this->get_menutitle(),
                   $this->get_capability(),
                   $this->get_menuid(), 
                   array( $this, 'show_page' ));

  }

  abstract function show_page();

  public function set_submenu_page($is_submenu_page, $parentpage_slug)
  {
    $this->_is_submenu_page = $is_submenu_page;
    $this->_parentpage_slug = $parentpage_slug;
  }

  public function is_submenu_page()
  {
    return $this->_is_submenu_page;
  }

  public function get_parentpage_slug()
  {
    return $this->_parentpage_slug;
  }

  public function set_options_page($is_options_page)
  {
    $this->_is_options_page = $is_options_page;
  }

  public function is_options_page()
  {
    return $this->_is_options_page;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_menuid()
  {
    return $this->get_id() . '-menu';
  }

  public function get_groupid()
  {
    return $this->get_id() . '-group';
  }

  public function get_title()
  {
    return $this->_title;
  }

  public function set_menutitle($menutitle)
  {
    $this->_menutitle = $menutitle;
  }

  public function get_menutitle()
  {
    if(empty($this->_menutitle))
    {
      return $this->get_title();
    }

    if($this->is_submenu_page())
    {
      if(!empty($this->get_submenutitle()))
      {
        return $this->get_submenutitle();
      }
    }
    return $this->_menutitle;
  }

  public function set_submenutitle($submenutitle)
  {
    $this->_submenutitle = $submenutitle;
  }

  public function get_submenutitle()
  {
    return $this->_submenutitle;
  }
}
