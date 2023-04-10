<?php

/**
  * WPOrganisation
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class WPOrganisation extends WPEntry
{
  private $_user_id;
  
  public function __construct() 
  {
  }

	public function set_user_id( $user_id ) 
  {
		$this->_user_id = $user_id;
	}

	public function get_user_id() 
  {
		return $this->_user_id;
	}
}
