<?php

/*
 * Parsing ICal Text Type
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class ICalVEventLocation
{
  private $logger;
  private $vLine;

  private $online;
  private $onlinelink;

  private $location;
  
  private $result;

  public function __construct($logger, $vLine)
  {
    $this->logger = $logger;
    $this->vLine = $vLine;
    $this->online = false;
    $this->onlinelink = null;
    $this->location = null;
  }

  private function getVLine()
  {
    return $this->vLine;
  }

  private function get_logger()
  {
    return $this->logger;
  }

  private function setLocation($location)
  {
    $this->location = $location;
  }

  public function getLocation()
  {
    return $this->location;
  }

  private function setOnline($online)
  {
    $this->online = $online;
  }

  public function isOnline()
  {
    return $this->online;
  }

  private function setOnlineLink($onlinelink)
  {
    $this->onlinelink = $onlinelink;
  }

  public function getOnlineLink()
  {
    return $this->onlinelink;
  }

  public function parse()
  {
    $strUtil = new PHPStringUtil();

    $vLine = $this->getVLine();
    $value = $vLine->get_value();

    // Remove the "\," because it is removed by wordpress and then we always get trouble with comparing
    $value = str_replace("\,", ",", $value);

    // Make sure we do not fill a location for
    // online events 
    $length = strlen( 'http' );
    if (substr( $value, 0, $length ) === 'http')
    {
      // For the Heinrich Boll Stiftung gab es
      // Online Veranstaltungen wo bei LOCATION
      // der Link eingegeben war, wir erlauben
      // das zu übernehmen wenn der noch nicht durch
      // URL eingegeben ist.
      $this->setOnline(true);
      $this->setOnlineLink($value);

    }
    else if($strUtil->contains($value, 'online', true))
    {
      $this->setOnline(true);
    }
    else if($strUtil->contains($value, 'zoom', true))
    {
      $this->setOnline(true);
    }
    else
    {
      $this->setLocation($value);
    }
  }
}

