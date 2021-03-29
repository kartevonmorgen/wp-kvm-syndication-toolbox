<?php

class MigrateUserUndInitiative
{
  public function migrate($ipost)
  {
    $orgpostid = $this->create_organisation($ipost);
    $orgpost = get_post($orgpostid);

    $this->migrate_field($orgpost, 'initiative_address', 
                                'organisation_address');
    $this->migrate_field($orgpost, 'initiative_zipcode',
                                'organisation_zipcode');
    $this->migrate_field($orgpost, 'initiative_city',
                                'organisation_city');
    $this->migrate_field($orgpost, 'initiative_lat',
                                'organisation_lat');
    $this->migrate_field($orgpost, 'initiative_lng',
                                'organisation_lng');
    $this->migrate_companytype($orgpost, 'initiative_company',
                                'organisation_type');
    $this->migrate_field($orgpost, 'initiative_kvm_id',
                                'organisation_kvm_id');

    $this->migrate_field($orgpost, 'first_name', 
                                'organisation_firstname');
    $this->migrate_field($orgpost, 'last_name', 
                                'organisation_lastname');
    $this->migrate_field($orgpost, 'dbem_phone', 
                                'organisation_phone');
    $this->migrate_field($orgpost, 'user_email', 
                                'organisation_email');
    $this->migrate_field($orgpost, 'user_url', 
                                'organisation_website');
  }

  private function create_organisation($ipost)
  {

    $post_title = $ipost->post_title;
    $post_name = $ipost->post_name;
    $post_author = $ipost->post_author;
    $post_content = $ipost->post_content;

    $orgpost = array(
      'comment_status' => 'closed',
      'post_author' => $post_author,
      'post_content' => $post_content,
      'post_title' => $post_title,
      'post_name' => $post_name,
      'post_status' => 'publish',
      'post_type' => 'organisation');
 
    $orgpost_id = wp_insert_post( $orgpost, true );
    echo '<p>Insert Organisation ' . $post_title . 
         '(orgpost_id: ' . $orgpost_id . ')</p>';
    // Insert the post into the database
    $this->copy_terms('category', $ipost->ID, $orgpost_id);
    $this->copy_terms('post_tag', $ipost->ID, $orgpost_id);
    return $orgpost_id;
  }

  private function copy_terms($tax, $ipost_id, $orgpost_id)
  {
    $terms = wp_get_object_terms($ipost_id, $tax);
    $termids = array();
    foreach($terms as $term)
    {
      array_push($termids, $term->term_id);
      echo '<p>Copy Term ' . $term->name . 
         ' to ' . $orgpost_id . ' for tax ' . $tax .'</p>';
    }
    wp_set_object_terms($orgpost_id, $termids, $tax);
  }

  private function migrate_field($post, 
                                 $id, 
                                 $new_id = null)
  {
    if(empty($new_id))
    {
      $new_id = $id;
    }
    $user_id = $post->post_author;
    $old_value = trim(get_post_meta($post->ID, $new_id, true));
    if(!empty($old_value))
    {
      echo '<p>Meta field ' . $new_id . ' is already filled ' .
           'with (' . $old_value . 
           ') so we do not update</p>';
      return;
    }
    $value = get_the_author_meta( $id, $user_id );
    if(empty($value))
    {
      echo '<p>Meta field for user (user.' . $id . ' )' . 
           ' is empty, so we do not update</p>';
      return;
    }

    echo '<p>Meta field (user.' . $id . ' -> organisation.' . 
         $new_id . ') filled with ' . $value . '</p>';
    update_post_meta($post->ID, $new_id, $value);
  }

  private function migrate_companytype($post,
                                       $id, 
                                       $new_id = null)
  {
    if(empty($new_id))
    {
      $new_id = $id;
    }
    $user_id = $post->post_author;
    $old_value = trim(get_post_meta($post->ID, $new_id, true));
    if(!empty($old_value))
    {
      echo '<p>Meta field ' . $new_id . ' is already filled ' .
           'with (' . $old_value . 
           ') so we do not update</p>';
      return;
    }
    $value = get_the_author_meta( $id, $user_id );
    if(empty($value))
    {
      $organisation_type = WPOrganisationType::INITIATIVE;
    }
    else
    {
      $organisation_type = WPOrganisationType::COMPANY;
    }
    echo '<p>Meta field (user.' . $id . ' -> organisation.' . 
         $new_id . ') filled with ' . $organisation_type . '</p>';
    update_post_meta($post->ID, $new_id, $organisation_type);
  }

}
