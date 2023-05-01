<?php

class RegisterProjectTemplates 
  extends WPAbstractModuleProvider
  implements WPModuleStarterIF
{
  public function setup($loader)
  {
    add_filter('the_content', array($this, 'single_content'));
  }

  public function start()
  {
    $module = $this->get_current_module();
    if($module->is_extend_the_content_for_single_project())
    {
      add_filter( 'single_template', 
                  array($this, 
                        'get_project_template'), 10, 2);
    }
    add_shortcode('projects', array( $this, 'projects_shortcode'));
  }

  public function projects_shortcode($atts)
  {
    $root = $this->get_root_module();

    $template = locate_template(array('plugins/' . 
                                      $root->get_id() . 
                                      '/templates/projects-template.php'));
    if( !$template )
    {
        $template = dirname( __FILE__, 4 ) . 
          '/templates/projects-template.php';
    }

    if( !$template )
    {
      return;
    }
    
    include($template);
  }

  public function single_content( $content )
  {
    $post = get_post();
    if( $post == null || !is_object($post))
    {
      return $content;
    }
    if( $post->post_type != 'project')
    {
      return $content;
    }

    $root = $this->get_root_module();
    
    $template = locate_template(array('plugins/' . 
                                     $root->get_id() . 
                                     '/templates/project-template.php'));
    if( !$template )
    {
      $module = $this->get_current_module();
      if($module->is_extend_the_content_for_single_project())
      {
        $template = dirname( __FILE__, 4 ) . 
          '/templates/project-template.php';
      }
    }

    if( !$template )
    {
      return $content;
    }

    ob_start();
    include($template);
    return ob_get_clean();
  }
 
  public function get_project_template( $template, $type ) 
  {
     global $post;

     if( !locate_template('single-project.php') && 
         $post->post_type == 'project' )
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
