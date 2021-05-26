
<?php

/**
 * Controller OrganisationAdminControl
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UIAbstractAdminControl  
{
  private $_current_module;

  public function __construct($current_module) 
  {
    $this->_current_module = $current_module;
  }

  public function get_current_module()
  {
    return $this->_current_module;
  }
}
