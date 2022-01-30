<?php
/**
  * Controller SSAbstractImport
  * Control the import of different Kind of Feeds
  * Different Feed types can be added by overriding this
  * class.
  *
  * @author     Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
  * @link	      https://github.com/kartevonmorgen
  */
abstract class SSAbstractImport extends WPAbstractModuleProvider
{
  private $_feed_url;
  private $_importtype;
  private $_feed_update_daily = false;
  private $_owner_user_id = 0;

  private $_feed_uuid;
  private $_feed_title;
  private $_error;
  private $_log = '';
  private $_echo_log = false;

  private $_raw_data;
  private $_xml_data;

  private $_processor;

	function __construct($module, $feed)
  {
    parent::__construct($module);
    $this->set_processor( new SSDefaultEventProcessor());
    $this->_feed = $feed;
  }

  public function set_processor($processor)
  {
    $this->_processor = $processor;
  }

  public function get_processor()
  {
    return $this->_processor;
  }

  public function get_feed()
  {
    return $this->_feed;
  }

  public function get_feed_id()
  {
    if(empty($this->get_feed()))
    {
      return 0;
    }
    return $this->get_feed()->ID;
  }

  public function is_feed_update_daily()
  {
    return $this->get_feed_meta('ss_feedupdatedaily');
  }

  public function get_feed_url()
  {
    return $this->get_feed_meta('ss_feedurl');
  }

  public function is_linkurl_valid_check_disabled()
  {
    return $this->get_feed_meta(
      'ss_disable_linkurl_valid_check');
  }

  public function get_owner_user_id()
  {
    if(empty($this->get_feed()))
    {
      return 0;
    }
    return $this->get_feed()->post_author;
  }

  public function get_feed_meta($key)
  {
    return get_post_meta($this->get_feed_id(), $key, 1);
  }

  public function get_importtype()
  {
    $importtypeid = $this->get_feed_meta('ss_feedurltype');

    $factory = SSImporterFactory::get_instance();
    return $factory->get_importtype( $importtypeid );
  }

  public function get_feed_filtered_tags()
  {
    $au = new PHPArrayUtil();

    $result = explode(',', 
      $this->get_feed_meta('ss_feed_filtered_tags'));
    return $au->remove_empty_entries($result);
  }

  public function get_feed_include_tags()
  {
    $au = new PHPArrayUtil();

    $result = explode(',', 
      $this->get_feed_meta('ss_feed_include_tags'));
    return $au->remove_empty_entries($result);
  }

  public function is_feed_define_location_by_geo()
  {
    return $this->get_feed_meta('ss_define_location_by_geo');
  }

  public function get_feed_wplocation_freetextformat_type()
  {
    $id = $this->get_feed_meta('ss_feed_wplocation_freetextformat_type');
    if(!empty(trim($id)))
    {
      return $id;
    }

    // Find the default if emmpty
    $root = $this->get_root_module();
    foreach($root->get_wplocation_freetextformat_types()
            as $type)
    {
      if($type->is_default())
      {
        return $type->get_id();
      }
    }

    return null;
  }

  public function is_feed_wplocation_freetextformat_type_local()
  {
    $root = $this->get_root_module();
    $id = $this->get_feed_wplocation_freetextformat_type();
    return $root->is_wplocation_freetextformat_type_local($id);
  }

  public function is_feed_wplocation_freetextformat_type_osm()
  {
    $root = $this->get_root_module();
    $id = $this->get_feed_wplocation_freetextformat_type();
    return $root->is_wplocation_freetextformat_type_osm($id);
  }

  public abstract function is_feed_valid();

  public abstract function read_feed_uuid();

  abstract function read_feed_title();

  abstract function read_events_from_feed();

  public function import()
  {
		global $current_site; 

    $updated_event_ids = array();

		if ( ! $this->is_feed_update_daily() ) 
    {
      if( !is_user_logged_in() )
      {
        $this->set_error( 
          "No user logged in ". $this->get_feed_url() );
        return;
      }
    }

		if ( ! $this->is_feed_valid() )
		{
      if($this->is_echo_log())
      {
        echo '<p>Feed ' . $this->read_feed_title() . ' is not valid</p>';
      }
      return;
    }

		$this->set_feed_uuid($this->read_feed_uuid());
    if( $this->has_error() )
    {
      return;
    }

		$this->set_feed_title($this->read_feed_title());
    if( $this->has_error() )
    {
      return;
    }

    if($this->is_echo_log())
    {
      echo '<p>Start reading events from feed ' . $this->get_feed_title() . '</p>';
    }
    $eiEvents = $this->read_events_from_feed();
    if( $this->has_error() )
    {
      return;
    }

    if($this->is_echo_log())
    {
      echo '<p>End reading events from feed</p>';
    }

    $logger = new PostMetaLogger('ss_feed_updatelog',
                                 $this->get_feed_id());
    $logger->add_date();
    $logger->add_line('Update Feed (feedid=' . 
      $this->get_feed_id() . ', user=' . 
      $this->get_owner_user_id() . '): '. 
      $this->get_feed_url());
    if($this->is_echo_log())
    {
      $logger->echo_log();
    }
    $logger->save();

    $now = time();
    $thismodule = $this->get_current_module();

    $max_events = $thismodule->get_max_events_pro_feed();
    $count = 0;
    if($this->is_echo_log())
    {
      echo '<p>Start importing events for feed ' . 
           $this->get_feed_title() . '</p>';
      echo '<p>Maximum number of events to import ' . 
           $max_events . '</p>';
    }
    
    $eiEventsToProcess = array();
    foreach ( $eiEvents as $eiEvent )
    {
      echo '<p>Import(' . $count . '):: ' . $eiEvent->get_title() . '</p>';
      if(($max_events > -1) && ($count >= $max_events) )
      {
        if($this->is_echo_log())
        {
          echo '<p>Maximum number of events reached</p>';
        }
        break;
      }
      $count = $count + 1;

      $logger->remove_prefix();
      $logger->add_newline();
      $logger->add_date();
      $logger->add_line('Prepare update Event ' . $eiEvent->get_uid());
      $logger->add_prefix('  ');
      // Do not import events from the past
      if(strtotime($eiEvent->get_start_date()) < $now)
      {
        $logger->add_line('Event is too old (' .
          $eiEvent->get_start_date() . '), no update');
        continue;
      }

      if( !$this->is_linkurl_valid_check_disabled() )
      {
        // Checks if the feed_url has the same host
        // as the events url/link
        if( !$this->is_linkurl_valid( $eiEvent ))
        {
          $logger->add_line('the linkurl (' . 
                            $eiEvent->get_link() . ') ' .
                            'does not orginate to the ' .
                            'feedurl ('. 
                            $this->get_feed_url() . ')');
          continue;
        }
      }

      if( !empty( $this->get_feed_filtered_tags()))
      {
        $found = false;
        foreach($this->get_feed_filtered_tags() as $tag)
        {
          if($this->contains_tag($eiEvent, $tag))
          {
            $found = true;
          }
        }
        if(!$found)
        {
          $logger->add_line('the filtered_tags of the feed (' . 
                            implode(',', $this->get_feed_filtered_tags()) . ') ' .
                            'does not match for this event (' .
                            $this->get_feed_url() . ')' .
                            $this->get_print_tags($eiEvent));
          continue;
        }
      }

      if( $thismodule->is_backlink_enabled())
      {
        $this->add_backlink($eiEvent);
      }

      if( !empty( $this->get_feed_include_tags()))
      {
        foreach($this->get_feed_include_tags() as $inc_tag)
        {
          $eiEvent->add_tag(new WPTag($inc_tag));
        }
      }

      $eiEvent->set_post_status('pending');

      if( $thismodule->is_publish_directly() )
      {
        $eiEvent->set_post_status('publish');
      }

      $eiEvent->set_owner_user_id($this->get_owner_user_id());

		  if( isset( $current_site ) )
      {
        $eiEvent->set_blog_id( $current_site->blog_id );
      }

      // Fill Lat/Lon coordinates by osm
      // so we can check if the location has been changed.
      $wpLocationHelper = new WPLocationHelper();
      $eiEventLocation = $eiEvent->get_location();
      if(!empty($eiEventLocation))
      {
        $lat = floatval($eiEventLocation->get_lat());
        $lon = floatval($eiEventLocation->get_lon());
        if($this->is_feed_define_location_by_geo() && 
          (!empty($lat)) && (!empty($lon)))
        {
          $logger->add_line(' location ' . 
            ' hat already coordinates (' . 
            $lat . ',' . $lon . ') ,' .
            'so we fill the location address by GEO');
          $eiEventLocation = 
            $wpLocationHelper->fill_by_osm_nominatim_geo($lat, $lon);

        }
        else
        {
          $logger->add_line('fill location (' . 
            $eiEventLocation->to_string() . ') by osm');
          $eiEventLocation = 
            $wpLocationHelper->fill_by_osm_nominatim(
              $eiEventLocation);
          $logger->add_line('  lat=' . 
            $eiEventLocation->get_lat()); 
          $logger->add_line('  lon=' . 
            $eiEventLocation->get_lon()); 
        }

        $wpLocationHelper->fill_name_if_empty($eiEventLocation);

        if($wpLocationHelper->is_valid($eiEventLocation))
        {
          $eiEvent->set_location($eiEventLocation);
        }
        else
        {
          $eiEvent->set_location( null );
          $logger->add_line(' location ' . 
            ' is not valid (' . 
            $eiEventLocation->to_string() . ')'); 
        }
      }

      array_push($eiEventsToProcess, $eiEvent);
      $logger->remove_prefix();
    }

    $logger->add_line('check import events finished: process events ');

    $processor = $this->get_processor();
    $processor->set_importer($this);
    $processor->set_logger($logger);

    $processor->process($eiEventsToProcess);

    $logger->remove_prefix();
    $logger->add_line('-- update feed finished ---');
    if($this->is_echo_log())
    {
      $logger->echo_log();
    }
    $logger->save();
  }
          
  private function is_linkurl_valid($eiEvent)
  {
    $feed_url = $this->get_feed_url();

    $feed_host = parse_url($feed_url, PHP_URL_HOST);
    $feed_host = str_replace('www.', '', $feed_host);
    $eiEvent_host = parse_url($eiEvent->get_link(), PHP_URL_HOST);
    $eiEvent_host = str_replace('www.', '', $eiEvent_host);
    return $feed_host == $eiEvent_host;
  }

  public function get_raw_data()
  {
    if( !empty( $this->_raw_data ))
    {
      return $this->_raw_data;
    }

    $req = new SimpleRequest('get', $this->get_feed_url());
    $client = new WordpressHttpClient();
    $resp = $client->send($req);
    if( $resp->getStatusCode() == 200 )
    {
      $this->_raw_data = $resp->getBody();
    }
    else
    {
      $this->set_error("GetRawData Error: An error occure while trying to read the feed from the URL (" . 
        $this->get_feed_url() . "): " .
        $resp->getReasonPhrase());
    }

    return $this->_raw_data;
  }



  public function get_xml_data()
  {
    if( !empty( $this->_xml_data ))
    {
      return $this->_xml_data;
    }
    
    try
    {
      $this->_xml_data = @simplexml_load_string( 
                                   $this->get_raw_data(), 
                                   "SimpleXMLElement", 
                                   LIBXML_NOCDATA );
    }
    catch( ErrorException $e )
    {
      $this->set_error("GetXMLData Error: An error occure while trying to read the ESS file from the URL: (" .$e. ")");
    }
    return $this->_xml_data;
  }


  public function set_feed_uuid($feed_uuid)
  {
    return $this->_feed_uuid = $feed_uuid;
  }

  public function get_feed_uuid()
  {
    return $this->_feed_uuid;
  }

  public function set_feed_title($feed_title)
  {
    return $this->_feed_title = $feed_title;
  }

  public function get_feed_title()
  {
    return $this->_feed_title;
  }
  
  public function add_backlink($eiEvent)
  {
    $backlink = $eiEvent->get_link();
    if(empty($backlink))
    {
      $backlink = $this->get_feed_url();
    }
    $backlink_html = '<p>Importiert von ';
    $backlink_html .= '<a href="';
    $backlink_html .= $backlink;
    $backlink_html .= '">';
    $backlink_html .= $backlink;
    $backlink_html .= '</a></p>';
    $eiEvent->set_description(
      $eiEvent->get_description() . $backlink_html);
  }

  public function set_error($error)
  {
    $this->_error = '['. $this->get_feed_url() . ']:' .$error;
  }

  public function get_error()
  {
    return $this->_error;
  }

  public function set_log( $log )
  {
    $this->_log = '['. $this->get_feed_url() . ']:' .$log;
  }

  public function add_log( $log )
  {
    $this->_log .= PHP_EOL;
    $this->_log .= '['. $this->get_feed_url() . ']:' . $log;
  }

  public function get_log()
  {
    return $this->_log;
  }

  public function has_error()
  {
    return !empty($this->_error);
  }

  public function set_echo_log($echo_log)
  {
    $this->_echo_log = $echo_log;
  }

  public function is_echo_log()
  {
    return $this->_echo_log;
  }

	/**
	 * Control if the URL is correctly formated (RFC 3986)
	 * An IP can also be submited as a URL.
	 *
	 * @access	public
	 * @param	String	stringDate string element to control
	 * @return	Boolean
	 */
	public function is_validURL( $url='' )
	{
		$url = trim( $url );
		$ereg = "/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";

		return ( preg_match( $ereg, $url ) > 0 && strlen( $url ) > 10 )? TRUE : $this->is_validIP( $url );
	}

	/**
	 * 	Control if the parameter submited is a valide IP v4
	 *
	 * 	@access public
	 * 	@param	String	Value of the IP to evaluate
	 * 	@return	Boolean	If the parameter submited is a valide IP return TRUE, FALSE else.
	 */
	public function is_validIP( $ip='' )
	{
		$ip = trim( $ip );
		$regexp = '/^((1?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(1?\d{1,2}|2[0-4]\d|25[0-5])$/';

		if ( preg_match( $regexp, $ip ) <= 0 )
		{
			return FALSE;
		}
		else
		{
			$a = explode( ".", $ip );

			if ( $a[0] > 255) { return FALSE; }
			if ( $a[1] > 255) { return FALSE; }
			if ( $a[2] > 255) {	return FALSE; }
			if ( $a[3] > 255) { return FALSE; }

			return TRUE;
    }
  }

  private function contains_tag($eiEvent, $tag)
  {
    $tag = trim($tag);
    foreach($eiEvent->get_tags() as $eiTag)
    {
      if($eiTag->get_name() == $tag)
      {
        return true;
      }
      if($eiTag->get_slug() == $tag)
      {
        return true;
      }
    }
    return false;
  }

  private function get_print_tags($eiEvent)
  {
    $print = '( ';
    $i = 0;
    foreach($eiEvent->get_tags() as $tag)
    {
      if($i > 0)
      {
        $print .= ', ';
      }
      $print .= $tag->get_name();
      $i = $i + 1;
    }
    $print .= ')';
    return $print;
  }
}
