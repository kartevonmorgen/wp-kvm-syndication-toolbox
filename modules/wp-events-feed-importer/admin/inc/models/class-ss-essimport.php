<?php
/**
  * Controller SSESSImport
  * Control the import of ESS feed
  *
  * @author     Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
  * @link		    https://github.com/kartevonmorgen
  */
class SSESSImport extends SSAbstractImport
{
  private $_isFeedValid = FALSE;
	/**
	 * Simple test to check if the URL targets to an existing file.
	 *
	 * @param 	String	feed_url	URL of the ess feed file to test
	 * @return	Boolean	result		return a boolean value.
	 */
	public function is_feed_valid()
	{
    if($this->_isFeedValid)
    {
      return TRUE;
    }

    $feed_url = $this->get_feed_url();
		if ( $this->is_validURL( $feed_url ) == FALSE )
		{
			$this->set_error( 'The ESS URL is not valid: '. $feed_url);
			return false;
		}
		else
		{
      $this->_isFeedValid = TRUE;
		  return TRUE;
		}
	}

  public function read_feed_uuid()
  {
    $ess_xml = $this->get_xml_data();

		if ( empty( $ess_xml ))
		{
      return;
    }

    // -- CHANNEL
    foreach ( $ess_xml->channel->children() as $channelChild )
		{
			$channelChildName = strtolower( $channelChild->getName() );

			if ( $channelChildName == 'id' )
      {
        return trim( $ess_xml->channel->$channelChildName );
      }
    }
    $this->set_error('No XML Element found for <channel><id> ');
    return '';
  }

  public function read_feed_title()
  {
    $ess_xml = $this->get_xml_data();

		if ( empty( $ess_xml ))
		{
      return;
    }

    // -- CHANNEL
    foreach ( $ess_xml->channel->children() as $channelChild )
		{
			$channelChildName = strtolower( $channelChild->getName() );

			if ( $channelChildName == 'title' )
      {
        return trim( $ess_xml->channel->$channelChildName );
      }
    }
    $this->set_error('No XML Element found for <channel><title> ');
    return '';
  }

  public function read_events_from_feed()
  {
    $ess_xml = $this->get_xml_data();

    $eiEvents = array();
    foreach ( $ess_xml->channel->children() as $channelChild )
		{
			$channelChildName = strtolower( $channelChild->getName() );

			if ( $channelChildName !== 'feed' )
      {
        continue;
      }

      $eiEvent = $this->read_ess_feed_event($channelChild);
      array_push($eiEvents, $eiEvent);
    }
    return $eiEvents;
  }

  private function read_ess_feed_event($channelChild)
  {
    $eiEvent = new EICalendarEvent();

    foreach ( $channelChild->children() as $feedChild )
    {
      $feedChildName  = strtolower( $feedChild->getName() );
      $value = trim( $feedChild );

      switch ($feedChildName) 
      {
        case "id":
          $eiEvent->set_uid( $value );
          $eiEvent->set_slug( $value );
          break;
        case "title":
          $eiEvent->set_title( $value );
          break;
        case "description":
          $eiEvent->set_description( $value );
          break;
        case "published":
          $eiEvent->set_published_date( $value );
          break;
        case "updated":
          $eiEvent->set_updated_date( $value );
          break;
        case "uri":
          $eiEvent->set_link( $value );
          break;
        case "tags":
          $this->read_ess_feed_tags($eiEvent, $feedChild);
          break;
        case "categories":
          $this->read_ess_feed_categories($eiEvent, $feedChild);
          break;
        case "dates":
          $this->read_ess_feed_dates($eiEvent, $feedChild);
          break;
        case "places":
          $this->read_ess_feed_places($eiEvent, $feedChild);
          break;
        case "people":
          $this->read_ess_feed_people($eiEvent, $feedChild);
          break;
      }
    }
    return $eiEvent;
  }

  function read_ess_feed_tags($eiEvent, $feedChild)
  {
    if( empty($feedChild->children()) )
    {
      return;
    }

    foreach ( $feedChild->children() 
              as $tag )
    {
      $tagValue = trim( $tag );
      $eiEvent->add_tag( new WPTag($tagValue));
    }
  }

  function read_ess_feed_categories($eiEvent, $feedChild)
  {
    if( empty($feedChild->children()) )
    {
      return;
    }

    $thismodule = $this->get_current_module();
    $category_prefix = $thismodule->get_category_prefix();

    foreach ( $feedChild->children() as $cItem )
    {
      $eiCatSlug = null;
      $eiCatName = null;
      foreach ( $cItem->children() as $cItemChild )
      {
        $cItemChildName = strtolower($cItemChild->getName());
        $value = trim( $cItemChild );

        switch ($cItemChildName) 
        {
           case "id":
             $value = str_replace($category_prefix, 
                                  '', $value);
             $eiCatSlug = $value;
             break;
           case "name":
             $eiCatName = $value;
             break;
        }
      }
              
      if(!empty($eiCatName))
      {
        $eiCat = new WPCategory( $eiCatName,
                                 $eiCatSlug);
        $eiEvent->add_category( $eiCat );
      }
    }
  }

  function read_ess_feed_dates($eiEvent, $feedChild)
  {
    if( empty($feedChild->children()) )
    {
      return;
    }

    foreach ( $feedChild->children() as $cItem )
    {
      $eiStartDate = null;
      $eiDuration = 1;
      foreach ( $cItem->children() as $cItemChild )
      {
        $cItemChildName = strtolower($cItemChild->getName());
        $value = trim( $cItemChild );

        switch ($cItemChildName) 
        {
           case "start":
             $eiStartDate = $value;
             break;
           case "duration":
             $eiDuration = $value;
             break;
        }
      }
              
      if(!empty($eiStartDate))
      {
        $eiEvent->set_start_date( $eiStartDate );
        $eiEndDate = strtotime( $eiStartDate ) + $eiDuration*60*60;
        $eiEvent->set_end_date( $eiEndDate );
      }
    }
  }

  function read_ess_feed_places($eiEvent, $feedChild)
  {
    if( empty($feedChild->children()) )
    {
      return;
    }

    $wpLocH = new WPLocationHelper();
    $eiEventLocation = new WPLocation();
    
    foreach ( $feedChild->children() as $cItem )
    {
      $eiStartDate = null;
      $eiDuration = 1;
      foreach ( $cItem->children() as $cItemChild )
      {
        $cItemChildName = strtolower($cItemChild->getName());
        $value = trim( $cItemChild );
        
        switch ($cItemChildName) 
        {
           case "name":
             $wpLocH->set_name( $eiEventLocation, $value );
             break;
           case "address":
             $wpLocH->set_address( $eiEventLocation, $value );
             break;
           case "zip":
             $wpLocH->set_zip( $eiEventLocation, $value );
             break;
           case "city":
             $wpLocH->set_city( $eiEventLocation, $value );
             break;
           case "state":
             $wpLocH->set_state( $eiEventLocation, $value );
             break;
           case "country_code":
             $wpLocH->set_country_code($eiEventLocation,$value);
             break;
        }
      }
    }
    $eiEvent->set_location($eiEventLocation);
  }

  function read_ess_feed_people($eiEvent, $feedChild)
  {
    if( empty($feedChild->children()) )
    {
      return;
    }

    foreach ( $feedChild->children() as $cItem )
    {
      $eiStartDate = null;
      $eiDuration = 1;
      foreach ( $cItem->children() as $cItemChild )
      {
        $cItemChildName = strtolower($cItemChild->getName());
        $value = trim( $cItemChild );
        
        switch ($cItemChildName) 
        {
           case "name":
             $eiEvent->set_contact_name( $value );
             break;
           case "phone":
             $eiEvent->set_contact_phone( $value );
             break;
           case "email":
             $eiEvent->set_contact_email( $value );
             break;
           case "uri":
             $eiEvent->set_contact_website( $value );
             break;
        }
      }
    }
  }
              
}
