<?php

class UserMenuActions
{
  public function setup($loader)
  {
    // Confirm
    $confirmAction = new UIUserTableAction('user_approve', 
                                           'Ein angemeldete Benutzer bestätigen',
                                           'Bestätigen');
    $confirmAction->add_enabled_role('subscriber');
    $confirmAction->set_useraction_listener(new class() implements UIUserTableActionIF
      {
        public function action($user_id, $user_meta)
        {
          $result = wp_update_user(array('ID'=>$user_id, 'role'=>'author'));
          if ( is_wp_error( $result ) )
          {
            // There was an error, probably that user doesn't exist.
            echo '<p>Benutzer id ' . $user_id;
            echo $result->get_error_message();
            echo '</p>';
            return;
          }

          $helper = new UserOrganisationHelper();

          // Success!
          $organisation = $helper->get_organisation_by_user($user_id);
          echo '<p>Benutzer ' . $user_meta->display_name . ' (id=' . $user_id . ')'; 
          if(empty($organisation ))
          {
            echo ' (noch keine Organisation erstellt)</br>';
          }
          else
          {
            echo ' (Organisation: ' . $organisation->post_title . ', id=' . $organisation->ID . ' ist bereits erstellt)</br>';
          }
          echo ' ist bestätigt</p>';
        }
      });
    $confirmAction->setup($loader);
  }
}
