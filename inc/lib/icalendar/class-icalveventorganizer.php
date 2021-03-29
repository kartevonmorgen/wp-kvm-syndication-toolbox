<?php

/*
 * Parsing ICal Organizer Type
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class ICalVEventOrganizer
{
  private $logger;
  private $vLine;

  private $email;
  private $name;

  public function __construct($logger, $vLine)
  {
    $this->logger = $logger;
    $this->vLine = $vLine;
  }

  public function get_logger()
  {
    return $this->logger;
  }

  public function log($log)
  {
    $this->get_logger()->add_log($log);
  }

  public function getVLine()
  {
    return $this->vLine;
  }

  private function setEmail($email)
  {
    $this->email = $email;
  }

  public function getEmail()
  {
    return $this->email;
  }

  private function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }

  public function parse()
  {
    $vLine = $this->getVLine();
    $su = new PHPStringUtil();
    
    if($vLine->has_parameter('CN'))
    {
      $name = $vLine->get_parameter('CN');
      $name = $su->remove_quotes($name);
      $name = $su->replace_procent_twenty($name);
      $this->setName($name);
    }
    
    $value = $vLine->get_value();
    if($su->startsWith($value, 'MAILTO:'))
    {
      $email = strstr($value, ':');
      $email = substr($email, 1);
      $this->setEmail($email);
    }
  }

}
