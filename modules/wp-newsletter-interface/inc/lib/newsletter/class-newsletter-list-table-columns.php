<?php

class NewsletterListTableColumns extends WPAbstractModuleProvider
{
  public function setup($loader)
  {
    $loader->add_filter( 'manage_newsletterlist_posts_columns', $this, 'modify_newsletterlist_table', 10, 1 );
    $loader->add_action( 'manage_newsletterlist_posts_custom_column', $this, 'modify_newsletterlist_table_row', 10, 2 );
    /*
    $loader->add_action( 'quick_edit_custom_box',  
                          $this,
                          'edit_fields', 
                          10, 
                          2 );
    $loader->add_action( 'save_post', 
                         $this, 
                         'quick_edit_save' );
    $loader->add_action('admin_footer', $this, 'add_js');
     */

    $tableAction = new UIPostTableAction('activate_list',
                                         'Activate List',
                                         'Activate List', 
                                         'newsletterlist',
                                         'Newsletter List');
    $tableAction->set_postaction_listener(
       new WPNewsletterAction($this->get_current_module(), true) );
    $tableAction->setup($loader);

    $tableAction = new UIPostTableAction('deactivate_list',
                                         'Deactivate List',
                                         'Deactivate List', 
                                         'newsletterlist',
                                         'Newsletter List');
    $tableAction->set_postaction_listener(
       new WPNewsletterAction($this->get_current_module(), false) );
    $tableAction->setup($loader);
  }

  function modify_newsletterlist_table( $column )
  {
    $column['newsletterlist_sendto'] = 'Send Newsletter To this List';
    return $column;
  }

  function modify_newsletterlist_table_row( $column_name, $post_id)
  {
    if ('newsletterlist_sendto' == $column_name) 
    {
      $value = get_post_meta($post_id, 'newsletterlist_sendto', true);
      if($value === 'on')
      {
        echo '<b>Aktiviert</b>';
      }
      else
      {
        echo 'Deaktiviert';
      }
    }
  }

  function edit_fields( $column_name, $post_type )
  {
    switch( $column_name )
    {
      case 'newsletterlist_sendto': {
        ?><fieldset class="inline-edit-col-left">
            <div class="inline-edit-col">
              <label>
                 <input type="checkbox" name="newsletterlist_sendto">Send to this List
              </label>
            </div>
          </fieldset>
        <?php
        break;
      }
    }
  }

  function quick_edit_save( $post_id )
  {
	  // check inlint edit nonce
    if ( ! wp_verify_nonce( $_POST[ '_inline_edit' ], 'inlineeditnonce' ) ) 
    {
		  return;
	  }

	  // update checkbox
	  $active = ( isset( $_POST[ 'newsletterlist_sendto' ] ) && 'on' == $_POST[ 'newsletterlist_sendto' ] ) ? 'on' : 'off';
	  update_post_meta( $post_id, 'newsletterlist_sendto', $active );

  }

  function add_js($data) 
  {
    $data = "<script>
jQuery( function( $ ){

	const wp_inline_edit_function = inlineEditPost.edit;

	// we overwrite the it with our own
	inlineEditPost.edit = function( post_id ) {

		wp_inline_edit_function.apply( this, arguments );

		// get the post ID from the argument
		if ( typeof( post_id ) == 'object' ) { // if it is object, get the ID number
			post_id = parseInt( this.getId( post_id ) );
		}

		// add rows to variables
		const edit_row = $( '#edit-' + post_id )
		const post_row = $( '#post-' + post_id )

		const newsletterlist_sendto = 'on' == $( '.column-newsletterlist_sendto', post_row ).text() ? true : false;
    console.log('RESULT' + newsletterlist_sendto);

		// populate the inputs with column data
		$( ':input[name=\"newsletterlist_sendto\"]', edit_row ).prop( 'checked', newsletterlist_sendto);
		
	}
});

</script>";
  	return $data;
  }

}
