<?php

/**
 * WidgetOrganisationSearch
 *
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.htm
 */
class WidgetOrganisationSearch extends WP_Widget 
{
 /**
	 * Register widget with WordPress.
	 */
	public function __construct() 
  {
		parent::__construct(
			'widget_organisation_search', // Base ID
			esc_html__( 'Suche', 'organisation' ), // Name
			array( 'description' => esc_html__( 'Organisation Suche', 
                                          'organisation' ), ) // Args
		);
	}

  /**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) 
  {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
    ?>
<div style="float:left;max-width:368px;border:1px #0F618E solid;border-radius:10px;mar    gin-top:20px;margin-right:20px;padding:20px">
  <form style="width:260px" action="<?php echo $current_url; ?>" method="post">
    <div style="float:left;width:240px">
      <div style="margin-top:5px;float:left;width:120px">
        <label for="search_organisation">Organisation</label>
      </div>
      <div style="margin-top:5px;float:left; width:120px">
        <input type="checkbox" 
               name="search_organisation" 
               id="search_organisation" 
               value="1" 
               <?php checked($_POST['search_organisation'],
                             true); ?>/>
      </div>
    </div>
    <div style="float:left;width:240px">
      <div style="margin-top:5px;float:left;width:120px">
        <label for="search_company">Unternehmen</label>
      </div>
      <div style="margin-top:5px;float:left;width:120px">
        <input type="checkbox" 
               name="search_company" 
               id="search_company" 
               value="1" 
               <?php checked($_POST['search_company'],
                             true); ?>/>
      </div>
    </div>
    <div style="float:left;width:240px">
      <div style="margin-top:5px;float:left;width:120px">
        <label for="search_term">Suche nach:</label>
      </div>
      <div style="margin-top:5px;float:left;width:120px">
        <input type="text" 
               class="regular-text" 
               name="search_term" 
               id="search_term" 
               size="15"
               value="<?php echo $_POST['search_term']; ?>"/>
      </div>
    </div>
    <div style="clear:both"></div>
    <div style="margin-top:15px;float:left">
      <input type="Submit" value="Suche" />
    </div>
    <div style="clear:both"></div>
    <?php
		echo $args['after_widget'];
	}

  /**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) 
  {
    $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'organisation' );
    ?>
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'organisation' ); ?></label> 
      <input class="widefat" 
             id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
             name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
             type="text" value="<?php echo esc_attr( $title ); ?>">
    </p>
		<?php 
	}

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  public function update( $new_instance, $old_instance ) 
  {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

    return $instance;
  }
}

