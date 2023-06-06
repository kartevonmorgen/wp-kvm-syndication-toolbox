<?php

/** 
 * UIMetaboxOpeningHoursField
 * implementation of the UIMetaboxField with a OpeningHours 
 * select box, stored accoring to 
 * https://wiki.openstreetmap.org/wiki/Key:opening_hours
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UIMetaboxOpeningHoursField extends UIMetaboxField
{
  function show_value($post)
  {
    $id = $this->get_id();
    $title = $this->get_title();
    $description = $this->get_description();
    $disabled_text = $this->get_disabled_text();
    $oh = $this->get_value($post);
    echo "<p>$title</p>";
    $this->show_days($id, $oh);
    if(!empty($description))
    {
      echo "<br/><span style='font-size:0,8 em'><em>$description</em></span>";
    }
  }

  private function show_days($id_prefix, $oh)
  {
    foreach($oh->get_days() as $day)
    {
      $this->show_day($day, 
                      $id_prefix); 
    }
  }

  private function show_day($day, $id_prefix)
  {
    $day_type = $day->get_day_type();
    $title = $day_type->get_title();

    echo "<div style='padding-left:10px'><p><b>$title</b></p><p style='padding-left:20px'>";

    $timeranges = $day->get_all_timeranges();
    $marginleft = '20px';
    foreach( $timeranges as $timerange)
    {   
      $index = $timerange->get_index();
      echo "<div style='display:inline-block;margin-left:$marginleft'>";
      $marginleft = '50px';

      $id = $day->get_view_id_start_time($id_prefix, $index);
      $value = $timerange->get_start_time();
      echo "<label for='$id'>Von: </label><input style='width:100px' type='time' name='$id' value='$value'/>";

      $id = $day->get_view_id_end_time($id_prefix, $index);
      $value = $timerange->get_end_time();
      echo "<label for='$id'> bis: </label><input style='width:100px' type='time' name='$id' value='$value'/>";
      echo "</div>";

    }
    echo "</p></div>";
  }
    
  public function get_value($post)
  {
    $value = parent::get_value($post);
    $oh = new OpeningHours();
    $oh->set_key($value);

    return $oh;
  }
    
  public function save_value($post_id, $value = null)
  {
    $oh = $value;
    if(!empty($oh))
    {
      parent::save_value($post_id, $oh->calculate_key());
    }

    $id = $this->get_id();
    
    $oh = new OpeningHours();
    parent::save_value($post_id, $oh->extract_key_from_post($id));
  }
}
