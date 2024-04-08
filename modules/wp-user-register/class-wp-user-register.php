<?php

class WPUserRegisterModule extends WPAbstractModule
{
  public function __construct()
  {
    parent::__construct('Benutzer registrieren');
    $this->set_description('Das Modul sorgt dafür das Benutzer ' . 
                           'sich gleich mit ihre Organisation ' .
                           'registrieren können. ');
  }

  public function setup_includes($loader)
  {
    $loader->add_include("/inc/lib/user/class-user-menuactions.php");
    $loader->add_include("/inc/lib/user/class-user-table-columns.php");
    $loader->add_include("/inc/lib/user/class-userregister-table-columns.php");
    $loader->add_include("/inc/lib/user/class-wp-register-userregister-posttype.php");
    $loader->add_include("/inc/lib/user/class-wp-register-userregister-layout.php");
    $loader->add_include("/inc/lib/user/class-wp-createdefault-userregister-posts.php");
    $loader->add_include("/inc/lib/user/ui/class-wp-userregister-field.php");
    $loader->add_include("/inc/lib/user/email/class-userregister-email.php");
    $loader->add_include("/inc/lib/user/class-wp-register-honeypot.php");

    // Model
    $loader->add_include("/admin/inc/models/class-in-usermodel.php");

    // Controllers
    $loader->add_include("/admin/inc/controllers/class-in-controlholder.php");
    $loader->add_include("/admin/inc/controllers/class-in-userprofilecontrol.php");
    $loader->add_include("/admin/inc/controllers/class-in-userregistercontrol.php");

    $loader->add_include('/admin/inc/controllers/class-userregister-admincontrol.php' );

    // Views
    $loader->add_include("/admin/inc/views/class-in-userregisterview.php");
    $loader->add_include("/admin/inc/views/class-in-userprofileview.php");
  }

  public function setup($loader)
  {
    $menuActions = new UserMenuActions($this);
    $menuActions->setup($loader);

    $tableColumns = new UserTableColumns($this);
    $tableColumns->setup($loader);

    $tableColumns = new UserRegisterTableColumns(); 
    $tableColumns->setup($loader);

    $urposttype = new WPRegisterUserRegisterPosttype($this);
    $urposttype->setup($loader);

    $urlayout = new WPRegisterUserRegisterLayout($this);
    $urlayout->setup($loader);

    $create_urposts = new WPCreateDefaultUserRegisterPosts();
    $create_urposts->setup($loader);

    $uremail = new UserRegisterEmail();
    $uremail->setup($loader);

    $loader->add_starter( new InControlHolder($this));
    $loader->add_starter($urposttype);
    $loader->add_starter( new UserRegisterAdminControl($this));

    $hpot = new WPRegisterHoneypot();
    $hpot->setup($loader);
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

  public function get_parent_classname()
  {
    return 'WPOrganisationModule';
  }

  public function get_publish_organisation_after_approve_id()
  {
    return 'publish_organisation_after_approve';
  }

  public function is_publish_organisation_after_approve()
  {
    return get_option(
      $this->get_publish_organisation_after_approve_id(), 
      false);
  }

  public function get_organisation_by_user($user_id)
  {
    return $this->get_parent_module()->get_organisation_by_user(
      $user_id);
  }
}

