<?php
/**
  * Controller ESSFeedHandler Output
  * The FeedHandler generates an ESS Feed and make it 
  * available, so other Websites can import this feed.
  *
  * @author  	Brice Pissard, Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  * @link		https://github.com/essfeed
  */
class ESSFeedHandler
{
	const EM_ESS_ARGUMENT 	= 'em_ess';

	public function __construct() 
  {
  }

	public function setup($loader) 
  {
    // Init was to early, so we do it after all plugins are loaded
    $loader->add_filter( 'wp_loaded', 
                         $this, 
                         'load' );
    $loader->add_filter( 'rewrite_rules_array', 
                         $this, 
                         'get_rewrite_rules_array');
    $loader->add_filter( 'query_vars', 
                         $this, 
                         'get_query_vars');
  }

	public function set_activation()
	{
	}

	public function set_deactivation()
	{
  }


	function load()
	{
    // Handle requests
		if ( preg_match( '/^\/?em_ess\/?$/', $_SERVER['REQUEST_URI']) || !empty( $_REQUEST[ ESSFeedHandler::EM_ESS_ARGUMENT ] ) )
    {
      
      $cat = ( isset( $_REQUEST[ 'cat'] ) )? $_REQUEST[ 'cat'] : ''; 
      $feedBuilder = new ESSFeedBuilder();
      $feedBuilder->output($cat);
      //( ( isset( $_REQUEST[ 'event_id'] ) )? $_REQUEST[ 'event_id'] : '' ),
      //( ( isset( $_REQUEST[ 'page'] ) )? $_REQUEST[ 'page'] : '' ),
      //( ( isset( $_REQUEST[ 'download']   )? ( ( intval( $_REQUEST[ 'download' ] ) >= 1 )? TRUE : FALSE ) : FALSE ) ),
      //( ( isset( $_REQUEST[ 'push'])? ( ( intval( $_REQUEST[ 'push'] ) >= 1 )? TRUE : FALSE ) : FALSE ))
      //);
			die;
		}
	}

	public function get_rewrite_rules_array( $rules )
	{
		return $rules + array( "/ess/?$"=>'index.php?'. ESSFeedHandler::EM_ESS_ARGUMENT . '=1' );
	}

	public function get_query_vars( $vars )
	{
		array_push( $vars, ESSFeedHandler::EM_ESS_ARGUMENT );
		return $vars;
	}
}
