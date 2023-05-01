<?php
/**
 * Das Projekt Modul erstellt ein eigens Posttype für Projekte
 * inkl. Custom Fields.
 */
class WPProjectModule extends WPAbstractModule 
{
  public function setup_includes($loader)
  {
    $loader->add_include('/inc/lib/project/class-project-posttype.php');
    $loader->add_include('/inc/lib/project/class-register-project-templates.php');

    // Admin
    $loader->add_include('/admin/inc/controllers/class-project-admincontrol.php');

  }

  public function setup($loader)
  {
    $templates = new RegisterProjectTemplates($this);
    $templates->setup($loader);

    $searcher = new EntrySearchBehaviour($this);
    $searcher->setup($loader);

    $loader->add_filter( 'excerpt_more', $this, 'excerpt_more');

    $kvmUploader = new UploadWPEntryToKVM($this);
    $kvmUploader->setup($loader);


    $menuActions = new EntryMenuActions($this, $kvmUploader);
    $menuActions->setup($loader);
    
    $loader->add_action( 'admin_menu', $this, 'remove_menus', 999 );

    $loader->add_starter(new ProjectPosttype($this));
    $loader->add_starter(new ProjectAdminControl($this));

    $loader->add_starter($templates);
  }

  public function module_activate()
  {
  }

  public function module_deactivate()
  {
  }

  public function module_uninstall()
  {
  }

  public function get_type()
  {
    if(empty($this->_entry_type_factory))
    {
      $this->_entry_type_factory = new WPEntryTypeFactory($this);
    }
    return $this->_entry_type_factory->get_type(WPEntryType::PROJECT);
  }

  public function get_extend_the_content_for_single_project_id()
  {
    return 'extend_the_content_for_single_project';
  }

  public function is_extend_the_content_for_single_project()
  {
    return get_option(
      $this->get_extend_the_content_for_single_project_id(), 
      true);
  }

  public function get_project_by_user($user_id)
  {
    $helper = new UserEntryHelper($this);
    return $helper->get_entry_by_user($user_id);
  }

  /** 
   * Remove Menus for Authors, which they do not need
   */ 
  public function remove_menus()
  {
    if( !current_user_can( 'edit_pages' ))
    {
      remove_menu_page('edit.php');
      remove_menu_page('edit-comments.php');
      remove_menu_page('tools.php');
    }
  }

  /**
   * Change the excerpt more string
   */
  function excerpt_more( $more ) 
  {
    return ' [..]';
  }

}

?>
