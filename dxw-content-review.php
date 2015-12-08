<?php
/*
Plugin Name: dxw Content Review
Plugin URI: http://dxw.com
Description: Allows users to set a review date either 3, 6, or 12 months in the future and an email to be alerted to review the content. Content can also be set to draft or sent to trash when the review date has passed.
Version: 0.0.1
Author: dxw
Author URI: http://dxw.com
Text Domain: dxwreview
*/

// If file is called directly, abort
if (!defined('WPINC')) {
    die;
}

if (!class_exists('Dxw_Content_Review')) :

    // Load the plugin class file
    require_once plugin_dir_path(__FILE__).'class-content-review.php';

    // Register activation and deactivation hooks
    register_activation_hook(__FILE__, array('Dxw_Content_Review', 'plugin_activation'));
    register_deactivation_hook(__FILE__, array('Dxw_Content_Review', 'plugin_deactivation'));

    // Load the plugin (not sure if this is really needed unless we're running the functionality)
    add_action('plugins_loaded', 'dxw_content_review_init');

    function dxw_content_review_init()
    {
        $content_review = Dxw_Content_Review::get_instance();

        $content_review->initialise();
    }

endif;
