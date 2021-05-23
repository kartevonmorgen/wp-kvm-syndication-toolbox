<?php

class RegisterOrganisationTemplates implements WPModuleStarterIF
{
  public function setup($loader)
  {
    add_filter('the_content', array($this, 'single_content'));
  }

  public function start()
  {
    add_filter( 'single_template', 
                array($this, 
                      'get_organisation_template'), 10, 2);
    add_shortcode('organisations', array( $this, 'organisations_shortcode'));
  }

  public function organisations_shortcode($atts)
  {
    $mc = WPModuleConfiguration::get_instance();
    $root = $mc->get_root_module();

    $template = locate_template(array('plugins/' . 
                                     $root->get_id() . 
                                     '/templates/organisations-template.php'));
    if( !$template )
    {
      $template = dirname( __FILE__, 4 ) . 
        '/templates/organisations-template.php';
    }
    include($template);
  }

  public function single_content( $content )
  {
    $post = get_post();
    if( $post->post_type != 'organisation')
    {
      return $content;
    }

    $mc = WPModuleConfiguration::get_instance();
    $root = $mc->get_root_module();

    ob_start();
    $template = locate_template(array('plugins/' . 
                                     $root->get_id() . 
                                     '/templates/organisation-template.php'));
    if( !$template )
    {
      $template = dirname( __FILE__, 4 ) . 
        '/templates/organisation-template.php';
    }
    include($template);

    return ob_get_clean();
  }
 
  public function get_organisation_template( $template, $type ) 
  {
     global $post;

     if( !locate_template('single-organisation.php') && 
         $post->post_type == 'organisation' )
     {
 
       $post_templates = array('page.php','index.php');
       if( !empty($post_templates) )
       {
         $post_template = locate_template($post_templates,false);
         if( !empty($post_template) ) 
         {
           $template = $post_template;
         }
       }
     }
     return $template;
  }
}
