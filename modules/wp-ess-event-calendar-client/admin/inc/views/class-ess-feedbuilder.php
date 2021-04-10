<?php
/**
  * View ESSFeedBuilder
  * ESS feed generator.
  *
  * @author  	Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
final class ESSFeedBuilder
{
  const DEFAULT_CATEGORY_TYPE = 'general';

	function __construct()
  {
  }

	public function output( $event_cat=NULL )
	{
    $mc = WPModuleConfiguration::get_instance();
    $ei_module = $mc->get_module('wp-events-interface');

		$events = $ei_module->get_events_by_cat( $event_cat );
    $essFeed = $this->getFeed($events);
    $essFeed->genarateFeed();
  }
      
	public function generateFeed( $events )
  {
    $essFeed = $this->getFeed($events);
    return $essFeed->getFeedData();
  }

	public function getFeed( $events )
  {
      // TODO: continue here
		$limit = 0; //no limit for now, but this shall be eventually set in the wP settings page, option name dbem_ess_limit

		$essFeed = new FeedWriter( 'en' );
    $essFeed->DEBUG = FALSE; // ###  DEBUG  MODE

    $link =  str_replace( '&push=1', '', 
                      str_replace( '&download=1', '', 
                      FeedWriter::getCurrentURL() ) );
    //echo 'LINK:'.$link;
	  $essFeed->setTitle( ''. $_SERVER[ 'HTTP_HOST' ] );
    $essFeed->setLink($link); 
    $essFeed->setPublished( 'now' ); 
    $essFeed->setRights( '' );


    if( count($events) > 0)
    {
			foreach ( $events as $ie => $event )
      {
        $newEvent = $this->createEventFeed($essFeed, $event);
        $essFeed->addItem( $newEvent );
      }
    }

	  $essFeed->IS_DOWNLOAD	= ( $is_download === TRUE ) ? TRUE : FALSE;
		$essFeed->AUTO_PUSH 	= ( $is_push === TRUE ) ? TRUE : FALSE;

		//var_dump( $essFeed );die;
		return $essFeed;
  }

  private function createEventFeed($essFeed, $event)
  {
		$newEvent = $essFeed->newEventFeed();
    $newEvent->setId( $event->get_uid());
    $newEvent->setTitle( $event->get_title());

	  $event_url = $event->get_link();
	  $newEvent->setUri( $event_url );
    $newEvent->setPublished( $event->get_published_date() );
    $newEvent->setUpdated( $event->get_updated_date() );
	  $newEvent->setDescription( $event->get_description() );

    $this->setTags($essFeed, $event, $newEvent);
    $this->setCategories($essFeed, $event, $newEvent);
    $this->setDates($essFeed, $event, $newEvent);
    $this->setPlaces($essFeed, $event, $newEvent);
    $this->setPrice($essFeed, $event, $newEvent);
    $this->setPeople($essFeed, $event, $newEvent);
    $this->setImages($essFeed, $event, $newEvent);
    return $newEvent;

    // NOT Implemented
    //$newEvent->setAccess(( ( intval( $EM_Event->event_private ) === 1 )? EssDTD::ACCESS_PRIVATE : EssDTD::ACCESS_PUBLIC ) );

    // not sure if it is necessery
    // -- encode a unic ID base 
    // on the host name + event_id (e.g. www.sample.com:123)
		//$newEvent->setId( ( ( strlen( @$_SERVER[ 'SERVER_NAME' ] ) > 0 )? $_SERVER[ 'SERVER_NAME' ] : @$_SERVER[ 'HTTP_HOST' ] ) . ":" . $EM_Event->event_id );

  }

  private function setTags($essFeed, $event, $newEvent)
  {
		$tags = $event->get_tags();

    if(empty( $tags ))
    {
      return;
    }
		
    $strTags = array();
 		foreach ( $tags as $tag )
    {
      array_push( $strTags, $tag->get_name() );
    }

    $newEvent->setTags( $strTags );
  }

  private function setCategories($essFeed, $event, $newEvent)
  {
		$cats = $event->get_categories();
    if(empty( $cats ))
    {
      return;
    }

    $cat_duplicates_ = array();
    $category_type = ESSFeedBuilder::DEFAULT_CATEGORY_TYPE; 

 		foreach ( $cats as $cat )
    {
      if ( in_array( strtolower( $cat->get_slug() ), $cat_duplicates_ ) == TRUE )
      {
        continue;
      }

      $newEvent->addCategory( $category_type, 
                              array(
                               'name'  => $cat->get_name(),
                               'id'  => $cat->get_slug()));

      $cat_duplicates_[] = strtolower( $cat->get_slug() );
    }
	}	

  private function setDates($essFeed, $event, $newEvent)
  {
    $event_start = NULL;
		if ( empty( $event->get_start_date() ))
    {
      return;
    }

    if ( empty( $event->get_end_date() ))
    {
      return;
    }
    

    // for now we anly support Standalone
    // ( $EM_Event->recurrence <= 0 )
    $this->setDatesStandalone($essFeed, $event, $newEvent);
    
    //  setDatesRecurcive($essFeed, $event, $newEvent);
    
  }

  private function setDatesStandalone($essFeed, $event, $newEvent)
  {
    $event_start = $event->get_start_date();
    $event_stop  = $event->get_end_date();

    // number of seconds between two dates
    $duration_s = FeedValidator::getDateDiff( 's', 
                                     $event_start, 
                                     $event_stop ); 
    $newEvent->addDate( 'standalone', 'hour', 
                        NULL,NULL,NULL,NULL, 
                        array(
                          // Must be a unique name per event
	                        'name' => __('Date','em-ess'), 
	                        'start'     => $event_start,
	                        'duration'	=> ( ( $duration_s > 0 ) ? $duration_s / 60 / 60 : 0 )
	                     ));
  }

  private function setDatesRecurcive($essFeed, $event, $newEvent)
  {
    $event_start = $event->get_start_date();
    $event_stop  = $event->get_end_date();
    $interval = intval( $event->get_repeat_interval() );

    $u = $event->get_repeat_frequency();
    $event_unit = ( ( $u == 'daily'   )? 'day'   :
		  					  ( ( $u == 'weekly'  )? 'week'  :
								  ( ( $u == 'monthly' )? 'month' :
						  	  ( ( $u == 'yearly'  )? 'year'  :
											     'hour'))));

    switch ( $event_unit )
    {
      default:
      case 'day': $limit = FeedValidator::getDateDiff('d',$event_start, $event_stop ); break; // number of days
      case 'hour'	: $limit = FeedValidator::getDateDiff( 'h',    $event_start, $event_stop ); break; // number of hours
      case 'week'	: $limit = FeedValidator::getDateDiff( 'ww',   $event_start, $event_stop ); break; // number of weeks
      case 'month': $limit = FeedValidator::getDateDiff( 'm',    $event_start, $event_stop ); break; // number of months
      case 'year'	: $limit = FeedValidator::getDateDiff( 'yyyy', $event_start, $event_stop ); break; // number of years
    }

    $d = 0; // intval( $EM_Event->recurrence_byday );
    $selected_day = ( ( $event_unit == 'year' || $event_unit == 'month' || $event_unit == 'week' )?
										( ( $d == 0 )? 'sunday' :
										( ( $d == 1 )? 'monday' :
										( ( $d == 2 )? 'tuesday' :
										( ( $d == 3 )? 'wednesday' :
										( ( $d == 4 )? 'thursday' :
										( ( $d == 5 )? 'friday' :
										( ( $d == 6 )? 'saturday' :
												 	   ''
                    ))))))) : '' );

    $w = intval( $EM_Event->recurrence_byweekno );
    $selected_week = ( ( $event_unit == 'month' )?
                    ( ( $w == -1 )? 'last' 	 :
										( ( $w == 1  )? 'first'  :
										( ( $w == 2  )? 'second' :
								 		( ( $w == 3  )? 'third'  :
										( ( $w == 4  )? 'fourth' :
										 				 	''
                    ))))) : '' );

    $newEvent->addDate( 'recurrent',
                        $event_unit,
                        $limit,
                        $interval,
                        $selected_day,
                        $selected_week,
      array(
        'name' 		=> sprintf( __( 'Date: %s', 'em-ess'), $event_start ),
        'start'		=> $event_start,
        'duration'	=> 0 // information lost...
        ));
	}

  private function setPlaces($essFeed, $event, $newEvent)
  {
    $location = $event->get_location();
		if ( empty ($location) ) 
    {
      return;
    } 
    $wpLocHelper = new WPLocationHelper();

		$name = $location->get_name();
		$address = $wpLocHelper->get_address($location);
    $countrycode = $location->get_country_code();

		if ( empty ($name) ) 
    {
      return;
    } 
    
    $newEvent->addPlace( 'fixed', 
                         null, 
       array('name' => $name,
          'latitude'  => '',
          'longitude' => '',
          'address'   => ((strlen($address)>0) ? $address : $name),
          'city' => $location->get_city(),
          'zip' => $location->get_zip(),
          'state' => $location->get_state(),
          'state_code' => $location->get_state(),
          'country'	=> FeedValidator::$COUNTRIES_[ strtoupper($countrycode) ],
          'country_code' => ((strtolower($countrycode) == 'xe' ) ?'':$countrycode)
          ));
  }
	  
  // TODO
  private function setPrice($essFeed, $event, $newEvent)
  {
    $price = $event->get_event_cost();
    //$newEvent->addPrice( 'standalone', $price, NULL, NULL, NULL, NULL, NULL, array(
		//				'name'		=> __( 'Free', 'em-ess' ),
		//				'currency' 	=> get_option( 'ess_feed_currency', ESS_Database::DEFAULT_CURRENCY ),
		//				'value'		=> 0
		//			));
    //
  }

  private function setPeople($essFeed, $event, $newEvent)
  {
    $name = $event->get_contact_name();
    if(empty($name))
    {
      return;
    }

    $uri = $event->get_contact_website();
   
    if ( !FeedValidator::isValidURL( $uri ) )
    {
      $uri = 'https://'. $uri;
    }
    
    if ( !FeedValidator::isValidURL( $uri ) )
    {
      $uri = null;
    }

    $email = $event->get_contact_email();
    if ( !FeedValidator::isValidEmail( $email ) )
    {
      $email = null;
    }
    $newEvent->addPeople( 'organizer', array(
      'name' => $name,
      'email' => $email,
      'phone' => $event->get_contact_phone(),
      'uri' => $uri));
  }

  // Export images
  private function setImages($essFeed, $event, $newEvent)
  {
    $images = array();
    $media_url = $event->get_event_image_url();

    $media_alt = $event->get_event_image_alt();
    if( empty ( $media_alt ) )
    {
      $media_alt = 'alt';
    }

    if ( FeedValidator::isValidURL( $media_url ) )
    {
      array_push( $images, array( 'name' => $media_alt,  
                                  'uri' => $media_url ) );
    }
    
    if ( $images == NULL)
    {
      return;
    }
    
    if ( count( $images ) == 0 )
    {
      return;
    }
		
    $images = array_map( "unserialize", 
                         array_unique( array_map( "serialize", $images) ) 
                       );
    $duplicates_ = array();
    foreach ( $images as $i => $image )
    {
      if ( !in_array( $image['uri'], $duplicates_ ) )
      {
        $newEvent->addMedia( 'image', 
           array('name' => ( ( strlen( $image['name'] ) > 0 )? sprintf( __('Image %d', 'em-ess'), $i ) : $image['name'] ),  'uri' => $image['uri'] ) );
        array_push( $duplicates_, $image['uri'] );
      }
    }
  }
}
