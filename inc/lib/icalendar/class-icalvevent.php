<?php

/*
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class ICalVEvent
{
  private $logger;
  private $dt_startdate;
  private $dt_enddate;
  private $dt_allday;
  private $lastmodified;
  private $created;
  private $uid;
  private $summary;
  private $description;
  private $location = null;
  private $location_lat;
  private $location_lon;
  private $location_online;
  private $location_onlinelink = null;
  private $organizer_name;
  private $organizer_email;
  private $url;
  private $recurring;
  private $recurring_dates = array();
  private $recurring_exdates = array();
  private $recurring_rule;
  private $categories = array();

  function __construct($logger)
  {
    $this->logger = $logger;
  }

  function get_logger()
  {
    return $this->logger;
  }

  function log($log)
  {
    $this->get_logger()->add_log($log);
  }

  function set_dt_startdate($startdate)
  {
    $this->dt_startdate = $startdate;
  }

  function get_dt_startdate()
  {
    return $this->dt_startdate;
  }

  function set_dt_allday($allday)
  {
    $this->dt_allday = $allday;
  }

  function is_dt_allday()
  {
    return $this->dt_allday;
  }

  function set_dt_enddate($enddate)
  {
    $this->dt_enddate = $enddate;
  }

  function get_dt_enddate()
  {
    return $this->dt_enddate;
  }

  function set_lastmodified($modified)
  {
    $this->lastmodified = $modified;
  }

  function get_lastmodified()
  {
    return $this->lastmodified;
  }

  function set_created($created)
  {
    $this->created = $created;
  }

  function get_created()
  {
    return $this->created;
  }

  function set_uid($uid)
  {
    $this->uid = $uid;
  }

  function get_uid()
  {
    return $this->uid;
  }

  function set_summary($summary)
  {
    $this->summary = $summary;
  }

  function get_summary()
  {
    return $this->summary;
  }

  function set_description($description)
  {
    $this->description = $description;
  }

  function get_description()
  {
    return $this->description;
  }

  function set_url($url)
  {
    $this->url = $url;
  }

  function get_url()
  {
    return $this->url;
  }

  function set_location($location)
  {
    $this->location = $location;
  }

  function get_location()
  {
    return $this->location;
  }

  function set_location_lat($lat)
  {
    $this->location_lat = $lat;
  }

  function get_location_lat()
  {
    return $this->location_lat;
  }

  function set_location_lon($lon)
  {
    $this->location_lon = $lon;
  }

  function get_location_lon()
  {
    return $this->location_lon;
  }

  function set_location_online($online)
  {
    $this->location_online = $online;
  }

  function is_location_online()
  {
    return $this->location_online;
  }

  function set_location_onlinelink($onlinelink)
  {
    $this->location_onlinelink = $onlinelink;
  }

  function get_location_onlinelink()
  {
    return $this->location_onlinelink;
  }


  function set_organizer_name($organizer_name)
  {
    $this->organizer_name = $organizer_name;
  }

  function get_organizer_name()
  {
    return $this->organizer_name;
  }

  function set_organizer_email($organizer_email)
  {
    $this->organizer_email = $organizer_email;
  }

  function get_organizer_email()
  {
    return $this->organizer_email;
  }

  function set_recurring($recurring)
  {
    $this->recurring = $recurring;
  }

  function is_recurring()
  {
    return $this->recurring;
  }

  function set_recurring_dates($recurring_dates)
  {
    $this->recurring_dates = $recurring_dates;
  }

  function get_recurring_dates()
  {
    return $this->recurring_dates;
  }

  function set_recurring_exdates($recurring_exdates)
  {
    $this->recurring_exdates = $recurring_exdates;
  }

  function get_recurring_exdates()
  {
    return $this->recurring_exdates;
  }

  function is_recurring_exdate($date)
  {
    foreach($this->get_recurring_exdates() as $exdate)
    {
      if($date === $exdate)
      {
        return true;
      }
    }
    return false;
  }

  function set_recurring_rule($recurring_rule)
  {
    $this->recurring_rule = $recurring_rule;
  }

  function get_recurring_rule()
  {
    return $this->recurring_rule;
  }

  function set_categories($categories)
  {
    $this->categories = $categories;
  }

  function get_categories()
  {
    return $this->categories;
  }

  function processLine($vLine)
  {
    switch ($vLine->get_id()) 
    {
      case 'DTSTART':
        $vEventDate = new ICalVEventDate($this->get_logger(), $vLine);
        $vEventDate->parse();
        $this->set_dt_startdate($vEventDate->getTimestamp());
        $this->set_dt_allday($vEventDate->isDate());
        break;
      case 'DTEND':
        $vEventDate = new ICalVEventDate($this->get_logger(), $vLine);
        $vEventDate->parse();
        $this->set_dt_enddate($vEventDate->getTimestamp());
        $this->set_dt_allday($vEventDate->isDate());
        break;
      case 'RRULE':
        $this->set_recurring(true);
        $this->set_recurring_rule($vLine->get_value());
        break;
      case 'RECURRENCE-ID':
        $vEventDate = new ICalVEventDate($this->get_logger(), 
                                         $vLine);
        $vEventDate->parse();
        $this->set_recurring(true);
        $this->set_recurring_dates($vEventDate->getTimestamps());
        break;
      case 'EXDATE':
        $vEventDate = new ICalVEventDate($this->get_logger(), $vLine);
        $vEventDate->parse();
        $this->set_recurring_exdates(
          $vEventDate->getTimestamps());
        break;
      case 'LAST_MODIFIED':
        $vEventDate = new ICalVEventDate($this->get_logger(), $vLine);
        $vEventDate->parse();
        $this->set_lastmodified($vEventDate->getTimestamp());
        break;
      case 'CREATED':
        $vEventDate = new ICalVEventDate($this->get_logger(), $vLine);
        $vEventDate->parse();
        $this->set_created($vEventDate->getTimestamp());
        break;
      case 'UID':
        $this->set_uid($vLine->get_value());
        break;
      case 'SUMMARY':
        $text = new ICalVEventText($this->get_logger(), $vLine);
        $text->parse();
        $this->set_summary($text->getResult());
        break;
      case 'DESCRIPTION':
        $text = new ICalVEventText($this->get_logger(), $vLine);
        $text->parse();
        $this->set_description($text->getResult());
        break;
      case 'URL':
        $this->set_url($vLine->get_value());
        break;
      case 'LOCATION':
        $text = new ICalVEventLocation($this->get_logger(), 
                                       $vLine);
        $text->parse();
        if($text->isOnline())
        {
          $this->set_location_online(true);
          $this->set_location_onlinelink($text->getOnlineLink());
        }
        else
        {
          $this->set_location($text->getLocation());
        }
        break;
      case 'GEO':
        $vEventGeo = new ICalVEventGeo($this->get_logger(), $vLine);
        $vEventGeo->parse();
        $this->set_location_lat($vEventGeo->getLat());
        $this->set_location_lon($vEventGeo->getLon());
        break;
      case 'ORGANIZER':
        $vEventOrganizer = new ICalVEventOrganizer($this->get_logger(), 
                                                   $vLine);
        $vEventOrganizer->parse();
        $this->set_organizer_name($vEventOrganizer->getName());
        $this->set_organizer_email($vEventOrganizer->getEmail());
        break;
      case 'CATEGORIES':
        $vEventCats = new ICalVEventCategories($this->get_logger(), $vLine);
        $vEventCats->parse();
        $this->set_categories($vEventCats->getCategories());
        break;
    }
  }

}
