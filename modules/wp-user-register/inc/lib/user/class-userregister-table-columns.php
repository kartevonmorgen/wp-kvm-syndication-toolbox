<?php

class UserRegisterTableColumns 
{
  public function setup($loader)
  {
    $loader->add_filter( 'manage_edit-urpost_sortable_columns', $this, 'sort_column', 10, 1 );
    $loader->add_filter( 'manage_urpost_posts_columns', $this, 'modify_urpost_table', 10, 1 );
    $loader->add_action( 'manage_urpost_posts_custom_column', $this, 'modify_urpost_table_row', 10, 2 );
  }

  function modify_urpost_table( $column )
  {
    $column['position'] = 'Position';
    $column['type'] = 'Type';
    return $column;
  }

  function modify_urpost_table_row( $column_name, $post_id)
  {
    if ('position' == $column_name) 
    {
      echo '' . get_post_meta($post_id, 'urpost_position', true);
    }

    if ('type' == $column_name) 
    {
      $value = '' . get_post_meta($post_id, 'urpost_typeid', true);
      if($value == 'text')
      {
        echo 'Description';
      }
      else
      {
        echo 'Field';
      }
    }
  }

  function sort_column( $columns ) 
  {
    $columns['position'] = 'position';

    //To make a column 'un-sortable' remove it from the array
    unset($columns['date']);

     return $columns;
   } 
}
