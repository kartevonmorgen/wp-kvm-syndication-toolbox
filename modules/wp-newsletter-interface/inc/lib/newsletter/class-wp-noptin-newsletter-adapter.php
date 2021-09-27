<?php
/**
 * WPNoptinNewsletterAdapter
 * used to implement a generic Adapter
 * for a Wordpress Newsletter Plugin
 *
 * @author    Sjoerd Takken
 * @copyright No Copyright.
 * @license   GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPNoptinNewsletterAdapter extends WPNewsletterAdapter
{
  private $_newsletterlist_fields = null;

  /** 
   * Add the listes to Noptin
   */
  public function setup($loader)
  {
    // Add tags in the Email Template for Events
    $loader->add_filter('noptin_mailer_default_merge_tags', 
                        $this,
                        'mailer_default_merge_tags',
                        10,
                        2);

    $loader->add_filter('noptin_default_newsletter_body',
                        $this,
                        'default_newsletter_body',
                        10,
                        1);

    $loader->add_action('add_meta_boxes_noptin_newsletters', 
                        $this, 
                        'add_newsletter_metaboxes');

    // Save Meta Boxes in Newsletter Caompaign
    $loader->add_filter('noptin_save_newsletter_campaign_details',
                        $this, 
                        'save_campaign', 
                        10, 
                        2);

    $loader->add_filter('noptin_background_mailer_subscriber_query',
                        $this, 
                        'query', 
                        10, 
                        2);

    $loader->add_filter('manage_noptin_newsletters_sortable_table_columns',
                        $this,
                        'sortable_columns',
                        10,
                        1);

    $loader->add_action( 'noptin_pre_get_subscribers', 
                         $this, 
                         'meta_orderby',
                         10,
                         1 );
  }

  public function default_newsletter_body($body)
  {
    if($this->get_current_module()->is_send_new_email_body_with_events())
    {
      $eventsPart = $this->get_mail_events_part();
      $body = '';
      $body .= '<p>Hallo [[first_name]]</p>';
      $body .= '<p>Hier gibt es ein Übersicht von die';
      $body .= ' kommende Veranstaltungen von ';
      $body .= '<i>[[noptin_company]]</i></p>';
      $body .= $eventsPart;
      $body .= '<p>Viel spaß</p>';
    }
    return $body;
  }

  public function mailer_default_merge_tags($default_merge_tags,
                                            $mailer)
  {
    $eventsPart = $this->get_mail_events_part();
    if(empty($eventsPart))
    {
      return $default_merge_tags;
    }
    $default_merge_tags['events'] = $eventsPart;
    return $default_merge_tags;
  }

  private function get_mail_events_part()
  {
    $mc = WPModuleConfiguration::get_instance();
    if(!$mc->is_module_enabled('wp-events-interface'))
    {
      return '';
    }

    $module = $this->get_current_module();
    $cat = $module->get_selected_category();
    if(empty($cat))
    {
      $cat = null;
    }
    $days = $module->get_number_of_days();

    $eiModule = $mc->get_module('wp-events-interface');

    // return EICalendarEvent[]
    $events = $eiModule->get_events_by_cat($cat, $days);

    setlocale(LC_TIME, "de_DE.utf8");
    $eventsPart = '';
    foreach($events as $event)
    {
      $startdate = strftime( '%A %e %B', 
                         strtotime( $event->get_start_date()));
      $enddate = strftime( '%A %e %B', 
                       strtotime( $event->get_end_date()));
      $starttime = strftime( '%R',  
                         strtotime( $event->get_start_date()));
      $endtime = strftime( '%R',  
                       strtotime( $event->get_end_date()));
      if($startdate == $enddate)
      {
        $date = $startdate;
      }
      else
      {
        $date = $startdate . ' - ' . $enddate;
      }
      $time = $starttime . ' - ' . $endtime;

      $name = '';
      $address = '';
      $city = '';
      $location = $event->get_location();
      if(!empty($location))
      {
        $name = $location->get_name();
        $wpLocationHelper = new WPLocationHelper();
        $address = $wpLocationHelper->get_address(
                                        $location);
        $city = $location->get_city();
      }

      $eventsPart .= '<p><b>';
      $eventsPart .= $date;
      $eventsPart .= '</b>&nbsp;';
      $eventsPart .= '<a href="';
      $eventsPart .= $event->get_link();
      $eventsPart .= '">';
      $eventsPart .= $event->get_title();
      $eventsPart .= '</a>&nbsp;';
      $eventsPart .= $time;
      $eventsPart .= '<br>';
      $eventsPart .= $event->generate_excerpt();
      $eventsPart .= '<br>Ort:&nbsp;<b>';
      $eventsPart .= $name;

      if(!empty($address))
      {
        $eventsPart .= ', ';
        $eventsPart .= $address;
      }
      if(!empty($city))
      {
        $eventsPart .= ', ';
        $eventsPart .= $city;
      }
      $eventsPart .= '</b><br>';
      $eventsPart .= '</p>';
    }

    return $eventsPart;
  }

  public function add_newsletter_metaboxes($campaign)
  {
    add_meta_box(
      'noptin_newsletter_preview_text',
      __('List','newsletter-optin-box'),
      array( $this, 'render_newsletter_metabox' ),
      'noptin_page_noptin-newsletter',
      'side',
      'low',
      'lists');
  }

  private function get_newsletterlist_fields()
  {
    if($this->_newsletterlist_fields !== null)
    {
      return $this->_newsletterlist_fields;
    }
    $fields = array();
    $args = array(
       'numberposts' => 20,
       'post_type'   => 'newsletterlist');
    $nl_posts = get_posts($args);

    $this->_newsletterlist_fields = $nl_posts;
    return $this->_newsletterlist_fields;

  }

  public function render_newsletter_metabox( $campaign, 
                                             $metabox ) 
  {
    foreach($this->get_newsletterlist_fields() as $nl_post)
    {
      $id = 'newsletterlist_' . $nl_post->ID;
      $list = is_object( $campaign ) ? get_post_meta( $campaign->ID, $id, true ) : '';
      $checked = '';
	    if($list) 
      { 
        $checked = ' checked="checked" '; 
      }
      echo '<p>' . $nl_post->post_title . '</p>';
      echo "<input ".$checked." id='$id' name='$id' type='checkbox' />";
    }
  }

  public function save_campaign($post, $data)
  {
    if(array_key_exists('campaign_id', $data))
    {
      $post['ID'] = $data['campaign_id'];
    }

    foreach($this->get_newsletterlist_fields() as $nl_post)
    {
      $id = 'newsletterlist_' . $nl_post->ID;
      if(array_key_exists($id, $data))
      {
        $post['meta_input'][$id] = 'active';
      }
      else
      {
        $post['meta_input'][$id] = false;
      }
    }

    return $post;
  }

  public function query($subscriber_query, $item)
  {
    $campaign_id = $item['campaign_id'];
    $list_query = array('relation' => 'OR');
    $nl_posts = $this->get_newsletterlist_fields();

    foreach($nl_posts as $nl_post)
    {
      $id = 'newsletterlist_' . $nl_post->ID;
      $list = get_post_meta( $campaign_id, $id, true );
      if($list == 'active')
      {
        log_noptin_message(' Campaign ' . $campaign_id . 
                           ' send to list: ' . $id);
        if($list)
        {
          $list_query[] = array('key' => $id,
                                'value' => 0,
                                'compare' => '>');
        }
      }
    }

    if(count($list_query) < 2)
    {
      $list_query[] = array('key' => 'newsletterlist_none',
                            'value' => 0,
                            'compare' => '>');
    }
    
    $subscriber_query['meta_query'][] = array($list_query);
    // log_noptin_message(' ITEM ' . print_r($item, true));
    // log_noptin_message(' QUERY ' . 
    //                    print_r($subscriber_query, true));
    return $subscriber_query;
  }

  public function get_id()
  {
    return 'noptin-newsletter';
  }

  public function get_description()
  {
    return 'Noptin Newsletter';
  }

  public function is_plugin_available() 
  {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    return is_plugin_active( 'newsletter-optin-box/noptin.php' );
  }

  public function has_newsletter_list()
  {
    return true;
  }

  public function update_newsletter_list($nl_post)
  {
    $found_cf = null;
    $customfields = get_noptin_option( 'custom_fields' );
    $newcustomfields = array();
    // echo 'UPDATE' . $nl_post->ID;
    // echo 'STATE' . $nl_post->post-status;
    foreach($customfields as $customfield)
    {
      if('newsletterlist_' . $nl_post->ID == 
         $customfield['merge_tag'])
      {
        $customfield['label'] = $nl_post->post_title;
        $found_cf = $customfield;
        if($nl_post->post_status == 'publish')
        {
          array_push($newcustomfields, $customfield);
        }
      }
      else
      {
        array_push($newcustomfields, $customfield);
      }
    }

    if(empty($found_cf) && 
       $nl_post->post_status == 'publish')
    {
      $customfield = array(
        'type' => 'checkbox',
        'merge_tag' => 'newsletterlist_' . $nl_post->ID,
        'label' => $nl_post->post_title,
        'visible' => '1',
        'subs_table' => '1',
        'predefined' => '1');
      array_push($newcustomfields, $customfield);
    }
    update_noptin_option('custom_fields', $newcustomfields);
  }
  
  public function sortable_columns($sortable)
  {
    foreach($this->get_newsletterlist_fields() as $nl_post)
    {
      $id = 'newsletterlist_' . $nl_post->ID;
      $sortable[$id] = array($id, false);
    }
    return $sortable;
  }

  public function meta_orderby( $query ) 
  {
    if( ! is_admin() )
    {
      return;
    }

    $orderby = $query->get( 'orderby');
    $util = new PHPStringUtil();
    if(!$util->startsWith($orderby, 'newsletterlist_'))
    {
      return;
    }

    foreach($this->get_newsletterlist_fields() as $nl_post)
    {
      $id = 'newsletterlist_' . $nl_post->ID;
      if( $id == $orderby ) 
      {
        $query->set('meta_key', $id);
        $query->set('orderby','meta_value');
        return;
      }
    }
  }
}
