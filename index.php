<?php
/**
 * Plugin Name: TheElegantYou Common Functions
 * Description: Reusable functions for other plugins. Other plugins depend on it.
 * Version:     1.0.1
 * Author:      Roman Koyfman
 */

require_once 'functions.php';

function theelega_load_postjs()
{    
    add_action('admin_print_footer_scripts', function()
    {
        echo '<script>';
        require_once "post.js";
        echo '</script>';
    });
}
?>