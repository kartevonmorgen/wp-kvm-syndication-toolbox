<?php
/**
  * Controller SSICalImport
  * Control the import of ESS feed
  *
  * @author     Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
  * @link		    https://github.com/kartevonmorgen
  */
class SSICalImport extends SSAbstractImport implements ICalLogger
{
  private $vCalendars = null;
  private $_ical_lines_data;

  public function get_ical_lines_data()
  {
    $stringUtil = new PHPStringUtil();

    if( !empty( $this->_ical_lines_data ))
    {
      return $this->_ical_lines_data;
    }

    $data = $this->get_raw_data();

    // First remove the linebreaks in the text
    $data = str_replace("\r\n ","",$data);
    
    $linesdata2 = array();
    $linesdata = explode(PHP_EOL, $data);
    foreach($linesdata as $linedata)
    {
      if($stringUtil->endsWith($linedata, "\r"))
      {
        $linedata = substr($linedata, 0, -1);
      }
      array_push($linesdata2, $linedata);
    }

    $this->_ical_lines_data = $linesdata2;
    return $this->_ical_lines_data;
  }

	/**
	 * Simple test to check if the URL targets to an existing file.
	 *
	 * @param 	String	feed_url	URL of the ess feed file to test
	 * @return	Boolean	result		return a boolean value.
	 */
	public function is_feed_valid()
	{
    $vCal = $this->get_vcalendar();
    if( empty($vCal))
    {
      return false;
    }
    return true;
	}

  public function read_feed_uuid()
  {
    $vCal = $this->get_vcalendar();
    if(empty($vCal))
    {
      $this->set_error('No VCALENDAR part found for ical feed ');
      return null;
    }
    if( empty( $vCal->get_prodid()))
    {
      $this->set_error('No PRODID found for ical feed ');
      return null;
    }
    return $vCal->get_prodid();
  }

  public function read_feed_title()
  {
    $vCal = $this->get_vcalendar();
    if(empty($vCal))
    {
      return '';
    }
    if( empty( $vCal->get_name()))
    {
      return $vCal->get_prodid();
    }
    return $vCal->get_name();
  }

  public function read_events_from_feed()
  {
    $now = time();
    $thismodule = $this->get_thismodule();
    $maxPeriodInDays = $thismodule->get_max_periodindays();
    $vCal = $this->get_vcalendar();
    if(empty($vCal))
    {
      return array();
    }

    $eiEvents = array();
    foreach ( $vCal->get_events() as $vEvent )
		{
      //$this->add_log('SUM: ' . $vEvent->get_summary() . '<br>');
      if($vEvent->is_recurring())
      {
        // For RECURRENCE-ID the recurring_dates 
        // are filled directly
        if(empty($vEvent->get_recurring_dates()))
        {
          $recurring = new ICalVEventRecurringDate(
                              $vEvent->get_recurring_rule(),
                              $vEvent->get_dt_startdate());
          $recurring->setMaxPeriodInDays($maxPeriodInDays);
          $vEvent->set_recurring_dates($recurring->getDates());
        }

        //$this->add_log('RSTARTDATE: ' . date("Y-m-d | h:i:sa", $vEvent->get_dt_startdate()) . '<br>');
        $index_added = 0;
        foreach( $vEvent->get_recurring_dates() as $date )
        {
          // If the Feed has to many events
          if($index_added >= $thismodule->get_max_recurring_count())
          {
            continue;
          }

          // If Event is in the past
          if($date < $now)
          {
            continue;
          }

          // If Event is to far in the future
          if($date > ($now + ($maxPeriodInDays * 24 * 60 * 60)))
          {
            continue;
          }

          // If the Event is explicit excluded in the feed
          if($vEvent->is_recurring_exdate($date))
          {
            continue;
          }
          
          $slug_suffix = date('__Ymd', $date);
          $index_added = $index_added + 1;
            //$this->add_log('RDATE: ' . date("Y-m-d | h:i:sa", $date) . ' i=' .$index_added . '<br>');
          array_push($eiEvents, 
                     $this->read_event($vEvent, 
                                       $date, 
                                       $slug_suffix));
        }
      }
      else
      {
        $date = $vEvent->get_dt_startdate();
        if($date > $now && $date < ($now + ($maxPeriodInDays * 24 * 60 * 60)))
        {
          //$this->add_log('STARTDATE: ' . date("Y-m-d | h:i:sa", $vEvent->get_dt_startdate()) . '<br>');
          array_push($eiEvents, 
                     $this->read_event($vEvent, $date, ''));
        }
      }
    }
    return $eiEvents;
  }

  private function read_event($vEvent, 
                              $startdate, 
                              $slug_suffix)
  {
    $eiEvent = new EiCalendarEvent();

    $uid = $vEvent->get_uid();
    $uid = $uid . $slug_suffix;

    $enddate = $startdate + ($vEvent->get_dt_enddate() - $vEvent->get_dt_startdate());
    $eiEvent->set_uid(sanitize_title($uid));
    $eiEvent->set_slug(sanitize_title($uid));
    $eiEvent->set_title($vEvent->get_summary());
    $eiEvent->set_description($vEvent->get_description());
    $eiEvent->set_link($vEvent->get_url());

    $eiEvent->set_start_date($startdate);
    $eiEvent->set_end_date($enddate);
    $eiEvent->set_all_day($vEvent->is_dt_allday());

    $eiEvent->set_published_date($vEvent->get_created());
    $eiEvent->set_updated_date($vEvent->get_lastmodified());

    $lat = $vEvent->get_location_lat();
    $lon = $vEvent->get_location_lon();

    $wpLocH = new WPLocationHelper();
    $wpLocation = null;
    if($this->is_feed_define_location_by_geo() && 
      (!empty($lat)) && (!empty($lon)))
    {
      $wpLocation = new WPLocation();
      $wpLocation->set_lat($lat);
      $wpLocation->set_lon($lon);
    }

    $location = $vEvent->get_location();
    $length = strlen( 'http' );
    if (substr( $location, 0, $length ) === 'http')
    {
      // For the Heinrich Boll Stiftung gab es
      // Online Veranstaltungen wo bei LOCATION
      // der Link eingegeben war, wir erlauben
      // das zu übernehmen wenn der noch nicht durch
      // URL eingegeben ist.
      if(empty($eiEvent->get_link()))
      {
        $eiEvent->set_link($location);
      }
    }
    else if(empty($wpLocation))
    {
      if($this->is_feed_wplocation_freetextformat_type_local())
      {
        $wpLocation = $wpLocH->create_from_freetextformat_local($location);
      }
      else if($this->is_feed_wplocation_freetextformat_type_osm())
      {
        $wpLocation = $wpLocH->create_from_freetextformat_osm($location);
      }
    }
      
    $eiEvent->set_location($wpLocation);

    $eiEvent->set_contact_name($vEvent->get_organizer_name());
    $eiEvent->set_contact_email($vEvent->get_organizer_email());

    // In ICal we do categories as tags, weil tags do not exists
    // and we do not know them really.
    foreach($vEvent->get_categories() as $cat)
    {
      $eiEvent->add_tag(new WPTag($cat));
    }
    return $eiEvent;
  }

  private function get_vcalendar()
  {
    $cals = $this->get_vcalendars();
    if(empty( $cals))
    {
      return null;
    }
    return reset($cals);
  }

  private function get_vcalendars()
  {
    if(!empty($this->vCalendars))
    {
      return $this->vCalendars;
    }

    $vCals = array();
    $vCal = null;
    foreach($this->get_ical_lines_data() as $line)
    {
      if ($this->is_element($line, 'BEGIN:VCALENDAR'))
      {
        $vCal = new ICalVCalendar($this);
        continue;
      }

      if ($this->is_element($line, 'END:VCALENDAR'))
      {
        array_push($vCals, $vCal);
        $vCal = null;
        continue;
      }

      if ($this->is_element($line, 'BEGIN:VEVENT'))
      {
        $vEvent = new ICalVEvent($this);
        continue;
      }

      if ($this->is_element($line, 'END:VEVENT'))
      {
        $vCal->add_event($vEvent);
        $vEvent = null;
        continue;
      }

      if(empty($vCal))
      {
        continue;
      }

      $vLine = new ICalVLine($this, $line);
      $vLine->parse();

      if(empty($vEvent))
      {
        $vCal->processLine($vLine); 
        continue;
      }

      $vEvent->processLine($vLine); 
    }

    $this->vCalendars = $vCals;
    return $this->vCalendars;
  }

  private function is_element($line, $element)
  {
    return stristr($line, $element) !== false;
  }
}



