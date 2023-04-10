<?php

/**
  * WPProject
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class WPProject extends WPEntry
{
  private $_organisation_id;
  
  public function __construct() 
  {
  }

	public function set_organisation_id( $organisation_id ) 
  {
		$this->_organisation_id = $organisation_id;
	}

	public function get_organisation_id() 
  {
		return $this->_organisation_id;
	}
}
