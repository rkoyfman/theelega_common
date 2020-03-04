<?php
/**
 * Plugin Name: TheElegantYou Common Functions
 * Description: Reusable functions for other plugins. Other plugins depend on it.
 * Version:     1.0.1
 * Author:      Roman Koyfman
 */

require_once 'functions.php';
require_once 'arrays.php';
require_once 'datasources.php';
require_once 'db.php';
require_once 'request.php';
require_once 'strings.php';
require_once 'XMLElement.php';
require_once 'XMLSpreadsheet2003Parser.php';

require_once 'startup.php';

add_action('wp_footer', function()
{
    echo '<script>';
    require_once "post.js";
    echo '</script>';
}, 1000);

add_action('admin_footer', function()
{
    echo '<script>';
    require_once "post.js";
    echo '</script>';
}, 1000);

function theelega_load_postjs()
{
    //Obsolete;
}
?>