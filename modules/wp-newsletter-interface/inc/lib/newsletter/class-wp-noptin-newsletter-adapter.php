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

    $loader->add_filter('noptin_default_newsletter_body',
                        $this,
                        'default_newsletter_body',
                        10,
                        1);

    $loader->add_filter('noptin_subscriber_newsletter_recipients',
                        $this, 
                        'recipients_list', 
                        10, 
                        2);
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

    $fmt = new IntlDateFormatter(
      'de-DE',
      IntlDateFormatter::FULL,
      IntlDateFormatter::FULL,
      wp_timezone_string(),
      IntlDateFormatter::GREGORIAN,
      'eeee dd MMMM');
    $fmt2 = new IntlDateFormatter(
      'de-DE',
      IntlDateFormatter::FULL,
      IntlDateFormatter::FULL,
      wp_timezone_string(),
      IntlDateFormatter::GREGORIAN,
      'HH:mm');

    $eventsPart = '';
    foreach($events as $event)
    {
      $startdate = $fmt->format( 
                         strtotime( $event->get_start_date()));
      $enddate = $fmt->format(  
                       strtotime( $event->get_end_date()));
      $starttime = $fmt2->format(   
                         strtotime( $event->get_start_date()));
      $endtime = $fmt2->format(   
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

  public function get_newsletterlists_to_send_to()
  {
    $result = array();
    $nl_posts = $this->get_newsletterlist_fields();
    foreach($nl_posts as $nl_post)
    {
      $id = $nl_post->ID;
      $v = get_post_meta($id, 'newsletterlist_sendto', true);
      if($v === 'on')
      {
        array_push($result, $nl_post);
      }
    }
    return $result;
  }

  public function recipients_list($unique, $campaign)
  {
    $new_unique = array();
    $lists = $this->get_newsletterlists_to_send_to();
    log_noptin_message(' Vorbereiten Sendenlist für  ' . $campaign->get_subject()); 
    foreach($unique as $subscriber_id)
    {
      if($this->is_in_lists_to_send_to($lists, $subscriber_id))
      {
        $subscriber = noptin_get_subscriber($subscriber_id);
        log_noptin_message(' Abonnent an Sendlist hinzufügen ' . $subscriber->get_name() . ' (' . $subscriber->get_email() . ')'); 
        array_push($new_unique, $subscriber_id);
      }
    }
    return $new_unique;
  }

  private function is_in_lists_to_send_to($lists, $subscriber_id)
  {
    // Get the Lists in which the Subscriber is abboniert
    $lists_for_subscriber = get_metadata('noptin_subscriber', $subscriber_id, 'newsletterlists');
    foreach($lists_for_subscriber as $id)
    {
      foreach($lists as $nl_post)
      {
        if($nl_post->ID == $id)
        {
          return true;
        }
      }
    }
    return false;
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

  public function remove_newsletter_list()
  {
        echo '<p>REmove</p>';
    $newcustomfields = array();
    $customfields = get_noptin_option( 'custom_fields' );
    if($customfields == null)
    {
      return;
    }

    foreach($customfields as $customfield)
    {
      if('newsletterlists' !== 
         $customfield['merge_tag'])
      {
        echo '<p>Add</p>';
        array_push($newcustomfields, $customfield);

      }
    }
    update_noptin_option('custom_fields', $newcustomfields);
  }

  public function update_newsletter_list()
  {
    $options = '';

    $nl_posts = $this->get_newsletterlist_fields();
    foreach($nl_posts as $nl_post)
    {
      if($nl_post->post_status == 'publish')
      {
        $options .= '' . $nl_post->ID;
        $options .= '|';
        $options .= $nl_post->post_title;
        $options .= PHP_EOL;
      }
    }
    
    $customfields = get_noptin_option( 'custom_fields' );
    $newcustomfields = array();
    if($customfields == null)
    {
      $customfields = array();
    }

    $newsletterlists_customfield = null;
    foreach($customfields as $customfield)
    {
      if('newsletterlists' == 
         $customfield['merge_tag'])
      {
        $customfield['options'] = $options;
        $newsletterlists_customfield = $customfield;
      }
      array_push($newcustomfields, $customfield);
    }

    if(empty($newsletterlists_customfield))
    {
      $newsletterlists_customfield = array(
        'type' => 'multi_checkbox',
        'merge_tag' => 'newsletterlists',
        'label' => 'Newsletter Lists',
        'visible' => '1',
        'subs_table' => '1',
        'predefined' => '1',
        'options' => $options);
      array_push($newcustomfields, $newsletterlists_customfield);
    }

    update_noptin_option('custom_fields', $newcustomfields);
  }

  
}
