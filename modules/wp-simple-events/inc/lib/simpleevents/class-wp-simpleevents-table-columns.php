<?php

class WpSimpleEventsTableColumns
  extends WPAbstractModuleProvider
{
  private function get_type_id()
  {
    $module = $this->get_current_module();
    return $module->get_posttype();
  }

  public function setup($loader)
  {
    $loader->add_filter(
      'manage_' . $this->get_type_id() . '_posts_columns', 
      $this, 
      'startdate_add_column');
    $loader->add_action(
      'manage_' . $this->get_type_id() . '_posts_custom_column', 
      $this, 
      'startdate_show_value');
    $loader->add_filter(
      'manage_' . $this->get_type_id() . '_posts_columns', 
      $this, 
      'enddate_add_column');
    $loader->add_action(
      'manage_' . $this->get_type_id() . '_posts_custom_column', 
      $this, 
      'enddate_show_value');
  }

  /**
   * Adds an 'Start Date' column to the post display table.
   *
   */
  function startdate_add_column($columns)
  {
    $cid = $this->get_type_id() . '_start_date';
    $columns[$cid] = 'Startdatum';
    return $columns;
  }

  /**
   * Fills the 'Startdate' column of the post display table.
   *
   *
   */
  function startdate_show_value($column_name)
  {
    $cid = $this->get_type_id() . '_start_date';
    if ($column_name !== $cid) {
        return;
    }

    global $post;

    $id = $post->ID;
    $startDate = get_post_meta($id, $cid, true);
    if(empty($startDate))
    {
      $display = '-';
    }
    else
    {
      // StartDate is saved as unixTime,
      // so we convert it to a readable local time.
      $dt = new DateTime(
        date( 'Y-m-d H:i:s', $startDate ),
        new DateTimeZone('UTC'));
      $localtimezone = wp_timezone();
      $dt->setTimezone( $localtimezone );
      $display = $dt->format( 'd.m.Y H:i' );
    }

    ?>
    <div class="post-startdate-col" data-id="<?php echo esc_attr($id); ?>">
        <?php echo esc_html($display); ?>
    </div> <?php
  }

  /**
   * Adds an 'End Date' column to the post display table.
   *
   */
  function enddate_add_column($columns)
  {
    $cid = $this->get_type_id() . '_end_date';
    $columns[$cid] = 'Enddatum';
    return $columns;
  }

  /**
   * Fills the 'Enddate' column of the post display table.
   *
   *
   */
  function enddate_show_value($column_name)
  {
    $cid = $this->get_type_id() . '_end_date';
    if ($column_name !== $cid) {
        return;
    }

    global $post;

    $id = $post->ID;
    $endDate = get_post_meta($id, $cid, true);
    if(empty($endDate))
    {
      $display = '-';
    }
    else
    {
      // EndDate is saved as unixTime,
      // so we convert it to a readable local time.
      $dt = new DateTime(
        date( 'Y-m-d H:i:s', $endDate ),
        new DateTimeZone('UTC'));
      $localtimezone = wp_timezone();
      $dt->setTimezone( $localtimezone );
      $display = $dt->format( 'd.m.Y H:i' );
    }
    ?>
    <div class="post-enddate-col" data-id="<?php echo esc_attr($id); ?>">
        <?php echo esc_html($display); ?>
    </div> <?php
  }

}

?>
