<?php

/*
 * Parsing ICal Dates
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class ICalVEventDate
{
  private $logger;
  private $vLine;

  private $isDate;
  private $timestamps = array();
  private $dateHelper;

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

  private function addTimestamp($timestamp)
  {
    array_push( $this->timestamps, $timestamp );
  }

  public function getTimestamps()
  {
    return $this->timestamps;
  }

  public function getTimestamp()
  {
    return reset($this->timestamps);
  }

  private function setDate($isDate)
  {
    $this->isDate = $isDate;
  }

  public function isDate()
  {
    return $this->isDate;
  }

  private function convert_windows_timezone_to_iana($windows_timezone) {
    $timezone_map = array(
      'W. Europe Standard Time' => 'Europe/Berlin',
      'Central Europe Standard Time' => 'Europe/Budapest',
      'Eastern Europe Standard Time' => 'Europe/Bucharest',
      'Romance Standard Time' => 'Europe/Paris',
      'GMT Standard Time' => 'Europe/London',
      'Greenwich Standard Time' => 'Atlantic/Reykjavik',
      'Central European Standard Time' => 'Europe/Warsaw',
      'W. Central Africa Standard Time' => 'Africa/Lagos',
      'Namibia Standard Time' => 'Africa/Windhoek',
      'Jordan Standard Time' => 'Asia/Amman',
      'GTB Standard Time' => 'Europe/Athens',
      'Middle East Standard Time' => 'Asia/Beirut',
      'Egypt Standard Time' => 'Africa/Cairo',
      'South Africa Standard Time' => 'Africa/Johannesburg',
      'FLE Standard Time' => 'Europe/Kiev',
      'Israel Standard Time' => 'Asia/Jerusalem',
      'South Sudan Standard Time' => 'Africa/Juba',
      'Sudan Standard Time' => 'Africa/Khartoum',
      'Libya Standard Time' => 'Africa/Tripoli',
      'Arabic Standard Time' => 'Asia/Baghdad',
      'Turkey Standard Time' => 'Europe/Istanbul',
      'Arab Standard Time' => 'Asia/Riyadh',
      'Belarus Standard Time' => 'Europe/Minsk',
      'Russian Standard Time' => 'Europe/Moscow',
      'E. Europe Standard Time' => 'Europe/Minsk',
      'Iran Standard Time' => 'Asia/Tehran',
      'Arabian Standard Time' => 'Asia/Dubai',
      'Astrakhan Standard Time' => 'Europe/Astrakhan',
      'Azerbaijan Standard Time' => 'Asia/Baku',
      'Russia Time Zone 3' => 'Europe/Samara',
      'Mauritius Standard Time' => 'Indian/Mauritius',
      'Saratov Standard Time' => 'Europe/Saratov',
      'Georgian Standard Time' => 'Asia/Tbilisi',
      'Volgograd Standard Time' => 'Europe/Volgograd',
      'Caucasus Standard Time' => 'Asia/Yerevan',
      'Afghanistan Standard Time' => 'Asia/Kabul',
      'West Asia Standard Time' => 'Asia/Tashkent',
      'Ekaterinburg Standard Time' => 'Asia/Yekaterinburg',
      'Pakistan Standard Time' => 'Asia/Karachi',
      'Qyzylorda Standard Time' => 'Asia/Qyzylorda',
      'India Standard Time' => 'Asia/Calcutta',
      'Sri Lanka Standard Time' => 'Asia/Colombo',
      'Nepal Standard Time' => 'Asia/Kathmandu',
      'Central Asia Standard Time' => 'Asia/Almaty',
      'Bangladesh Standard Time' => 'Asia/Dhaka',
      'Omsk Standard Time' => 'Asia/Omsk',
      'Myanmar Standard Time' => 'Asia/Rangoon',
      'SE Asia Standard Time' => 'Asia/Bangkok',
      'Altai Standard Time' => 'Asia/Barnaul',
      'W. Mongolia Standard Time' => 'Asia/Hovd',
      'North Asia Standard Time' => 'Asia/Krasnoyarsk',
      'N. Central Asia Standard Time' => 'Asia/Novosibirsk',
      'Tomsk Standard Time' => 'Asia/Tomsk',
      'China Standard Time' => 'Asia/Shanghai',
      'North Asia East Standard Time' => 'Asia/Irkutsk',
      'Singapore Standard Time' => 'Asia/Singapore',
      'W. Australia Standard Time' => 'Australia/Perth',
      'Taipei Standard Time' => 'Asia/Taipei',
      'Ulaanbaatar Standard Time' => 'Asia/Ulaanbaatar',
      'Aus Central W. Standard Time' => 'Australia/Eucla',
      'Transbaikal Standard Time' => 'Asia/Chita',
      'Tokyo Standard Time' => 'Asia/Tokyo',
      'North Korea Standard Time' => 'Asia/Pyongyang',
      'Korea Standard Time' => 'Asia/Seoul',
      'Yakutsk Standard Time' => 'Asia/Yakutsk',
      'Cen. Australia Standard Time' => 'Australia/Adelaide',
      'AUS Central Standard Time' => 'Australia/Darwin',
      'E. Australia Standard Time' => 'Australia/Brisbane',
      'AUS Eastern Standard Time' => 'Australia/Sydney',
      'West Pacific Standard Time' => 'Pacific/Port_Moresby',
      'Tasmania Standard Time' => 'Australia/Hobart',
      'Vladivostok Standard Time' => 'Asia/Vladivostok',
      'Lord Howe Standard Time' => 'Australia/Lord_Howe',
      'Bougainville Standard Time' => 'Pacific/Bougainville',
      'Russia Time Zone 10' => 'Asia/Srednekolymsk',
      'Magadan Standard Time' => 'Asia/Magadan',
      'Norfolk Standard Time' => 'Pacific/Norfolk',
      'Sakhalin Standard Time' => 'Asia/Sakhalin',
      'Central Pacific Standard Time' => 'Pacific/Guadalcanal',
      'Russia Time Zone 11' => 'Asia/Kamchatka',
      'New Zealand Standard Time' => 'Pacific/Auckland',
      'UTC+12' => 'Etc/GMT-12',
      'Fiji Standard Time' => 'Pacific/Fiji',
      'Kamchatka Standard Time' => 'Asia/Kamchatka',
      'Chatham Islands Standard Time' => 'Pacific/Chatham',
      'UTC+13' => 'Etc/GMT-13',
      'Tonga Standard Time' => 'Pacific/Tongatapu',
      'Samoa Standard Time' => 'Pacific/Apia',
      'Line Islands Standard Time' => 'Pacific/Kiritimati'
    );

    return isset($timezone_map[$windows_timezone]) ? $timezone_map[$windows_timezone] : 'UTC';
  }

  public function parse()
  {
    $timezone = wp_timezone();
    $dateHelper = new ICalDateHelper();
    $vLine = $this->getVLine();

    $this->setDate(false);
    $value = $vLine->get_parameter('VALUE');
    if($value == 'DATE')
    {
      $this->setDate(true);
    }
    $value = $vLine->get_parameter('TZID');
    if(!empty($value))
    {
      try {
        $timezone = new DateTimeZone($value);
      } catch (Exception $e) {
        // If the timezone is invalid, try to convert from Windows format
        $iana_timezone = $this->convert_windows_timezone_to_iana($value);
        $timezone = new DateTimeZone($iana_timezone);
      }
    }
    $value = $vLine->get_value();
    $dateValues = explode(',', $value);
    foreach($dateValues as $dateValue)
    {
      $ts = $dateHelper->fromiCaltoUnixDateTime($dateValue, $timezone);
      $this->addTimestamp($ts);
    }
  }

}
