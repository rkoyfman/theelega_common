<?php
/**
 * Plugin Name: TheElegantYou Common Functions
 * Description: Reusable functions for other plugins. Other plugins depend on it.
 * Version:     2.0
 * Author:      Roman Koyfman
 */

$files = glob(__DIR__ . '/*.php');
foreach ($files as $f)
{
    require_once $f;
}

global $THEELEGA_UnitTestingOn;
if ($THEELEGA_UnitTestingOn)
{
    $files = glob(__DIR__ . '/UnitTestingFramework/*.php');
    foreach ($files as $f)
    {
        require_once $f;
    }
    
    require_once __DIR__ . '/tests/MiniSuite.php';
    require_once __DIR__ . '/tests/FullTestSuite.php';
}

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
?>