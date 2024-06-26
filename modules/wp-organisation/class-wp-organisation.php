<?php
/**
 * Das Organisation Modul erstellt ein eigens Posttype für Organisation
 * inkl. Custom Fields.
 */
class WPOrganisationModule extends WPAbstractModule 
{
  private $_entry_type_types = array();
  private $_entry_type_factory = null;

  public function __construct()
  {
    parent::__construct('Organisation');
    $this->set_description('Der Karte von morgen Schnittstelle sorgt dafür das Veranstaltungen und ' .
                           'Organisation auf die Karte von morgen hochgeladen werden ' .
                           'Veranstaltungen werden über Das Events interface geladen und weiter ' .
                           'gegeben an der Karte von morgen. ' .
                           'Die Initiativen werden aus dem Initiative-Modul geladen und weiter gegeben ' . 
                           'an der Karte von morgen. ');

  }

  public function setup_includes($loader)
  {
    $loader->add_include('/inc/lib/kvm/class-upload-wpentry-to-kvm.php');
    $loader->add_include('/inc/lib/kvm/class-download-wpentry-from-kvm.php');
    $loader->add_include('/inc/lib//kvm/class-archive-wpentry-to-kvm.php');
    
    $loader->add_include("/inc/lib/user/class-user-entry-helper.php");

    $loader->add_include('/inc/lib/entry/class-entry-menuactions.php');
    $loader->add_include('/inc/lib/entry/class-entry-posttype.php');
    $loader->add_include('/inc/lib/entry/class-entry-search-behaviour.php');
    $loader->add_include('/inc/lib/entry/class-entry-expirator.php');
    $loader->add_include('/inc/lib/entry/class-entry-cron-expirator.php');
    $loader->add_include('/inc/lib/entry/class-entry-template-helper.php');
    $loader->add_include('/inc/lib/entry/class-wpentry-type-factory.php');
    $loader->add_include('/inc/lib/entry/class-wpentry-type.php');

    $loader->add_include('/inc/lib/organisation/class-organisation-posttype.php');
    $loader->add_include('/inc/lib/organisation/class-register-organisation-templates.php');
    //$loader->add_include('inc/lib/organisation/class-widget-organisation-search.php');

    // Admin
    $loader->add_include('/admin/inc/controllers/class-organisation-admincontrol.php');

  }

  public function setup($loader)
  {
    $this->init_entry_type_types();

    $templates = new RegisterOrganisationTemplates($this);
    $templates->setup($loader);

    $searcher = new EntrySearchBehaviour($this);
    $searcher->setup($loader);

    $loader->add_filter( 'excerpt_more', $this, 'excerpt_more');

    $kvmUploader = new UploadWPEntryToKVM($this);
    $kvmUploader->setup($loader);


    $menuActions = new EntryMenuActions($this, $kvmUploader);
    $menuActions->setup($loader);
    
    $menuActions = new ArchiveWPEntryToKVM($this);
    $menuActions->setup($loader);
    
    $loader->add_action( 'admin_menu', $this, 'remove_menus', 999 );

    $orgaPostType = new OrganisationPosttype($this);
    $orgaPostType->setup($loader);
    $loader->add_starter($orgaPostType);
      
    $loader->add_starter(new OrganisationAdminControl($this));

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

  public function get_entry_type_factory()
  {
    if(empty($this->_entry_type_factory))
    {
      $this->_entry_type_factory = new WPEntryTypeFactory($this);
    }
    return $this->_entry_type_factory;
  }

  public function get_type()
  {
    $entry_type_factory = $this->get_entry_type_factory();
    return $entry_type_factory->get_type(WPEntryType::ORGANISATION);
  }

  private function init_entry_type_types()
  {
    array_push($this->_entry_type_types,
               new WPEntryTypeType(WPEntryTypeType::INITIATIVE, 
                                      'Initiative', true)); 
    array_push($this->_entry_type_types,
               new WPEntryTypeType(WPEntryTypeType::COMPANY, 
                                      'Company')); 
  }

  public function get_entry_type_types()
  {
    return $this->_entry_type_types;
  }

  public function get_extend_the_content_for_single_organisation_id()
  {
    return 'extend_the_content_for_single_organisation';
  }

  public function is_extend_the_content_for_single_organisation()
  {
    return get_option(
      $this->get_extend_the_content_for_single_organisation_id(), 
      true);
  }

  public function get_organisation_by_user($user_id)
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
