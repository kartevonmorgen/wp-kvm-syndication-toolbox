<?php
/**
 * Das Organisation Modul erstellt ein eigens Posttype für Organisation
 * inkl. Custom Fields.
 */
class WPOrganisationModule extends WPAbstractModule 
{
  private $_organisation_types = array();

  public function setup_includes($loader)
  {
    $loader->add_include('/inc/lib/kvm/class-upload-wporganisation-to-kvm.php');
    $loader->add_include('/inc/lib/kvm/class-download-wporganisation-from-kvm.php');
    
    $loader->add_include("/inc/lib/user/class-user-organisation-helper.php");

    $loader->add_include('/inc/lib/initiative/class-initiative-posttype.php');
    $loader->add_include('/inc/lib/initiative/class-migrate-user-und-initiative.php');
    $loader->add_include('/inc/lib/initiative/class-initiative-menuactions.php');
    $loader->add_include('/inc/lib/initiative/class-user-oldvalues-modeladapter.php');

    $loader->add_include('/inc/lib/organisation/class-organisation-posttype.php');
    $loader->add_include('/inc/lib/organisation/class-organisation-search-behaviour.php');
    $loader->add_include('/inc/lib/organisation/class-organisation-menuactions.php');
    $loader->add_include('/inc/lib/organisation/class-register-organisation-templates.php');
    $loader->add_include('/inc/lib/organisation/class-organisation-template-helper.php');
    //$loader->add_include('inc/lib/organisation/class-widget-organisation-search.php');

    // Admin
    $loader->add_include('/admin/inc/controllers/class-organisation-admincontrol.php');

  }

  public function setup($loader)
  {
    $this->init_organisation_types();

    $templates = new RegisterOrganisationTemplates();
    $templates->setup($loader);

    $searcher = new OrganisationSearchBehaviour();
    $searcher->setup($loader);

    $loader->add_filter( 'excerpt_more', $this, 'excerpt_more');

    $kvmUploader = new UploadWPOrganisationToKVM();
    $kvmUploader->setup($loader);


    if($this->is_migration_enabled())
    {
      $menuActions = new InitiativeMenuActions();
      $menuActions->setup($loader);
    }

    $menuActions = new OrganisationMenuActions($kvmUploader);
    $menuActions->setup($loader);
    

    
    $loader->add_action( 'admin_menu', $this, 'remove_menus', 999 );

    if($this->is_migration_enabled())
    {
      $loader->add_starter(new InitiativePosttype());
    }

    $loader->add_starter(new OrganisationPosttype());
    $loader->add_starter(new OrganisationAdminControl());

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

  private function init_organisation_types()
  {
    array_push($this->_organisation_types,
               new WPOrganisationType(WPOrganisationType::INITIATIVE, 
                                      'Initiative', true)); 
    array_push($this->_organisation_types,
               new WPOrganisationType(WPOrganisationType::COMPANY, 
                                      'Company')); 
  }

  public function get_organisation_types()
  {
    return $this->_organisation_types;
  }

  public function get_multiple_organisation_pro_user_id()
  {
    return 'organisation-multiple_pro_user';
  }

  public function is_multiple_organisation_pro_user_allowed()
  {
    return get_option($this->get_multiple_organisation_pro_user_id(), false);
  }

  public function get_migration_enabled_id()
  {
    return 'organisation-migration-enabled';
  }

  public function is_migration_enabled()
  {
    return get_option($this->get_migration_enabled_id(), false);
  }

  public function get_organisation_by_user($user_id)
  {
    $helper = new UserOrganisationHelper();
    return $helper->get_organisation_by_user($user_id);
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
