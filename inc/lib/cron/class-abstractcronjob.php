<?php

/**
 * AbstractCronJob for basic functioionality for 
 * Cronjobs
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class AbstractCronJob extends WPAbstractModuleProvider 
{
  private $_cron_id;
  private $_recurrence;

    public function __construct($module, 
                                $cron_id, 
                                $recurrence = 'daily')
  {
    parent::__construct($module);
    $this->_cron_id = $cron_id;
    $this->_recurrence = $recurrence;
  }

  public function get_cron_id()
  {
    return $this->_cron_id;
  }

  public function get_recurrence()
  {
    return $this->_recurrence;
  }

  public function setup($loader)
  {
    $loader->add_action( $this->get_cron_id(),
                         $this,
                         'execute_cron');
  }

  /**
   * schedule the first event, if it is not already
   * scheduled before.
   * $recurrence: can be 'hourly', 'twicedaily', 'daily', 'weekly'
   */
  public function schedule()
  {
    $recurrence = $this->get_recurrence();
    if (!wp_next_scheduled ( $this->get_cron_id() ))
    {
       wp_schedule_event( time(), $recurrence, $this->get_cron_id() );
    }
  }

  /**
   * Stop the scheduled execution of the Cronjob
   */
  public function stop()
  {
    wp_clear_scheduled_hook( $this->get_cron_id() );
  }

  function execute_cron()
  {
    $this->execute();
  }

  public abstract function execute();
}
