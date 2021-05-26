<?php

abstract class WPTemplateHelper
{
  private $post_type = 'post';
  private $posts = array();
  private $pointer = -1;
  private $page = 0;
  private $parent_elements = array();
  private $last_page = true;

  public function __construct($post_type)
  {
    $this->post_type = $post_type;
  }

  /**
   * Loads a List of posts from the Database
   */
  public function load($page = 0, $posts_per_page = 2)
  {
    $posts_offset = $page * $posts_per_page;

    $args = array(
      'post_type' => $this->post_type,
      'posts_per_page'  => $posts_per_page,
      'offset'          => $posts_offset);

    $this->posts = get_posts($args);
    $this->pointer = -1;
    $this->page = $page;
    
    $this->last_page = true;
    if(count($this->posts) == $posts_per_page)
    {
      $this->last_page = false;
    }
  }

  /**
   * Loads a single post from the
   * database.
   */
  public function load_single($post)
  {
    $this->posts = array( get_post($post));
    $this->pointer = 0;
  }

  /**
   * Show the title of a post
   */
  public function the_title($element = 'h3', 
                            $clazz = null, 
                            $style = null, 
                            $stylea = null)
  {
    $post = $this->current();
    $stylea_css = '';
    if(!empty($stylea))
    {
      $stylea_css = ' style="' . $stylea . '"';
    }
    $value = '<a href="'. 
             get_the_permalink($post) . 
             '"' . 
             $stylea_css . 
             '>';
    $value .= get_the_title($post);
    $value .= '</a>';
    $this->the_element( $value, $element, $clazz, $style );
  }

  /**
   * Show a subtitle into the post
   */
  public function the_subtitle($subtitle, 
                               $element = 'h4', 
                               $clazz = null, 
                               $style = null)
  {
    $this->the_element( $subtitle, $element, $clazz, $style );
  }

  /**
   * Show the Excerpt of a post
   */
  public function the_excerpt($element = 'p', 
                              $clazz = null, 
                              $style = null)
  {
    $post = $this->current();
    $this->the_element( get_the_excerpt($post), 
                        $element, 
                        $clazz, 
                        $style );
  }

  /**
   * Show the content of a post
   */
  public function the_content($element = 'div', 
                              $clazz = null, 
                              $style = null)
  {
    $org = $this->current();
    $this->the_element( get_the_content(null, false, $org), 
                        $element, 
                        $clazz, 
                        $style );
  }

  /**
   * Show the link to the single post
   */
  public function the_permalink($text = '<i>Mehr lesen</i>', 
                                $element = 'p', 
                                $clazz = null, 
                                $style = null,
                                $stylea = null)
  {
    $post = $this->current();
    $stylea_css = '';
    if(!empty($stylea))
    {
      $stylea_css = ' style="' . $stylea . '"';
    }
    $this->the_element( '<a href="' . 
                          get_the_permalink($post) . 
                          '"' .
                          $stylea_css . '>' . $text. '</a>', 
                        $element, 
                        $clazz, 
                        $style );
  }

  public function has_categories()
  {
    $org = $this->current();
    if(get_the_category_list(', ', '', $org->ID) == false)
    {
      return false;
    }
    return true;
  }


  /**
   * Shows the Categories of the post
   */
  public function the_categories($element = 'p', 
                                 $clazz = null, 
                                 $style = null)
  {
    $org = $this->current();
    $this->the_element( get_the_category_list(', ',
                          '', 
                          $org->ID), 
                        $element, 
                        $clazz, 
                        $style );
  }

  public function has_tags()
  {
    $org = $this->current();
    if(get_the_tag_list('', ', ', '', $org->ID) == false)
    {
      return false;
    }
    return true;
  }

  /**
   * Shows the Tags of the post
   */
  public function the_tags($element = 'p', 
                           $clazz = null, 
                           $style = null)
  {
    $org = $this->current();
    $this->the_element( '&nbsp;' . get_the_tag_list('', 
                          ', ',
                          '', 
                          $org->ID), 
                        $element, 
                        $clazz, 
                        $style );
  }


  /**
   * Add Previous und Next buttons under on the
   * list view
   */
  public function the_pagination($element = 'div', 
                                 $clazz = null)
  {
    $this->the_linebreak(null, 'div', null, 'clear:both');
    if($this->page > 0)
    {
      $this->the_element('<a href="' . 
                           get_permalink() . 
                           '?pno=' . 
                           ($this->page - 1) . 
                           '">Vorherige</a>', 
                         $element, 
                         $clazz, 
                         'float:left');
    }
    if(!$this->last_page)
    {
      $this->the_element('<a href="' . 
                           get_permalink() . 
                           '?pno=' . 
                         ($this->page + 1) . 
                         '">Nächste</a>', 
                       $element, 
                       $clazz, 
                       'float:right');
    }
    $this->the_linebreak(null, 'div', null, 'clear:both');
  }

  /**
   * Add an empty line in html 
   */
  public function the_linebreak($height = null,
                                $element = 'div', 
                                $clazz = null, 
                                $style = null)
  {
    if($style == null && $height != null)
    {
      $style = 'height:' . $height;
    }
    $this->the_element( '&nbsp;', $element, $clazz, $style );
  }

  /**
   * Add in HTML Element with content ($value)
   */
  public function the_element($value, 
                              $element = 'p', 
                              $clazz = null,
                              $style = null)
  {
    $this->the_begin($element, $clazz, $style);
    echo '' . $value;
    $this->the_end();
  }

  /**
   * Add the opening html element with 
   * a clazz and/or style
   */ 
  public function the_begin($element = 'p', 
                            $clazz = null, 
                            $style = null)
  {
    $clazz_css = '';
    $style_css = '';
    array_push($this->parent_elements, $element);

    if(!empty($clazz))
    {
      $clazz_css = ' class="' . $clazz . '"';
    }

    if(!empty($style))
    {
      $style_css = ' style="' . $style . '"';
    }

    if(count($this->parent_elements) > 1)
    {
      echo '  ';
    }
    
    echo "<" . $element . $clazz_css . $style_css . ">";
    if($element == 'p')
    {
      echo "\n";
    }
  }

  /**
   * Add the closing HTML Element of the last 
   * opened HTML Element.
   */
  public function the_end()
  {
    $element = array_pop($this->parent_elements);
    echo '</' . $element . '>';
    echo "\n";
  }

  /**
   * Select the next post in the list
   * and return it
   */
  public function next()
  {
    $this->pointer = $this->pointer + 1;
    return $this->current();
  }
  
  /**
   * Check if there is a following post
   * on the list
   */
  public function has_next()
  {
    return ($this->pointer + 1 < count($this->posts));
  }

  /**
   * Return the current post on the list
   */
  public function current()
  {
    return $this->posts[$this->pointer];
  }
 
  /**
   * Check if the current element is there
   */
  public function has_current()
  {
    return ($this->pointer < count($this->posts));
  }

}
