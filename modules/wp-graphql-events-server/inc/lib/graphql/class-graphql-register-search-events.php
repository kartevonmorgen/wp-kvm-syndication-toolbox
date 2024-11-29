<?php

class GraphQLRegisterSearchEvents extends WPAbstractModuleProvider
{
  public function setup($loader)
  {
    $loader->add_action('graphql_register_types', 
                        $this, 'register_schema' );

  }

  function register_schema()
  {
    $this->register_scalars();
    $this->register_config_type();
    $this->register_event_type();
    $this->register_events_type();


    register_graphql_field( 'RootQuery', 'config', [
      'type' => 'ConfigType',
      'resolve' => function() {
        $module = $this->get_current_module();
        return [
         'name' => $module->get_graphql_config_name(),
         'description' => $module->get_graphql_config_description(),
        ];
      }
    ] );

    register_graphql_field( 'RootQuery', 'searchEvents', [
      'type' => 'EventsType',
      'args' => [
        'beginsOn' => [ 'type' => 'DateTime' ],
        'category' => [ 'type' => 'String' ]
      ],
      'resolve' => function( $source, $args, $context, $info ) 
      {
        $module = $this->get_current_module();
        $parent = $module->get_parent_module();

        $cat = null;
        if(array_key_exists('category', $args))
        {
          $cat = $args['category'];
        }
        $fromDate = null;
        if(array_key_exists('beginsOn', $args))
        {
          $fromDate = $args['beginsOn'];
        }
        
        $ei_events = $parent->get_events_by_cat($cat, 
                                                null, 
                                                $fromDate );
        $events = array();
        $locHelper = new WPLocationHelper();

        foreach($ei_events as $ei_event)
        {
          $location = $ei_event->get_location();

          $ei_tags = $ei_event->get_tags();
          $tags = array();
          foreach($ei_tags as $ei_tag)
          {
            $tag = 
              [ 
                'title' => $ei_tag->get_name(),
                'slug' => $ei_tag->get_slug()
              ];
            array_push($tags, $tag);
          }

          $ei_cats = $ei_event->get_categories();
          $cats = array();
          foreach($ei_cats as $ei_cat)
          {
            $cat = 
              [ 
                'title' => $ei_cat->get_name(),
                'slug' => $ei_cat->get_slug()
              ];
            array_push($cats, $cat);
          }

          $event =
            [
              'uuid' => $ei_event->get_uid(),
              'title' => $ei_event->get_title(),
              'slug' => $ei_event->get_slug(),
              'description' => $ei_event->get_description(),
              'local' => true,
              'url' => $ei_event->get_link(),
              'beginsOn' => $ei_event->get_start_date(),
              'endsOn' => $ei_event->get_end_date(),
              'status' => $ei_event->get_post_status() == 'publish' ? 'confirmed' : 'tentative',
              'physicalAddress' =>
                [
                  'description' => $location->get_name(),
                  'street' => $location->get_street() . ' ' . $location->get_streetnumber(),
                  'postalCode' => $location->get_zip(),
                  'locality' => $location->get_city(),
                  'region' => $location->get_state(),
                  'country' => $location->get_country_code(),
                  'geom' => $location->get_lon() . ';' . $location->get_lat(),
                ],
              'attributedTo' =>
                [
                  'name' => $ei_event->get_contact_name(),
                  'preferedUsername' => $ei_event->get_contact_name(),
                ],
              'options' => 
                [
                  'isOnline' => $locHelper->is_location_empty($location) 
                  && !empty($ei_event->get_link()),

                ],
              'onlineAddress' => $ei_event->get_contact_website(),
              'phoneAddress' => $ei_event->get_contact_phone(),
              'updatedAt' => $ei_event->get_updated_date(),
              'tags' => $tags,
              'excategories' => $cats,
            ];
          array_push($events, $event);
        }


        return [ 
          'fromDate' => $args['beginsOn'],
          'elements' => $events,
          'total' => count($events)
        ];
      }
    ] );

  }

  function register_config_type()
  {
    register_graphql_object_type( 'ConfigType', [
      'description' => 'ConfigType for info',
      'fields' => [
         'name' => [
           'type' => 'String',
           'description' => 'Name of the Wordpress Instance',
         ],
         'description' => [
           'type' => 'String',
           'description' => 'Description of the Wordpress Instance',
         ],
      ],
    ] );
  }

  function register_events_type()
  {
    register_graphql_object_type( 'EventsType', [
      'description' => 'EventsType',
      'fields' => [
         'fromDate' => [
           'type' => 'DateTime',
           'description' => 'From Date',
         ],
         'total' => [
           'type' => 'Int',
           'description' => 'Total elements',
         ],
         'elements' => [
           'type' => [ 'list_of' => 'EventType',
           'description' => 'List of Events',
           ],
         ],
      ],
    ]);
  }

  function register_event_type()
  {
    
    $this->register_event_status_type();
    $this->register_address_type();
    $this->register_actor_type();
    $this->register_eventoptions_type();
    $this->register_tag_type();
    $this->register_category_type();


    register_graphql_object_type( 'EventType', [
      'description' => 'EventType for info',
      'fields' => [
         'uuid' => [
           'type' => 'String',
           'description' => 'The Event UUID',
         ],
         'url' => [
           'type' => 'String',
           'description' => 'The ActivityPub Event URL',
         ],
         'title' => [
           'type' => 'String',
           'description' => 'The event title',
         ],
         'slug' => [
           'type' => 'String',
           'description' => 'The event description slug',
         ],
         'description' => [
           'type' => 'String',
           'description' => 'The event description',
         ],
         'local' => [
           'type' => 'Boolean',
           'description' => 'If the Event is on this instance',
         ],
         'beginsOn' => [
           'type' => 'DateTime',
           'description' => 'Datetime for when the event begins',
         ],
         'endsOn' => [
           'type' => 'DateTime',
           'description' => 'Datetime for when the event ends',
         ],
         'status' => [
           'type' => 'EventStatus',
           'description' => 'Status of the event',
         ],
         'physicalAddress' => [
           'type' => 'Address',
           'description' => 'Address of the event',
         ],
         'attributedTo' => [
           'type' => 'Actor',
           'description' => 'Actor of the event',
         ],
         'options' => [
           'type' => 'EventOptions',
           'description' => 'Options of the event',
         ],
         'onlineAddress' => [
           'type' => 'String',
           'description' => 'The online address (Website url)',
         ],
         'phoneAddress' => [
           'type' => 'String',
           'description' => 'The phonenumber ',
         ],
         'updatedAt' => [
           'type' => 'DateTime',
           'description' => 'Last update date ',
         ],
         'tags' => [
           'type' => ['list_of' => 'EventTag'],
           'description' => 'List of eventtags ',
         ],
         'excategories' => [
           'type' => ['list_of' => 'ExEventCategory'],
           'description' => 'Extended Field: List of eventcategories ',
         ],
      ],
    ] );
  }

  function register_event_status_type()
  {
    register_graphql_enum_type( 'EventStatus', [
      'description' => 'Status of the event',
        'values' => [
          'CONFIRMED' => [
            'value' => 'confirmed'
          ],
          'TENTATIVE' => [
            'value' => 'tentative'
          ],
          'CANCELLED' => [
            'value' => 'cancelled'
          ],
        ],
    ] );
  }

  function register_address_type()
  {
    register_graphql_object_type( 'Address', [
      'description' => 'An addresss object',
      'fields' => [
         'geom' => [
           'type' => 'String',
           'description' => 'The geocoordinates for the point where this address is (lon;lat)',
         ],
         'street' => [
           'type' => 'String',
           'description' => 'The addresss street name (with number)',
         ],
         'locality' => [
           'type' => 'String',
           'description' => 'The addresss locality',
         ],
         'postalCode' => [
           'type' => 'String',
           'description' => 'The addresss postal code',
         ],
         'region' => [
           'type' => 'String',
           'description' => 'The addresss region',
         ],
         'country' => [
           'type' => 'String',
           'description' => 'The addresss country',
         ],
         'description' => [
           'type' => 'String',
           'description' => 'The addresss description',
         ],
      ],
    ] );
  }

  function register_actor_type()
  {
    register_graphql_object_type( 'Actor', [
      'description' => 'An actor object',
      'fields' => [
         'name' => [
           'type' => 'String',
           'description' => 'The Actor displayed name',
         ],
         'preferredUsername' => [
           'type' => 'String',
           'description' => 'The Actor preferred username',
         ],
      ],
    ]);
  }

  function register_eventoptions_type()
  {
    register_graphql_object_type( 'EventOptions', [
      'description' => 'An EventOptions object',
      'fields' => [
         'isOnline' => [
           'type' => 'Boolean',
           'description' => 'Whether the event is fully online',
         ],
      ],
    ]);
  }

  function register_tag_type()
  {
    register_graphql_object_type( 'EventTag', [
      'description' => 'An tag object',
      'fields' => [
         'id' => [
           'type' => 'String',
           'description' => 'The tag id',
         ],
         'slug' => [
           'type' => 'String',
           'description' => 'The tag slug',
         ],
         'title' => [
           'type' => 'String',
           'description' => 'The tag title',
         ],
      ],
    ]);
  }

  function register_category_type()
  {
    register_graphql_object_type( 'ExEventCategory', [
      'description' => 'An category object',
      'fields' => [
         'id' => [
           'type' => 'String',
           'description' => 'The tag id',
         ],
         'slug' => [
           'type' => 'String',
           'description' => 'The tag slug',
         ],
         'title' => [
           'type' => 'String',
           'description' => 'The tag title',
         ],
      ],
    ]);
  }

  public function register_scalars()
  {
    register_graphql_scalar(
      'DateTime',
      [
        'description' => 
          __( 'A date-time string at UTC, such as 2007-12-03T10:15:30Z, ' .
              'compliant with the `date-time` format outlined in section 5.6 of ' .
              'the RFC 3339 profile of the ISO 8601 standard for representation ' .
              'of dates and times using the Gregorian calendar.', 'wp-graphql' ),
        'serialize' => function( $value ) 
        {
          return $value;
        },
        'parseValue' => function( $value ) 
        {
			    return $value;
		    },
        'parseLiteral' => function( $valueNode, array $variables = null ) 
        {
          // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException 
          // to benefit from GraphQL
			    // error location in query:
          if (!$valueNode instanceof \GraphQL\Language\AST\StringValueNode) 
          {
            throw new Error('Query error: Can only parse strings got: ' . 
              $valueNode->kind, [$valueNode]);
          }
          return $valueNode->value;
        }
      ]);
  }

}

?>
