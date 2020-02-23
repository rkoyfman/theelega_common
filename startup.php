<?php
/*
    Once all plugins have loaded, send an event to our plugins, giving them an opportunity
    to check if their dependencies have been loaded.
*/
add_action('wp_loaded', function()
{
    /*
        The callback function sent by this event accepts an array of strings, then checks if the
        corresponding plugins have been loaded.
    */
    do_action('theelega_startup', function($dependencies)
    {        
        $deps_available = [
            'acf' => class_exists('ACF'),
            'woocommerce' => class_exists('WooCommerce'),
            'wpallimport' => class_exists('PMXI_Plugin'),
            'wpallimport-woocommerce' => class_exists('PMWI_Plugin'),
        ];

        $dependencies = is_array($dependencies) ? $dependencies : [$dependencies];
        foreach($dependencies as $d)
        {
            if (!$deps_available[$d])
            {
                return false;
            }
        }

        return true;
    });
}, 1000);
?>