<?php

add_filter( 'upload_size_limit', 
            'wplib_filter_site_upload_size_limit', 20 );

/**
 * Filter the upload size limit for non-administrators.
 *
 * @param string $size Upload size limit (in bytes).
 * @return int (maybe) Filtered size limit.
 */
function wplib_filter_site_upload_size_limit( $size ) 
{
  // Set the upload size limit to 10 MB for users lacking the 'manage_options' capability.
  if ( current_user_can( 'manage_options' ) ) 
  {
    return $size;
  }

  $enabled = get_option('wplib_max_media_uploadenabled');
  if(empty($enabled))
  {
    return $size;
  }

  if(!$enabled)
  {
    return $size;
  }

  $limit = get_option('wplib_max_media_uploadsize');
  if(empty($limit))
  {
    $limit = 500;
  }
 
  return 1024 * $limit;
}



//add_filter('wp_handle_upload_prefilter', 
//           'max_media_upload_error_message');

function max_media_upload_error_message($file) 
{
  $enabled = get_option('wplib_max_media_uploadenabled');
  if(empty($enabled))
  {
    return $file;
  }
  $limit = get_option('wplib_max_media_uploadsize');
  if(empty($limit))
  {
    $limit = 500;
  }
  $limit_output = $limit . ' kB';

  $size = $file['size'];
  $size = $size / 1024;

  if ( ( $size > $limit ) ) 
  {
    $file['error'] = 'Bilder, Video, '.
                     'Audio sollten kleiner sein dann ' . 
                     $limit_output . ' ( ' . round($size) . 
                     ' > ' . $limit . ' )';
  }
  return $file;
}

//add_action('admin_head', 'upload_load_styles');

function upload_load_styles() 
{
  $enabled = get_option('wplib_max_media_uploadenabled');
  if(empty($enabled))
  {
    return;
  }
  $limit = get_option('wplib_max_media_uploadsize');
  if(empty($limit))
  {
    $limit = 500;
  }
  $limit_output = $limit . ' kB';
  ?>
  <!-- .Custom Max Upload Size -->
	<style type="text/css">
		.after-file-upload {
			display: none;
		}
		.upload-flash-bypass:after {
			content: 'Maximale große für Bilder, Video und Audio: <?php echo $limit_output ?>.';
			display: block;
			margin: 15px 0;
		}

  </style>
  <!-- END Custom Max Upload Size -->
  <?php
}
