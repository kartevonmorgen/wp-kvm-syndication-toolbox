<?php

/*
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class ICalVCalendar
{
  private $logger;
  private $vEvents;
  private $link;
  private $name;
  private $prodid;
  
  function __construct($logger)
  {
    $this->logger = $logger;
    $this->vEvents = array();
    $this->prodid = null;
    $this->name = null;
    $this->link = null;
  }

  function get_logger()
  {
    return $this->logger;
  }

  function log($log)
  {
    $this->get_logger()->add_log($log);
  }

  function processLine($vLine)
  {
    switch ($vLine->get_id()) 
    {
      case 'X-ORGINAL_URL':
        $this->set_link($vLine->get_value());
        break;
      case 'X-WR-CALNAME':
        $this->set_name($vLine->get_value());
        break;
      case 'PRODID':
        $this->set_prodid($vLine->get_value());
        break;
    }
  }

  function add_event($vEvent)
  {
    array_push($this->vEvents, $vEvent);
  }

  function get_events()
  {
    return $this->vEvents;
  }

  function set_name($name)
  {
    $this->name = $name;
  }

  function get_name()
  {
    return $this->name;
  }

  function set_link($link)
  {
    $this->link = $link;
  }

  function get_link()
  {
    return $this->link;
  }

  function set_prodid($prodid)
  {
    $this->prodid = $prodid;
  }

  function get_prodid()
  {
    return $this->prodid;
  }
}
