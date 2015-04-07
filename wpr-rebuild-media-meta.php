<?php
/*
Plugin Name: WPR Rebuild Meta Data
Plugin URI: http://wpranger.co.uk/plugins/wpr-rebuild-meta-data/
Description: Rebuilds the meta data for WordPress Media Library images
Version: 0.2.1
Author: Dave Naylor
Author URI: http://wpranger.co.uk
License: GPL2
GitHub Plugin URI: https://github.com/WPRanger/wp-upload-permissions
GitHub Branch: master
*/

/* Built from an original function by Ross McKay: http://snippets.webaware.com.au/snippets/repair-wordpress-image-meta/ */

add_action('admin_init', 'rebuild_meta_init' );
add_action('admin_menu', 'rebuild_meta_admin_menu');

function rebuild_meta_init(){
    register_setting( 'rebuild_meta', 'rebuild_meta', 'intval' );
}

function rebuild_meta_admin_menu() {
add_management_page( 'Rebuild Meta', 'Rebuild Meta', 'manage_options', 'rebuild-meta', 'rebuild_meta_options_page' ); 
}

function rebuild_meta_scripts($hook) {
    if($hook != 'tools_page_rebuild-meta')  {
        return;
    }
    wp_register_style( 'rebuild_meta', plugins_url( 'css/style.css', __FILE__));
    wp_enqueue_style( 'rebuild_meta' );
}
add_action( 'admin_enqueue_scripts', 'rebuild_meta_scripts' );

function rebuild_meta_options_page() {
    global $wpdb;
 
    $sql = "
        select ID from {$wpdb->posts}
        where post_type = 'attachment'
        and post_mime_type like 'image/%'
    ";
    $images = $wpdb->get_col($sql);
    echo "<table class='wpr-table'>";
    echo "<tr><th colspan='2'><h2>Rebuild Image Meta Data</h2></th></tr>";
    echo "<tr>";
    foreach ($images as $id) {
        $meta = wp_get_attachment_metadata($id);
        if ($meta) {
            $file = get_attached_file($id);
            if( !empty($file) && file_exists( $file) ){
                echo "<td><strong>Rebuilding:</strong></td><td>$file</td>";
                $info = getimagesize($file);
                $meta = array (
                    'width' => $info[0],
                    'height' => $info[1],
                    'hwstring_small' => "height='{$info[1]}' width='{$info[0]}'",
                    'file' => basename($file),
                    'sizes' => array(),         // thumbnails etc.
                    'image_meta' => array(),    // EXIF data
                );
                update_post_meta($id, '_wp_attachment_metadata', $meta);
            }else{
              echo "<td><strong>File does not exist.</strong></td><td>$file</td>";
            }
        }
    echo "</tr>";
    }
    echo "<tr><td colspan='2'>&nbsp;</td</tr>";
    echo "<tr><td colspan='2'>";
    echo "<span class='wpr-notice'><em>Now regenerate your thumbnails using a plugin such as:</em>
         <a href='http://wordpress.org/plugins/force-regenerate-thumbnails/'>Force Regenerate Thumbnails</a></td>";
    echo "</table>";
}

