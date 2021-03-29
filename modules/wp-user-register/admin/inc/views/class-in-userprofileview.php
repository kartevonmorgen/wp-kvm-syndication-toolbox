<?php

class InUserProfileView extends UIView
{
  public function init()
  {
    $va = $this->add_va('post_title');
    $va->set_disabled(true);
    $va = $this->add_va('organisation_type');
    $va->set_disabled(true);
    $va = $this->add_va('organisation_address');
    $va->set_disabled(true);
    $va = $this->add_va('organisation_zipcode');
    $va->set_disabled(true);
    $va = $this->add_va('organisation_city');
    $va->set_disabled(true);

    $va = $this->add_va('organisation_ds');
    $va->set_disabled(true);

    
    $mc = WPModuleConfiguration::get_instance();
    $module = $mc->get_module('wp-organisation');
    if($module->is_migration_enabled())
    {
      $va = $this->add_va('organisation_oldvalues');
      $va->set_disabled(true);
    }
  }

  public function show()
  {
?>
  <h3>Extra Informationen über die Organisation</h3>
    <table class="form-table"><?php
    foreach($this->get_viewadapters() as $va)
    {
?><tr><th><?php
      $va->show_label();
?></th><td><?php
      $va->show_field();
      if ( $va->has_description())
      {
        $va->show_newline();
        $va->show_description();
      }
?></td</tr><?php
    }
?></table>
<?php
  }
}
