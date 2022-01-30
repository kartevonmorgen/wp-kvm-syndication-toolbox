<?php
/**
  * Controller SSAbstractImportProcessor
  * Process the imported events from the Feed
  *
  * @author     Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
  * @link	      https://github.com/kartevonmorgen
  */
abstract class SSAbstractEventProcessor
{
  private $_importer;
  private $_logger;

  public function set_logger($logger)
  {
    $this->_logger = $logger;
  }

  public function get_logger()
  {
    return $this->_logger;
  }

  public function set_importer($importer)
  {
    $this->_importer = $importer;
  }

  public function get_importer()
  {
    return $this->_importer;
  }

  public abstract function process($eiEvents);

}

