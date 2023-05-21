<?php

class EntryExpirator
  extends WPAbstractModuleProvider
{
  public function get_type()
  {
    return $this->get_current_module()->get_type();
  }

  public function get_type_id()
  {
    return $this->get_type()->get_id();
  }

  public function setup($loader)
  {
    $loader->add_filter(
      'manage_' . $this->get_type_id() . '_posts_columns', 
      $this, 
      'expirator_add_column');
    $loader->add_action(
      'manage_' . $this->get_type_id() . '_posts_custom_column', 
      $this, 
      'expirator_show_value');
  }

  /**
   * Adds an 'Expires' column to the post display table.
   *
   */
  function expirator_add_column($columns)
  {
    $cid = $this->get_type_id() . '_expiration_date';
    $columns[$cid] = 'Ablaufdatum';
    return $columns;
  }

  /**
   * Fills the 'Expires' column of the post display table.
   *
   *
   */
  function expirator_show_value($column_name)
  {
    $cid_enabled = $this->get_type_id() . '_expiration_enabled';
    $cid = $this->get_type_id() . '_expiration_date';
    if ($column_name !== $cid) {
        return;
    }

    global $post;

    $id = $post->ID;
    $expirationDateEnabled = get_post_meta($id, $cid_enabled, true);
    $expirationDate = get_post_meta($id, $cid, true);
    if(!$expirationDateEnabled || empty($expirationDate))
    {
      $display = '-';
    }
    else
    {
      // ExpirationDate is saved as unixTime,
      // so we convert it to a readable local time.
      $dt = new DateTime(
        date( 'Y-m-d H:i:s', $expirationDate ),
        new DateTimeZone('UTC'));
      $localtimezone = wp_timezone();
      $dt->setTimezone( $localtimezone );
      $display = $dt->format( 'd.m.Y H:i' );
      //$display = '' . $expirationDate; 
    }

    ?>
    <div class="post-expire-col" data-id="<?php echo esc_attr($id); ?>">
        <?php echo esc_html($display); ?>
    </div> <?php
  }
}

?>
