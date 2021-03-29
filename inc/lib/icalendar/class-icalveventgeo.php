<?php

/*
 * Parsing ICal Latitude und Longitude for a location 
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class ICalVEventGeo
{
  private $logger;
  private $vLine;

  private $lat;
  private $lon;

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

  private function setLat($lat)
  {
    $this->lat = $lat;
  }

  public function getLat()
  {
    return $this->lat;
  }

  private function setLon($lon)
  {
    $this->lon = $lon;
  }

  public function getLon()
  {
    return $this->lon;
  }

  public function parse()
  {
    $vLine = $this->getVLine();
    $value = $vLine->get_value();

    $lat = strstr($value, ';', true);
    $this->setLat($lat);

    $lon = strstr($value, ';');
    $lon = substr($lon, 1);
    $this->setLon($lon);
  }

}
