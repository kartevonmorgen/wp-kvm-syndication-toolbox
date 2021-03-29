<?php

/** 
 * UISettingsPage
 * This Class is used to add a Settings Page to the menu
 * it uses the Settings API to bind options to settings in 
 * the page.
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UISettingsPage extends UIPage
{
  private $_sections = array();

  public function add_section($section_id, $section_title)
  {
    $section = new UISettingsSection($section_id, $section_title);
    array_push($this->_sections, $section);
    return $section;
  }

  function admin_init()
  {
    foreach($this->get_sections() as $section)
    {
      $section->admin_init($this->get_menuid(), $this->get_groupid());
    }
  }

  function show_page()
  {
    ?>
     <div class="wrap">
         <h2><?php echo $this->get_title(); ?></h2>
         <form action="options.php" method="POST">
             <?php settings_fields( $this->get_groupid() ); ?>
             <?php do_settings_sections( $this->get_menuid() ); ?>
             <?php submit_button(); ?>
         </form>
     </div>
    <?php
  }

  public function get_sections()
  {
    return $this->_sections;
  }

}
