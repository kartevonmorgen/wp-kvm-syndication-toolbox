<?php
/**
 * Das Projekt Modul erstellt ein eigens Posttype für Projekte
 * inkl. Custom Fields.
 */
class WPProjectModule extends WPAbstractModule 
{
  private $_cron_expirator;

  public function setup_includes($loader)
  {
    $loader->add_include('/inc/lib/project/class-project-posttype.php');
    $loader->add_include('/inc/lib/project/class-register-project-templates.php');

    // Admin
    $loader->add_include('/admin/inc/controllers/class-project-admincontrol.php');

  }

  public function setup($loader)
  {
    $this->_cron_expirator = new EntryCronExpirator($this, 
      'project_expirator',
      'hourly');
    $this->_cron_expirator->setup($loader);
    $this->_cron_expirator->schedule();

    $templates = new RegisterProjectTemplates($this);
    $templates->setup($loader);

    $searcher = new EntrySearchBehaviour($this);
    $searcher->setup($loader);

    $loader->add_filter( 'excerpt_more', $this, 'excerpt_more');

    $kvmUploader = new UploadWPEntryToKVM($this);
    $kvmUploader->setup($loader);

    $menuActions = new EntryMenuActions($this, $kvmUploader);
    $menuActions->setup($loader);

    $archiver = new ArchiveWPEntryToKVM($this);
    $archiver->setup($loader);

    $expirator = new EntryExpirator($this);
    $expirator->setup($loader);
    
    $loader->add_action( 'admin_menu', $this, 'remove_menus', 999 );

    $loader->add_starter(new ProjectPosttype($this));
    $loader->add_starter(new ProjectAdminControl($this));

    $loader->add_starter($templates);
  }

  public function module_activate()
  {
		flush_rewrite_rules();

		if ( !current_user_can( 'activate_plugins' ) ) 
    {
      return;
    }
    $this->_cron_expirator->schedule();
  }

  public function module_deactivate()
  {
    $this->_cron_expirator->stop();
  }

  public function module_uninstall()
  {
    $this->_cron_expirator->stop();
  }

  public function get_type()
  {
    $parent = $this->get_parent_module();
    $entry_type_factory = $parent->get_entry_type_factory();
    return $entry_type_factory->get_type(WPEntryType::PROJECT);
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

  public function get_cron_expiration_messages_id()
  {
    return 'project_cron_expiration_messages';
  }

  public function get_cron_expiration_messages()
  {
    return get_option(
      $this->get_project_cron_expiration_messages_id(),
      '');
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
