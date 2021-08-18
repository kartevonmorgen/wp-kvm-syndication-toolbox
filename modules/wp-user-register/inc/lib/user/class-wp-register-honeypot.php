<?php
/**
 * Sets up and initializes the Registration Honeypot 
 * for Wordpress registrations.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
final class WPRegisterHoneypot 
{
  public function __construct() 
  {
  }

	/**
	 * Sets up needed actions for the Honeypot
	 */
  public function setup($loader)
  {
    $loader->add_action( 'login_head',          
                         $this, 
                         'print_styles' );
    $loader->add_action( 'register_form',
                         $this, 
                         'register_form', 
                         99 );
    $loader->add_action( 'register_post',       
                         $this, 
                         'check_honeypot', 
                         0 );
    $loader->add_action( 'login_form_register', 
                         $this, 
                         'check_honeypot', 
                         0 );
	}

	/**
	 * Checks if a spambot stuck his hand in the honeypot.  
   * If so, we'll cut off the user registration 
	 * process so that the spam user account 
   * never gets registered.
	 */
	public function check_honeypot() {

		if ( isset( $_POST['th_rh_name'] ) && 
         !empty( $_POST['th_rh_name'] ) )
    {
			wp_die( 'Du hast ein feld ausgefullt das Spammers stoppen soll, versuche es erneut, oder kontaktieren Sie der Administrator.' );
    }
	}

  /**
   * Outputs custom CSS to the login head to hide the 
   * honeypot field on the user registration form.
   */
  public function print_styles() 
  { ?>
		<style type="text/css">.th_rh_name_field { display: none; }</style>
  <?php }

  /**
   * Outputs custom jQuery to make sure the honeypot 
   * field is empty by default.
   *
   */
  public function print_scripts() 
  { ?>
    <script type="text/javascript">jQuery( '#th_rh_name' ).val( '' );</script>
  <?php }

  /**
   * Adds a hidden field that spambots will 
   * fill out but normal humans won't see.  
   * In the off-chance 
   * that a real human has CSS disabled 
   * on their browser, the label should let 
   * them know not to fill 
   * out this form field.  
   * This field will be checked to see 
   * if the visitor/spambot entered text into 
	 * it. This will let us know that they're a spambot.
	 */
  public function register_form() 
  {

    /* Load scripts for register form. */
    wp_enqueue_script( 'jquery' );
		add_action( 'login_footer', 
                array( $this, 
                       'print_scripts' ), 
                25 ); ?>

		<p class="th_rh_name_field">
			<label for="th_rh_name"><?php _e( 'Only fill in if you are not human', 'registration-honeypot' ); ?></label><br />
			<input type="text" name="th_rh_name" id="th_rh_name" class="input" value="" size="25" autocomplete="off" /></label>
		</p>
  <?php }
}
